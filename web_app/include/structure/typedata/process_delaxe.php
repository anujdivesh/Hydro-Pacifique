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
$id_axe = $dataInfo['id_axe'];

// Initialisation Variables
$del_axe = true;
$message_info = '';

// Requête sur la table TABLE_REGION

$sql_axe = "SELECT DISTINCT id, axe FROM ".TABLE_DATA_TYPE_AXE."
                                        WHERE id = ".$id_axe;                                       
$axe_query = tep_db_query($sql_link,$sql_axe);
$axe_tab = tep_db_fetch_array($axe_query);

// Suppression de la Région
if(isset($axe_tab))
{
    // On vérifie si l'axe n'est pas lié à un type de chronique 
    $sql_ctrl_axe_chron = "SELECT DISTINCT count(*) as nb_axe FROM ".TABLE_TYPE_DATA." WHERE axe_data=".$id_axe;
    $ctrl_axe_chron_query = tep_db_query($sql_link,$sql_ctrl_axe_chron);
    $ctrl_axe_chron_tab = tep_db_fetch_array($ctrl_axe_chron_query);

    if($ctrl_axe_chron_tab['nb_axe'] < 1)
    {
        $sql_delete_axe = "DELETE FROM " . TABLE_DATA_TYPE_AXE . " WHERE id = " . $id_axe;
        $result_delete = tep_db_query($sql_link, $sql_delete_axe);

        $message_info .= "L'Axe - ".$axe_tab['axe']." - a bien été supprimé.";
    }
    else
    {
        $del_axe = false;
        $message_info .= "L'Axe - ".$axe_tab['axe']." - n'a pas pû être supprimé.<br>";
        $message_info .= "Il est lié à au moins une Chronique.";
    }
    
}
else
{
    $del_axe = false;
    $message_info .= "L'Axe n'existe pas.";
}


// Remplissage du tableau de retour

$responseData = array(
    'del_axe' => $del_axe,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>