<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant de supprimer une commune
Appelé depuis gestion_geo.php -> form_geo_commune.php
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
$id_commune = $dataInfo['id_commune'];

// Initialisation Variables
$del_commune = true;
$message_info = '';

// Requête sur la table TABLE_REGION

$sql_commune = "SELECT DISTINCT id_commune, nom_commune FROM ".TABLE_COMMUNE."
                                        WHERE id_commune = ".$id_commune;
$commune_query = tep_db_query($sql_link,$sql_commune);
$commune_tab = tep_db_fetch_array($commune_query);

// Suppression de la Région
if(isset($commune_tab))
{
    // On vérifie si la Commune n'est pas liée à une Station
    $sql_ctrl_commune_station = "SELECT DISTINCT count(*) as nb_station_commune FROM ".TABLE_STATION." WHERE id_commune=".$id_commune;
    $ctrl_commune_station_query = tep_db_query($sql_link,$sql_ctrl_commune_station);
    $ctrl_commune_station_tab = tep_db_fetch_array($ctrl_commune_station_query);

    if($ctrl_commune_station_tab['nb_station_commune'] < 1)
    {
        $sql_delete_commune = "DELETE FROM " . TABLE_COMMUNE . " WHERE id_commune = " . $id_commune;
        $result_delete = tep_db_query($sql_link, $sql_delete_commune);

        $message_info .= "La Commune - ".$commune_tab['nom_commune']." - a bien été supprimée.";
    }
    else
    {
        $del_commune = false;
        $message_info .= "La Commune - ".$commune_tab['nom_commune']." - n'a pas pû être supprimée.<br>";
        $message_info .= "Elle est liée à au moins une Station.";
    }
    
}
else
{
    $del_commune = false;
    $message_info .= "La Commune n'existe pas.";
}


// Remplissage du tableau de retour

$responseData = array(
    'del_commune' => $del_commune,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>