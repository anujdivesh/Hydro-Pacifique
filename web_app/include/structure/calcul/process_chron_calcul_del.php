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
$id_meta = $dataInfo['id_meta'];

// Initialisation Variables
$id_correction = 0;
$msg_del = '';

// Requête sur la table META_CORRECTION pour récupérer les informations avant la suppression
$sql_meta_correction = "SELECT id, obs, id_correction, datetime_first, datetime_end
				FROM ".TABLE_DATA_META_CORRECTION." 
				WHERE id = ".$id_meta;
$meta_correction_query = tep_db_query($sql_link,$sql_meta_correction);
$meta_correction_tab = tep_db_fetch_array($meta_correction_query);

// Suppression de la correction sélectionnée s'il elle existe
if(isset($meta_correction_tab))
{
    $id_correction = $meta_correction_tab['id_correction'];

    $datetime_first = $meta_correction_tab['datetime_first'];
    $datetime_first_tab = explode(' ',$datetime_first);
    $datetime_first_formated = dateus_fr($datetime_first_tab[0]).' '.$datetime_first_tab[1];

    $datetime_end = $meta_correction_tab['datetime_end'];
    $datetime_end_tab = explode(' ',$datetime_end);
    $datetime_end_formated = dateus_fr($datetime_end_tab[0]).' '.$datetime_end_tab[1];

    $sql_data_correction = "DELETE FROM " . TABLE_DATA_ALL_CORRECTION . " WHERE id_meta = " . $id_meta;
    tep_db_query($sql_link, $sql_data_correction);
    
    $sql_meta_correction = "DELETE FROM " . TABLE_DATA_META_CORRECTION . " WHERE id = " . $id_meta;
    tep_db_query($sql_link, $sql_meta_correction);

    $msg_del = "La correction a bien été supprimée : 
                <br>".
                $meta_correction_tab['obs']."
                <br>
                Période : ".$datetime_first_formated." - ".$datetime_end_formated;
}
else{$msg_del = "La suppression de la correction n'a pas pû être réalisée";}


// Rmeplissage du tableau de retour

$responseData = array(
    'id_correction' => $id_correction,
    'msg_del' => $msg_del  
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;


?>