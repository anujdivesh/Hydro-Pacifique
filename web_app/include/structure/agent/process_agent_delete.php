<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Procédure pour afficher un bloc de contrôle de supression
Processus asynchrone AJAX coté serveur
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
$id_agent = $dataInfo['id_agent'];
$id_user_agent = $dataInfo['id_user_agent'];

//---------------------------------------------------------------
// TABLE SQL - Recupération DATA



// -------------------------------------------------------------


// Initialisation Variables Globales
$type_action = 1; // Action Rapport d'activité
$dateheure_action =  date("Y-m-d H:i:s");
$msg_info = '';
$del = false;


// Requête d'accès à la table Agent
$sql_agent = "SELECT DISTINCT ag.id, ag.nom, ag.prenom
            FROM ".TABLE_AGENT." ag
            WHERE id = ".$id_agent;       

$agent_query = tep_db_query($sql_link,$sql_agent);
$agent_tab = tep_db_fetch_array($agent_query);

if(isset($agent_tab))
{   
    $nom = $agent_tab['nom'];
    $prenom = $agent_tab['prenom'];

    tep_db_query($sql_link,"DELETE FROM ".TABLE_AGENT." WHERE id=".$id_agent);
    
    $msg_info .= "<span style='font-size:16px;'>";
        $msg_info .= "La fiche Agent a bien été supprimée";
    $msg_info .= "</span>";
    $msg_info .= "<br><br>";

    $msg_info .= "Agent : ".$nom." ".$prenom;

    $del = true;

    // Enregistrement de l'action dans la table ACTION qui suit toutes les actions de la plateforme
                
    $info_action = "Suppression Agent <br>";
    $info_action .= "Agent : ".$nom." ".$prenom;
    $info_action = post_secure($sql_link,$info_action); 

    $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) VALUES (".$id_user_agent.",'".$type_action."','".$info_action."','".$dateheure_action."')";
    tep_db_query($sql_link,$query);
}
else
{
    $msg_info .= "<span style='font-size:16px;'>";
        $msg_info .= "Une erreur est survenue lors de la suppression de la fiche Agent.";
    $msg_info .= "</span>";
}

// Remplissage du tableau de retour

$responseData = array(
    'msg_info' => $msg_info,
    'del' => $del
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>