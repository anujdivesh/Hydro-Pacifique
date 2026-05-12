<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant de supprimer une région géographique
Appelé depuis gestion_eq_jaugeage.php -> form_eq_jge_moulinets.php
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
$id_moulinet = $dataInfo['id_moulinet'];

// Initialisation Variables
$del_moulinet = true;
$message_info = '';

// Requête sur la table TABLE_REGION

$sql_moulinet = "SELECT DISTINCT id, num FROM ".TABLE_MOULINET."
                                        WHERE id = ".$id_moulinet;
$moulinet_query = tep_db_query($sql_link,$sql_moulinet);
$moulinet_tab = tep_db_fetch_array($moulinet_query);

// Suppression de la Région
if(isset($moulinet_tab))
{
    // On vérifie si le moulinet n'est pas lié à un JGE déjà réalisé
    $sql_ctrl_moulinet_jge = "SELECT DISTINCT count(*) as nb_moulinet_jge FROM ".TABLE_DATA_JGE_BRAS." WHERE id_moulinet=".$id_moulinet;
    $ctrl_moulinet_jge_query = tep_db_query($sql_link,$sql_ctrl_moulinet_jge);
    $ctrl_moulinet_jge_tab = tep_db_fetch_array($ctrl_moulinet_jge_query);

    if($ctrl_moulinet_jge_tab['nb_moulinet_jge'] < 1)
    {
        $sql_delete_moulinet = "DELETE FROM " . TABLE_MOULINET . " WHERE id = " . $id_moulinet;
        $result_delete = tep_db_query($sql_link, $sql_delete_moulinet);

        $message_info .= "Le Moulinet - ".$moulinet_tab['num']." - a bien été supprimé.";
    }
    else
    {
        $del_moulinet = false;
        $message_info .= "Le Moulinet - ".$moulinet_tab['num']." - n'a pas pû être supprimé.<br>";
        $message_info .= "Il est lié à au moins un Jaugeage.";
    }
    
}
else
{
    $del_moulinet = false;
    $message_info .= "Le Moulinet n'existe pas.";
}


// Remplissage du tableau de retour

$responseData = array(
    'del_moulinet' => $del_moulinet,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>