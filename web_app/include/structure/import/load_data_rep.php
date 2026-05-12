<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Importation des données REP (Repère sur une station piézométrique)
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

$import_result_error = "";
$import_result = '';

$num_ligne = 0;
$nb_data_import = 0;

$date_debut = ''; // date de début de la chronique à chargé
$date_fin = ''; // date de fin de la chronique à chargé

$rows_deleted = 0;

$id_station = 0;
$id_chron = 0;
$id_ext_file = 0;



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
            // Démarrer une transaction
            mysqli_begin_transaction($sql_link, MYSQLI_TRANS_START_READ_WRITE);
            try{

                // On efface d'abord les données entre Date_Debut et Date_Fin chronique, qu'elles existent ou pas
                $sql_delete_data = "DELETE FROM ".TABLE_STATION_PIEZO_REPERE." WHERE id_station = ?";
                $stmt_delete = $sql_link->prepare($sql_delete_data);
                $stmt_delete->bind_param('i', $id_station);
                $stmt_delete->execute();
                $stmt_delete->close();

                // Lire chaque ligne du fichier CSV
                while (($data = fgetcsv($handle, 10000, ';')) !== false) 
                {
                    $import_valid = true;
                    $num_ligne++;

                    // On lit les champs du fichier
                    if($num_ligne > 2) // Les 2 premières lignes ne sont pas lues (Titre et Intitulé des colonnes)
                    {
                        // Date Début de validité du repère
                        $datedebut_rep = '';
                        $dateString = trim($data[6]);
                        $dateString_rep = isValidDateImport($dateString);
                        if ($dateString_rep == 'Invalid') 
                        {
                            $import_valid = false;
                            $import_result_error .= "Ligne $num_ligne : Date invalide.\n";
                        }
                        else
                        {
                            $dateParts = explode(' ', $dateString_rep);
                            $datedebut_rep = $dateParts[0]; // Récupère la date sans l'heure
                        }

                        // Date Fin de validité du repère
                        $datefin_rep = '';
                        $dateString = trim($data[7]);

                        if($dateString !== '') // Vérifie si la date est non vide
                        { 
                            $dateString_rep = isValidDateImport($dateString);
                            if ($dateString_rep == 'Invalid') 
                            {
                                $import_valid = false;
                                $import_result_error .= "Ligne $num_ligne : Date invalide.\n";
                            }
                            else
                            {
                                $dateParts = explode(' ', $dateString_rep);
                                $datefin_rep = $dateParts[0]; // Récupère la date sans l'heure
                            }
                        }
                        
                        // Enregistrement de la ligne si elle est valide dans les tables avant import dans la base
                        if($import_valid)
                        {
                            $num_station = $station_all_array[$id_station]['code_station'];
                            $nom_station = $station_all_array[$id_station]['nom_station'];

                            $nature_rep = trim($data[2]);
                            $code_rep = trim($data[3]);

                            $z_rep = is_numeric($data[4]) ? (float)$data[4] : null;
                            $precision_rep = trim($data[5]);

                            $nature_g1_rep = trim($data[8]);
                            $z_g1_rep = is_numeric($data[9]) ? (float)$data[9] : null;

                            $nature_g2_rep = trim($data[10]);
                            $z_g2_rep = is_numeric($data[11]) ? (float)$data[11] : null;

                            $obs_rep = trim($data[12]);

                            // Mise à jour des données existantes
                            $sql_insert_rep = "
                                                INSERT INTO " . TABLE_STATION_PIEZO_REPERE . " (
                                                    id_station, nature_repere, code_repere, z_repere, precision_repere,
                                                    date_debut_valid, date_fin_valid, 
                                                    nature_repere_1, z_repere_g1, nature_repere_2, z_repere_g2, obs
                                                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                            ";
                                            
                            $stmt_insert = $sql_link->prepare($sql_insert_rep);
                            $stmt_insert->bind_param(
                                'issdssssdsds',
                                $id_station, $nature_rep, $code_rep, $z_rep, $precision_rep,
                                $datedebut_rep, $datefin_rep,
                                $nature_g1_rep, $z_g1_rep, $nature_g2_rep, $z_g2_rep, $obs_rep
                            );
                            $stmt_insert->execute();
                            $stmt_insert->close();

                            $import_result .= "\n";

                            $nb_data_import++;

                        } // Si l'import est ok après vérification des champs

                    } // Condition de num_ligne > 2
                    
                } // Fermeture de la boucle de lecture

                // Commit de la transaction si tout est correct
                mysqli_commit($sql_link);

            } catch (Exception $e) {
                // Annuler la transaction en cas d'erreur
                mysqli_rollback($sql_link);
                $import_result_error .= "Erreur : " . $e->getMessage();
            }
            
            fclose($handle); // Fermer le fichier CSV à importer
        }
    }    


$endTime = microtime(true); // Temps à la fin du script
$executionTime = number_format($endTime - $startTime,1);

// Enregistrement dans les bases

    // Information Load File
    $import_result .= "\n----\n";
    $import_result .= "Fichier : ".$import_data['file_import']."\n";
    $import_result .= "Station : ".$station_all_array[$import_data['id_station']]['nom_station']."\n";
    $import_result .= "Chronique : REP\n";
    $import_result .= "\n";
          
    $import_result .= "Le traitement du fichier est terminé.\n";
    $import_result .= "\n";
    $import_result .= "Durée du traitement : ".$executionTime." sec.\n";
    $import_result .= "Nombre de REP importés : ".$nb_data_import."\n";
    $import_result .= "Nombre d'erreurs : ".(($num_ligne-2) - $nb_data_import)."\n"; 

    if(file_exists($chemin_file)){unlink($chemin_file);} // On supprime le fichier 

    

    // Mises à jour de l'import dans la table de suivi
    
    $query = "UPDATE ".TABLE_IMPORT_SUIVI." SET nb_data='".$nb_data_import."', 
                                                datetime_first='".$date_debut."', 
                                                datetime_end='".$date_fin."',
                                                import=1
                                            WHERE id=".$idImport;

    tep_db_query($sql_link, $query);         

    // -------------------------------------
    // Création d'un fichier txt contenant les infos détaillés sur l'import
    $text_import_result = $import_result.$import_result_error;

    $resultFilename = $folder.$import_data['id_import'].'_REP.txt';
    if(file_exists($resultFilename)){unlink($resultFilename);sleep(1);} // Supprimer le fichier existant s'il existe
    file_put_contents($resultFilename, mb_convert_encoding($text_import_result, 'ISO-8859-1', 'UTF-8')); // Ecrire le résultat de l'import dans un fichier texte

    // Enregistrement de l'action Export dans la base action
    $type_action = 37;
    $info_action = "Importation des données REP - Fichier : ".$import_data['file_import']." - Station : ".$station_all_array[$import_data['id_station']]['nom_station'];


    $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure,id_import) 
                                    VALUES (".$import_data['id_user'].",'".$type_action."','".$info_action."','".$import_data['dateheure']."','".$idImport."')";
    tep_db_query($sql_link,$query);


    $tab_result = [
        'text' => $import_result,
        'nbData' => $nb_data_import
    ];

    
    echo json_encode($tab_result);
	
?>
