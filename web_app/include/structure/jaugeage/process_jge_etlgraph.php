<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export 
- Ce script permet de générer le graph dans la page des ETL
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
$dataJson = json_decode($jsonDataGraph, true);

// Accéder aux données du tableau récupérer
$id_station = $dataJson['idStation'];
$jge_hmoy = $dataJson['jgeHmoy'];
$jge_q = $dataJson['jgeQ'];
$jge_date = $dataJson['jgeDate'];
$jge_heure = $dataJson['jgeHeure'];

// Initialisation des variables de résultats à envoyer côté client
$text_info = '';
$edit_graph = false;
$js_graph = '';

//---------------------------------------------------------------
// SQL - Récupérer les données de la base

// TABLE STATION
$sql_station = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station, s.active_station, s.id_region
				FROM ".TABLE_STATION." s 
				WHERE s.id_station=".$id_station;
$station_query = tep_db_query($sql_link,$sql_station);
$station = tep_db_fetch_array($station_query);

$nom_station =  htmlaccent(html_entity_decode($station['nom_station'] ?? $default_string));
$code_station =  htmlaccent(html_entity_decode($station['code_station'] ?? $default_string));
		
$text_info .= "<p style='margin-bottom:5px;font-size:18px;'>
                    <span style='font-weight:bold;'>Station : </span>
                    ".$code_station." - ".$nom_station."
                </p>";



// Initialisation pour le graph
$data_graph = '';
$data_graph_jge = '';
$load_data = '';
$load_data_jge = '';

$min_h_fix = 0;
$max_h_fix = 100;
$min_q_fix = 0;
$max_q_fix = 5;
$colorEtl = "#3B6790";

// ETAPE 1 : on récupère l'ETL liée à la date de JGE
$sql_ETL = "SELECT DISTINCT id, datetime_first, datetime_end
            FROM ".TABLE_DATA_ETL." etl
            WHERE id_station=".$id_station."
            AND datetime_first <= '".dateus_fr($jge_date)."'
            AND datetime_end >= '".dateus_fr($jge_date)."'
            ORDER BY datetime_end DESC";
$ETL_query = tep_db_query($sql_link,$sql_ETL);

if (mysqli_num_rows($ETL_query) > 0) // Vérifie le nombre de lignes retournées
{
    $ETL_tab = tep_db_fetch_array($ETL_query);
    $id_etl = $ETL_tab['id'];
    $datetime_first = $ETL_tab['datetime_first'];
    $datetime_end = $ETL_tab['datetime_end'];

    $formatted_date_first = date('d-m-Y', strtotime($datetime_first));
    $formatted_date_end = date('d-m-Y', strtotime($datetime_end));

    $text_info .= "<p style='margin-bottom:5px;font-size:16px;'>
                        <span style='font-weight:bold;'>Période de validité de l'ETL : </span>
                         du ".$formatted_date_first." au ".$formatted_date_end."
                    </p>";
    


    // ----------------------------------------------------------------
    // ETAPE 2 : on récupère les données de l'ETL liée à la date de JGE
    // Données de l'ETL qui doit s'afficher
    $nb_pts = 0;
    $graph_x = '';
    $graph_y = '';

    $sql_ETL_data = "SELECT DISTINCT id, hauteur, debit, code_qualite
                    FROM ".TABLE_DATA_ETL_DATA." 
                    WHERE id_etl=".$id_etl." ORDER BY hauteur ASC";

    $ETL_data_query = tep_db_query($sql_link,$sql_ETL_data);
    while($ETL_data_tab = tep_db_fetch_array($ETL_data_query))
    {
        $id_etl_data = $ETL_data_tab['id'];
        $hauteur = $ETL_data_tab['hauteur'];            
        $debit = $ETL_data_tab['debit'];     
        $code_qualite = $ETL_data_tab['code_qualite'];

        $graph_x .= $hauteur.',';
        $graph_y .= $debit.',';        

        //if($min_h_fix > $hauteur){$min_h_fix = $hauteur * 0.95;}
        if($nb_pts < 1){$min_h_fix = $hauteur * 0.95;}
        if($max_h_fix < $hauteur){$max_h_fix = $hauteur * 1.05;}
        if($min_q_fix > $debit){$min_q_fix = $debit * 0.95;}
        if($max_q_fix < $debit){$max_q_fix = $debit * 1.05;}

        $nb_pts++;
    }

    // On enlève la dernière virgule des variables
    //$index_pts = substr($index_pts, 0, -1);
    $graph_x = substr($graph_x, 0, -1);
    $graph_y = substr($graph_y, 0, -1);

    if($nb_pts > 0)
    { 
        $data_graph .=
                        "
                        var data_etl = 
                        { 
                            x: [". $graph_x ."],
                            y: [". $graph_y ."],

                            name: 'ETL',
                            
                            // Format d'étiquette des données au survol
                            hovertemplate:  'H : %{x} cm<br>' +  // Format pour x 
                                            'Q : %{y} m³/s',   // Format pour y 
                                                                

                            mode: 'markers+lines', // type de trace (scatter plot)
                            type: 'scatter', // type de graphique
                            marker: {
                                size: 8, 
                                symbol: 'square',
                                color: '".$colorEtl."' // Couleur du marqueur
                            }, 
                            line: {
                                color: '".$colorEtl."' // Même couleur pour la ligne
                            }  
                        };  
                            
                            
                        ";
    }
            
    $load_data .= "data_etl,";

    $sql_jge = "SELECT DISTINCT jge.id, jge.datetime, jge.depouil_hmoy, jge.depouil_q
                    FROM ".TABLE_DATA_JGE." jge
                    WHERE jge.id_station = ".$id_station." 
                    AND jge.datetime >= '".$datetime_first."'  
                    AND jge.datetime <= '".$datetime_end."'  
                    AND jge.depouil_hmoy REGEXP '^-?[0-9]+(\.[0-9]+)?$'  -- Vérifie si c'est un nombre
                    AND jge.depouil_hmoy < 9999
                    AND jge.depouil_q REGEXP '^-?[0-9]+(\.[0-9]+)?$'       -- Vérifie si c'est un nombre
                    ORDER BY jge.datetime ASC";
                    
}
else
{
    $sql_jge = "SELECT DISTINCT jge.id, jge.datetime, jge.depouil_hmoy, jge.depouil_q
                    FROM ".TABLE_DATA_JGE." jge
                    WHERE jge.id_station = ".$id_station."  
                    AND jge.depouil_hmoy REGEXP '^-?[0-9]+(\.[0-9]+)?$'  -- Vérifie si c'est un nombre
                    AND jge.depouil_hmoy < 9999
                    AND jge.depouil_q REGEXP '^-?[0-9]+(\.[0-9]+)?$'       -- Vérifie si c'est un nombre
                    ORDER BY jge.datetime ASC";

    // Afficher un message : aucune relation d'étalonnage (ETL) n'a été trouvée. 
    $text_info .= "<p style='font-size:16px;'>
                        <span style='font-weight:bold;'>
                            Aucune relation d'Etalonnage (ETL) ne couvre la date du Jaugeage
                        </span>
                    </p>";
}

