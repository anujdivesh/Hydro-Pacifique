<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export au format csv 
- Ce script permet de générer les fichiers csv directement sur le serveur en tâche cachée, asynchrone.
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


// ------------------------


// pour solutionner les pb d'accents
header('Content-Type: text/html; charset=utf-8');

// connexion à la base de données	
$sql_link = mysqli_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE) or die('Impossible de se connecter à la base de données!');
mysqli_query ($sql_link,'SET NAMES UTF8');


// Récupération des données JSON envoyées depuis la requête AJAX
$jsonData = file_get_contents('php://input');

// Décodage des données JSON en tableau associatif
$data = json_decode($jsonData, true);

// Accès aux données individuelles
$Filename = $data['Filename'];
$folder_download = $data['folder_download'];
$chemin_folder = $data['chemin_folder'];
$id_station = $data['id_station'];
$code_station = $data['code_station'];
$nom_station = $data['nom_station'];
$init_chron = $data['init_chron']; // On récupère l'initial de la Chronique - Cela peut-être RA, LAB, TOT, JGE, ETL, REP, CTE, DIAC pour les exports hors chroniques classiques 
$sql_chron = $data['sql_chron'];
$nbdata_chron = $data['nbdata_chron'];
//$multi_file = $data['multi_file'];
$entete_col = $data['entete_col'];

// // Récupérer le type de données de la station : Pluvio/Hydro/Piezo
$sql_station = "SELECT DISTINCT station_type FROM ".TABLE_STATION." WHERE id_station=".$id_station;
$station_query = tep_db_query($sql_link, $sql_station);
$station_tab = tep_db_fetch_array($station_query);
$type_eq = $station_tab['station_type'];


// Récupérer les données qualité dans la table correspondante
$sql_quality = "SELECT DISTINCT id_data_qualite, init_qualite_data FROM ".TABLE_DATA_QUALITE;
$quality_query = tep_db_query($sql_link,$sql_quality);
while($quality_tab = tep_db_fetch_array($quality_query))
{
	$quality_array[$quality_tab['id_data_qualite']] = html_entity_decode($quality_tab['init_qualite_data'] ?? $default_string);
}

// --------------------------------------
// Initialisation de variables locales


// Spécifiez le nom du dossier à créer
$chemin_folder_process = '../../../' . $chemin_folder; 
$csvFilename = $chemin_folder_process.'/'.$Filename;

// Vérifiez si le dossier n'existe pas déjà
if (!is_dir($chemin_folder_process)) 
{
    // Créez le dossier avec les permissions appropriées (par exemple, 0755)
    mkdir($chemin_folder_process, 0755, true); // Le troisième paramètre true crée les dossiers parents si nécessaire
} 


// --------------------------------------
// CREATION FICHIER

$total_time = 0;
$content = '';

