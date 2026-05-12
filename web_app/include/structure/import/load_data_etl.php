<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Importation des données ETL (courbes d'étalonnage), 
Ce script est appelé après avoir sélectionné et vérifier les fichiers à importer (load_file.php) 
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

$import_result = '';
$import_result_details = '';
$nb_data_import = 0;

$num_ligne = 0;

$date_debut = ''; // date de début des données ETL à chargé
$date_fin = ''; // date de fin des données ETL à chargé

$rows_deleted = 0;

$id_station = 0;
$id_ext_file = 0;

$import_valid = true;


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

        $columns = [];

        // Ouvrir le fichier CSV en mode lecture
        if (($handle = fopen($chemin_file, 'r')) !== false) 
        {   
            $num_ligne = 0; // Compteur de lignes

            while (($row = fgetcsv($handle, 1000, ';')) !== false) 
            {
                $num_ligne++;
                // Vérifier et retirer le BOM sur la première ligne (pour résoudre un pb d'encodage avec les fichiers UTF8)
                if ($num_ligne === 1 && isset($row[0]) && substr($row[0], 0, 3) === "\xEF\xBB\xBF") 
                {
                    // Retirer le BOM
                    $row[0] = substr($row[0], 3);
                }

                foreach ($row as $index => $value) 
                {
                    $columns[$index][] = $value; // Ajouter chaque valeur à sa colonne
                }
            }

            // Vérifier si les colonnes sont par paires
            if (count($columns) % 2 !== 0) 
            {
                $import_valid = false;
                $import_result_details .= "Le fichier CSV doit avoir un nombre pair de colonnes.\n";
            }

            fclose($handle); // Fermer le fichier CSV à importer
        }
        else{$import_valid = false;}
    }
    else{$import_valid = false;}



    if($import_valid)
    {        
        $num_etl = 0;
        for ($i = 0; $i < count($columns); $i += 2) 
        {
            $num_etl++;

            // Extraire les données de chaque couple de colonnes
            $datesStart = $columns[$i];
            $datesEnd = $columns[$i + 1];
            $hauteur_tab = array_slice($columns[$i], 1); // Toutes les valeurs sauf la première
            $debit_tab = array_slice($columns[$i + 1], 1); // Toutes les valeurs sauf la première
        
            if (count($hauteur_tab) !== count($debit_tab)) 
            {
                $import_valid = false;
                $import_result_details .= "Certains couples de données Hauteur / Débit ne sont pas cohérents.\n";
                break;
            }

            $datetimeFirstString = $datesStart[0]; // Première valeur de la colonne des dates de début
            $datetimeFirst = isValidDateImport($datetimeFirstString);
            if ($datetimeFirst == 'Invalid') 
            {
                $import_valid = false;
                $import_result_details .= "La date '".$datetimeFirstString."' n'est pas dans un bon format dd/mm/yyyy hh:mm:ss\n";
                break;
            }

            $datetimeEndString = $datesEnd[0]; // Première valeur de la colonne des dates de début
            $datetimeEnd = isValidDateImport($datetimeEndString);
            if ($datetimeEnd == 'Invalid') 
            {
                $import_valid = false;
                $import_result_details .= "La date '".$datetimeEndString."' n'est pas dans un bon format dd/mm/yyyy hh:mm:ss\n";
                break;
            }

            if(!$import_valid){continue;}
            
            // Ajouter les données de ce couple dans le tableau temporaire `data_etl`
            $temp_etl[$num_etl] = [
                                        'datetime_first' => $datetimeFirst,
                                        'datetime_end' => $datetimeEnd
                                    ];

            foreach($hauteur_tab as $index => $hauteur) 
            {
                $debit = $debit_tab[$index];

                if ($hauteur !== "" && $debit !== "") 
                {
                    $hauteur_verif = NULL;
                    if (is_numeric($hauteur)) {$hauteur_verif = (float)$hauteur;}
                    else
                    {
                        $import_valid = false;
                        $import_result_details .= "Certaines Hauteurs ne sont pas des valeurs numériques.\n";
                        break;
                    }

                    
                    $debit_verif = NULL;
                    if (is_numeric($debit)) {$debit_verif = (float)$debit;}
                    else
                    {
                        $import_valid = false;
                        $import_result_details .= "Certains Débits ne sont pas des valeurs numériques.\n";
                        break;
                    }

                    // Ajouter les données de ce couple dans le tableau temporaire `data_etl_data`
                    $temp_data_etl[$num_etl][] = [
                                                    'hauteur' => $hauteur_verif,
                                                    'debit' => $debit_verif
                                                ];
                    
                }
            }       
        }
    }

    // Démarrer une transaction
    if($import_valid)
    {
        // Tableau pour stocker toutes les requêtes SQL pour contrôle
        $sql_log = [];

        // Démarrer une transaction
        mysqli_begin_transaction($sql_link, MYSQLI_TRANS_START_READ_WRITE);

        try{
                    
            // Tableau pour stocker les IDs supprimés
            $deleted_etl_ids = [];


            // Étape 1 : Sélectionner les données ETL à supprimer
            $select_etl = "SELECT id FROM " . TABLE_DATA_ETL . " WHERE id_station = " . intval($id_station);
            $query_select_etl = tep_db_query($sql_link, $select_etl);

            //$sql_log[] = $select_etl; // Ajouter la requête au log

            while ($etl_tab = tep_db_fetch_array($query_select_etl)) 
            {
                $deleted_etl_ids[] = $etl_tab['id']; // Ajouter les IDs ETL à supprimer dans un tableau
            }

            // Étape 2 : Supprimer les données liées dans TABLE_DATA_ETL_DATA
            if (!empty($deleted_etl_ids)) 
            {
                $ids_to_delete = implode(',', $deleted_etl_ids);
                $query_delete_etl_data = "DELETE FROM " . TABLE_DATA_ETL_DATA . " WHERE id_etl IN (" . $ids_to_delete . ")";
                tep_db_query($sql_link, $query_delete_etl_data);

                //$sql_log[] = $query_delete_etl_data; // Ajouter la requête au log
            
            }

            // Étape 3 : Supprimer les lignes de TABLE_DATA_ETL
            $query_delete_etl = "DELETE FROM " . TABLE_DATA_ETL . " WHERE id_station = " . intval($id_station);
            tep_db_query($sql_link, $query_delete_etl);

            //$sql_log[] = $query_delete_etl; // Ajouter la requête au log
        

            // Étape 4 : Insérer les nouvelles données dans TABLE_DATA_ETL
            $insert_etl = "INSERT INTO " . TABLE_DATA_ETL . "
                            (`id_station`, `datetime_first`, `datetime_end`) 
                            VALUES (?, ?, ?)";
            $stmt_etl = mysqli_prepare($sql_link, $insert_etl);

            //$sql_log[] = $insert_etl; // Ajouter la requête au log

            // Ajout des données ETL
            foreach ($temp_etl as $num_etl => $etl_info) 
            {
                mysqli_stmt_bind_param(
                    $stmt_etl,
                    "iss",
                    $id_station,
                    $etl_info['datetime_first'],
                    $etl_info['datetime_end']
                );
                mysqli_stmt_execute($stmt_etl);

                // Récupérer l'ID ETL nouvellement inséré
                $new_etl_id = mysqli_insert_id($sql_link);

                // Étape 5 : Insérer les données associées dans TABLE_DATA_ETL_DATA
                $insert_etl_data = "INSERT INTO " . TABLE_DATA_ETL_DATA . "
                                    (`id_etl`, `hauteur`, `debit`) 
                                    VALUES (?, ?, ?)";
                $stmt_etl_data = mysqli_prepare($sql_link, $insert_etl_data);

                //$sql_log[] = $insert_etl_data; // Ajouter la requête au log

                foreach ($temp_data_etl[$num_etl] as $measurement) 
                {
                    mysqli_stmt_bind_param(
                        $stmt_etl_data,
                        "idd",
                        $new_etl_id,
                        $measurement['hauteur'],
                        $measurement['debit']
                    );
                    mysqli_stmt_execute($stmt_etl_data);

                    $nb_data_import++;
                }

                mysqli_stmt_close($stmt_etl_data);
            }
            
            // Étape 6 : Commit de la transaction si tout est correct
            mysqli_commit($sql_link);
            $import_result_details .= "Les données ont bien été mises à jour.\n";


        } catch (Exception $e) 
        {
            // Annuler la transaction en cas d'erreur
            mysqli_rollback($sql_link);

            // Afficher un message d'erreur
            $import_result_details .= "Erreur lors de l'exécution de la transaction : " . $e->getMessage();
        } finally 
        {
            if (isset($stmt_etl) && $stmt_etl instanceof mysqli_stmt) {
                mysqli_stmt_close($stmt_etl);
            }
        }

        // Afficher ou enregistrer toutes les requêtes SQL générées
        //file_put_contents('sql_log.txt', implode("\n", $sql_log));
    }