// ----------------------------------------------------------------
// ETAPE 3 : on récupère les pts de JGE correspondant à l'ETL lié à la date de JGE

$nb_pts_jge = 0;
$graph_x_jge = '';
$graph_y_jge = '';
$graph_date_jge = '';

// Maintenant on récupère les pts de JGE correspondant (même station, même période si ETL sinon toutes les périodes)
$jge_query = tep_db_query($sql_link,$sql_jge);
if (mysqli_num_rows($jge_query) > 0) // Vérifie le nombre de lignes retournées
{
    while($jge_tab = tep_db_fetch_array($jge_query))
    {
        $h_jge = abs($jge_tab['depouil_hmoy']);
        $q_jge = abs($jge_tab['depouil_q']);
        $date_jge = $jge_tab['datetime'];
        $date_jge_formatee = date("d-m-Y H:i:s", strtotime($date_jge));  

        if($nb_pts_jge < 1){$date_first = date("d-m-Y", strtotime($date_jge));}
        
        
        $graph_x_jge .= $h_jge.',';
        $graph_y_jge .= $q_jge.',';  
        $graph_date_jge .= "'".$date_jge_formatee."',"; 


        if($nb_pts_jge < 1)
        {
            $min_h_fix = 0;
            $max_h_fix = 10;
            $min_q_fix = 0;
            $max_q_fix = 1;
        }
        if($max_h_fix < $h_jge){$max_h_fix = $h_jge * 1.1;}
        if($min_q_fix > $q_jge){$min_q_fix = $q_jge * 0.95;}
        if($max_q_fix < $q_jge){$max_q_fix = $q_jge * 1.1;}

        $nb_pts_jge++;
    }
}
else
{
    // Afficher un message : aucune donnée de Jaugeage (JGE) n'a été trouvée. 
    $text_info .= "<p style='font-size:16px;'>
                        <span style='font-weight:bold;'>
                            Aucune donnée de Jaugeage (JGE) n'a été trouvée
                        </span>
                    </p>";
}

$graph_x_jge = substr($graph_x_jge, 0, -1);
$graph_y_jge = substr($graph_y_jge, 0, -1);
$graph_date_jge = substr($graph_date_jge, 0, -1);

