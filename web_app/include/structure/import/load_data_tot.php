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


$import_result = '';
$import_result_details = '';
$nb_data_import = 0;

$nb_warning_qualite = 0;

$num_ligne = 0;

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

    // Lecture et Vérification des données avant chargement dans la base 
    if($import_data['file_ext'] == 'csv')
    {
        $chemin_file = $folder.'files/'.$import_data['file_import'];

        
        // Ouvrir le fichier CSV en mode lecture
        if (($handle = fopen($chemin_file, 'r')) !== false) 
        {   
            // Lire chaque ligne du fichier CSV
            while (($data = fgetcsv($handle, 10000, ';')) !== false) 
            {
                $import_valid = true;

                $num_ligne++;
                
                // Colonne 1 (date)
                $data_date='';

                if(isset($data[0]))
                {   
                    // Vérifier et retirer le BOM sur la première ligne - Il peut y avoir un caractère invisible qui bloc le processus
                    $dateString = $data[0];
                    if ($num_ligne === 1 && isset($row[0]) && substr($dateString, 0, 3) === "\xEF\xBB\xBF") 
                    {
                        // Enlever le BOM
                        $dateString = substr($dateString, 3);
                    }
                    
                    $data_date = isValidDateImport($dateString);
                    if($data_date == 'Invalid')
                    {
                        $import_valid = false; 
                        $import_result_details .= "La date '".$datetimeEndString."' n'est pas dans un bon format dd/mm/yyyy hh:mm:ss.\n";
                        break;
                    }
                }
                else
                {
                    $import_valid = false;  
                    $import_result_details .= "Au moins une date n'est pas renseignée.\n";
                    break;
                }



                // Colonne 2 (valeur début)
                $data_valeurDebut='null';
                if($import_valid)
                {
                    if($data[1] !== "" && is_numeric($data[1]))
                    {                        
                        $data_valeurDebut = (int)$data[1];
                    }
                    else
                    {
                        $import_valid = false; 
                        $import_result_details .= "Au moins une valeur de la 2de colonne n'est pas dans un format numérique.\n";
                        break;
                    }
                }
                
                // Colonne 3 (valeur fin)
                $data_valeurFin='null';
                if($import_valid)
                {
                    if ($data[2] !== "" && is_numeric($data[2]))
                    {                        
                        $data_valeurFin = (int)$data[2];
                    }
                    else
                    {
                        $import_valid = false;    
                        $import_result_details .= "Au moins une valeur de la 3eme colonne n'est pas dans un format numérique.\n";
                        break;
                    }
                }   

                // Colonne 4 (ecart précédent)
                $data_ecartPrecedent='null';
                if($import_valid)
                {
                    if($data[3] !== "" )
                    {
                        if (is_numeric($data[3]))
                        {                        
                            $data_ecartPrecedent = (int)$data[3];
                        }
                        else
                        {
                            $import_valid = false;   
                            $import_result_details .= "Au moins une valeur de la 4eme colonne n'est pas dans un format numérique.\n";
                            break;
                        }
                    }
                    else
                    {
                        $data_ecartPrecedent = 99999; // Si la valeur d'écart n'est pas renseignée, on l'a met à 0
                    }
                }   

                // Colonne 5 (Champs observation)
                $data_obs = '';
                if($import_valid)
                {
                    if(isset($data[4]))
                    {
                        $data_obs = mb_convert_encoding($data[4], 'UTF-8', 'ISO-8859-1');  // Conversion en UTF-8 si nécessaire
                    }
                }   

                // Colonne 6 (Code Qualité)
                $id_data_qualite='null';
                if($import_valid)
                {
                    $quality_ok = false;

                    if(isset($data[5]))
                    {
                        if(isset($quality_data_array[$data[5]])) // si l'intitulé du code qualité est enregistrée dans la base
                        {
                            $id_data_qualite = $quality_data_array[$data[5]]['id_data_qualite'];
                        }
                        else
                        {
                            $nb_warning_qualite++;
                        }
                    }
                }
                
                // Enregistrement de la ligne si elle est valide dans les tables avant import dans la base
                if($import_valid)
                {
                    if($num_ligne == 1){$date_debut = $data_date;} // si on lit la première ligne on note la date
                    $date_fin = $data_date; // on met à jour la date de fin si la ligne est valide 

                    // Les données à importer sont mises dans un tableau
                    $data_tab[] = array('dateheure' => $data_date,
                                    'valeurDebut' => $data_valeurDebut,  
                                    'valeurFin' => $data_valeurFin,  
                                    'ecartPrecedent' => $data_ecartPrecedent, 									
                                    'obs' => $data_obs,      					
                                    'qualite' => $id_data_qualite	
                                    );   
                    
                    
                }
            }
            
            fclose($handle); // Fermer le fichier CSV à importer
        }
    }    

   
    // Enregistrement dans les bases
    if($import_valid)
    {        
        // Démarrer la transaction - Enregistrement des données TABLE_DATA_TOT
      
        mysqli_begin_transaction($sql_link, MYSQLI_TRANS_START_READ_WRITE);

        try
        {
            // Étape 1 : Supprimer les données liées dans TABLE_DATA_ETL_DATA
            // On efface d'abord les données entre Date_Debut et Date_Fin chronique, qu'elles existent ou pas
            $sql_delete_tot_data = "DELETE FROM ".TABLE_DATA_TOT." WHERE id_station = ".$import_data['id_station'].
                                " AND date_heure >= '".$date_debut."' AND date_heure <= '".$date_fin."'";
            tep_db_query($sql_link,$sql_delete_tot_data);
            $rows_deleted = mysqli_affected_rows($sql_link);

            // Étape 2 : Supprimer les données liées dans TABLE_DATA_ETL_DATA
            $query_insert_tot_data = "INSERT INTO ".TABLE_DATA_TOT." 
                                        (`id_station`,`date_heure`,`valeurDebut`,`valeurFin`,`ecartPrecedent`,`obs`,`id_data_qualite`)
                                        VALUES (?,?,?,?,?,?,?)";
            $stmt_tot_data = mysqli_prepare($sql_link, $query_insert_tot_data);

            foreach ($data_tab as $row) 
            { 
                mysqli_stmt_bind_param(
                    $stmt_tot_data,
                    "isdddsi",
                    $import_data['id_station'],
                    $row['dateheure'],
                    $row['valeurDebut'],
                    $row['valeurFin'],
                    $row['ecartPrecedent'],
                    $row['obs'],
                    $row['qualite']
                );
                mysqli_stmt_execute($stmt_tot_data);
                
                $nb_data_import++;
            }
            
            // Étape 3 : Commit de la transaction si tout est correct
            mysqli_commit($sql_link);
            $import_result_details .= "Les données ont bien été mises à jour.\n";
       
        }
        catch (Exception $e) 
        {
            // Annuler la transaction en cas d'erreur
            mysqli_rollback($sql_link);

            // Afficher un message d'erreur
            $import_result_details .= "Erreur lors de l'exécution de la transaction : " . $e->getMessage();
        } finally 
        {
            if (isset($stmt_tot_data) && $stmt_tot_data instanceof mysqli_stmt) {
                mysqli_stmt_close($stmt_tot_data);
            }
        }
    }

