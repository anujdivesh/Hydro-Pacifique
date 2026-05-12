<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Procédure pour afficher dans un RA dans le bloc d'affichage
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
$id_ra = $dataInfo['id_ra'];

//---------------------------------------------------------------
// TABLE SQL - Recupération DATA

// TABLE STATION
$sql_station_all = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station, s.station_type
					FROM ".TABLE_STATION." s";
$station_all_query = tep_db_query($sql_link,$sql_station_all);
while ($station_all = tep_db_fetch_array($station_all_query))
{	
    $nom_station = htmlaccent(html_entity_decode($station_all['nom_station'] ?? $default_string));

	$station_all_array[$station_all['id_station']] = array('code_station' => $station_all['code_station'],
															'nom_station' => $nom_station,
															'station_type' => $station_all['station_type'],
															);
}

// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_color_background,type_graph 
                FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1 
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
while ($eq_type_tab = tep_db_fetch_array($eq_type_query))
{
	$eq_type_array[$eq_type_tab['id_eq_type']] = array('nom_eq_type' => $eq_type_tab['nom_eq_type'],
                                                        'unite_eq_type' => $eq_type_tab['unite_eq_type'],
                                                        'valeur_data_type' => $eq_type_tab['valeur_data_type'],
														'type_graph' => $eq_type_tab['type_graph'],
                                                        'type_color_border' => $eq_type_tab['type_color_border'],
                                                        'type_color_background' => $eq_type_tab['type_color_background'],
                                                    );
}


// -------------------------------------------------------------


// Initialisation Variables Globales
$tab_html = '';


// Requête d'accès aux RA
$sql_RA = "SELECT DISTINCT ra.id_ra, ra.id_station, ra.date_heure_ra, ra.id_eq_type
            FROM ".TABLE_DATA_RA." ra
            WHERE id_ra = ".$id_ra;       

$RA_query = tep_db_query($sql_link,$sql_RA);
$RA_tab = tep_db_fetch_array($RA_query);

    
    $id_station = $RA_tab['id_station'];
    $code_station = $station_all_array[$id_station]['code_station'];    
    $nom_station = $station_all_array[$id_station]['nom_station'];
    $info_station = $code_station.' - '.$nom_station;

    $date_heure_ra_tab =  explode(" ",$RA_tab['date_heure_ra']);
    $date_ra = dateus_fr($date_heure_ra_tab[0]);
    $heure_ra = $date_heure_ra_tab[1];
    $date_heure_ra_fr = $date_ra.' '.$heure_ra;

    
    $tab_html .= "<div id='cadre_view_del' style='width:500px;margin-top:100px;padding:0;background-color:#FBF9F1;' >";

        $tab_html .= "<p style='width:100%;height:30px;padding:5px 0;text-align:center;font-size:18px;font-weight:bold;color:#fff;background-color:#000;'>";
            $tab_html .= "Êtes vous sûr de vouloir supprimer ce RA ?";
        $tab_html .= "</p>\n";  

        $tab_html .= "<div style='float:left;width:100%;margin-top:25px;margin-left:10%;'>";

            $tab_html .= "<p style='width:100%;font-size:18px;'>";
                $tab_html .= "<span style='font-weight: bold;'>Station : </span>".$info_station;
            $tab_html .= "</p>\n";  
            $tab_html .= "<p style='width:100%;margin-top:15px;font-size:18px;'>";
                $tab_html .= "<span style='font-weight: bold;'>Date : </span>".$date_heure_ra_fr;
            $tab_html .= "</p>\n";  

        $tab_html .= "</div>";

        $tab_html .= "<div style='float:left;width:80%;margin-top:25px;margin-left:10%;'>";
        
                $tab_html .= "<div style='float:left;width:45%;'>";
                    $tab_html .= "<input type='submit' class='button' id='del_ra' name='del_ra' value='Supprimer' onClick='delRA(".$id_ra.");'>";
                $tab_html .= "</div>";

                $tab_html .= "<div style='float:left;width:45%;'>";
                    $tab_html .= "<input type='button' id='button_close' class='button_close' value='Annuler' onClick=\"document.getElementById('box_del_ra').style.display='none'\">";
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