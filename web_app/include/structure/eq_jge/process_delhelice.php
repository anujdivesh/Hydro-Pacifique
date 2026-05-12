<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant de supprimer une région géographique
Appelé depuis gestion_eq_jaugeage.php -> form_eq_jge_helices.php
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
$id_helice = $dataInfo['id_helice'];

// Initialisation Variables
$del_helice = true;
$message_info = '';

// Requête sur la table TABLE_REGION

$sql_helice = "SELECT DISTINCT id, num FROM ".TABLE_HELICE."
                                        WHERE id = ".$id_helice;
$helice_query = tep_db_query($sql_link,$sql_helice);
$helice_tab = tep_db_fetch_array($helice_query);

// Suppression de la Région
if(isset($helice_tab))
{
    // On vérifie si le helice n'est pas lié à un JGE déjà réalisé
    $sql_ctrl_helice_jge = "SELECT DISTINCT count(*) as nb_helice_jge FROM ".TABLE_DATA_JGE_BRAS." WHERE id_helice=".$id_helice;
    $ctrl_helice_jge_query = tep_db_query($sql_link,$sql_ctrl_helice_jge);
    $ctrl_helice_jge_tab = tep_db_fetch_array($ctrl_helice_jge_query);

    if($ctrl_helice_jge_tab['nb_helice_jge'] < 1)
    {
        $sql_delete_helice = "DELETE FROM " . TABLE_HELICE . " WHERE id = " . $id_helice;
        $result_delete = tep_db_query($sql_link, $sql_delete_helice);

        $message_info .= "L'Hélice - ".$helice_tab['num']." - a bien été supprimée.";
    }
    else
    {
        $del_helice = false;
        $message_info .= "L'Hélice - ".$helice_tab['num']." - n'a pas pû être supprimée.<br>";
        $message_info .= "Elle est liée à au moins un Jaugeage.";
    }
    
}
else
{
    $del_helice = false;
    $message_info .= "L'Hélice n'existe pas.";
}


// Remplissage du tableau de retour

$responseData = array(
    'del_helice' => $del_helice,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>