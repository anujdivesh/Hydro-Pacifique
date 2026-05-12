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



$html_stats = "";


// -------------------------------------------------------------
// CALCULS ET AFFICHAGE DES STATISTIQUES


// PER MONTH, YEAR (MOYENNE)

    $sql_stats_by_month_year = "
                            SELECT
                                YEAR(da.dateheure) AS year,
                                MONTH(da.dateheure) AS month,
                                AVG(da.valeur) AS calc_valeur
                            FROM
                                ".TABLE_DATA_ALL." da
                            JOIN
                                ".TABLE_DATA_META." dm ON da.id_meta=dm.id
                            WHERE
                                dm.id_typedata = ".$id_typedata."
                                AND dm.id_station = ".$cle_station."
                                AND da.valeur >= 0
                                AND da.valeur < 99999
                                AND da.dateheure >= '".$format_min_x."'
                                AND da.dateheure <= '".$format_max_x."'
                            GROUP BY
                                YEAR(da.dateheure), MONTH(da.dateheure)
                            ORDER BY
                                month DESC, year DESC 
                            ";

    $stats_by_month_year = array();
    $stats_by_month_year_query = tep_db_query($sql_link,$sql_stats_by_month_year);
    while ($stats_by_month_year_tab = tep_db_fetch_array($stats_by_month_year_query)) 
    {
        $year = $stats_by_month_year_tab['year'];
        $month = $stats_by_month_year_tab['month'];

        $moy = $stats_by_month_year_tab['calc_valeur'];
        $moy_format = rtrim(rtrim(number_format((float)$moy, 3, '.', ' '), '0'), '.');

        $stats_by_month_year[$month][$year] = $moy;
    }

    $stats_by_month = array();
    foreach ($stats_by_month_year as $month => $years) 
    {
        $max_year = array_search(max($years), $years);
        $min_year = array_search(min($years), $years);

        $max_val = max($years);
        $min_val = min($years);

        $sum_month = array_sum($years);
        $nb_month = count($years);

        $moy_month = 0;
        if($nb_month > 0){$moy_month = $sum_month/$nb_month;}

        $p25 = calculerPercentile($years, 25);

        $p50 = calculerPercentile($years, 50);

        $p75 = calculerPercentile($years, 75);

        $stats_by_month[$month] = array(
                                        'moy_month' => $moy_month,
                                        'max_year' => $max_year,
                                        'max_val' => $max_val,
                                        'min_year' => $min_year,
                                        'min_val' => $min_val,
                                        'p25' => $p25,
                                        'p50' => $p50,
                                        'p75' => $p75,
                                        );
    }

    $tab_rows = [
                    'min_year' => 'Année du min.',
                    'min_val' => 'Moyenne mensuelle min.',
                    'p25' => 'Quartile (25%)',
                    'p50' => 'Médiane',
                    'p75' => 'Quartile (75%)',
                    'max_val' => 'Moyenne mensuelle max.',
                    'max_year' => 'Année du max.',
                    'moy' => 'Moyenne globale'
                ];

    $mois_noms_courts = [
                            1 => 'Jan.', 2 => 'Fév.', 3 => 'Mar.', 4 => 'Avr.',
                            5 => 'Mai', 6 => 'Juin', 7 => 'Juil.', 8 => 'Août',
                            9 => 'Sep.', 10 => 'Oct.', 11 => 'Nov.', 12 => 'Déc.'
                        ];

    // HTML

    $html_stats .= "
            
            <p class='info_stats'>
                "."Synthèse des données mensuelles"."
            </p>


            <table id='table_tri' style='font-size:12px;'>
                <tr>
                    <th style='width:170px;text-align:center;font-size:13px;'>&nbsp;</th>
            ";

                foreach ($mois_noms_courts as $mois) 
                {
                    $html_stats .= "
                                    <th style='width:7%;text-align:center;font-size:13px;'><span>".$mois."</span></th>
                                    ";
                }

                $html_stats .= "
                                </tr>
                                ";
                    

                $row=0;
                foreach ($tab_rows as $key => $label)
                {
                    if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                    else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
                    
                    $color='';
                    if($key == 'moy_month')
                    {
                        $color='color:#930000;';
                    }

                    $html_stats .= "
                                    <tr ".$row_l.">
                                        <td style='padding: 8px 0;text-align:center;font-size:12px;font-weight:bold;".$color."'>
                                            <span>".$label."</span>
                                        </td>
                                ";  

                        foreach ($mois_noms_courts as $num_mois => $mois) 
                        {
                            $valeur = '-';
                            if(isset($stats_by_month[$num_mois][$key]))
                            {
                                $valeur = rtrim(rtrim(round($stats_by_month[$num_mois][$key], 3), '0'), '.');
                            }
                            $html_stats .= "
                                            <td style='text-align:center;'>".$valeur."</td>
                                        ";                                
                        }
                    
                    $html_stats .= "
                                </tr>
                                ";




                    $row++;
                }


    $html_stats .= "

                    </table>
                ";


    // Graphique Box Plot                
    $html_stats_graph = "";
    $html_stats_graph .= "
                            <p class='info_stats'>
                                "."Graphique de la synthèse mensuelle"."
                            </p>

                            <div id='plotStats' class='graph_stats' >
					        </div>
                        ";

    $stat_graph = true; 
    $js_graph = "";    

    // Initialiser le tableau de données pour Plotly.js
    $js_graph .= "const dataStatGraph = [";    

        foreach ($mois_noms_courts as $num_mois => $mois) 
        {
            // Préparation des données pour Plotly.js
            if (isset($stats_by_month[$num_mois])) 
            {
                //$values = array_values($stats_by_month[$num_mois]);
                //sort($values);
                //print_r($values);
                
                $min = $stats_by_month[$num_mois]['min_val'];
                $p25 = $stats_by_month[$num_mois]['p25'];
                $p50 = $stats_by_month[$num_mois]['p50'];
                $p75 = $stats_by_month[$num_mois]['p75'];
                $max = $stats_by_month[$num_mois]['max_val'];

                $values = $min.",".$p25.",".$p50.",".$p50.",".$p75.",".$max;

                $js_graph .= "
                                {
                                    y: [".$values."], 
                                    type: 'box',
                                    name: '$mois',
                                    hoverinfo: 'y',
                                    hovertemplate: '<extra></extra>',
                                    //hovertemplate: 'Mois:%{x}<br>Min: %{y[0]}<br>Q(25%): %{y[1]}<br>Median: %{y[2]}<br>Q(75%): %{y[3]}<br>Max: %{y[4]}<extra></extra>',
                                    marker: {
                                                color: 'rgb(9,56,125)'
                                            },
                                    boxpoints: false,
                                    //hovertemplate: 'test'
                                    
                                },";
            }
        }
    
    $js_graph .= "];"; 
    

    $js_graph .= "
                    const layoutStatGraph = 
                                            {
                                                //title: '"."Box Plot des Données par Mois"."',
                                                yaxis: {
                                                    title: '".$axe." (".$unite.")',

                                                    tickfont: {size: 11}, // Taille des caractères des graduations

                                                    titlefont: {family: 'roboto, arial, helvetica',
                                                            size: 14,
                                                            bold: true,
                                                            color: '#000000'},
                                                    ticklen: 5,
                                                    showline: true,
                                                    linewidth: 1,

                                                    automargin: true,
                                                    fixedrange: true // Empêche le zoom sur l'axe y
                                                },
                                                xaxis: {
                                                    fixedrange: true
                                                },

                                                showlegend: false,

                                                hoverlabel: { bgcolor: '#fff', font: { size: 12, color: '#000' } },
                                                margin: {l: 60, r: 10, t: 10, b: 40}, // Par défault : l: 60, r: 60, t: 80, b: 60


                                            };

                    Plotly.newPlot('plotStats', dataStatGraph, layoutStatGraph,config);
                ";




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