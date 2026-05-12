<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant de supprimer une région géographique
Appelé depuis gestion_eq_jaugeage.php -> form_eq_jge_saumons.php
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
$id_saumon = $dataInfo['id_saumon'];

// Initialisation Variables
$del_saumon = true;
$message_info = '';

// Requête sur la table TABLE_REGION

$sql_saumon = "SELECT DISTINCT id, num FROM ".TABLE_SAUMON."
                                        WHERE id = ".$id_saumon;
$saumon_query = tep_db_query($sql_link,$sql_saumon);
$saumon_tab = tep_db_fetch_array($saumon_query);

// Suppression de la Région
if(isset($saumon_tab))
{
    // On vérifie si le saumon n'est pas lié à un JGE déjà réalisé
    $sql_ctrl_saumon_jge = "SELECT DISTINCT count(*) as nb_saumon_jge FROM ".TABLE_DATA_JGE_BRAS." WHERE id_saumon=".$id_saumon;
    $ctrl_saumon_jge_query = tep_db_query($sql_link,$sql_ctrl_saumon_jge);
    $ctrl_saumon_jge_tab = tep_db_fetch_array($ctrl_saumon_jge_query);

    if($ctrl_saumon_jge_tab['nb_saumon_jge'] < 1)
    {
        $sql_delete_saumon = "DELETE FROM " . TABLE_SAUMON . " WHERE id = " . $id_saumon;
        $result_delete = tep_db_query($sql_link, $sql_delete_saumon);

        $message_info .= "Le Saumon - ".$saumon_tab['num']." - a bien été supprimé.";
    }
    else
    {
        $del_saumon = false;
        $message_info .= "Le Saumon - ".$saumon_tab['num']." - n'a pas pû être supprimé.<br>";
        $message_info .= "Il est lié à au moins un Jaugeage.";
    }
    
}
else
{
    $del_saumon = false;
    $message_info .= "Le Saumon n'existe pas.";
}


// Remplissage du tableau de retour

$responseData = array(
    'del_saumon' => $del_saumon,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>