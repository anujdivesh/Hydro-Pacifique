<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Importation des données JGE (Hydrométrie) - Formùat Spécifique à la NC, 
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

                // Lire chaque ligne du fichier CSV
                while (($data = fgetcsv($handle, 10000, ';')) !== false) 
                {
                    $import_valid = true;
                    $num_ligne++;

                    // On lit les champs du fichier
                    if($num_ligne > 2) // Les 2 premières lignes ne sont pas lues (Titre et Intitulé des colonnes)
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
                        
                        $dateheure_jge = isValidDateImport($dateheureString);
                        if ($dateheure_jge == 'Invalid') 
                        {
                            $dateheure_jge = '';
                            $import_valid = false;
                            $import_result_error .= "Ligne $num_ligne : Date invalide.\n";
                        }

                        // Enregistrement de la ligne si elle est valide dans les tables avant import dans la base
                        if($import_valid)
                        {

                            // Conversion et validation des champs numériques
                            $depouil_hmoy = is_numeric($data[7]) ? (float)$data[7] : null;
                            $depouil_q = is_numeric($data[8]) ? (float)$data[8] : null;
                            $depouil_sect = is_numeric($data[9]) ? (float)$data[9] : null;
                            $depouil_vmoy = is_numeric($data[10]) ? (float)$data[10] : null;
                            $depouil_vsurf = is_numeric($data[11]) ? (float)$data[11] : null;
                            $depouil_rh = is_numeric($data[12]) ? (float)$data[12] : null;
                            $depouil_profmoy = is_numeric($data[13]) ? (float)$data[13] : null;
                            $depouil_nbvert = is_numeric($data[14]) ? (float)$data[14] : null;

                            //$data[15] -- id_moulinet , il faut le relier à la table moulinet
                            //$data[16] -- id_moulinet , il faut le relier à la table moulinet

                            // Validation et nettoyage des chaînes de texte
                            $obs_jge = htmlspecialchars(trim($data[17]), ENT_QUOTES, 'UTF-8');
                            $agents = htmlspecialchars(trim($data[18]), ENT_QUOTES, 'UTF-8');
                            $x_gps = htmlspecialchars(trim($data[19]), ENT_QUOTES, 'UTF-8');
                            $y_gps = htmlspecialchars(trim($data[20]), ENT_QUOTES, 'UTF-8');
                            $fichier = htmlspecialchars(trim($data[24]), ENT_QUOTES, 'UTF-8');

                            //$data[21] -- SIG X
                            //$data[22] -- SIG Y
                            //$data[23] -- Code qualité // il faut le relier à la table code qualité
                        
                            // Vérifier si l'enregistrement existe déjà
                            $sql_jge = "SELECT id FROM " . TABLE_DATA_JGE . "
                                        WHERE id_station = ? AND datetime = ?";
                            $stmt = $sql_link->prepare($sql_jge);
                            $stmt->bind_param('is', $id_station, $dateheure_jge);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($row = $result->fetch_assoc()) 
                            {
                                // Mise à jour des données existantes
                                $sql_update_jge = "
                                                    UPDATE " . TABLE_DATA_JGE . "
                                                    SET 
                                                        datetime = ?, x_gps = ?, y_gps = ?, depouil_hmoy = ?, depouil_q = ?,
                                                        depouil_sect = ?, depouil_vmoy = ?, depouil_vsurf = ?, depouil_rh = ?,
                                                        depouil_profmoy = ?, depouil_nbvert = ?, code_qualite = ?, obs = ?, fichier = ?
                                                    WHERE id = ?
                                                ";
                                $stmt_update = $sql_link->prepare($sql_update_jge);
                                $stmt_update->bind_param(
                                    'sssddddddddissi',
                                    $dateheure_jge, $x_gps, $y_gps, $depouil_hmoy, $depouil_q,
                                    $depouil_sect, $depouil_vmoy, $depouil_vsurf, $depouil_rh,
                                    $depouil_profmoy, $depouil_nbvert, $id_code_qualite, $obs_jge, $fichier,
                                    $row['id']
                                );
                                $stmt_update->execute();
                                $stmt_update->close();
                            }
                            else
                            {
                                // Insertion d'un nouveau Jaugeage
                                $sql_insert_jge = "
                                                    INSERT INTO " . TABLE_DATA_JGE . " (
                                                        datetime, id_station, x_gps, y_gps, depouil_hmoy, depouil_q,
                                                        depouil_sect, depouil_vmoy, depouil_vsurf, depouil_rh,
                                                        depouil_profmoy, depouil_nbvert, code_qualite, obs, fichier
                                                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                                ";
                                $stmt_insert = $sql_link->prepare($sql_insert_jge);
                                $stmt_insert->bind_param(
                                    'sissddddddddiss',
                                    $dateheure_jge, $id_station, $x_gps, $y_gps, $depouil_hmoy, $depouil_q,
                                    $depouil_sect, $depouil_vmoy, $depouil_vsurf, $depouil_rh,
                                    $depouil_profmoy, $depouil_nbvert, $id_code_qualite, $obs_jge, $fichier
                                );
                                $stmt_insert->execute();
                                $stmt_insert->close();
                            }

                            $nb_data_import++;

                        } // Condition de num_ligne > 2
                    } 
                    
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
    $import_result .= "Chronique : JGE\n";
    $import_result .= "\n";
          
    $import_result .= "Le traitement du fichier est terminé.\n";
    $import_result .= "\n";
    $import_result .= "Durée du traitement : ".$executionTime." sec.\n";
    $import_result .= "Nombre de JGE importés : ".$nb_data_import."\n";
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

    $resultFilename = $folder.$import_data['id_import'].'_JGE.txt';
    if(file_exists($resultFilename)){unlink($resultFilename);sleep(1);} // Supprimer le fichier existant s'il existe
    file_put_contents($resultFilename, mb_convert_encoding($text_import_result, 'ISO-8859-1', 'UTF-8')); // Ecrire le résultat de l'import dans un fichier texte

    // Enregistrement de l'action Export dans la base action
    $type_action = 37;
    $info_action = "Importation des données JGE - Fichier : ".$import_data['file_import']." - Station : ".$station_all_array[$import_data['id_station']]['nom_station'];


    $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure,id_import) 
                                    VALUES (".$import_data['id_user'].",'".$type_action."','".$info_action."','".$import_data['dateheure']."','".$idImport."')";
    tep_db_query($sql_link,$query);


    $tab_result = [
        'text' => $import_result,
        'nbData' => $nb_data_import
    ];

    
    echo json_encode($tab_result);
	
?>