if($nb_pts_jge > 0)
{ 
    $text_info .= "<p style='font-size:16px;'>
                        <span style='font-weight:bold;'>Nombre de points de JGE sur la période : </span>
                        ".$nb_pts_jge."
                    </p>";
    
    $data_graph_jge .=
        "
            var data_jge = 
            { 
                x: [". $graph_x_jge ."],
                y: [". $graph_y_jge ."],  
                customdata: [". $graph_date_jge ."],

                name: 'JGE',
                
                // Format d'étiquette des données au survol
                hovertemplate:  'Date : %{customdata}<br>' + // Date
                                'H : %{x:.0f} cm<br>' +  // Format pour x 
                                'Q : %{y:.3f} m³/s' ,   // Format pour y 
                                                    

                mode: 'markers', // type de trace (scatter plot)
                type: 'scatter', // type de graphique
                marker: {
                    size: 12, 
                    symbol: 'star',
                    color: '".$colorEtl."', // Couleur du marqueur
                    line: { 
                        color: 'black',  // Couleur du contour
                        width: 1        // Épaisseur du contour
                    }
                } // taille des marqueurs   
            };  

            var special_pts = 
            {
                x: [" . $jge_hmoy . "],
                y: [" . $jge_q . "], 
                customdata: ['".$jge_date." ".$jge_heure."'],

                name: 'JGE - En cours',
                
                // Format d'étiquette des données au survol
                hovertemplate:  'Date : %{customdata}<br>' + // Date
                                'H : %{x:.0f} cm<br>' +  // Format pour x 
                                'Q : %{y:.3f} m³/s' ,   // Format pour y 
                                                    

                mode: 'markers', // type de trace (scatter plot)
                type: 'scatter', // type de graphique
                marker: {
                    size: 18, 
                    symbol: 'star',
                    color: '#EFB036', // Couleur du marqueur
                    line: { 
                        color: 'black',  // Couleur du contour
                        width: 1        // Épaisseur du contour
                    }
                } // taille des marqueurs   
            };  
        ";
    
    $load_data .= "data_jge,special_pts,";
}

$load_data = substr($load_data, 0, -1);
$load_data_all = "[".$load_data."]";


$layout_graph =
                "
                    var layout = 
                    {
                        xaxis: 
                        {
                            title: {
                                text: 'Hauteur (cm)',
                                standoff: 20, // Ajuster la distance entre le titre et l'axe
                                font: {
                                    family: 'roboto, arial, helvetica',
                                    size: 14,
                                    bold: true,
                                    color: '#000000'
                                }
                            },
                            
                            autorange: false,
                            range: [".$min_h_fix.", ".$max_h_fix."], 
                        },
                        yaxis:
                        {
                            title: {
                                text: 'Débit (m3/s)',
                                standoff: 15, // Ajuster la distance entre le titre et l'axe
                                font: {
                                    family: 'roboto, arial, helvetica',
                                    size: 14,
                                    bold: true,
                                    color: '#000000'
                                }
                            },
                                                
                            autorange: false,
                            range: [".$min_q_fix.", ".$max_q_fix*1.1."], 
                        },
                        
                        hovermode: '',
                        hoverlabel: { bgcolor: '#fff', font: { size: 16, color: '#000' } },
                        margin: {l: 50, r: 10, t: 0, b: 40}, // Par défault : l: 60, r: 60, t: 80, b: 60 
                        showlegend: true,
                        legend: 
                        {
                            x: 0,
                            y: 1.05,
                            orientation: 'v',
                        },
                    };
                ";

$config_graph =
                "
                    var config = 
                    {
                        responsive: true,
                        doubleClickDelay: 1000, //Delay du zoom
                        
                        displaylogo: false,
                        displayModeBar: true, // Affichage constant du menu de la figure
                        scrollZoom: true, // Zoom avec la roulette de la souris
                        modeBarOrientation: 'v',
                    
                        // Organisation personnalisée des boutons
                        modeBarButtons: [
                            [
                                {
                                    name: 'Export SVG',
                                    icon: Plotly.Icons.disk,
                                    click: function(gd) {
                                        Plotly.downloadImage(gd, {format: 'svg', filename: 'mon_grap'});
                                    }
                                },            
                                'toImage',
                                'zoom2d',
                                'pan2d',
                                'resetScale2d'
                            ]
                        ],

                        modeBarButtonsToRemove: ['select2d', 'lasso2d', 'autoScale2d', 'zoomIn2d', 'zoomOut2d']
                    };
                "; 

// Préparation des données à renvoyer coté Client
$editGraph = 
"
    Plotly.newPlot('plot_etl',".$load_data_all.",layout,config);
";                    

$js_graph = $config_graph.$data_graph.$data_graph_jge.$layout_graph.$editGraph;
$edit_graph = true;

      



$responseData = array(
    'js_text' => $text_info,
    'edit_graph' => $edit_graph,
    'js_graph' => $js_graph,
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>