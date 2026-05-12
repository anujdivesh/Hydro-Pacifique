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
$id_meta_correct = $data['id_meta_correct'];

// -----------------------------------------
// Récupération des données dans les tables

$sql_meta_correction = "SELECT mc.id, mc.id_station, mc.id_typedata, mc.id_codequal, mc.info_correction
                        FROM ".TABLE_DATA_META_CORRECTION." mc
                        WHERE mc.id = ".$id_meta_correct;
$meta_correction_query = tep_db_query($sql_link,$sql_meta_correction);
$meta_correction_tab = tep_db_fetch_array($meta_correction_query);

$detail_correction_part = explode(':', $meta_correction_tab['info_correction']);
if(count($detail_correction_part) > 1) 
{
    // Récupérer la partie droite après les deux-points
    $detail_correction = trim($detail_correction_part[1]); // Enlever les espaces vides autour
    // Supprimer l'espace interne entre le nombre et l'unité
    $detail_correction = str_replace(' ', '', $detail_correction);
} else {
    // Si pas de ':', retourner une chaîne vide ou gérer comme vous le souhaitez
    $detail_correction_part = str_replace(' ', '', $meta_correction_tab['info_correction']);
}


// TABLE STATION
$sql_station = "SELECT DISTINCT id_station, nom_station, code_station, station_type, active_station
					FROM ".TABLE_STATION." 
				    WHERE id_station=".$meta_correction_tab['id_station'];
$station_query = tep_db_query($sql_link,$sql_station);
$station_tab = tep_db_fetch_array($station_query);

$code_station = $station_tab['code_station'];
$nom_station = htmlaccent(html_entity_decode($station_tab['nom_station'] ?? $default_string));

// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data
				  FROM ".TABLE_TYPE_DATA." 
				  WHERE id_data_type=".$meta_correction_tab['id_typedata'];
$type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
$type_chron_tab = tep_db_fetch_array($type_chron_query);

$init_type_data = $type_chron_tab['init_type_data'];
$nom_type_data = $type_chron_tab['nom_type_data'];

// TABLE QUALITE
$init_qualite_data = '';
if(tep_not_null($meta_correction_tab['id_codequal']))
{
    $sql_quality = "SELECT DISTINCT id_data_qualite, init_qualite_data
                    FROM ".TABLE_DATA_QUALITE."
                    WHERE id_data_qualite=".$meta_correction_tab['id_codequal'];
    $quality_query = tep_db_query($sql_link,$sql_quality);
    $quality_tab = tep_db_fetch_array($quality_query);

    $init_qualite_data = $type_chron_tab['init_qualite_data'];
}


// --------------------------------------
// Initialisation de variables locales

$todayTime = new DateTime(); // Crée un objet DateTime pour la date actuelle
$today_formatted = $todayTime->format('dmYHi');

// Spécifiez le nom du dossier à créer
$file_extension = 'csv';
$nom_station_filename = ucfirst(strtolower(nettoyerNomFichier($nom_station)));
$Filename = $code_station.'_'.$init_type_data.'_'.$detail_correction.'_'.$nom_station_filename.'.'.$file_extension;
$chemin_folder = 'data/export/temp';
$chemin_folder_process = '../../../' . $chemin_folder; 
$csvFilename = $chemin_folder_process.'/'.$Filename;

// Vérifiez si le dossier n'existe pas déjà
if (!is_dir($chemin_folder_process)) 
{
    // Créez le dossier avec les permissions appropriées (par exemple, 0755)
    mkdir($chemin_folder_process, 0755, true); // Le troisième paramètre true crée les dossiers parents si nécessaire
} 
else
{
    // Si le dossier existe, on le vide avant de créer un nouveau fichier
    $files = glob($chemin_folder_process . '/*'); // Obtenir tous les fichiers dans le dossier
        
    // Parcourir tous les fichiers et les supprimer
    foreach($files as $file) 
    {
        if (is_file($file)) 
        {
            unlink($file); // Supprimer le fichier
        }
    }
}



// --------------------------------------
// CREATION FICHIER

$total_time = 0;
$content = '';

$startTime = microtime(true); // Temps au début du script

    $sql_chron = "SELECT da.dateheure, da.valeur
                    FROM ".TABLE_DATA_ALL_CORRECTION." da
                    WHERE da.id_meta = ".$id_meta_correct." 
                    ORDER BY da.dateheure ASC";
    $data_chron_query = tep_db_query($sql_link, $sql_chron);
    while ($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
    {
        $dateTime_formatted = $data_chron_tab['dateheure'];

        // Ligne à écrire dans le fichier csv
        $content .= $dateTime_formatted.";".$data_chron_tab['valeur'].";".$init_qualite_data."\n";
    }
    file_put_contents($csvFilename, $content);    

    if(isset($data_chron_query)){mysqli_free_result($data_chron_query);} // Libération des ressources de résultat


// ---------------------------------------------------

$endTime = microtime(true); // Temps à la fin du script

// Calcul de la durée d'exécution en secondes
$executionTime = number_format($endTime - $startTime,1);

// Remplissage du tableau de retour
$responseData = array(
    'statut' => true,
    'executionTime' => $executionTime,
    'csvFile' => $Filename
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>