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
$dataStats = json_decode($jsonData, true);

$year_select = $dataStats['yearSelect'];
$dataGraph = $dataStats['stats'];

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

$startDate = "$year_select-01-01";
$endDate = "$year_select-12-31";

// PER MONTH, YEAR (MOYENNE)

    $sql_stats = "
                    SELECT
                        DAY(da.dateheure) AS day,
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
                        AND da.dateheure >= '".$startDate."'
                        AND da.dateheure <= '".$endDate."'
                    GROUP BY
                        DAY(da.dateheure),
                        MONTH(da.dateheure)
                    ORDER BY
                        month, day
                    ";

    $organizedData = [];                    
    
    $stats_query = tep_db_query($sql_link,$sql_stats);
    while ($stats_tab = tep_db_fetch_array($stats_query)) 
    {
        $month = $stats_tab['month'];
        $day = $stats_tab['day'];

        $valeur = $stats_tab['calc_valeur'];
        $valeur_format = rtrim(rtrim(round($valeur, 3), '0'), '.');

        if (!isset($organizedData[$month])) {$organizedData[$month] = [];}

        $organizedData[$month][$day] = $valeur_format;
    }

    // Déterminer les valeurs maximales et minimales pour chaque mois
    $maxValues = [];
    $minValues = [];
    $monthlyAverages = [];
    
    foreach ($organizedData as $month => $days) 
    {
        $maxValues[$month] = max($days);
        $minValues[$month] = min($days);
        $monthlyAverages[$month] = mean($days);
    }


    $mois_noms_courts = [
                            1 => 'Jan.', 2 => 'Fév.', 3 => 'Mar.', 4 => 'Avr.',
                            5 => 'Mai', 6 => 'Juin', 7 => 'Juil.', 8 => 'Août',
                            9 => 'Sep.', 10 => 'Oct.', 11 => 'Nov.', 12 => 'Déc.'
                        ];


    
    // HTML

    $html_stats .= "
            
            <div style='margin-left:10%;margin-bottom:20px;'>            
                        
                <table id='table_tri' style='width:85%;font-size:12px;'>
                    <tr>
                        <th style='width:2%;text-align:center;font-weight:bold;color:#000;'>"."Jour"."</th>
                ";

                foreach ($mois_noms_courts as $mois) 
                {
                    $html_stats .= "
                                    <th style='width:5%;text-align:center;font-size:13px;'><span>".$mois."</span></th>
                                    ";
                }

                $html_stats .= "
                                </tr>
                                ";


                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, 1, $year_select);
                $row=1;
                for ($day = 1; $day <= $daysInMonth; $day++) 
                {
                    if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                    else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";}


                    $html_stats .= "<tr ".$row_l.">
                                
                                    <td style='height:25px;text-align:center;font-weight:bold;'>".$day."</td>";
                        
                        for ($month = 1; $month <= 12; $month++) 
                        {       
                                if (isset($organizedData[$month][$day])) 
                                {
                                    $value = $organizedData[$month][$day];
                                    
                                    $style_bg = "";
                                    $style = "";

                                    if(!empty($value))
                                    {
                                        if($value == $minValues[$month]){$style_bg = "background-color:#A8F1FF;";}
                                        if($value == $maxValues[$month]){$style_bg = "background-color:#FFDCDC;";}
                                        
                                        $style = ($value == $maxValues[$month]) ? "style='font-size:13px;font-weight:bold;color:#930000;'" : "";
                                    }

                                    $html_stats .= "<td style='height:25px;text-align:center;".$style_bg."'>";

                                        //$html_stats .= "<span " . $style . ">" . round($value, 2) . "</span>";
                                        $html_stats .= "<span " . $style . ">" . $value . "</span>";

                                    $html_stats .= "</td>";
                                } 
                                else 
                                {
                                    $html_stats .= "<td style='height:25px;text-align:center;'>";
                                        $html_stats .= "-";
                                    $html_stats .= "</td>";
                                }
                        }

                    $html_stats .= "</tr>";


                    $row++;
                }

                $html_stats .= "<tr><td colspan='13' style='height: 20px;'></td></tr>";

                // Ajouter une ligne pour les moyennes mensuelles
                $html_stats .= "<tr>";

                    $html_stats .= "<td style='text-align:center;font-weight:bold;'>"."Moyenne"."</td>";

                    for ($month = 1; $month <= 12; $month++) 
                    {
                        $html_stats .= "<td style='text-align:center;'>";
                            $html_stats .= isset($monthlyAverages[$month]) ? round($monthlyAverages[$month], 2) : '-';
                        $html_stats .= "</td>";
                    }

                $html_stats .= "</tr>";

                // Ajouter une ligne pour le max
                $html_stats .= "<tr class='row2'>";

                    $html_stats .= "<td style='text-align:center;font-weight:bold;background-color:#FFDCDC;'>"."Maximum"."</td>";

                    for ($month = 1; $month <= 12; $month++) 
                    {
                        $html_stats .= "<td style='text-align:center;'>";
                            //$html_stats .= isset($maxValues[$month]) ? round($maxValues[$month], 2) : '-';
                            $html_stats .= isset($maxValues[$month]) ? $maxValues[$month] : '-';
                        $html_stats .= "</td>";
                    }
                    
                $html_stats .= "</tr>";

                // Ajouter une ligne pour le min
                $html_stats .= "<tr>";

                    $html_stats .= "<td style='text-align:center;font-weight:bold;background-color:#A8F1FF;'>"."Minimum"."</td>";

                    for ($month = 1; $month <= 12; $month++) 
                    {
                        $html_stats .= "<td style='text-align:center;'>";
                            //$html_stats .= isset($minValues[$month]) ? round($minValues[$month], 2) : '-';
                            $html_stats .= isset($minValues[$month]) ? $minValues[$month] : '-';
                        $html_stats .= "</td>";
                    }
                    
                $html_stats .= "</tr>";
                        

        $html_stats .= "

                        </table>
                    </div>
                    ";



$responseData = array(
    'html_stats' => $html_stats
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>