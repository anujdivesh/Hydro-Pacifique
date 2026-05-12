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
$id_chron = $dataInfo['id_typedata'];

// Initialisation Variables
$del_chron = true;
$message_info = '';

// Requête sur la table TABLE_REGION

$sql_chronique = "SELECT DISTINCT id_data_type, init_type_data FROM ".TABLE_TYPE_DATA."
                                        WHERE id_data_type = ".$id_chron;                                       
$chronique_query = tep_db_query($sql_link,$sql_chronique);
$chronique_tab = tep_db_fetch_array($chronique_query);

// Suppression de la Région
if(isset($chronique_tab))
{
    // On vérifie si le type de chronique n'est pas liée à une META DONNEE
    $sql_ctrl_chron_meta = "SELECT DISTINCT count(*) as nb_chron FROM ".TABLE_DATA_META." WHERE id_typedata=".$id_chron;
    $ctrl_chron_meta_query = tep_db_query($sql_link,$sql_ctrl_chron_meta);
    $ctrl_chron_meta_tab = tep_db_fetch_array($ctrl_chron_meta_query);

    if($ctrl_chron_meta_tab['nb_chron'] < 1)
    {
        $sql_delete_chron = "DELETE FROM " . TABLE_TYPE_DATA . " WHERE id_data_type = " . $id_chron;
        $result_delete = tep_db_query($sql_link, $sql_delete_chron);

        $message_info .= "Le type de Chronique - ".$chronique_tab['init_type_data']." - a bien été supprimé.";
    }
    else
    {
        $del_chron = false;
        $message_info .= "Le type de Chronique - ".$chronique_tab['init_type_data']." - n'a pas pû être supprimé.<br>";
        $message_info .= "Il est lié à au moins une données.";
    }
    
}
else
{
    $del_chron = false;
    $message_info .= "Le type de Chronique n'existe pas.";
}


// Remplissage du tableau de retour

$responseData = array(
    'del_typedata' => $del_chron,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>