<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant de supprimer une région géographique
Appelé depuis gestion_geo.php -> pform_geo_regiongeo.php
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
$id_region = $dataInfo['id_region'];

// Initialisation Variables
$del_region = true;
$message_info = '';

// Requête sur la table TABLE_REGION

$sql_regiongeo = "SELECT DISTINCT id_region, nom_region FROM ".TABLE_REGION."
                                        WHERE id_region = ".$id_region;
$regiongeo_query = tep_db_query($sql_link,$sql_regiongeo);
$regiongeo_tab = tep_db_fetch_array($regiongeo_query);

// Suppression de la Région
if(isset($regiongeo_tab))
{
    // On vérifie si la région n'est pas liée à une Commune ou à une Station
    $sql_ctrl_region_commune = "SELECT DISTINCT count(*) as nb_commune_region FROM ".TABLE_COMMUNE." WHERE id_region=".$id_region;
    $ctrl_region_commune_query = tep_db_query($sql_link,$sql_ctrl_region_commune);
    $ctrl_region_commune_tab = tep_db_fetch_array($ctrl_region_commune_query);

    $sql_ctrl_region_station = "SELECT DISTINCT count(*) as nb_station_region FROM ".TABLE_STATION." WHERE id_region=".$id_region;
    $ctrl_region_station_query = tep_db_query($sql_link,$sql_ctrl_region_station);
    $ctrl_region_station_tab = tep_db_fetch_array($ctrl_region_station_query);

    if($ctrl_region_commune_tab['nb_commune_region'] < 1 && $ctrl_region_station_tab['nb_station_region'] < 1)
    {
        $sql_delete_region = "DELETE FROM " . TABLE_REGION . " WHERE id_region = " . $id_region;
        $result_delete = tep_db_query($sql_link, $sql_delete_region);

        $message_info .= "La Région Géographique - ".$regiongeo_tab['nom_region']." - a bien été supprimée.";
    }
    else
    {
        $del_region = false;
        $message_info .= "La Région Géographique - ".$regiongeo_tab['nom_region']." - n'a pas pû être supprimée.<br>";
        $message_info .= "Elle est liée à au moins une Commune ou à une Station.";
    }
    
}
else
{
    $del_region = false;
    $message_info .= "La Région Géographique n'existe pas.";
}


// Remplissage du tableau de retour

$responseData = array(
    'del_region' => $del_region,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>