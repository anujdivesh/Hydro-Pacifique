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


// PER YEAR
    
    
    $sql_stats_by_year = "
                            SELECT
                                YEAR(da.dateheure) AS year,                                
                                SUM(da.valeur) AS cumul,
                                AVG(da.valeur) AS moy,
                                STD(da.valeur) AS std,
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
                                AND da.valeur < 99999
                                AND da.dateheure >= '".$format_min_x."'
                                AND da.dateheure <= '".$format_max_x."'
                            GROUP BY
                                YEAR(da.dateheure)
                            ORDER BY
                                year DESC
                        ";

    $stats_by_year = array();
    $stats_by_year_query = tep_db_query($sql_link,$sql_stats_by_year);
    while ($stats_by_year_tab = tep_db_fetch_array($stats_by_year_query)) 
    {
        $year = $stats_by_year_tab['year'];

        $cumul = $stats_by_year_tab['cumul'];
        $cumul_format = rtrim(rtrim(number_format((float)$cumul, 3, '.', ' '), '0'), '.');

        $moy = $stats_by_year_tab['moy'];
        $moy_format = rtrim(rtrim(number_format((float)$moy, 3, '.', ' '), '0'), '.');

        $std = $stats_by_year_tab['std'];
        $std_format = rtrim(rtrim(number_format((float)$std, 3, '.', ' '), '0'), '.');

        $min = $stats_by_year_tab['min'];
        $min_format = rtrim(rtrim(number_format((float)$min, 3, '.', ' '), '0'), '.');

        $max = $stats_by_year_tab['max'];
        $max_format = rtrim(rtrim(number_format((float)$max, 3, '.', ' '), '0'), '.');

        $stats_by_year[$year] = array(
            'cumul' => $cumul,
            'cumul_format' => $cumul_format,
            'moy' => $moy,
            'moy_format' => $moy_format,
            'std' => $std_format,
            'std_format' => $std_format,
            'min' => $min,
            'min_format' => $min_format,   
            'max' => $max,         
            'max_format' => $max_format
        );
    }



    $sql_data_by_year = "
        SELECT
            YEAR(da.dateheure) AS year,
            da.valeur
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
        ORDER BY
            da.valeur DESC
    ";

    $data_query = tep_db_query($sql_link, $sql_data_by_year);

    $data_by_year = array();
    while ($row = tep_db_fetch_array($data_query)) 
    {
        $year = $row['year'];

        $valeur = $row['valeur'];

        if (!isset($data_by_year[$year])) {
            $data_by_year[$year] = array();
        }

        $data_by_year[$year][] = $valeur;
    }

    $percentiles_by_year = array();
    foreach ($data_by_year as $year => $data_tab) 
    {
        $p10 = calculerPercentile($data_tab, 10);
        $p10_format = rtrim(rtrim(number_format((float)$p10, 3, '.', ' '), '0'), '.');
        
        $p25 = calculerPercentile($data_tab, 25);
        $p25_format = rtrim(rtrim(number_format((float)$p25, 3, '.', ' '), '0'), '.');

        $p50 = calculerPercentile($data_tab, 50);
        $p50_format = rtrim(rtrim(number_format((float)$p50, 3, '.', ' '), '0'), '.');

        $p75 = calculerPercentile($data_tab, 75);
        $p75_format = rtrim(rtrim(number_format((float)$p75, 3, '.', ' '), '0'), '.');

        $p90 = calculerPercentile($data_tab, 90);
        $p90_format = rtrim(rtrim(number_format((float)$p90, 3, '.', ' '), '0'), '.');

        $percentiles_by_year[$year] = array(
            'p10_format' => $p10_format,
            'p10' => $p10,
            'p25_format' => $p25_format,
            'p25' => $p25,
            'p50_format' => $p50_format,
            'p50' => $p50,
            'p75_format' => $p75_format,
            'p75' => $p75,
            'p90_format' => $p90_format,
            'p90' => $p90
        );
    }

    // HTML
    
    // Graphique Box Plot                
    $html_stats_graph = "";
    $html_stats_graph .= "
                            <p class='info_stats'>
                                "."Graphique de la synthèse intra-annuelle"."
                            </p>

                            <div id='plotStats' class='graph_stats' >
					        </div>
                        ";

    $stat_graph = true; 
    $js_graph = ""; 
    $js_graph .= "const dataStatGraph = [";    

    // Tableau Stat

    $html_stats .= "

            <p class='info_stats'>
                "."Synthèse des données annuelles"."
            </p>

            <table id='table_tri' style='font-size:12px;'>
                <tr>
                    <th style='width:100px;text-align:center;font-size:12px;'><span>Année</span></th>
                    <th style='width:150px;text-align:center;font-size:12px;'><span>Minimum</span></th>
                    <th style='width:150px;text-align:center;font-size:12px;'><span>Quartile (25%)</span></th>
                    <th style='width:150px;text-align:center;font-size:12px;'><span>Médiane</span></th>
                    <th style='width:150px;text-align:center;font-size:12px;'><span>Quartile (75%)</span></th>
                    <th style='width:150px;text-align:center;font-size:12px;'><span>Maximum</span></th>
                    <th style='width:150px;text-align:center;font-size:12px;color:#930000;'><span>Moyenne</span></th>
                    <th style='width:150px;text-align:center;font-size:12px;color:#930000;'><span>Écart-type</span></th>
                </tr>
                ";

                $row=0;
                foreach ($stats_by_year as $year => $stats) 
                {
                    if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                    else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 

                    $percentiles = $percentiles_by_year[$year];

                    $min = $stats['min'];
                    $min_format = $stats['min_format'];

                    $p10 = $percentiles['p10'];
                    $p10_format = $percentiles['p10_format'];
                    $p25 = $percentiles['p25'];
                    $p25_format = $percentiles['p25_format'];
                    $p50 = $percentiles['p50'];
                    $p50_format = $percentiles['p50_format'];
                    $p75 = $percentiles['p75'];
                    $p75_format = $percentiles['p75_format'];
                    $p90 = $percentiles['p90'];
                    $p90_format = $percentiles['p90_format'];

                    $max = $stats['max'];
                    $max_format = $stats['max_format'];

                    $moy = $stats['moy'];
                    $moy_format = $stats['moy_format'];
                    $std = $stats['std'];
                    $std_format = $stats['std_format'];


                    $html_stats .= "
                        <tr ".$row_l.">
                            <td style='text-align:center;font-weight:bold;'>$year</td>
                            <td style='text-align:center;'>".$min_format."</td>
                            <td style='text-align:center;'>".$p25_format."</td>
                            <td style='text-align:center;'>".$p50_format."</td>
                            <td style='text-align:center;'>".$p75_format."</td>
                            <td style='text-align:center;'>".$max_format."</td>
                            <td style='text-align:center;'>".$moy_format."</td>
                            <td style='text-align:center;'>".$std_format."</td>
                        </tr>";

                    //$values = $min.",".$p25.",".$p50.",".$p50.",".$p75.",".$max;
                    $values = $p10.",".$p25.",".$p50.",".$p50.",".$p75.",".$p90;

                    $js_graph .= "
                                    {
                                        y: [".$values."], 
                                        type: 'box',
                                        name: '$year',
                                        hoverinfo: 'y',
                                        //hovertemplate: '10ème Percentile: %{y[0]}<br>25ème Percentile: %{y[1]}<br>Médiane: %{y[2]}<br>75ème Percentile: %{y[3]}<br>90ème Percentile: %{y[4]}<extra></extra>',
                                        //hovertemplate: '%{y:.3f} ".$unite."<extra></extra>',
                                        hovertemplate: '<extra></extra>',
                                        //hovertemplate: 'Mois:%{x}<br>Min: %{y[0]}<br>Q(25%): %{y[1]}<br>Median: %{y[2]}<br>Q(75%): %{y[3]}<br>Max: %{y[4]}<extra></extra>',
                                        marker: {
                                                    color: 'rgb(9,56,125)'
                                                },
                                        /*        
                                        jitter: 0.3,
                                        pointpos: -1.8,       
                                        boxpoints: 'all',
                                        */
                                        boxpoints: false,
                                    },";                        


                    $row++;
                }

    $html_stats .= "

            </table>
            ";

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
                                                    fixedrange: false // Empêche le zoom sur l'axe y
                                                },
                                                xaxis: {
                                                    fixedrange: false
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