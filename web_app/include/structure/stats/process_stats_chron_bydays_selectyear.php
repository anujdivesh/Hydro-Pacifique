<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export 
- Ce script permet d'écrire dans un fichier les infos sur l'export
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

require('../../function/math.php');

// pour solutionner les pb d'accents
header('Content-Type: text/html; charset=utf-8');

// connexion à la base de données	
$sql_link = mysqli_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE) or die('Impossible de se connecter à la base de données!');
mysqli_query ($sql_link,'SET NAMES UTF8');


// Récupération des données JSON envoyées depuis la requête AJAX
$jsonData = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataGraph = json_decode($jsonData, true);

// Accéder aux données du tableau récupérer
$territoire_id = $dataGraph['territoireId'];
$lang = $dataGraph['lang'];

// RECUPERATION DU TEXT - POUR LA TRADUCTION
require('../../text_content_'.$lang.'.php');

$cle_station = $dataGraph['cle_station'];
$type_station = $dataGraph['type_station'];
$id_typedata = $dataGraph['id_typedata'];
$min_x = $dataGraph['min_x'];
$max_x = $dataGraph['max_x'];

$date = DateTime::createFromFormat('d-m-Y', $min_x);
$year_min_x = $date->format('Y');

$date = DateTime::createFromFormat('d-m-Y', $max_x);
$year_max_x = $date->format('Y');


$html_stats = "";

if($year_min_x <= $year_max_x)
{
    $html_stats .= "
                
                <p class='info_stats'>
                    "."Synthèse des données quotidiennes"."
                </p>

                

                <select id='yearSelect' style='float:left;width:65px;margin-left:10.5%;font-size:13px;' onchange='statsChronDays(this.value);'>";

                    for ($year = $year_max_x; $year >= $year_min_x; $year--) 
                    {
                        $html_stats .= "<option value='$year'>$year</option>";
                    }

    $html_stats .= "
                    
                </select>

                <div style='float:left;margin-left:10px;padding-top:0px;'>

                        <img src='".DIR_WS_IMG_ICO."arrow_previous.png' style='width:20px;cursor:pointer;' 
                                title='"."Next Year"."'
                                onclick='prevYear()'
                                onmouseover=\"this.src='".DIR_WS_IMG_ICO."arrow_previous_over.png';\" onmouseout=\"this.src='".DIR_WS_IMG_ICO."arrow_previous.png';\" >


                    <img src='".DIR_WS_IMG_ICO."arrow_next.png' style='width:20px;margin-left:15px;cursor:pointer;' 
                            title='"."Next Year"."'
                            onclick='nextYear()'
                            onmouseover=\"this.src='".DIR_WS_IMG_ICO."arrow_next_over.png';\" onmouseout=\"this.src='".DIR_WS_IMG_ICO."arrow_next.png';\" >
                

                </div>

                <hr>

                <div id='contenu_stats_days' ></div>

                    
                ";
}

/*
                <button onclick='nextYear()' style='width:30px;'>&gt;</button>
                <button onclick='prevYear()' style='height:30px;margin-left:5px;'>&lt;</button>
                */
    

    $stat_graph = true; 
    $html_stats_graph = '';
    $js_graph = '';    




$responseData = array(
    'html_stats' => $html_stats,
    'stat_graph' => $stat_graph,
    'html_stats_graph' => $html_stats_graph,
    'js_graph' => $js_graph
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>