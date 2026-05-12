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
$dataJson = json_decode($jsonDataGraph, true);

// Accéder aux données du tableau récupérer
$timezone_php = $dataJson['timezone_php'];

$min_x = $dataJson['xDateMin'];
$max_x = $dataJson['xDateMax'];
$min_x_fix = null;
$max_x_fix = null;

$station_chron = $dataJson['idStation'];


// Initialisation des variables pour l'affichage du graph

$date_1 = '1950-01-01';

date_default_timezone_set($timezone_php); 
$today = new DateTime(); // Crée un objet DateTime pour la date du jour
$date_2 = $today->format('Y-m-d');  // Formatage de la date

if(tep_not_null($min_x) && tep_not_null($max_x))
{
    $date_1 = datefr_us($min_x);
    $date_2 = datefr_us($max_x);
}


$data_graph = '';
$load_data = '';


    // ---------------------------------------------
    // Chron. HAUTEUR EAU

    $graph_x = '';
    $graph_y = '';       
    
    $nb_etl = 0;
    $previous_end_date = null; // Variable pour stocker la date_end précédente

    // Données de la couverture des ETL
    $sql_etl = "SELECT DISTINCT etl.id, etl.datetime_first, etl.datetime_end
                FROM ".TABLE_DATA_ETL." etl 
                WHERE etl.id_station=".$station_chron."
                AND etl.datetime_first <= '".$date_2." 00:00:00'
                AND etl.datetime_end >= '".$date_1." 23:59:59'
                ORDER BY etl.datetime_first ASC";


    $etl_query = tep_db_query($sql_link,$sql_etl);
    while($etl_tab = tep_db_fetch_array($etl_query))
    {
        $graph_data_x = '';
        $graph_data_y = '';
        
        $timestamp_first = strtotime($etl_tab['datetime_first']); 
        $datefirst_etl_js = date("Y-m-d", $timestamp_first);

        $timestamp = strtotime($etl_tab['datetime_end']); 
        $dateend_etl_js = date("Y-m-d", $timestamp);

        // Vérifier si datetime_first est égal à datetime_end précédent
        if($previous_end_date && $datefirst_etl_js == $previous_end_date) 
        {
            // Si égal, ajouter un jour à datetime_first
            $timestamp_first = strtotime("+1 day", $timestamp_first);
            $datefirst_etl_js = date("Y-m-d", $timestamp_first);
        }

        // Vérifier si dateend_etl_js est supérieur à aujourd'hui
        if ($dateend_etl_js > $date_2) 
        {
            $dateend_etl_js = $date_2; // Si oui, remplacer par la date actuelle
        }

        $previous_end_date = $dateend_etl_js;
            
        $graph_data_x .= "'".$datefirst_etl_js."','".$dateend_etl_js."'";
        $graph_data_y .= "'ETL','ETL'" ;

        // Appliquer une légende différente pour chaque point
        $legend_pts[] = "'#9B7EBD'"; // Couleur pour datetime_first

        $nb_etl++;

        $data_graph .= "
                        var data_etl_".$nb_etl." = 
                        { 
                            x: [".$graph_data_x."],
                            y: [".$graph_data_y."],   

                            mode: 'line', // type de trace (scatter plot)
                            type: 'scatter', // type de graphique
                            
                            hovermode: 'closest',
                            hovertemplate: '<b>Date</b>: %{x|%d-%m-%Y}<extra></extra>',
                            text: ['Date Début','Date Fin'],

                            line: {width: 4,color: '#9B7EBD'}, 
							marker: {
										size: 15, // taille des marqueurs 
										symbol: 'line-ns-open', // Forme des marqueurs (ici 'square' pour carré)
										line: {width: 3}										
									},   
                        }; 
                        "; 
        
        $load_data .= "data_etl_".$nb_etl.",";// . $js_load_trace_etl; // Pour inverser l'affichage sur le graph
    }
    $load_data = rtrim($load_data, ',');


$layout_graph =
            "
                var layout = 
                {
                    xaxis: {
                        autorange: false,
                        range: ['".$date_1."', '".$date_2."'] // Plage de dates pour l'axe Y
                    },
                    
                    title: 'Couverture des ETL',
                    dragmode: false, // Désactive les interactions de zoom
                    
                    hoverlabel: {bgcolor: '#fff', font: {size: 14, color: '#000'} },
                    margin: {l: 50, r: 10, t: 0, b: 40},
                    showlegend: false
                };
            ";

$config_graph =
        "
            var config = 
            {
                responsive: true,
                doubleClickDelay: 1000, //Delay du zoom

                displayModeBar: false, // Affichage constant du menu de la figure
                scrollZoom: false, // Zoom avec la roulette de la souris

                modeBarButtonsToRemove: ['select2d','lasso2d','autoScale2d','zoomIn2d','zoomOut2d'],
                modeBarOrientation: 'v',

                displaylogo: false
            };
        ";



        // Préparation des données à renvoyer coté Client
$editGraph = 
    "
        Plotly.newPlot('plot_etl',[".$load_data."],layout,config);        
    ";

$responseData = array(
    'js_text' =>  $config_graph.$data_graph.$layout_graph.$editGraph
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>