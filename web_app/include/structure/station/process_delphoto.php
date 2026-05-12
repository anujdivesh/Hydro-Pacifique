<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant d'affichage des photos des stations
Protocole sur serveur (AJAX) 
Appelé depuis include/structure/form_station_photos.php
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
$jsonDataInfo = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataInfo = json_decode($jsonDataInfo, true);

// Accéder aux données du tableau récupérer
$id_photo = $dataInfo['id_photo'];


// Initialisation Variables
$dossier_photos = '../../../'.DIR_WS_DATA_PHOTOS;
$image_path = '';
$del_img = true;

// Requête sur la table photos de la station 
$sql_photo = "SELECT DISTINCT id, id_station, date_photo, file_photo
				FROM ".TABLE_STATION_PHOTOS."
                WHERE id = ".$id_photo;
$photo_query = tep_db_query($sql_link,$sql_photo);
$photo_tab = tep_db_fetch_array($photo_query);

// Suppression de l'image dans la table
if(isset($photo_tab))
{
    // Chemin de l'image
    $image_path = $dossier_photos.$photo_tab['file_photo'];
     
    $sql_delete_photo = "DELETE FROM " . TABLE_STATION_PHOTOS . " WHERE id = " . $id_photo;
    $result_delete = tep_db_query($sql_link, $sql_delete_photo);
}
else{$del_img = false;}


// Suppression du fichier image si il existe
if(is_file($image_path)){unlink($image_path);}
else{$del_img = false;}

if($del_img){$message_info = "Le fichier photo ".$photo_tab['file_photo']." a bien été supprimé.";}
else{$message_info = "Le fichier photo n'a pas pû être supprimé.";}

echo $message_info;


?>