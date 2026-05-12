<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export 
- Ce script permet d'écrire dans un fichier les infos sur l'export
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
$text_result = $data['text_result'];
$id_user = $data['id_user'];
$date_export = $data['date_export'];
$folder_download = $data['folder_download'];
$chemin_folder = $data['chemin_folder'];


// Spécifiez le nom du dossier à créer
$chemin_folder_process = '../../../'.DIR_WS_DATA_EXPORT;
$resultFilename = $chemin_folder_process.'/'.$folder_download.'.txt';
// --------------------------------------
// CREATION FICHIER

file_put_contents($resultFilename, mb_convert_encoding($text_result, 'ISO-8859-1', 'UTF-8'));

// Enregistrement de l'action Export dans la base action
$type_action = 36;
$info_action = htmlaccent('Exportation des données');

$query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure,file_export) VALUES (".$id_user.",'".$type_action."','".$info_action."','".$date_export."','".$folder_download.".tar')";
tep_db_query($sql_link,$query);

    
?>