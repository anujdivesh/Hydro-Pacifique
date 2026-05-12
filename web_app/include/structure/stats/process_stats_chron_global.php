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
$format_min_x = $date->format('Y-m-d');

$date = DateTime::createFromFormat('d-m-Y', $max_x);
$format_max_x = $date->format('Y-m-d');



// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_color_background, type_graph 
                FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1 
                AND id_eq_type = ".$type_station."
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
$eq_type_tab = tep_db_fetch_array($eq_type_query);

    $id_eq_type = isset($eq_type_tab['id_eq_type']) ? $eq_type_tab['id_eq_type'] : '';
    $nom_eq_type = isset($eq_type_tab['nom_eq_type']) ? $eq_type_tab['nom_eq_type'] : '';
    $unite_eq_type = isset($eq_type_tab['unite_eq_type']) ? $eq_type_tab['unite_eq_type'] : '';
    $valeur_data_type = isset($eq_type_tab['valeur_data_type']) ? $eq_type_tab['valeur_data_type'] : '';
    $type_color_border = isset($eq_type_tab['type_color_border']) ? $eq_type_tab['type_color_border'] : '';
    $type_color_background = isset($eq_type_tab['type_color_background']) ? $eq_type_tab['type_color_background'] : '';
    $type_graph = isset($eq_type_tab['type_graph']) ? $eq_type_tab['type_graph'] : '';

// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, 
                                to_periode, id_chon_periode, traitement, type_graph
				    FROM ".TABLE_TYPE_DATA." 
                    WHERE id_data_type = ".$id_typedata."
				    ORDER BY init_type_data ASC";
$type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
$type_chron_tab = tep_db_fetch_array($type_chron_query);

    $init_type_data = isset($type_chron_tab['init_type_data']) ? $type_chron_tab['init_type_data'] : '';
    $nom_type_data = isset($type_chron_tab['nom_type_data']) ? $type_chron_tab['nom_type_data'] : '';
    $axe_data = isset($type_chron_tab['axe_data']) ? $type_chron_tab['axe_data'] : '';
    $unite = isset($type_chron_tab['unite']) ? $type_chron_tab['unite'] : '';
    $to_periode = isset($type_chron_tab['to_periode']) ? $type_chron_tab['to_periode'] : '';
    $id_chon_periode = isset($type_chron_tab['id_chon_periode']) ? $type_chron_tab['id_chon_periode'] : '';
    $traitement = isset($type_chron_tab['traitement']) ? $type_chron_tab['traitement'] : '';
    $typegraph = isset($type_chron_tab['type_graph']) ? $type_chron_tab['type_graph'] : '';


// DATA TYPE AXE 
$sql_data_type_axe = "SELECT DISTINCT id, axe, unite 
                        FROM ".TABLE_DATA_TYPE_AXE."
                        WHERE id = ".$axe_data;
$data_type_axe_query = tep_db_query($sql_link,$sql_data_type_axe);
$data_type_axe_tab = tep_db_fetch_array($data_type_axe_query);

    $axe = isset($data_type_axe_tab['axe']) ? $data_type_axe_tab['axe'] : '';
    $typegruniteaph = isset($data_type_axe_tab['unite']) ? $data_type_axe_tab['unite'] : '';






// -------------------------------------------------------------
// CALCULS ET AFFICHAGE DES STATISTIQUES

