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
$id_caract = $dataInfo['id_caract'];

// Initialisation Variables
$del_caract = true;

// Requête sur la table PIEZO_CARACTERISTIQUE
$sql_caract = "SELECT DISTINCT c.id, c.date
				FROM ".TABLE_STATION_PIEZO_CARACTERISTIQUE." c
                WHERE id = ".$id_caract;
$caract_query = tep_db_query($sql_link,$sql_caract);
$caract_tab = tep_db_fetch_array($caract_query);

// Suppression de la fiche Caractéristique dans la table PIEZO_CARACTERISTIQUE
if(isset($caract_tab))
{
    $sql_delete_caract = "DELETE FROM " . TABLE_STATION_PIEZO_CARACTERISTIQUE . " WHERE id = " . $caract_tab['id'];
    $result_delete = tep_db_query($sql_link, $sql_delete_caract);
}
else{$del_caract = false;}


if($del_caract){$message_info = "Les infos de caractéristique du puits du ".dateus_fr($caract_tab['date'])." ont bien été supprimés.";}
else{$message_info = "Les infos n'ont pas pû être supprimées.";}


// Remplissage du tableau de retour

$responseData = array(
    'del_caract' => $del_caract,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>