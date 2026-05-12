<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant de supprimer un Code Qualité
Ce n'est possible que si le code qualité n'a jamais été utilisé sur une données
Appelé depuis gestion_quality_data.php -> form_qualitydata.php
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
$id_qualitydata = $dataInfo['id_qualitydata'];

// Initialisation Variables
$del_qualitydata = true;
$message_info = '';

// Requête sur la table TABLE_REGION

$sql_qualitydata = "SELECT DISTINCT id_data_qualite, init_qualite_data FROM ".TABLE_DATA_QUALITE."
                                        WHERE id_data_qualite = ".$id_qualitydata;
$qualitydata_query = tep_db_query($sql_link,$sql_qualitydata);
$qualitydata_tab = tep_db_fetch_array($qualitydata_query);

// Suppression de le code qualité
if(isset($qualitydata_tab))
{
    // On vérifie si le Code Qualité a déjà été utilisé dans la table DATA_META ou dans la table DATA_ETL_DATA
	$sql_verif_del_qd = "SELECT EXISTS (
                                    SELECT 1 FROM (
                                        SELECT 1 FROM ".TABLE_DATA_META." WHERE id_codequal = ".$id_qualitydata."
                                        UNION
                                        SELECT 1 FROM ".TABLE_DATA_ETL_DATA." WHERE code_qualite = ".$id_qualitydata."
                                    ) AS subquery
                                    LIMIT 1
                                ) AS id_cq_exists";

    $verif_del_qd_query = tep_db_query($sql_link,$sql_verif_del_qd);
    $verif_del_qd = tep_db_fetch_array($verif_del_qd_query);

    if($verif_del_qd['id_cq_exists'] != 1)	// Si le Code Qualité est utilisé
    {
        $sql_delete_qualitydata = "DELETE FROM " . TABLE_DATA_QUALITE . " WHERE id_data_qualite = " . $id_qualitydata;
        $result_delete = tep_db_query($sql_link, $sql_delete_qualitydata);

        $message_info .= "Le Code Qualité - ".$qualitydata_tab['init_qualite_data']." - a bien été supprimé.";
    }
    else
    {
        $del_qualitydata = false;
        $message_info .= "Le Code Qualité - ".$qualitydata_tab['init_qualite_data']." - n'a pas pû être supprimé.<br>";
        $message_info .= "Il est lié à au moins une donnée.";
    }
    
}
else
{
    $del_qualitydata = false;
    $message_info .= "Le Code Qualité n'existe pas.";
}


// Remplissage du tableau de retour

$responseData = array(
    'del_qualitydata' => $del_qualitydata,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>