<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant de supprimer une Rivière
Appelé depuis gestion_geo.php -> form_geo_tournee.php
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
$id_tournee = $dataInfo['id_tournee'];

// Initialisation Variables
$del_tournee = true;
$message_info = '';

// Requête sur la table TABLE_REGION

$sql_tournee = "SELECT DISTINCT id, nom FROM ".TABLE_TOURNEE."
                                        WHERE id = ".$id_tournee;
$tournee_query = tep_db_query($sql_link,$sql_tournee);
$tournee_tab = tep_db_fetch_array($tournee_query);

// Suppression de la Rivière
if(isset($tournee_tab))
{   
    // On vérifie si la Tournee n'est pas liée à une Station
    $sql_ctrl_tournee_station = "SELECT DISTINCT count(*) as nb_station_tournee FROM ".TABLE_STATION_TO_TOURNEE." WHERE id_tournee=".$id_tournee;
    $ctrl_tournee_station_query = tep_db_query($sql_link,$sql_ctrl_tournee_station);
    $ctrl_tournee_station_tab = tep_db_fetch_array($ctrl_tournee_station_query);

    if($ctrl_tournee_station_tab['nb_station_tournee'] < 1)
    {
        $sql_delete_tournee = "DELETE FROM " . TABLE_TOURNEE . " WHERE id = " . $id_tournee;
        $result_delete = tep_db_query($sql_link, $sql_delete_tournee);

        $message_info .= "La Tournée - ".$tournee_tab['nom']." - a bien été supprimée.";
    }
    else
    {
        $del_tournee = false;
        $message_info .= "La Tournée - ".$tournee_tab['nom']." - n'a pas pû être supprimée.<br>";
        $message_info .= "Elle est liée à au moins une Station.";
    }
    
}
else
{
    $del_tournee = false;
    $message_info .= "La Tournée n'existe pas.";
}


// Remplissage du tableau de retour

$responseData = array(
    'del_tournee' => $del_tournee,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>