$startTime = microtime(true); // Temps au début du script


    // Ecriture des données de Chroniques
    if(($init_chron != 'RA') && ($init_chron != 'JGE') && ($init_chron != 'ETL') && ($init_chron != 'LAB') && ($init_chron != 'TOT') && ($init_chron != 'REP') && ($init_chron != 'CTE') && ($init_chron != 'DIAC'))
    {
        $handle = fopen($csvFilename, 'w');
        if ($handle !== false) 
        {
            // Ajouter le BOM UTF-8 - pour l'encodage des caractères particuliers
            fwrite($handle, "\xEF\xBB\xBF");

            $n=0;
            $data_chron_query = tep_db_query($sql_link, $sql_chron);
            while ($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
            {
                $dateTime_formatted = $data_chron_tab['dateheure'];

                $quality_valeur = '';
                if(isset($quality_array[$data_chron_tab['id_codequal']]) && tep_not_null($quality_array[$data_chron_tab['id_codequal']]))
                {$quality_valeur = $quality_array[$data_chron_tab['id_codequal']];}

                // Créer un tableau de données à écrire dans le fichier CSV
                $data = [
                    $dateTime_formatted,
                    $data_chron_tab['valeur'],
                    $quality_valeur
                ];

                // Écrire la ligne dans le fichier CSV en utilisant fputcsv
                fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'

            }

            // Fermer le fichier après écriture
            fclose($handle);

                // Ligne à écrire dans le fichier csv
                //$content .= $dateTime_formatted.";".$data_chron_tab['valeur'].";".$quality_valeur."\n";
            
        }
    }

    if($init_chron === 'LAB')
    {
        $handle = fopen($csvFilename, 'w');
        if ($handle !== false) 
        {
            // Ajouter le BOM UTF-8 - pour l'encodage des caractères particuliers
            fwrite($handle, "\xEF\xBB\xBF");

            $n=0;
            $data_chron_query = tep_db_query($sql_link, $sql_chron);
            while ($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
            {
                $dateTime_formatted = $data_chron_tab['dateheure'];

                $quality_valeur = '';
                if(isset($quality_array[$data_chron_tab['id_codequal']]) && tep_not_null($quality_array[$data_chron_tab['id_codequal']]))
                {$quality_valeur = $quality_array[$data_chron_tab['id_codequal']];}

                $total = $data_chron_tab['total'];
                $obs = $data_chron_tab['obs'];

                // Créer un tableau de données à écrire dans le fichier CSV
                $data = [
                    $dateTime_formatted,
                    $data_chron_tab['valeur'],
                    $total,
                    $quality_valeur,
                    $obs
                ];

                // Écrire la ligne dans le fichier CSV en utilisant fputcsv
                fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'

            }

            // Fermer le fichier après écriture
            fclose($handle);
        }
    }

    if($init_chron === 'TOT')
    {
        $handle = fopen($csvFilename, 'w');
        if ($handle !== false) 
        {
            // Ajouter le BOM UTF-8 - pour l'encodage des caractères particuliers
            fwrite($handle, "\xEF\xBB\xBF");

            $n=0;
            $data_chron_query = tep_db_query($sql_link, $sql_chron);
            while ($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
            {
                $dateTime_formatted = $data_chron_tab['dateheure'];

                $quality_valeur = '';
                if(isset($quality_array[$data_chron_tab['id_codequal']]) && tep_not_null($quality_array[$data_chron_tab['id_codequal']]))
                {$quality_valeur = $quality_array[$data_chron_tab['id_codequal']];}

                $obs = $data_chron_tab['obs'];

                // Créer un tableau de données à écrire dans le fichier CSV
                $data = [
                    $dateTime_formatted,
                    $data_chron_tab['valeurDebut'],
                    $data_chron_tab['valeurFin'],
                    $data_chron_tab['valeur'],
                    $quality_valeur,
                    $obs
                ];

                // Écrire la ligne dans le fichier CSV en utilisant fputcsv
                fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'
            }

            // Fermer le fichier après écriture
            fclose($handle);
        }
    }

    // Ecriture des RA    
    if($init_chron === 'RA')
    {
        $handle = fopen($csvFilename, 'w');
        if ($handle !== false) 
        {
            // Ajouter le BOM UTF-8 - pour l'encodage des caractères particuliers
            fwrite($handle, "\xEF\xBB\xBF");

            // Première ligne 
            $firstLine = ['Rapport d\'Activité - Station ' . $nom_station .' - '.$code_station];
            fputcsv($handle, $firstLine, ';');

            // En-tête des colonnes

            if($type_eq == 1) // Pluvio
            {
                $data = [
                    'Station Numéro','Station Nom','Relevé Date','Relevé Heure',
                    'Appareil N° K7','Appareil Type','Appareil Numéro','Appareil Heure',
                    'Type de totalisateur','Cumul Arrivée (mm)','Cumul Départ (mm)','Heure basculement',
                    'Durée enregistrement JJ','Durée enregistrement HH','Durée enregistrement MM',
                    'Dernier enregistrement JJ','Dernier enregistrement HH','Dernier enregistrement MM',
                    'Nombre de basculement', 'Nombre d\'octets','Numéro batterie','Tension batterie',
                    'Numéro cassette','Heure initialisation','Heure 1er basculement',       
                    'Cumul du totalisateur','Cumul du pluviomètre','Différence : TOT - Pluvio (mm)','Calage heure (hh:mm)','Test de l\'auget',
                    'Action Bouchage','Action Débrousaillage','Action Eau Batterie','Action Huile Tot.','Action Transfert','Action Mémoire Effacée',
                    'Observation','A prévoir','Agents',
                    'Coordonnées X','Coordonnées Y',
                    'Commentaire', 'Nom OE2',
                    'Nom Fichier','Observation Fichier',
                    'Pré-Marquant','Fait-Marquant'                   
                ];
                //'A prévoir Marquant','Fait Marquant'
            }


            if($type_eq == 11) // Hydro
            {
                $data = [
                    'Station Numéro','Station Nom','Relevé Date','Relevé Heure',
                    'Appareil N° K7','Appareil Type','Appareil Numéro','Appareil Heure',
                    'Côte Limnimétrique Heure','Côte Limnimétrique H. Sonde','Côte Limnimétrique H. Echl.','Côte Limnimétrique H. Second. Echl.',
                    'Durée enregistrement JJ','Durée enregistrement HH','Durée enregistrement MM',
                    'Dernier enregistrement JJ','Dernier enregistrement HH','Dernier enregistrement MM',
                    'Numéro Sonde','Nb Octets % Mem',
                    'Batterie Numéro','Batterie Tension',
                    'Nouvelle cartouche N° K7','Nouvelle cartouche Heure Init.','Nouvelle cartouche H. Sonde',
                    'Contrôle Hech-Hspi','Contrôle Recalage Sonde','Contrôle Recalage Data',            
                    'Action Purge sonde','Action Jaugeage','Action Débrousaillage','Action Eau Batterie',
                    'Action Transfert','Action Mémoire Effacée',
                    'Observation','A prévoir','Agents',
                    'Coord X','Coord Y',
                    'Nom Fichier','Obs. Fichier',
                    'Pré-Marquant','Fait-Marquant'
                ];
            }

            if($type_eq == 5) // Piezo
            {
                $data = [
                    'Station Numéro','Station Nom','Relevé Date','Relevé Heure',
                    'Sonde Fixe - Type','Sonde Fixe - Numéro','Sonde Fixe - Heure',
                    'Sonde Manuelle - Type','Sonde Manuelle - Numéro',
                    'Mesure Sonde - prof. toit de la nappe (m)','Mesure Sonde - Conductivité','Mesure Sonde - Température',
                    'Mesure Manuelle - prof. toit de la nappe (m)','Mesure Manuelle - prof. toit de la nappe (cm)','Profondeur ouvrage (m)',
                    'Contrôle - Diff. (manuel - sonde)','Contrôle - Recalage sonde','Contrôle - Recalage heure',
                    'Mémoire nb enregistrement','Mémoire effacée',
                    'Batterie % Mem',
                    'Nature du repère','Z(mNGNC)',
                    'Action Pompage en cours','Pompage proche','Action Pluie et/ou Crue','Action Journée sèche',
                    'Observation','A prévoir','Agents',
                    'Coord X','Coord Y',
                    'Nom Fichier','Obs. Fichier',
                    'Pré-Marquant','Fait-Marquant'
                ];
                
                //'Nom du fichier de relève / Num cassette',
                //'Action Photos',
                //'Système de coordonnées','Précision GPS',
            }

            // Écrire la ligne dans le fichier CSV en utilisant fputcsv
            fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'
            
            // Données
            $data_chron_query = tep_db_query($sql_link, $sql_chron);
            while($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
            {
                $tab_date_heure_ra = explode(" ", $data_chron_tab['date_heure_ra'] ?? '');
                //$date_ra =  str_replace("-", "/",dateus_fr($tab_date_heure_ra[0]));
                $date_ra =  str_replace("-", "/",$tab_date_heure_ra[0]);
                $heure_ra =  $tab_date_heure_ra[1];
                

                // Gérer les retours à la ligne et les guillemets dans les champs texte (comme 'obs')
                $data_chron_tab['ra_obs'] = isset($data_chron_tab['ra_obs']) ? str_replace(array("\r", "\n"), ' ', $data_chron_tab['ra_obs']) : '';
                //$data_chron_tab['ra_obs'] = '"' . str_replace('"', '""', $data_chron_tab['ra_obs']) . '"'; 

                $data_chron_tab['ra_futur'] = isset($data_chron_tab['ra_futur']) ? str_replace(array("\r", "\n"), ' ', $data_chron_tab['ra_futur']) : '';
                //$data_chron_tab['ra_futur'] = '"' . str_replace('"', '""', $data_chron_tab['ra_futur']) . '"'; 

                // Pour JMM - Pour les cases cochées on enlève les 0 pour n'afficher un espace vide
                if($data_chron_tab['plu_ra_bouchage'] < 1){$data_chron_tab['plu_ra_bouchage'] = '';}
                if($data_chron_tab['ra_debroussaillage'] < 1){$data_chron_tab['ra_debroussaillage'] = '';}
                if($data_chron_tab['ra_eau_batterie'] < 1){$data_chron_tab['ra_eau_batterie'] = '';}
                if($data_chron_tab['plu_ra_huile_tot'] < 1){$data_chron_tab['plu_ra_huile_tot'] = '';}
                if($data_chron_tab['ra_transfert_data'] < 1){$data_chron_tab['ra_transfert_data'] = '';}
                if($data_chron_tab['ra_delete_memory'] < 1){$data_chron_tab['ra_delete_memory'] = '';}
                if($data_chron_tab['hydro_purge_sonde'] < 1){$data_chron_tab['hydro_purge_sonde'] = '';}
                if($data_chron_tab['hydro_ra_jaugeage'] < 1){$data_chron_tab['hydro_ra_jaugeage'] = '';}

                
                if($data_chron_tab['piezo_pompage_encours'] < 1){$data_chron_tab['piezo_pompage_encours'] = '';}
                if($data_chron_tab['piezo_pompage_proche'] < 1){$data_chron_tab['piezo_pompage_proche'] = '';}
                if($data_chron_tab['piezo_pluie_crue'] < 1){$data_chron_tab['piezo_pluie_crue'] = '';}
                if($data_chron_tab['piezo_temps_sec'] < 1){$data_chron_tab['piezo_temps_sec'] = '';}
                
                if($data_chron_tab['pre_marquant'] < 1){$data_chron_tab['pre_marquant'] = '';}
                if($data_chron_tab['fait_marquant'] < 1){$data_chron_tab['fait_marquant'] = '';}

                
                $hechhsonde = '';
                //$hechhsonde = $data_chron_tab['hydro_h_echelle_1'] - $data_chron_tab['hydro_h_sonde'];
                
                $duree_enregistrement_JJ = '';$duree_enregistrement_HH = '';$duree_enregistrement_MM = '';
                $last_enregistrement_JJ = '';$last_enregistrement_HH = '';$last_enregistrement_MM = '';
                $coord_x = '';$coord_y = '';
                $commentaire = '';$nom_oe2 = '';

                // Ecriture dans le fichier csv
                if($type_eq == 1) // Pluvio
                {
                    $data = [
                                $code_station,$nom_station ?? '',$date_ra,$heure_ra, 
                                $data_chron_tab['num_cassette'],$data_chron_tab['type_appareil'] ?? '',$data_chron_tab['num_appareil'],$data_chron_tab['heure_appareil'],
                                $data_chron_tab['plu_tot_type'],$data_chron_tab['plu_tot_first'],$data_chron_tab['plu_tot_last'],$data_chron_tab['plu_tot_heure_basc'],
                                $duree_enregistrement_JJ,$duree_enregistrement_HH,$duree_enregistrement_MM,
                                $last_enregistrement_JJ,$last_enregistrement_HH,$last_enregistrement_MM,
                                $data_chron_tab['plu_nb_basculement'],$data_chron_tab['nb_octet'],$data_chron_tab['num_batterie'],$data_chron_tab['tension_batterie'],
                                $data_chron_tab['num_cassette'],$data_chron_tab['heure_init_cassette'],$data_chron_tab['plu_heure_bascul1_cassette'],
                                $data_chron_tab['plu_cumul_tot'],$data_chron_tab['plu_cumul_plu'],$data_chron_tab['plu_diff_tot_plu'],
                                $data_chron_tab['plu_recalage_heure_plu'],$data_chron_tab['plu_test_auget'],
                                $data_chron_tab['plu_ra_bouchage'],$data_chron_tab['ra_debroussaillage'],$data_chron_tab['ra_eau_batterie'],$data_chron_tab['plu_ra_huile_tot'],
                                $data_chron_tab['ra_transfert_data'],$data_chron_tab['ra_delete_memory'],
                                $data_chron_tab['ra_obs'] ?? '',$data_chron_tab['ra_futur'] ?? '',$data_chron_tab['agents_complement'],
                                $coord_x,$coord_y,
                                $commentaire,$nom_oe2,
                                $data_chron_tab['name_file_data'],$data_chron_tab['obs_file_data'] ?? '',
                                $data_chron_tab['pre_marquant'],$data_chron_tab['fait_marquant']                                
                            ];
                }


                if($type_eq == 11) // Hydro
                {
                    $data = [
                                $code_station,$nom_station ?? '',$date_ra,$heure_ra, 
                                $data_chron_tab['num_cassette'],$data_chron_tab['type_appareil'] ?? '',$data_chron_tab['num_appareil'],$data_chron_tab['heure_appareil'],
                                $data_chron_tab['hydro_heure_cote'],$data_chron_tab['hydro_h_sonde'],$data_chron_tab['hydro_h_echelle_1'],$data_chron_tab['hydro_h_echelle_2'],
                                $duree_enregistrement_JJ,$duree_enregistrement_HH,$duree_enregistrement_MM,
                                $last_enregistrement_JJ,$last_enregistrement_HH,$last_enregistrement_MM,
                                $data_chron_tab['hydro_num_sonde'],$data_chron_tab['nb_octet'] ?? '',
                                $data_chron_tab['num_batterie'],$data_chron_tab['tension_batterie'] ?? '',
                                $data_chron_tab['num_cassette'],$data_chron_tab['heure_init_cassette'],$data_chron_tab['hydro_h_sonde_cassette'],
                                $hechhsonde,$data_chron_tab['hydro_recalage_sonde'],$data_chron_tab['hydro_recalage_heure_sonde'],
                                $data_chron_tab['hydro_purge_sonde'],$data_chron_tab['hydro_ra_jaugeage'],$data_chron_tab['ra_debroussaillage'],$data_chron_tab['ra_eau_batterie'],
                                $data_chron_tab['ra_transfert_data'],$data_chron_tab['ra_delete_memory'],
                                $data_chron_tab['ra_obs'] ?? '',$data_chron_tab['ra_futur'] ?? '',$data_chron_tab['agents_complement'],
                                $coord_x,$coord_y,
                                $data_chron_tab['name_file_data'],$data_chron_tab['obs_file_data'] ?? '',
                                $data_chron_tab['pre_marquant'],$data_chron_tab['fait_marquant']
                            ];
                }

                if($type_eq == 5) // Piezo
                {  
                    $data_chron_tab['piezo_nature_repere'] = isset($data_chron_tab['piezo_nature_repere']) ? str_replace(array("\r", "\n"), ' ', $data_chron_tab['piezo_nature_repere']) : '';
                    
                    $data_chron_tab['piezo_x_terrain'] = isset($data_chron_tab['piezo_x_terrain']) ? str_replace(array("\r", "\n"), ' ', $data_chron_tab['piezo_x_terrain']) : '';
                    
                    $data_chron_tab['piezo_y_terrain'] = isset($data_chron_tab['piezo_y_terrain']) ? str_replace(array("\r", "\n"), ' ', $data_chron_tab['piezo_y_terrain']) : '';
                    
                    $data_sonde_fixe_type = $data_chron_tab['type_appareil'] ?? ''; 
                    $data_sonde_fixe_num = $data_chron_tab['num_appareil'] ?? '';   
                    $data_sonde_fixe_heure = $data_chron_tab['heure_appareil'] ?? '';  

                    $sondefixe_toitnappe_m = $data_chron_tab['piezo_toitnappesonde'] ?? ''; // en m
                    $sondefixe_conductivite = $data_chron_tab['piezo_conductivite'] ?? '';
                    $sondefixe_temperature = $data_chron_tab['piezo_temperature'] ?? '';
                    
                    $data_manuelle_type = $data_chron_tab['piezo_instrument'] ?? '';
                    $data_manuelle_num = $data_chron_tab['piezo_num_instrument'] ?? '';   

                    $manuelle_toitnappe_m = $data_chron_tab['piezo_prof_toitnappe'] ?? ''; // en m 
                    $manuelle_toitnappe_cm = $data_chron_tab['piezo_prof_toitnappe'] * 100; // conversion en cm
                    $manuelle_proftotale_m = $data_chron_tab['piezo_prof_totale'] ?? '';

                    if ($sondefixe_toitnappe_m === '' || $manuelle_toitnappe_m === '') {
                        $diff_manuelle_fixe = ''; // Retourne une chaîne vide si l'un des champs est vide
                    } else {
                        $diff_manuelle_fixe = (float) $manuelle_toitnappe_m - (float) $sondefixe_toitnappe_m;
                    }
                    $recalage_sonde_fixe = $data_chron_tab['piezo_recalage_sonde'] ?? '';
                    $recalage_heure_fixe = $data_chron_tab['piezo_recalage_heure_sonde'] ?? '';

                    $coord_x = $data_chron_tab['piezo_x_terrain'] ?? '';
                    $coord_y = $data_chron_tab['piezo_y_terrain'] ?? '';

                    $nature_repere = $data_chron_tab['piezo_nature_repere'] ?? '';
                    $z_repere = '';

                    $data = [
                                $code_station,$nom_station ?? '',$date_ra,$heure_ra, 
                                $data_sonde_fixe_type,$data_sonde_fixe_num,$data_sonde_fixe_heure,
                                $data_manuelle_type,$data_manuelle_num,
                                $sondefixe_toitnappe_m, $sondefixe_conductivite, $sondefixe_temperature,
                                $manuelle_toitnappe_m, $manuelle_toitnappe_cm, $manuelle_proftotale_m,
                                $diff_manuelle_fixe, $recalage_sonde_fixe, $recalage_heure_fixe,
                                $data_chron_tab['nb_octet'] ?? '',$data_chron_tab['ra_delete_memory'],
                                $data_chron_tab['tension_batterie'] ?? '',
                                $nature_repere,$z_repere,
                                $data_chron_tab['piezo_pompage_encours'],$data_chron_tab['piezo_pompage_proche'],$data_chron_tab['piezo_pluie_crue'],$data_chron_tab['piezo_temps_sec'],
                                $data_chron_tab['ra_obs'] ?? '',$data_chron_tab['ra_futur'] ?? '',$data_chron_tab['agents_complement'] ?? '',
                                $coord_x,$coord_y,
                                $data_chron_tab['name_file_data'] ?? '',$data_chron_tab['obs_file_data'] ?? '',
                                $data_chron_tab['pre_marquant'],$data_chron_tab['fait_marquant']
                            ];
                }

                
                // Écrire la ligne dans le fichier CSV en utilisant fputcsv
                fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'
            }

            // Fermer le fichier après écriture
            fclose($handle);
        }
    }

    // Ecriture des JGE
    if($init_chron === 'JGE')
    {

        $handle = fopen($csvFilename, 'w');
        if ($handle !== false) 
        {
            // Ajouter le BOM UTF-8 - pour l'encodage des caractères particuliers
            fwrite($handle, "\xEF\xBB\xBF");

            // Première ligne 
            $firstLine = 'Données des Jaugeages - Station ' . $nom_station .' - '.$code_station;
            fputcsv($handle, [$firstLine], ';');
            
            
            // En-tête des colonnes
            $data = [
                        'Station Numéro','Station Nom',
                        'Date',
                        'Début Heure','Début H échel.','Fin Heure','Fin H échel.',
                        'Dépouillement H moy.','Dépouillement Q','Dépouillement Section','Dépouillement V moy.',
                        'Dépouillement V surf.','Dépouillement Rh','Dépouillement Prof moy.','Dépouillement Nb vert.',
                        'Matériel Moulinet', 'Matériel Hélice',
                        'Observation','Agents',
                        'Corrdonnées GPS X','Corrdonnées GPS Y','Corrdonnées SIG X','Corrdonnées SIG Y',
                        'Nom Fichier','Code Qualité'
            ];
            
            // Écrire la ligne dans le fichier CSV en utilisant fputcsv
            fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'
            
            // Données
            $data_chron_query = tep_db_query($sql_link, $sql_chron);
            while($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
            {
                $tab_date_heure_jge = explode(" ", $data_chron_tab['datetime'] ?? '');
                $date_jge =  str_replace("-", "/",$tab_date_heure_jge[0]);
                //$heure_jge =  $tab_date_heure_jge[1];

                // Ecriture dans le fichier csv
                $data = [
                            $code_station,$nom_station,
                            $date_jge,
                            $data_chron_tab['heure_first'],$data_chron_tab['h_ech_first'],$data_chron_tab['heure_end'],$data_chron_tab['h_ech_end'],
                            $data_chron_tab['depouil_hmoy'],$data_chron_tab['depouil_q'],$data_chron_tab['depouil_sect'],$data_chron_tab['depouil_vmoy'],
                            $data_chron_tab['depouil_vsurf'],$data_chron_tab['depouil_rh'],$data_chron_tab['depouil_profmoy'],$data_chron_tab['depouil_nbvert'],
                            $data_chron_tab['id_moulinet'],$data_chron_tab['id_helice'],
                            $data_chron_tab['obs'],
                            '', // agents non gérés dans la table pour le moment
                            $data_chron_tab['x_gps'],$data_chron_tab['y_gps'],
                            '','', // Coordonnees SIG pas dans la table pour le moment
                            $data_chron_tab['fichier'],$data_chron_tab['code_qualite']
                ];
                
                // Écrire la ligne dans le fichier CSV en utilisant fputcsv
                fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'
            }
            

            // Fermer le fichier après écriture
            fclose($handle);

        }
    }

    // Ecriture des ETL
    if($init_chron === 'ETL')
    {
        $handle = fopen($csvFilename, 'w');
        if ($handle !== false) 
        {
            // Ajouter le BOM UTF-8 - pour l'encodage des caractères particuliers
            fwrite($handle, "\xEF\xBB\xBF");

            // Initialiser
            $data_dates = [];    // Tableau pour stocker les dates sur la première ligne
            $data_values = [];   // Tableau pour stocker les valeurs (hauteur et débit) sur plusieurs lignes

            // Données
            $data_chron_query = tep_db_query($sql_link, $sql_chron);
            while($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
            {
                $tab_date_heure_first = explode(" ", $data_chron_tab['datetime_first'] ?? '');
                $date_first = str_replace("-", "/",$tab_date_heure_first[0]);
                $heure_first = $tab_date_heure_first[1];
                $data_heure_first = $date_first.' '.$heure_first;

                $tab_date_heure_end = explode(" ", $data_chron_tab['datetime_end'] ?? '');
                $date_end = str_replace("-", "/",$tab_date_heure_end[0]);
                $heure_end = $tab_date_heure_end[1];
                $data_heure_end = $date_end.' '.$heure_end;
                
                // Ajouter les dates dans le tableau $data_dates
                $data_dates[] = [$data_heure_first, $data_heure_end];

                
                // Données des ETL
                $sql_etl_data = "SELECT DISTINCT ed.id, ed.hauteur, ed.debit, ed.code_qualite
                                    FROM ".TABLE_DATA_ETL_DATA." ed
                                    WHERE ed.id_etl=".$data_chron_tab['id']." 
                                    ORDER BY ed.hauteur ASC";
                
                $etl_data_query = tep_db_query($sql_link, $sql_etl_data);

                // Stockage des valeurs de hauteur et débit
                $temp_values = []; // Temporaire pour stocker les valeurs de hauteur et débit pour ce couple de dates

                // Récupération des valeurs
                while ($etl_data_tab = tep_db_fetch_array($etl_data_query)) 
                {
                    // Ajouter les valeurs de hauteur et debit
                    $temp_values[] = [$etl_data_tab['hauteur'], $etl_data_tab['debit']];
                }

                // Ajouter les valeurs au tableau global en préservant les couples
                $data_values[] = $temp_values; // Chaque couple de dates a son propre ensemble de valeurs

            }

            // Préparer la première ligne avec les paires de dates
            $header = [];
            foreach ($data_dates as $dates) 
            {
                $header[] = $dates[0]; // Date de début
                $header[] = $dates[1]; // Date de fin
            }

            // Écriture des données dans le fichier CSV
            fputcsv($handle, $header, ';');

            // Trouver le maximum de lignes de données de hauteur/debit pour aligner
            $max_rows = 0;
            foreach ($data_values as $values) 
            {
                $max_rows = max($max_rows, count($values));
            }

            // Écrire les lignes avec les valeurs
            for ($row_index = 0; $row_index < $max_rows; $row_index++) 
            {
                $row = [];

                foreach ($data_values as $values) 
                {
                    // Vérifier si la valeur existe pour cette ligne
                    if (isset($values[$row_index])) 
                    {
                        $row[] = $values[$row_index][0]; // Hauteur
                        $row[] = $values[$row_index][1]; // Débit
                    } else 
                    {
                        // Si aucune valeur, ajouter des vides pour garder l'alignement
                        $row[] = ''; // Hauteur vide
                        $row[] = ''; // Débit vide
                    }
                }
                // Écrire la ligne dans le CSV
                fputcsv($handle, $row, ';');
            }

        }
        // Fermer le fichier après écriture
        fclose($handle);
    }

    // Ecriture des REP - Repère station Piézo
    if($init_chron === 'REP')
    {

        $handle = fopen($csvFilename, 'w');
        if ($handle !== false) 
        {
            // Ajouter le BOM UTF-8 - pour l'encodage des caractères particuliers
            fwrite($handle, "\xEF\xBB\xBF");

            // Première ligne 
            $firstLine = 'Repères - Station ' . $nom_station .' - '.$code_station;
            fputcsv($handle, [$firstLine], ';');
            
            
            // En-tête des colonnes
            $data = [
                        'Station Numéro','Station Nom',
                        'Nature Repère','Code Repère','Z Repère','Précision Repère',
                        'Début validité','Fin validité',
                        'Nature Repère Géomètre 1','Z Géomètre 1',
                        'Nature Repère Géomètre 2','Z Géomètre 2',
                        'Observation'
            ];
            
            // Écrire la ligne dans le fichier CSV en utilisant fputcsv
            fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'
            
            // Données
            $data_chron_query = tep_db_query($sql_link, $sql_chron);
            while($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
            {
                $tab_date_debut_valid = explode(" ", $data_chron_tab['date_debut_valid'] ?? '');
                $date_debut_valid =  str_replace("-", "/",$tab_date_debut_valid[0]);

                $tab_date_fin_valid = explode(" ", $data_chron_tab['date_fin_valid'] ?? '');
                $date_fin_valid =  str_replace("-", "/",$tab_date_fin_valid[0]);

                $data_chron_tab['obs'] = isset($data_chron_tab['obs']) ? str_replace(array("\r", "\n"), ' ', $data_chron_tab['obs']) : '';

                // Ecriture dans le fichier csv
                $data = [
                            $code_station,$nom_station,
                            $data_chron_tab['nature_repere'],$data_chron_tab['code_repere'],$data_chron_tab['z_repere'],$data_chron_tab['precision_repere'],
                            $date_debut_valid,$date_fin_valid,
                            $data_chron_tab['nature_repere_1'],$data_chron_tab['z_repere_g1'],
                            $data_chron_tab['nature_repere_2'],$data_chron_tab['z_repere_g2'],
                            $data_chron_tab['obs']
                ];
                
                
                // Écrire la ligne dans le fichier CSV en utilisant fputcsv
                fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'
            }
            

            // Fermer le fichier après écriture
            fclose($handle);
        }
    }
    
    // Ecriture des CTE - Caractéristiques station Piézo
    if($init_chron === 'CTE')
    {

        $handle = fopen($csvFilename, 'w');
        if ($handle !== false) 
        {
            // Ajouter le BOM UTF-8 - pour l'encodage des caractères particuliers
            fwrite($handle, "\xEF\xBB\xBF");

            // Première ligne 
            $firstLine = 'Caractéristiques - Station ' . $nom_station .' - '.$code_station;
            fputcsv($handle, [$firstLine], ';');
            
            
            // En-tête des colonnes
            $data = [
                        'Station Numéro','Station Nom','Date',
                        'Profondeur',
                        'Matériaux Tête','Dimension extérieure','Matériaux tubage intérieur','Dimension tubage intérieur [mm]',
                        'Materiaux dalle','Dimension dalle','Présence capot',
                        'Distance capot/tube','Distance tube/dalle','Distance dalle/sol',
                        'Etat','En activite','Usage','Equipement',
                        'Schéma','Protection',
                        'Observations'
            ];
            
            // Écrire la ligne dans le fichier CSV en utilisant fputcsv
            fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'
            
            // Données
            $data_chron_query = tep_db_query($sql_link, $sql_chron);
            while($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
            {
                $tab_date_cte = explode(" ", $data_chron_tab['date'] ?? '');
                $date_cte =  str_replace("-", "/",$tab_date_cte[0]);

                if($data_chron_tab['presence_capot'] < 1){$data_chron_tab['presence_capot'] = '';}
                if($data_chron_tab['activite'] < 1){$data_chron_tab['activite'] = '';}
                if($data_chron_tab['schema_tete'] < 1){$data_chron_tab['schema_tete'] = '';}
                if($data_chron_tab['schema_protect'] < 1){$data_chron_tab['schema_protect'] = '';}

                $data_chron_tab['etat'] = isset($data_chron_tab['etat']) ? str_replace(array("\r", "\n"), ' ', $data_chron_tab['etat']) : '';
                $data_chron_tab['utilisation'] = isset($data_chron_tab['utilisation']) ? str_replace(array("\r", "\n"), ' ', $data_chron_tab['utilisation']) : '';
                $data_chron_tab['equipement_exploitation'] = isset($data_chron_tab['equipement_exploitation']) ? str_replace(array("\r", "\n"), ' ', $data_chron_tab['equipement_exploitation']) : '';
                $data_chron_tab['obs'] = isset($data_chron_tab['obs']) ? str_replace(array("\r", "\n"), ' ', $data_chron_tab['obs']) : '';


                // Ecriture dans le fichier csv
                $data = [
                            $code_station,$nom_station,$date_cte,
                            $data_chron_tab['prof'],
                            $data_chron_tab['materiaux_tete'],$data_chron_tab['dim_tete_ext'],$data_chron_tab['materiaux_tub_inter'],$data_chron_tab['diam_tub_inter'],
                            $data_chron_tab['materiaux_dalle'],$data_chron_tab['dim_dalle'],$data_chron_tab['presence_capot'],
                            $data_chron_tab['dist_capto_tube'],$data_chron_tab['dist_tube_dalle'],$data_chron_tab['dist_dalle_sol'],
                            $data_chron_tab['etat'],$data_chron_tab['activite'],$data_chron_tab['utilisation'],$data_chron_tab['equipement_exploitation'],
                            $data_chron_tab['schema_tete'],$data_chron_tab['schema_protect'],
                            $data_chron_tab['obs']
                ];
                
                // Écrire la ligne dans le fichier CSV en utilisant fputcsv
                fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'
            }
            

            // Fermer le fichier après écriture
            fclose($handle);
        }
    }

    if($init_chron === 'DIAC') // Diagraphie de conductivité
    {
        $handle = fopen($csvFilename, 'w');
        if ($handle !== false) 
        {
            // Ajouter le BOM UTF-8 - pour l'encodage des caractères particuliers
            fwrite($handle, "\xEF\xBB\xBF");

            // En-tête des colonnes
            $data = [
                'Station Numéro','Station Nom','Date',
                'Profondeur','Conductivité','Température',
                'Observations'
            ];

            // Écrire la ligne dans le fichier CSV en utilisant fputcsv
            fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'

            $data_chron_query = tep_db_query($sql_link, $sql_chron);
            while ($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
            {
                $tab_date_heure_diac = explode(" ",$data_chron_tab['date_heure_ra'] ?? '');
                $date_diac =  str_replace("-", "/",$tab_date_heure_diac[0]);
                
                $data_chron_tab['obs'] = isset($data_chron_tab['obs']) ? str_replace(array("\r", "\n"), ' ', $data_chron_tab['obs']) : '';


                // Créer un tableau de données à écrire dans le fichier CSV
                $data = [
                    $code_station,$nom_station,$date_diac,
                    $data_chron_tab['profondeur'],
                    $data_chron_tab['conductivite'],
                    $data_chron_tab['temperature'],
                    $data_chron_tab['obs']
                ];

                // Écrire la ligne dans le fichier CSV en utilisant fputcsv
                fputcsv($handle, $data, ';'); // Le troisième paramètre définit le délimiteur, ici ';'
            }

            // Fermer le fichier après écriture
            fclose($handle);
        }
    }

if(isset($data_chron_query)){mysqli_free_result($data_chron_query);} // Libération des ressources de résultat



// ---------------------------------------------------
// On tente de remplir un fichier Excel
/*
    $xlsFilename = $chemin_folder_process.'/test.xlsx'; // Chemin vers le fichier Excel

    // Vérifier si le fichier Excel existe
    if (file_exists($xlsFilename)) 
    {   
        $spreadsheet = IOFactory::load($xlsFilename); // Charger le fichier Excel existant 
    } else 
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet(); // Créer un nouveau fichier Excel
    }

    // Créer un objet Reader pour le fichier CSV
    $reader = IOFactory::createReader('Csv');
    $reader->setDelimiter(';');

    $csvSpreadsheet = $reader->load($csvFilename); // Charger les données du fichier CSV dans un nouvel objet PhpSpreadsheet

    // Copier les données de la première feuille du fichier CSV dans une nouvelle feuille du fichier Excel

    // Renommer la feuille de calcul si elle porte le même nom que la feuille par défaut
    if ($csvSpreadsheet->getActiveSheet()->getTitle() == 'Worksheet') {
        $csvSpreadsheet->getActiveSheet()->setTitle($Filename);
    }

    // Ajouter la feuille de calcul à la feuille de calcul existante
    $spreadsheet->addSheet($csvSpreadsheet->getActiveSheet());

    // Enregistrer les modifications dans le fichier Excel
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($xlsFilename);

*/
// ---------------------------------------------------





$endTime = microtime(true); // Temps à la fin du script

// Calcul de la durée d'exécution en secondes
$executionTime = number_format($endTime - $startTime,1);
$total_time += $executionTime;

echo json_encode($executionTime, JSON_UNESCAPED_UNICODE);

?>