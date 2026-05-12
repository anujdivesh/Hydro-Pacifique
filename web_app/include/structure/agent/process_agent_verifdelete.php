<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Procédure pour afficher dans un bloc de vérification pour la suppression d'une fiche agent
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

//---------------------------------------------------------------
// TABLE SQL - Recupération DATA

// -------------------------------------------------------------


// Initialisation Variables Globales
$tab_html = '';

// Requête d'accès à la table Agent
$sql_agent = "SELECT DISTINCT ag.id, ag.nom, ag.prenom
            FROM ".TABLE_AGENT." ag
            WHERE id = ".$id_agent;       

$agent_query = tep_db_query($sql_link,$sql_agent);
$agent_tab = tep_db_fetch_array($agent_query);

    $nom = $agent_tab['nom'];
    $prenom = $agent_tab['prenom'];

    
    $tab_html .= "<div id='cadre_view_del' style='width:600px;margin-top:100px;padding:0;background-color:#FBF9F1;' >";

        $tab_html .= "<p style='width:100%;height:40px;padding:5px 0;text-align:center;font-size:24px;font-weight:bold;color:#fff;background-color:#000;'>";
            $tab_html .= "Êtes vous sûr de vouloir supprimer cette fiche Agent ?";
        $tab_html .= "</p>\n";  

        $tab_html .= "<div style='float:left;width:100%;margin-top:25px;margin-left:10%;'>";

            $tab_html .= "<p style='width:100%;font-size:18px;'>";
                $tab_html .= "<span style='font-weight: bold;'>Nom : </span>".$nom;
            $tab_html .= "</p>\n";  
            $tab_html .= "<p style='width:100%;margin-top:15px;font-size:18px;'>";
                $tab_html .= "<span style='font-weight: bold;'>Prénom : </span>".$prenom;
            $tab_html .= "</p>\n";  

        $tab_html .= "</div>";

        $tab_html .= "<div style='float:left;width:80%;margin-top:25px;margin-left:10%;'>";
        
                $tab_html .= "<div style='float:left;width:45%;'>";
                    $tab_html .= "<input type='submit' class='button' id='del_agent' name='del_agent' value='Supprimer' onClick='delAgent(".$id_agent.");'>";
                $tab_html .= "</div>";

                $tab_html .= "<div style='float:left;width:45%;'>";
                    $tab_html .= "<input type='button' id='button_close' class='button_close' value='Annuler' onClick=\"document.getElementById('box_del_agent').style.display='none'\">";
                $tab_html .= "</div>";
            
        $tab_html .= "<hr>";
        $tab_html .= "</div>";
    
    $tab_html .= "<hr>";
    $tab_html .= "</div>";


// Remplissage du tableau de retour

$responseData = array(
    'tab_html' => $tab_html
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>