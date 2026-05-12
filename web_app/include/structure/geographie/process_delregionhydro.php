<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant de supprimer une Région Hydro
Appelé depuis gestion_geo.php -> form_geo_regionhydro.php
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
$id_regionhydro = $dataInfo['id_regionhydro'];

// Initialisation Variables
$del_regionhydro = true;
$message_info = '';

// Requête sur la table TABLE_REGION

$sql_regionhydro = "SELECT DISTINCT id, nom FROM ".TABLE_REGIONHYDRO."
                                        WHERE id = ".$id_regionhydro;
$regionhydro_query = tep_db_query($sql_link,$sql_regionhydro);
$regionhydro_tab = tep_db_fetch_array($regionhydro_query);

// Suppression de la Région
if(isset($regionhydro_tab))
{
    // On vérifie si la Région Hydro n'est pas liée à une Rivière
    $sql_ctrl_regionhydro_riviere = "SELECT DISTINCT count(*) as nb_riviere_regionhydro FROM ".TABLE_RIVIERE." WHERE id_regionhydro=".$id_regionhydro;
    $ctrl_regionhydro_riviere_query = tep_db_query($sql_link,$sql_ctrl_regionhydro_riviere);
    $ctrl_regionhydro_riviere_tab = tep_db_fetch_array($ctrl_regionhydro_riviere_query);
    
    // On vérifie si la Région Hydro n'est pas liée à une Station
    $sql_ctrl_regionhydro_station = "SELECT DISTINCT count(*) as nb_station_regionhydro FROM ".TABLE_STATION." WHERE id_regionhydro=".$id_regionhydro;
    $ctrl_regionhydro_station_query = tep_db_query($sql_link,$sql_ctrl_regionhydro_station);
    $ctrl_regionhydro_station_tab = tep_db_fetch_array($ctrl_regionhydro_station_query);

    if($ctrl_regionhydro_riviere_tab['nb_riviere_regionhydro'] < 1 && $ctrl_regionhydro_station_tab['nb_station_regionhydro'] < 1)
    {
        $sql_delete_regionhydro = "DELETE FROM " . TABLE_REGIONHYDRO . " WHERE id = " . $id_regionhydro;
        $result_delete = tep_db_query($sql_link, $sql_delete_regionhydro);

        $message_info .= "La Région Hydrologique - ".$regionhydro_tab['nom']." - a bien été supprimée.";
    }
    else
    {
        $del_regionhydro = false;
        $message_info .= "La Région Hydrologique - ".$regionhydro_tab['nom']." - n'a pas pû être supprimée.<br>";
        $message_info .= "Elle est liée à au moins une Rivière ou une Station.";
    }
    
}
else
{
    $del_regionhydro = false;
    $message_info .= "La Région Hydrologique n'existe pas.";
}


// Remplissage du tableau de retour

$responseData = array(
    'del_regionhydro' => $del_regionhydro,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>