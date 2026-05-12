<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Importation des données TOT (Pluviométrie) - Format Spécifique à la NC, 
Ce script il est appelé après avoir sélectionné et vérifier les fichiers à importer (load_file.php) 
Script coté serveur appelé par procédure AJAX depuis import.php
----------------------------------------
*/

// ----------------------------------------------
// nécessaire pour la configuration du script

require('../../config.php');
require('../../database_tables.php');

require('../../function/date.php');	
require('../../function/database.php');	
require('../../function/html_output.php');
require('../../function/general.php');
require('../../function/sql_function.php');

// pour solutionner les pb d'accents
header('Content-Type: text/html; charset=utf-8');

// connexion à la base de données	
$sql_link = mysqli_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE) or die('Impossible de se connecter à la base de données!');
mysqli_query ($sql_link,'SET NAMES UTF8');


// Récupération des données JSON envoyées depuis la requête AJAX
$jsonIdImport = file_get_contents('php://input');

// Décodage des données JSON 
$idImport = json_decode($jsonIdImport, true);

// Gestion du temps en fonction du fuseau horaire du territoire
$timezone_php = 'Pacific/Noumea'; // à modifier en fonction du territoire
date_default_timezone_set($timezone_php); 
$today = date('Y-m-d H:i:s');; // Crée un objet DateTime pour la date actuelle

// ----------------------------------------------
// Récupération de données dans la base

// TABLE IMPORT FILES (Caractéristiques des fichiers importables)
$sql_import_files = "SELECT DISTINCT id, name_ext, multi_feuil, separateur, description, algo 
                    FROM ".TABLE_IMPORT_FILES." 
                    ORDER BY id ASC";
$import_files_query = tep_db_query($sql_link,$sql_import_files);									
while ($import_files_tab = tep_db_fetch_array($import_files_query))
{
    $name_ext = mb_convert_encoding($import_files_tab['name_ext'], 'ISO-8859-1', 'UTF-8');

	$import_files[$name_ext] = array('id' => $import_files_tab['id'],
                                    'multi_feuil' => $import_files_tab['multi_feuil'],
                                    'separateur' => $import_files_tab['separateur'],                                                    
                                    'description' => mb_convert_encoding($import_files_tab['description'], 'ISO-8859-1', 'UTF-8'),
                                    'algo' => $import_files_tab['algo'] // ce champs peu contenir l'algo de lecture du type de fichier !!! Attention potentiellement dangereux pour la sécurité
                                    );
}

// TABLE STATION
$sql_station_all = "SELECT DISTINCT id_station, nom_station, code_station, station_type, active_station
					FROM ".TABLE_STATION;
$station_all_query = tep_db_query($sql_link,$sql_station_all);
while ($station_all = tep_db_fetch_array($station_all_query))
{	
	$station_all_array[$station_all['id_station']] = array('code_station' => $station_all['code_station'],
															'nom_station' => $station_all['nom_station'],
															'station_type' => $station_all['station_type'],
															);
}

// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_color_background, type_graph 
                FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1 
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
while ($eq_type_tab = tep_db_fetch_array($eq_type_query))
{
	$eq_type_array[$eq_type_tab['id_eq_type']] = array('id_eq_type' => $eq_type_tab['id_eq_type'],
														'nom_eq_type' => htmlaccent(html_entity_decode($eq_type_tab['nom_eq_type'] ?? $default_string)),
                                                        'unite_eq_type' => $eq_type_tab['unite_eq_type'],
                                                        'valeur_data_type' => $eq_type_tab['valeur_data_type'],
														'type_color_border' => $eq_type_tab['type_color_border'],
                                                        'type_color_background' => $eq_type_tab['type_color_background'],
														'type_graph' => $eq_type_tab['type_graph']
                                                    );
}


// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, unite
				  FROM ".TABLE_TYPE_DATA." 
				  ORDER BY init_type_data ASC";
$type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
while ($type_chron_tab = tep_db_fetch_array($type_chron_query))
{
	$type_chron_array[$type_chron_tab['id_data_type']] = array('init_type_data' => $type_chron_tab['init_type_data'],
															'nom_type_data' => $type_chron_tab['nom_type_data'],
															'unite' => $type_chron_tab['unite'],															
															'id_eq_type_data' => $type_chron_tab['id_eq_type_data']
															);
}

// TABLE QUALITY_DATA 
$sql_quality_data = "SELECT DISTINCT id_data_qualite, init_qualite_data, nom_qualite_data, info_qualite_data
				  	FROM ".TABLE_DATA_QUALITE;
$quality_data_query = tep_db_query($sql_link,$sql_quality_data);									
while ($quality_data_tab = tep_db_fetch_array($quality_data_query))
{
	$quality_data_array[$quality_data_tab['init_qualite_data']] = array('id_data_qualite' => $quality_data_tab['id_data_qualite'],
																		'nom_qualite_data' => mb_convert_encoding($quality_data_tab['nom_qualite_data'] ?? '', 'ISO-8859-1', 'UTF-8'),												
																		'info_qualite_data' => mb_convert_encoding($quality_data_tab['info_qualite_data'] ?? '', 'ISO-8859-1', 'UTF-8')
																		);
}


