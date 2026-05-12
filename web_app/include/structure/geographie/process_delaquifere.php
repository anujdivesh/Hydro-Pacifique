<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant de supprimer une Rivière
Appelé depuis gestion_geo.php -> form_geo_aquifere.php
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
$id_aquifere = $dataInfo['id_aquifere'];

// Initialisation Variables
$del_aquifere = true;
$message_info = '';

// Requête sur la table TABLE_REGION

$sql_aquifere = "SELECT DISTINCT id, nom FROM ".TABLE_GEO_AQUIFERE."
                                        WHERE id = ".$id_aquifere;
$aquifere_query = tep_db_query($sql_link,$sql_aquifere);
$aquifere_tab = tep_db_fetch_array($aquifere_query);

// Suppression de la Rivière
if(isset($aquifere_tab))
{   
    // On vérifie si la aquifere n'est pas liée à une Station
    $sql_ctrl_aquifere_station = "SELECT DISTINCT count(*) as nb_station_aquifere FROM ".TABLE_STATION." WHERE id_aquifere=".$id_aquifere;
    $ctrl_aquifere_station_query = tep_db_query($sql_link,$sql_ctrl_aquifere_station);
    $ctrl_aquifere_station_tab = tep_db_fetch_array($ctrl_aquifere_station_query);

    if($ctrl_aquifere_station_tab['nb_station_aquifere'] < 1)
    {
        $sql_delete_aquifere = "DELETE FROM " . TABLE_GEO_AQUIFERE . " WHERE id = " . $id_aquifere;
        $result_delete = tep_db_query($sql_link, $sql_delete_aquifere);

        $message_info .= "L'Aquifere - ".$aquifere_tab['nom']." - a bien été supprimé.";
    }
    else
    {
        $del_aquifere = false;
        $message_info .= "L'Aquifere - ".$aquifere_tab['nom']." - n'a pas pû être supprimée.<br>";
        $message_info .= "Il est lié à au moins une Station.";
    }
    
}
else
{
    $del_aquifere = false;
    $message_info .= "L'Aquifere n'existe pas.";
}


// Remplissage du tableau de retour

$responseData = array(
    'del_aquifere' => $del_aquifere,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>