// GLOBAL

    $sql_stats = "
                    SELECT 
                        AVG(da.valeur) AS moy,  -- Calcule la moyenne des valeurs
                        STD(da.valeur) AS std,  -- Calcule l'écart-type des valeurs
                        MIN(da.valeur) AS min,
                        MAX(da.valeur) AS max
                    FROM 
                        ".TABLE_DATA_ALL." da
                    JOIN 
                        ".TABLE_DATA_META." dm ON da.id_meta=dm.id
                    WHERE 
                        dm.id_typedata = ".$id_typedata."
                        AND dm.id_station = ".$cle_station."
                        AND da.valeur >= 0 
                        AND da.valeur < 99999 -- pour ne pas prendre en compte les lacunes
                        AND da.dateheure >= '".$format_min_x."' 
                        AND da.dateheure <= '".$format_max_x."'
                    ORDER BY da.dateheure DESC
                    ";
    $stats_query = tep_db_query($sql_link,$sql_stats);
    $stats_tab = tep_db_fetch_array($stats_query);


    $moy_all = $stats_tab['moy']; 
    $moy_all_format = rtrim(rtrim(number_format((float)$moy_all, 3, '.', ' '), '0'), '.'); // Supprimer les zéros non significatifs à la fin
    $std_all= $stats_tab['std']; 
    $std_all_format = rtrim(rtrim(number_format((float)$std_all, 3, '.', ' '), '0'), '.'); 

    $min_all = $stats_tab['min']; 
    $min_all_format = rtrim(rtrim(number_format((float)$min_all, 3, '.', ' '), '0'), '.'); 
    $max_all = $stats_tab['max']; 
    $max_all_format = rtrim(rtrim(number_format((float)$max_all, 3, '.', ' '), '0'), '.'); 


    $sql_data = "
                    SELECT da.dateheure, da.valeur
                    FROM 
                        ".TABLE_DATA_ALL." da
                    JOIN 
                        ".TABLE_DATA_META." dm ON da.id_meta=dm.id
                    WHERE 
                        dm.id_typedata = ".$id_typedata."
                        AND dm.id_station = ".$cle_station."
                        AND da.valeur >= 0 
                        AND da.valeur < 99999 -- pour ne pas prendre en compte les lacunes
                        AND da.dateheure >= '".$format_min_x."'
                        AND da.dateheure <= '".$format_max_x."'
                    ORDER BY da.valeur DESC
                    ";
    $data_query = tep_db_query($sql_link,$sql_data);

    $data_tab = array();
    while ($row = tep_db_fetch_array($data_query)) 
    {
        $data_tab[] = $row['valeur'];
    }


    $p25 = calculerPercentile($data_tab,25);
    $p25_format = rtrim(rtrim(number_format((float)$p25, 3, '.', ' '), '0'), '.'); 

    $p50 = calculerPercentile($data_tab,50);
    $p50_format = rtrim(rtrim(number_format((float)$p50, 3, '.', ' '), '0'), '.');

    $p75 = calculerPercentile($data_tab,75);
    $p75_format = rtrim(rtrim(number_format((float)$p75, 3, '.', ' '), '0'), '.'); 


    // HTML
    $html_stats = "";

    $html_stats .= "

            <p class='info_stats'>
                "."Donnnées Générales"."
            </p>

            <table id='table_tri' style='font-size:12px;' >

                <tr>
                    <th style='width:150px;text-align:center;font-size:12px;'><span>"."Minimum"."</span></th>
                    <th style='width:150px;text-align:center;font-size:12px;'><span>"."Quartile (25%)"."</span></th>
                    <th style='width:150px;text-align:center;font-size:12px;'><span>"."Médiane"."</span></th>
                    <th style='width:150px;text-align:center;font-size:12px;'><span>"."Quartile (75%)"."</span></th>
                    <th style='width:150px;text-align:center;font-size:12px;'><span>"."Maximum"."</span></th>

                    <th style='width:150px;text-align:center;font-size:12px;color:#930000;'><span>"."Moyenne"."</span></th>
                    <th style='width:150px;text-align:center;font-size:12px;color:#930000;'><span>"."Ecart-type"."</span></th>
                </tr>

                <tr>
                    <td style='text-align:center;'>".$min_all_format."</td>
                    <td style='text-align:center;'>".$p25_format."</td>
                    <td style='text-align:center;'>".$p50_format."</td>
                    <td style='text-align:center;'>".$p75_format."</td>        
                    <td style='text-align:center;'>".$max_all_format."</td>

                    <td style='text-align:center;'>".$moy_all_format."</td>
                    <td style='text-align:center;'>".$std_all_format."</td>
                </tr>

            </table>
        ";


    // Graphique Box Plot                
    
    $stat_graph = false; 
    $js_graph = "";  

    
$responseData = array(
    'html_stats' => $html_stats,
    'stat_graph' => $stat_graph,
    'js_graph' => $js_graph
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>