$endTime = microtime(true); // Temps à la fin du script
$executionTime = number_format($endTime - $startTime,1);

// Enregistrement dans les bases

    // Information Load File
    
    $import_result .= "\n----\n";
    $import_result .= "Fichier : ".$import_data['file_import']."\n";
    $import_result .= "Station : ".$station_all_array[$import_data['id_station']]['nom_station']."\n";
    $import_result .= "Chronique : ETL\n";
    $import_result .= "\n";
          
    $import_result .= "Le traitement du fichier est terminé.\n";
    $import_result .= "\n";
    $import_result .= "Durée du traitement : ".$executionTime." sec.\n";
    $import_result .= "Nombre de données importées : ".$nb_data_import."\n";
    $import_result .= "Information(s) : \n".$import_result_details."\n";

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
    $text_import_result = $import_result;

    $resultFilename = $folder.$import_data['id_import'].'_ETL.txt';
    if(file_exists($resultFilename)){unlink($resultFilename);sleep(1);} // Supprimer le fichier existant s'il existe
    file_put_contents($resultFilename, mb_convert_encoding($text_import_result, 'ISO-8859-1', 'UTF-8')); // Ecrire le résultat de l'import dans un fichier texte

    // Enregistrement de l'action Export dans la base action
    $type_action = 33;
    $info_action = "Importation des données ETL - Fichier : ".$import_data['file_import']." - Station : ".$station_all_array[$import_data['id_station']]['nom_station'];


    $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure,id_import) 
                                    VALUES (".$import_data['id_user'].",'".$type_action."','".$info_action."','".$import_data['dateheure']."','".$idImport."')";
    tep_db_query($sql_link,$query);


    $tab_result = [
        'text' => $text_import_result,
        'nbData' => $nb_data_import
    ];

    
echo json_encode($tab_result);
    
	
?>