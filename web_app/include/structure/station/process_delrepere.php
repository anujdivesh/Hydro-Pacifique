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
$id_repere = $dataInfo['id_repere'];

// Initialisation Variables
$del_repere = true;
$message_info = '';

// Requête sur la table PIEZO_REPERE

$sql_repere = "SELECT DISTINCT r.id, r.date_debut_valid
				FROM ".TABLE_STATION_PIEZO_REPERE." r
                WHERE id = ".$id_repere;
$repere_query = tep_db_query($sql_link,$sql_repere);
$repere_tab = tep_db_fetch_array($repere_query);

// Suppression de la fiche Caractéristique dans la table PIEZO_CARACTERISTIQUE
if(isset($repere_tab))
{
    $sql_delete_repere = "DELETE FROM " . TABLE_STATION_PIEZO_REPERE . " WHERE id = " . $repere_tab['id'];
    $result_delete = tep_db_query($sql_link, $sql_delete_repere);
}
else{$del_repere = false;}


if($del_repere){$message_info = "Les infos de Repère du puits (Date début : ".dateus_fr($repere_tab['date_debut_valid']).") ont bien été supprimés.";}
else{$message_info = "Les infos n'ont pas pû être supprimées.";}


// Remplissage du tableau de retour

$responseData = array(
    'del_repere' => $del_repere,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>