$endTime = microtime(true); // Temps à la fin du script
$executionTime = number_format($endTime - $startTime,1);
        
    // Information Load File
        
    $import_result .= "\n----\n";
    $import_result .= "Fichier : ".$import_data['file_import']."\n";
    $import_result .= "Station : ".$station_all_array[$import_data['id_station']]['nom_station']."\n";
    $import_result .= "Chronique : TOT\n";
    $import_result .= "\n";
        
    $import_result .= "Le traitement du fichier est terminé.\n";
    $import_result .= "\n";
    $import_result .= "Durée du traitement : ".$executionTime." sec.\n";
    $import_result .= "Nombre de données importées : ".$nb_data_import."\n";
    if($rows_deleted > 0)
    {
        $import_result .= "Nombre de données supprimées : ".$rows_deleted."\n";
    }    
    $import_result .= "Information(s) : \n".$import_result_details;

    /* A voir plus tard
    if($nb_warning_qualite>0)
    {
        $import_result .= "Warning sur le code qualité (colonne 6) qui n'est pas référencé ou n'est pas renseigné : ".$nb_warning_qualite." ligne(s) concernée(s).\n";
    }
    */         
    $date_debut_tab = explode(" ", $date_debut);
    $import_result .= "Date de début de la chronique : ".dateus_fr($date_debut_tab[0])."\n";
    $date_fin_tab = explode(" ", $date_fin);
    $import_result .= "Date de fin de la chronique : ".dateus_fr($date_fin_tab[0])."\n";

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

    
    $resultFilename = $folder.$import_data['id_import'].'_TOT.txt';
    if(file_exists($resultFilename)){unlink($resultFilename);sleep(1);} // Supprimer le fichier existant s'il existe
    file_put_contents($resultFilename, mb_convert_encoding($text_import_result, 'ISO-8859-1', 'UTF-8')); // Ecrire le résultat de l'import dans un fichier texte


    // Enregistrement de l'action Export dans la base action
    $type_action = 4;
    $info_action = "Importation des données TOT - Fichier : ".$import_data['file_import']." - Station : ".$station_all_array[$import_data['id_station']]['nom_station'];


    $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure,id_import) 
                                    VALUES (".$import_data['id_user'].",'".$type_action."','".$info_action."','".$import_data['dateheure']."','".$idImport."')";
    tep_db_query($sql_link,$query);

    $tab_result = [
        'text' => $import_result,
        'nbData' => $nb_data_import
    ];


echo json_encode($tab_result);
					

?>
