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

// pour solutionner les pb d'accents
header('Content-Type: text/html; charset=utf-8');

// connexion à la base de données	
$sql_link = mysqli_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE) or die('Impossible de se connecter à la base de données!');
mysqli_query ($sql_link,'SET NAMES UTF8');


// Récupération des données JSON envoyées depuis la requête AJAX
$jsonDataGraph = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataGraph = json_decode($jsonDataGraph, true);

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
$format_min_x = $date->format('Y-m-d');

$date = DateTime::createFromFormat('d-m-Y', $max_x);
$format_max_x = $date->format('Y-m-d');

// Chargement de table nécessaire au traitement de l'algorithme

// TABLE STATION
$sql_station = "SELECT DISTINCT s.id_station, s.nom_station, s.nom_court, s.code_station
				FROM ".TABLE_STATION." s
				WHERE s.id_station=".$cle_station;
$station_query = tep_db_query($sql_link,$sql_station);
$station_tab = tep_db_fetch_array($station_query);

    $id_station = $station_tab['id_station'];
    $nom_station = $station_tab['nom_station'];
    $code_station = $station_tab['code_station'];

// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_color_background, type_graph 
                FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1 
                AND id_eq_type = ".$type_station."
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
$eq_type_tab = tep_db_fetch_array($eq_type_query);

    $id_eq_type = $eq_type_tab['id_eq_type'];
    $nom_eq_type = $eq_type_tab['nom_eq_type'];
    $unite_eq_type = $eq_type_tab['unite_eq_type'];
    $valeur_data_type = $eq_type_tab['valeur_data_type'];
    $type_color_border = $eq_type_tab['type_color_border'];
    $type_color_background = $eq_type_tab['type_color_background'];
    $type_graph = $eq_type_tab['type_graph'];


// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, 
                                to_periode, id_chon_periode, traitement, type_graph
				    FROM ".TABLE_TYPE_DATA." 
                    WHERE id_data_type = ".$id_typedata."
				    ORDER BY init_type_data ASC";
$type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
$type_chron_tab = tep_db_fetch_array($type_chron_query);

    $init_type_data = $type_chron_tab['init_type_data'];
    $nom_type_data = $type_chron_tab['nom_type_data'];
    $axe_data = isset($type_chron_tab['axe_data']) ? $type_chron_tab['axe_data'] : '';
    $unite = $type_chron_tab['unite'];
    $to_periode = $type_chron_tab['to_periode'];
    $id_chon_periode = $type_chron_tab['id_chon_periode'];
    $traitement = $type_chron_tab['traitement'];
    $typegraph = $type_chron_tab['type_graph'];

// DATA TYPE AXE 
$sql_data_type_axe = "SELECT DISTINCT id, axe, unite 
                        FROM ".TABLE_DATA_TYPE_AXE."
                        WHERE id = ".$axe_data;
$data_type_axe_query = tep_db_query($sql_link,$sql_data_type_axe);
$data_type_axe_tab = tep_db_fetch_array($data_type_axe_query);

    $axe = isset($data_type_axe_tab['axe']) ? $data_type_axe_tab['axe'] : '';
    $typegruniteaph = isset($data_type_axe_tab['unite']) ? $data_type_axe_tab['unite'] : '';
        

// On parcours la table contenant toutes les chroniques à traiter

    $html_stats_title = "";
    
    $html_stats_title = "<p style='font-weight:bold;font-size:20px;margin-bottom:10px;'>"."Statistiques "."</p>".
                        "<span style='font-weight:bold;'>"."Station"." : </span>".$code_station." - ".$nom_station.
                        "<br>".
                        "<span style='font-weight:bold;'>"."Chonique"." : </span>".$init_type_data." - ".$nom_type_data                        
                        ;
                
    // ------------------------------
    $html_stats_menu = "";
    
    $html_stats_menu = "
                    
                    <button id='global' class='bstats active' style='margin-top:0px;' onClick=\"statsChron('global')\">
						Données générales
					</button>

					<button id='byyear' class='bstats' onClick=\"statsChron('byyear')\">
						Synthèse des données annuelles
					</button>

					<button id='bymonth' class='bstats' onClick=\"statsChron('bymonth')\">
						Synthèse des données mensuelles
					</button>

                    <button id='bydays' class='bstats' onClick=\"statsChron('bydays')\">
						Synthèse des données quotidiennes
					</button>
                    
                    ";
                    /*
					<button id='workbymonth' class='bstats' onClick=\"statsChron('workbymonth')\">
						Analyse des données mensuelles
					</button>
                    
                    "
                    ;
                    */
    

    // ------------------------------
    $html_stats_general = "";
    
    $html_stats_general = " 

                            <p style='margin-left: 10px;font-size:13px;'>
                                <span style='font-weight:bold;'>Période évaluée : </span>
                                du ".$min_x." au ".$max_x."
                            </p>

                            <p style='margin-top:5px;margin-left:10px;font-size:13px;'>
                                <span style='font-weight:bold;'>Données : </span>
                                ".$axe." (".$unite.")
                            </p>
                    
                        "
                        ;

                        

$responseData = array(
    'html_stats_title' => $html_stats_title,
    'html_stats_menu' => $html_stats_menu,
    'html_stats_general' => $html_stats_general
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>