// ----------------------------------------------
// Initialisation de variables
$folder =  '../../../data/uploads/'; // Chemin de destination

$import_warning_ligne = '';
$import_result_error = "";
$import_result = '';

$num_ligne = 0;
$nb_data_import = 0;

$date_debut = ''; // date de début de la chronique à chargé
$date_fin = ''; // date de fin de la chronique à chargé

$db_load = true;
$rows_deleted = 0;

$id_station = 0;
$id_chron = 0;
$id_ext_file = 0;

$data_tab = array();



$startTime = microtime(true); // Temps au début du script

    // Récupération des données du fichier à importer 
    $sql_import = "SELECT DISTINCT id_import, file_import, file_ext, dateheure, id_station, id_chron, id_user
                        FROM ".TABLE_IMPORT_SUIVI." 
                        WHERE id=".$idImport;
    $import_query = tep_db_query($sql_link,$sql_import);		
    $import_data = tep_db_fetch_array($import_query);

    $id_station = $import_data['id_station'];

    // Lecture et Vérification des données avant chargement dans la base 
    if($import_data['file_ext'] == 'csv')
    {
        $chemin_file = $folder.'files/'.$import_data['file_import'];

        // Ouvrir le fichier CSV en mode lecture
        if (($handle = fopen($chemin_file, 'r')) !== false) 
        {   
            // Lire la première ligne pour vérifier et enlever le BOM (si présent)
            $firstLine = fgets($handle); // Lire la première ligne

                // Vérifier si la première ligne contient le BOM UTF-8
                if (substr($firstLine, 0, 3) === "\xEF\xBB\xBF") 
                {
                    // Enlever le BOM
                    $firstLine = substr($firstLine, 3);
                }

            // Remettre le pointeur de fichier au début pour continuer à lire les données
            fseek($handle, 0);

            
            // Lire chaque ligne du fichier CSV
            while (($data = fgetcsv($handle, 10000, ';')) !== false) 
            {
                $import_valid = true;

                $num_ligne++;

                // Initialisation de toutes les variables
                // Nécessaire pour l'enregistrement en base
                $id_agent = NULL;
                $data_date = NULL;

                $appareil_k7 = NULL;
                $appareil_type = NULL;
                $appareil_num = NULL;
                $appareil_heure= NULL;
                $plu_taille_auget = NULL;
                $etat_ra= NULL;

                $duree_enreg_jj = NULL;
                $duree_enreg_hh = NULL;
                $duree_enreg_mm = NULL;
                $last_enreg_jj = NULL;
                $last_enreg_hh = NULL;
                $last_enreg_mm = NULL;

                $cote_limni_heure = NULL;
                $cote_limni_hsonde = NULL;
                $cote_limni_hech = NULL;
                $cote_limni_hech2 = NULL;
                $num_sonde = NULL;

                $plu_tot_type = NULL;
                $plu_tot_first = NULL;
                $plu_tot_last = NULL;
                $plu_tot_heure_basc = NULL;
                $plu_cumul_tot = NULL;
                $plu_cumul_plu = NULL;
                $plu_diff_tot_plu = NULL;
                $plu_recalage_heure_plu = NULL;
                $plu_test_auget = NULL;
                $plu_nb_basculement = NULL;

                $nb_octets = NULL;
                $batt_num = NULL;
                $batt_tension = NULL;
                $num_k7 = NULL;
                $heure_init_k7 = NULL;

                $h_sonde_k7 = NULL;
                $ctrl_diff_ech_sonde = NULL;
                $plu_heure_bascul1_cassette = NULL;
                $ctrl_recal_sonde = NULL;
                $ctrl_recal_heure_sonde = NULL;
                $purge_sonde = NULL;
                $jaugeage = NULL;

                $plu_ra_bouchage = NULL;
                $plu_ra_huile_tot = NULL;
                $debrous = NULL;
                $eau_batt = NULL;
                $transfert = NULL;

                $memoire_delete = NULL;
                $obs = NULL;
                $aprevoir = NULL;
                $nom_file = NULL;
                $obs_file = NULL;

                $pre_marquant = NULL;
                $fait_marquant = NULL;
                $agents = NULL;
                
                $piezo_toitnappesonde = NULL;
                $piezo_conductivite = NULL;
                $piezo_temperature = NULL;

                $piezo_recalage_diff = NULL;
                $piezo_recalage_sonde = NULL;
                $piezo_recalage_heure_sonde = NULL;

                $piezo_nature_repere = NULL;
                $piezo_instrument = NULL;
                $piezo_num_instrument = NULL;
                $piezo_prof_toitnappe = NULL;
                $piezo_prof_totale = NULL;

                $piezo_z_mNGNC =NULL;
                
                $coord_x = NULL;
                $coord_y = NULL;
                $piezo_gps_precision = NULL;
                $piezo_systeme_coord = NULL;

                $piezo_pompage_encours = NULL;
                $piezo_pompage_proche = NULL;
                $piezo_pluie_crue = NULL;
                $piezo_temps_sec = NULL;
                $piezo_photos= NULL;
                

                // On lit les champs du fichier
                if($num_ligne > 2) // Les 2 premières lignes ne sont pas lues (Titre et Intitulé des colonnes)
                {
                    if($data[0] == $station_all_array[$id_station]['code_station'])        
                    {
                        $dateString = $data[2];
                        $heureString = $data[3];

                        // Vérifie si $dateString contient déjà une heure
                        if (preg_match('/\d{2}:\d{2}(:\d{2})?/', $dateString)) 
                        {
                            // Il y a déjà l'heure dans $date[2]
                            $dateheureString = $dateString;
                        } else {
                            // Pas d'heure, on concatène
                            $dateheureString = $dateString.' '.$heureString;
                        }

                        $data_date = isValidDateImport($dateString);
                        if ($data_date == 'Invalid') {
                            $data_date = '';
                            $import_valid = false;
                        }

                        // Station Pluviométrique
                        if($station_all_array[$id_station]['station_type'] == 1) 
                        { 
                            if (mb_detect_encoding($data[4], 'UTF-8', true) !== 'UTF-8') {
                                $appareil_k7 = mb_convert_encoding($data[4], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $appareil_k7 = $data[4];
                            }
                            $appareil_k7 = htmlspecialchars($appareil_k7, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[5], 'UTF-8', true) !== 'UTF-8') {
                                $appareil_type = mb_convert_encoding($data[5], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $appareil_type = $data[5];
                            }
                            $appareil_type = htmlspecialchars($appareil_type, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[6], 'UTF-8', true) !== 'UTF-8') {
                                $appareil_num = mb_convert_encoding($data[6], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $appareil_num = $data[6];
                            }
                            $appareil_num = htmlspecialchars($appareil_num, ENT_QUOTES, 'UTF-8');
                        
                            $timeString = $data[7];
                            $appareil_heure = isValidTimeImport($timeString);
                            if ($appareil_heure == 'Invalid') {
                                $appareil_heure = '';
                            }

                            if (mb_detect_encoding($data[8], 'UTF-8', true) !== 'UTF-8') {
                                $plu_tot_type = mb_convert_encoding($data[8], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $plu_tot_type = $data[8];
                            }
                            $plu_tot_type = htmlspecialchars($plu_tot_type, ENT_QUOTES, 'UTF-8');
                        
                            if (is_numeric($data[9])) { $plu_tot_first = (int)$data[9];} 
                        
                            if (is_numeric($data[10])) {$plu_tot_last = (int)$data[10];}
                        
                            $timeString = $data[11];
                            $plu_tot_heure_basc = isValidTimeImport($timeString);
                            if ($plu_tot_heure_basc == 'Invalid') {
                                $plu_tot_heure_basc = '';
                            }

                            if (is_numeric($data[12])) {$duree_enreg_jj = (int)$data[12];}
                        
                            if (is_numeric($data[13])) {$duree_enreg_hh = (int)$data[13];} 
                        
                            if (is_numeric($data[14])) {$duree_enreg_mm = (int)$data[14];} 
                        
                            if (is_numeric($data[15])) {$last_enreg_jj = (int)$data[15];}
                                                
                            if (is_numeric($data[16])) {$last_enreg_hh = (int)$data[16];}
                        
                            if (is_numeric($data[17])) {$last_enreg_mm = (int)$data[17];} 

                            if (is_numeric($data[18])) {$plu_nb_basculement = (int)$data[18];} 
                            
                            if (mb_detect_encoding($data[19], 'UTF-8', true) !== 'UTF-8') {
                                $nb_octets = mb_convert_encoding($data[19], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $nb_octets = $data[19];
                            }
                            $nb_octets = htmlspecialchars($nb_octets, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[20], 'UTF-8', true) !== 'UTF-8') {
                                $batt_num = mb_convert_encoding($data[20], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $batt_num = $data[20];
                            }
                            $batt_num = htmlspecialchars($batt_num, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[21], 'UTF-8', true) !== 'UTF-8') {
                                $batt_tension = mb_convert_encoding($data[21], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $batt_tension = $data[21];
                            }
                            $batt_tension = htmlspecialchars($batt_tension, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[22], 'UTF-8', true) !== 'UTF-8') {
                                $num_k7 = mb_convert_encoding($data[22], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $num_k7 = $data[22];
                            }
                            $num_k7 = htmlspecialchars($num_k7, ENT_QUOTES, 'UTF-8');
                        
                            $timeString = $data[23];
                            $heure_init_k7 = isValidTimeImport($timeString);
                            if ($heure_init_k7 == 'Invalid') {
                                $heure_init_k7 = '';
                            }

                            $timeString = $data[24];
                            $plu_heure_bascul1_cassette = isValidTimeImport($timeString);
                            if ($plu_heure_bascul1_cassette == 'Invalid') {
                                $plu_heure_bascul1_cassette = '';
                            }

                            if (is_numeric($data[25])) {$plu_cumul_tot = (int)$data[25];}
                        
                            if (is_numeric($data[26])) {$plu_cumul_plu = (int)$data[26];}
                        
                            if (is_numeric($data[27])) {$plu_diff_tot_plu = (int)$data[27];}

                            $timeString = $data[28];
                            $plu_recalage_heure_plu = isValidTimeImport($timeString);
                            if ($plu_recalage_heure_plu == 'Invalid') {
                                $plu_recalage_heure_plu = '';
                            }

                            $plu_test_auget = '';
                            if (mb_detect_encoding($data[29], 'UTF-8', true) !== 'UTF-8') {
                                $plu_test_auget = mb_convert_encoding($data[29], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $plu_test_auget = $data[29];
                            }
                            $plu_test_auget = htmlspecialchars($plu_test_auget, ENT_QUOTES, 'UTF-8');
                        
                            if (tep_not_null($data[30])) {$plu_ra_bouchage = 1;}
                        
                            if (tep_not_null($data[31])) {$debrous = 1;}
                        
                            if (tep_not_null($data[32])) {$eau_batt = 1;}

                            if (tep_not_null($data[33])) { $plu_ra_huile_tot = 1;}
                        
                            if (tep_not_null($data[34])) {$transfert = 1;}
                        
                            if (tep_not_null($data[35])) {$memoire_delete = 1;}
                        
                            if (mb_detect_encoding($data[36], 'UTF-8', true) !== 'UTF-8') {
                                $obs = mb_convert_encoding($data[36], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $obs = $data[36];
                            }
                            $obs = htmlspecialchars($obs, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[37], 'UTF-8', true) !== 'UTF-8') {
                                $aprevoir = mb_convert_encoding($data[37], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $aprevoir = $data[37];
                            }
                            $aprevoir = htmlspecialchars($aprevoir, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[38], 'UTF-8', true) !== 'UTF-8') {
                                $agents = mb_convert_encoding($data[38], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $agents = $data[38];
                            }
                            $agents = htmlspecialchars($agents, ENT_QUOTES, 'UTF-8');
                        
                            if (is_numeric($data[39])) {$coord_x = (float)$data[39];}
                        
                            if (is_numeric($data[40])) {$coord_y = (float)$data[40];}

                            $plu_commentaire = ''; // colonne 41 - chps obsolète mais nc
                            $plu_nomO2 = ''; // colonne 42 - chps obsolète mais nc
                        
                            if (mb_detect_encoding($data[43], 'UTF-8', true) !== 'UTF-8') {
                                $nom_file = mb_convert_encoding($data[43], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $nom_file = $data[43];
                            }
                            $nom_file = htmlspecialchars($nom_file, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[44], 'UTF-8', true) !== 'UTF-8') {
                                $obs_file = mb_convert_encoding($data[44], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $obs_file = $data[44];
                            }
                            $obs_file = htmlspecialchars($obs_file, ENT_QUOTES, 'UTF-8');
                        
                            
                            if (isset($data[45]) && tep_not_null($data[45])) {$pre_marquant = 1;}
                        
                            if (isset($data[46]) && tep_not_null($data[46])) {$fait_marquant = 1;}   
                        }
                        
                        // ------------------------------------------------------------

                        // Station Piézométrique
                        if($station_all_array[$id_station]['station_type'] == 5) 
                        { 
                            // Sonde Fixe - Type
                            if (mb_detect_encoding($data[4], 'UTF-8', true) !== 'UTF-8') {
                                $appareil_type = mb_convert_encoding($data[4], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $appareil_type = $data[4];
                            }
                            $appareil_type = htmlspecialchars($appareil_type, ENT_QUOTES, 'UTF-8');
                        
                            // Sonde Fixe - Numéro
                            if (mb_detect_encoding($data[5], 'UTF-8', true) !== 'UTF-8') {
                                $appareil_num = mb_convert_encoding($data[5], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $appareil_num = $data[5];
                            }
                            $appareil_num = htmlspecialchars($appareil_num, ENT_QUOTES, 'UTF-8');
                        
                            // Sonde Fixe - Heure
                            $timeString = $data[6];
                            $appareil_heure = isValidTimeImport($timeString);
                            if ($appareil_heure == 'Invalid') {
                                $appareil_heure = '';
                            }

                            // Sonde Manuelle - Type - piezo_instrument
                            if (mb_detect_encoding($data[7], 'UTF-8', true) !== 'UTF-8') {
                                $piezo_instrument = mb_convert_encoding($data[7], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $piezo_instrument = $data[7];
                            }
                            $piezo_instrument = htmlspecialchars($piezo_instrument, ENT_QUOTES, 'UTF-8');

                            // Sonde Manuelle - Numero - piezo_num_instrument
                            if (mb_detect_encoding($data[8], 'UTF-8', true) !== 'UTF-8') {
                                $piezo_num_instrument = mb_convert_encoding($data[8], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $piezo_num_instrument = $data[8];
                            }
                            $piezo_num_instrument = htmlspecialchars($piezo_num_instrument, ENT_QUOTES, 'UTF-8');
                        
                            // piezo_toitnappesonde - Sonde Fixe
                            if (is_numeric($data[9])) { $piezo_toitnappesonde = (float)$data[9];} 
                        
                            // piezo_conductivite - Sonde Fixe
                            if (is_numeric($data[10])) {$piezo_conductivite = (float)$data[10];}

                            // piezo_temperature - Sonde Fixe
                            if (is_numeric($data[11])) {$piezo_temperature = (float)$data[11];}

                            // piezo_prof_toitnappe - Sonde Manuelle ($data[13] est la même données en cm)
                            if (is_numeric($data[12])) {$piezo_prof_toitnappe = (float)$data[12];}

                            // piezo_prof_totale - Sonde Manuelle
                            if (is_numeric($data[14])) {$piezo_prof_totale = (float)$data[14];}

                            // piezo_recalage_diff = Sonde Manuelle - Sonde Fixe
                            if (is_numeric($data[15])) {$piezo_recalage_diff = (float)$data[15];}

                            // piezo_recalage_sonde
                            if (mb_detect_encoding($data[16], 'UTF-8', true) !== 'UTF-8') {
                                $piezo_recalage_sonde = mb_convert_encoding($data[16], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $piezo_recalage_sonde = $data[16];
                            }
                            $piezo_recalage_sonde = htmlspecialchars($piezo_recalage_sonde, ENT_QUOTES, 'UTF-8');

                            // piezo_recalage_heure_sonde
                            $timeString = $data[17];
                            $piezo_recalage_heure_sonde = isValidTimeImport($timeString);
                            if ($piezo_recalage_heure_sonde == 'Invalid') {
                                $piezo_recalage_heure_sonde = '';
                            }

                            if (mb_detect_encoding($data[18], 'UTF-8', true) !== 'UTF-8') {
                                $nb_octets = mb_convert_encoding($data[18], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $nb_octets = $data[18];
                            }
                            $nb_octets = htmlspecialchars($nb_octets, ENT_QUOTES, 'UTF-8');


                            if (tep_not_null($data[19])) {$memoire_delete = 1;}
                        

                            if (mb_detect_encoding($data[20], 'UTF-8', true) !== 'UTF-8') {
                                $batt_tension = mb_convert_encoding($data[20], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $batt_tension = $data[20];
                            }
                            $batt_tension = htmlspecialchars($batt_tension, ENT_QUOTES, 'UTF-8');


                            if (mb_detect_encoding($data[21], 'UTF-8', true) !== 'UTF-8') {
                                $piezo_nature_repere = mb_convert_encoding($data[21], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $piezo_nature_repere = $data[21];
                            }
                            $piezo_nature_repere = htmlspecialchars($piezo_nature_repere, ENT_QUOTES, 'UTF-8');

                            // data[22] : Z(mNGNC) - c'est une donnée qui est dans la table repère
                            // Elle n'est pas destinée à être mise à jour ici 
                            // On l'enregistre dans une variable mais elle ne sera pas sauvegarder en base
                            if (is_numeric($data[22])) {$piezo_z_mNGNC = (float)$data[22];}

                            if (tep_not_null($data[23])) {$piezo_pompage_encours = 1;}
                        
                            if (tep_not_null($data[24])) {$piezo_pompage_proche = 1;}
                        
                            if (tep_not_null($data[25])) {$piezo_pluie_crue = 1;}

                            if (tep_not_null($data[26])) {$piezo_temps_sec = 1;}

                            
                            if (mb_detect_encoding($data[27], 'UTF-8', true) !== 'UTF-8') {
                                $obs = mb_convert_encoding($data[27], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $obs = $data[27];
                            }
                            $obs = htmlspecialchars($obs, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[28], 'UTF-8', true) !== 'UTF-8') {
                                $aprevoir = mb_convert_encoding($data[28], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $aprevoir = $data[28];
                            }
                            $aprevoir = htmlspecialchars($aprevoir, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[29], 'UTF-8', true) !== 'UTF-8') {
                                $agents = mb_convert_encoding($data[29], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $agents = $data[29];
                            }
                            $agents = htmlspecialchars($agents, ENT_QUOTES, 'UTF-8');
                        
                            if (is_numeric($data[30])) {$coord_x = (float)$data[30];}
                        
                            if (is_numeric($data[31])) {$coord_y = (float)$data[31];}
                        
                            if (mb_detect_encoding($data[32], 'UTF-8', true) !== 'UTF-8') {
                                $nom_file = mb_convert_encoding($data[32], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $nom_file = $data[32];
                            }
                            $nom_file = htmlspecialchars($nom_file, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[33], 'UTF-8', true) !== 'UTF-8') {
                                $obs_file = mb_convert_encoding($data[33], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $obs_file = $data[33];
                            }
                            $obs_file = htmlspecialchars($obs_file, ENT_QUOTES, 'UTF-8');
                        
                            
                            if (isset($data[34]) && tep_not_null($data[34])) {$pre_marquant = 1;}
                        
                            if (isset($data[35]) && tep_not_null($data[35])) {$fait_marquant = 1;}   
                        }

                        // ------------------------------------------------------------

                        // Station Hydrométrie

                        if($station_all_array[$id_station]['station_type'] == 11) 
                        { 
                            if (mb_detect_encoding($data[4], 'UTF-8', true) !== 'UTF-8') {
                                $appareil_k7 = mb_convert_encoding($data[4], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $appareil_k7 = $data[4];
                            }
                            $appareil_k7 = htmlspecialchars($appareil_k7, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[5], 'UTF-8', true) !== 'UTF-8') {
                                $appareil_type = mb_convert_encoding($data[5], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $appareil_type = $data[5];
                            }
                            $appareil_type = htmlspecialchars($appareil_type, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[6], 'UTF-8', true) !== 'UTF-8') {
                                $appareil_num = mb_convert_encoding($data[6], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $appareil_num = $data[6];
                            }
                            $appareil_num = htmlspecialchars($appareil_num, ENT_QUOTES, 'UTF-8');
                        
                            $dateString = $data[7];
                            $appareil_heure = isValidTimeImport($dateString);
                            if ($appareil_heure == 'Invalid') {
                                $appareil_heure = '';
                            }
                        
                            $dateString = $data[8];
                            $cote_limni_heure = isValidTimeImport($dateString);
                            if ($cote_limni_heure == 'Invalid') {
                                $cote_limni_heure = '';
                            }
                        
                            if (is_numeric($data[9])) {$cote_limni_hsonde = (float)$data[9];}
                        
                            if (is_numeric($data[10])) {$cote_limni_hech = (float)$data[10];}
                        
                            if (is_numeric($data[11])) {$cote_limni_hech2 = (float)$data[11];}

                            
                            if (is_numeric($data[12])) {$duree_enreg_jj = (int)$data[12];} 
                        
                            if (is_numeric($data[13])) {$duree_enreg_hh = (int)$data[13];} 
                        
                            if (is_numeric($data[14])) {$duree_enreg_mm = (int)$data[14];} 

                            if (is_numeric($data[15])) {$last_enreg_jj = (int)$data[15];}
                        
                            if (is_numeric($data[16])) {$last_enreg_hh = (int)$data[16];} 
                        
                            if (is_numeric($data[17])) {$last_enreg_mm = (int)$data[17];}
                        
                            if (mb_detect_encoding($data[18], 'UTF-8', true) !== 'UTF-8') {
                                $num_sonde = mb_convert_encoding($data[18], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $num_sonde = $data[18];
                            }
                            $num_sonde = htmlspecialchars($num_sonde, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[19], 'UTF-8', true) !== 'UTF-8') {
                                $nb_octets = mb_convert_encoding($data[19], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $nb_octets = $data[19];
                            }
                            $nb_octets = htmlspecialchars($nb_octets, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[20], 'UTF-8', true) !== 'UTF-8') {
                                $batt_num = mb_convert_encoding($data[20], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $batt_num = $data[20];
                            }
                            $batt_num = htmlspecialchars($batt_num, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[21], 'UTF-8', true) !== 'UTF-8') {
                                $batt_tension = mb_convert_encoding($data[21], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $batt_tension = $data[21];
                            }
                            $batt_tension = htmlspecialchars($batt_tension, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[22], 'UTF-8', true) !== 'UTF-8') {
                                $num_k7 = mb_convert_encoding($data[22], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $num_k7 = $data[22];
                            }
                            $num_k7 = htmlspecialchars($num_k7, ENT_QUOTES, 'UTF-8');
                        
                            $dateString = $data[23];
                            $heure_init_k7 = isValidTimeImport($dateString);
                            if ($heure_init_k7 == 'Invalid') {
                                $heure_init_k7 = '';
                            }
                        
                            if (is_numeric($data[24])) {$h_sonde_k7 = (float)$data[24];}
                            
                            if (is_numeric($data[25])) {$ctrl_diff_ech_sonde = (float)$data[25];}
                        
                            if (mb_detect_encoding($data[26], 'UTF-8', true) !== 'UTF-8') {
                                $ctrl_recal_sonde = mb_convert_encoding($data[26], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $ctrl_recal_sonde = $data[26];
                            }
                            $ctrl_recal_sonde = htmlspecialchars($ctrl_recal_sonde, ENT_QUOTES, 'UTF-8');
                        
                            $dateString = $data[27];
                            $ctrl_recal_heure_sonde = isValidTimeImport($dateString);
                            if ($ctrl_recal_heure_sonde == 'Invalid') {
                                $ctrl_recal_heure_sonde = '';
                            }
                        
                            if (tep_not_null($data[28])) {$purge_sonde = 1;}
                        
                            if (tep_not_null($data[29])) {$jaugeage = 1;}
                        
                            if (tep_not_null($data[30])) {$debrous = 1;}
                        
                            if (tep_not_null($data[31])) {$eau_batt = 1;}
                        
                            if (tep_not_null($data[32])) {$transfert = 1;}
                        
                            if (tep_not_null($data[33])) {$memoire_delete = 1;}
                        
                            if (mb_detect_encoding($data[34], 'UTF-8', true) !== 'UTF-8') {
                                $obs = mb_convert_encoding($data[34], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $obs = $data[34];
                            }
                            $obs = htmlspecialchars($obs, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[35], 'UTF-8', true) !== 'UTF-8') {
                                $aprevoir = mb_convert_encoding($data[35], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $aprevoir = $data[35];
                            }
                            $aprevoir = htmlspecialchars($aprevoir, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[36], 'UTF-8', true) !== 'UTF-8') {
                                $agents = mb_convert_encoding($data[36], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $agents = $data[36];
                            }
                            $agents = htmlspecialchars($agents, ENT_QUOTES, 'UTF-8');
                        
                            if (is_numeric($data[37])) {$coord_x = (float)$data[37];}
                        
                            if (is_numeric($data[38])) {$coord_y = (float)$data[38];}
                        
                            if (mb_detect_encoding($data[39], 'UTF-8', true) !== 'UTF-8') {
                                $nom_file = mb_convert_encoding($data[39], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $nom_file = $data[39];
                            }
                            $nom_file = htmlspecialchars($nom_file, ENT_QUOTES, 'UTF-8');
                        
                            if (mb_detect_encoding($data[40], 'UTF-8', true) !== 'UTF-8') {
                                $obs_file = mb_convert_encoding($data[40], 'UTF-8', 'ISO-8859-1');
                            } else {
                                $obs_file = $data[40];
                            }
                            $obs_file = htmlspecialchars($obs_file, ENT_QUOTES, 'UTF-8');
                        
                            if (isset($data[41]) && tep_not_null($data[41])) {$pre_marquant = 1;}
                        
                            if (isset($data[42]) && tep_not_null($data[42])) {$fait_marquant = 1;}  
                        }
                    }
                    else
                    {
                        $import_valid = false;
                    }

                    // Enregistrement de la ligne si elle est valide dans les tables avant import dans la base
                    if($import_valid)
                    {
                        // On efface d'abord les données entre Date_Debut et Date_Fin chronique, qu'elles existent ou pas
                        $sql_delete_data = "DELETE FROM ".TABLE_DATA_RA." WHERE id_station = ".$id_station.
                                        " AND date_heure_ra = '".$data_date."'";
                                        //AND DATE_FORMAT(dateheure, '%Y-%m-%d %H:%i') = DATE_FORMAT('$data_date', '%Y-%m-%d %H:%i');
                                        // Cette ligne effacera tous les RA dans la même minute

                        
                        $query_insert_data = "
                                            INSERT INTO " . TABLE_DATA_RA . "
                                            (`datetime_saisie`, `id_agent_user`, `id_station`, `date_heure_ra`,
                                            `id_eq_type`, `type_appareil`, `num_appareil`, `heure_appareil`,
                                            `plu_taille_auget`, `etat_ra`, `hydro_heure_cote`, `hydro_h_sonde`,
                                            `hydro_h_echelle_1`, `hydro_h_echelle_2`, `hydro_num_sonde`,
                                            `plu_tot_type`, `plu_tot_first`, `plu_tot_last`, `plu_tot_heure_basc`,
                                            `plu_cumul_tot`, `plu_cumul_plu`, `plu_diff_tot_plu`, `plu_recalage_heure_plu`,
                                            `plu_test_auget`, `plu_nb_basculement`, `nb_octet`, `num_batterie`,
                                            `tension_batterie`, `num_cassette`, `heure_init_cassette`, `hydro_h_sonde_cassette`,
                                            `plu_heure_bascul1_cassette`, `hydro_recalage_sonde`, `hydro_recalage_heure_sonde`,
                                            `hydro_purge_sonde`, `hydro_ra_jaugeage`, `plu_ra_bouchage`, `plu_ra_huile_tot`,
                                            `ra_debroussaillage`, `ra_eau_batterie`, `ra_transfert_data`, `ra_delete_memory`,
                                            `ra_obs`, `ra_futur`, `name_file_data`, `obs_file_data`, `pre_marquant`,
                                            `fait_marquant`, `agents_complement`, `piezo_toitnappesonde`, `piezo_conductivite`,
                                            `piezo_temperature`, `piezo_recalage_diff`, `piezo_recalage_sonde`,
                                            `piezo_recalage_heure_sonde`, `piezo_nature_repere`,
                                            `piezo_instrument`, `piezo_num_instrument`, `piezo_prof_toitnappe`,
                                            `piezo_prof_totale`, `piezo_x_terrain`, `piezo_y_terrain`, `piezo_gps_precision`,
                                            `piezo_systeme_coord`, `piezo_pompage_encours`, `piezo_pompage_proche`,
                                            `piezo_pluie_crue`, `piezo_temps_sec`, `piezo_photos`)
                                            VALUES
                                            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                                             ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                                             ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                                             ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                                             ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                                             ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                                             ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                            ";                                      

                        mysqli_begin_transaction($sql_link, MYSQLI_TRANS_START_READ_WRITE);
                        try
                        {
                            tep_db_query($sql_link,$sql_delete_data);
                            //tep_db_query($sql_link,$query_insert_data);
                            
                            
                            // Préparation de la requête
                            $stmt = $sql_link->prepare($query_insert_data);
                            
                            // Liaison des paramètres avec les types corrects
                            $stmt->bind_param(
                                            'sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss',
                                            $today, $id_agent, $id_station, $data_date,
                                            $station_all_array[$id_station]['station_type'],
                                            $appareil_type, $appareil_num, $appareil_heure,
                                            $plu_taille_auget, $etat_ra,
                                            $cote_limni_heure, $cote_limni_hsonde, $cote_limni_hech, $cote_limni_hech2, $num_sonde,
                                            $plu_tot_type, $plu_tot_first, $plu_tot_last, $plu_tot_heure_basc,
                                            $plu_cumul_tot, $plu_cumul_plu, $plu_diff_tot_plu, $plu_recalage_heure_plu,
                                            $plu_test_auget, $plu_nb_basculement,
                                            $nb_octets, $batt_num, $batt_tension,
                                            $num_k7, $heure_init_k7, $h_sonde_k7,
                                            $plu_heure_bascul1_cassette,
                                            $ctrl_recal_sonde, $ctrl_recal_heure_sonde, $purge_sonde,
                                            $jaugeage,
                                            $plu_ra_bouchage, $plu_ra_huile_tot,
                                            $debrous, $eau_batt, $transfert, $memoire_delete,
                                            $obs, $aprevoir,
                                            $nom_file, $obs_file,
                                            $pre_marquant, $fait_marquant,
                                            $agents,
                                            $piezo_toitnappesonde, $piezo_conductivite, $piezo_temperature, $piezo_recalage_diff,
                                            $piezo_recalage_sonde, $piezo_recalage_heure_sonde, $piezo_nature_repere,
                                            $piezo_instrument, $piezo_num_instrument, $piezo_prof_toitnappe, $piezo_prof_totale,
                                            $coord_x, $coord_y, $piezo_gps_precision, $piezo_systeme_coord,
                                            $piezo_pompage_encours, $piezo_pompage_proche, $piezo_pluie_crue, $piezo_temps_sec,
                                            $piezo_photos
                                        );

                            // Exécution de la requête préparée
                            $stmt->execute();

                            // Si tout se passe bien, vous pouvez confirmer la transaction
                            mysqli_commit($sql_link);  // Valider la transaction
                        
                            $nb_data_import++;
                        }
                        catch (Exception $e) 
                        {
                            $db_load = false;
                            mysqli_rollback($sql_link);// Annuler la transaction en cas d'erreur
                            $import_result_error .= "Erreur lors de l'enregistrement dans la base : " . $e->getMessage();
                        } 
                                
                    }
                }
            }
            
            fclose($handle); // Fermer le fichier CSV à importer
        }
    }    

    // Enregistrement dans les bases
    if(isset($data_tab) && sizeof($data_tab))
    {
        // Information Load File
        $import_result .= "\n----\n";
        $import_result .= "Fichier : ".$import_data['file_import']."\n";
        $import_result .= "Station : ".$station_all_array[$id_station]['nom_station']."\n";
        $import_result .= "Chronique : RA - ".$eq_type_array[$station_all_array[$id_station]['station_type']]['nom_eq_type']."\n";
        $import_result .= "\n";
    }

$endTime = microtime(true); // Temps à la fin du script
$executionTime = number_format($endTime - $startTime,1);
        
    if($db_load)
    {        
        $import_result .= "Le traitement du fichier est terminé.\n";
        $import_result .= "\n";
        $import_result .= "Durée du traitement : ".$executionTime." sec.\n";
        $import_result .= "Nombre de RA importées : ".$nb_data_import."\n";
        $import_result .= "Nombre d'erreurs : ".(($num_ligne-2) - $nb_data_import)."\n"; 

        // Mises à jour de l'import dans la table de suivi
        $query = "UPDATE ".TABLE_IMPORT_SUIVI." SET nb_data='".$nb_data_import."', 
                                                    datetime_first='".$date_debut."', 
                                                    datetime_end='".$date_fin."',
                                                    import=1
                                                WHERE id=".$idImport;

        tep_db_query($sql_link, $query); 
        
    }
    else
    {
        $import_result .= "Les données n'ont pas pu être importées.\n";
    }

    $import_result .= "\n";



// -------------------------------------
// Création d'un fichier txt contenant les infos détaillés sur l'import
//if($nb_error_file>0){$import_warning_ligne .= "Erreurs sur le format du fichier qui n'est pas correct : ".$nb_error_file." ligne(s) affectée(s).\n";}

//$text_import_result = $import_result.$import_result_error.$import_warning_ligne;
//$text_import_result .= "----\n";
$text_import_result = $import_result."\n".$import_result_error;

$resultFilename = $folder.$import_data['id_import'].'_RA.txt';
if(file_exists($resultFilename)){unlink($resultFilename);sleep(1);} // Supprimer le fichier existant s'il existe
file_put_contents($resultFilename, mb_convert_encoding($text_import_result, 'ISO-8859-1', 'UTF-8')); // Ecrire le résultat de l'import dans un fichier texte

// Enregistrement de l'action IMPORT dans la base action
$type_action = 37;
$info_action = "Importation des données RA - Fichier : ".$import_data['file_import']." - Station : ".$station_all_array[$import_data['id_station']]['nom_station'];
// Échappez la variable contenant des caractères spéciaux
$info_action = mysqli_real_escape_string($sql_link,$info_action);

// Préparez la requête SQL
$query = "INSERT INTO " . TABLE_ACTIONS . " (id_user, type_action, info, dateheure, id_import) VALUES (?, ?, ?, ?, ?)";

// Préparez la requête
$stmt = $sql_link->prepare($query);

// Liez les paramètres avec les types corrects
$stmt->bind_param(
    'issss',
    $import_data['id_user'],
    $type_action,
    $info_action,
    $import_data['dateheure'],
    $idImport
);

// Exécutez la requête préparée
$stmt->execute();


$tab_result = [
    'text' => $import_result,
    'nbData' => $nb_data_import
];


echo json_encode($tab_result);
					

?>
