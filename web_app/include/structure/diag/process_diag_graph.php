<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export 
- Ce script permet d'afficher le tableau des diagraphies pouvant être affichées
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
$jsonDataTab = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataJson = json_decode($jsonDataTab, true);

// Accéder aux données du tableau récupérer
$list_diag = $dataJson['listDiag'];

// Initialisation des vairables
$data_graph_diag = '';
$load_data_diag = '';

$colorGraph = colorList();

$nb_diag = 1;
$xmax = 0;
$ymin = 0;

$sql_ra_diag = "SELECT DISTINCT r.id_ra, r.date_heure_ra, r.id_station, 
                                s.code_station, s.nom_station
                    FROM ".TABLE_DATA_RA." r
                    JOIN 
                        ".TABLE_STATION." s ON s.id_station = r.id_station
                    WHERE r.id_ra IN (".$list_diag.")
                    ORDER BY code_station DESC, date_heure_ra DESC";
$ra_diag_query = tep_db_query($sql_link,$sql_ra_diag);
while ($ra_diag = tep_db_fetch_array($ra_diag_query))  
{	
    $id_ra =  $ra_diag['id_ra'];

    $date_heure_ra = $ra_diag['date_heure_ra'];
    $date_ra = DateTime::createFromFormat('Y-m-d H:i:s', $date_heure_ra);
    $formatted_date_ra = $date_ra->format('d-m-Y');   

    $id_station =  $ra_diag['id_station'];
    $code_station =  $ra_diag['code_station'];
    $nom_station =  $ra_diag['nom_station'];	

    $graph_x_diag = '';
    $graph_y_diag = '';
    $graph_temps_diag = '';
    $graph_obs_diag = '';

    $sql_diag = "SELECT DISTINCT id_ra, profondeur, conductivite, temperature, obs
                    FROM ".TABLE_DATA_RA_PIEZO_PROFIL." 
                    WHERE id_ra = ".$id_ra."
                    ORDER BY profondeur ASC";
    $diag_query = tep_db_query($sql_link,$sql_diag);                    
    while ($diag_tab = tep_db_fetch_array($diag_query))    
    {
        $profondeur =  (-1)*$diag_tab['profondeur'];
        $conductivite =  $diag_tab['conductivite'];
        $temperature =  $diag_tab['temperature'];
        $obs = $diag_tab['obs'];
        if(empty($obs)){$obs = '-';}

        if($xmax < $conductivite){$xmax = $conductivite;}
        if($ymin > $profondeur){$ymin = $profondeur;}

        $graph_x_diag .= $conductivite.',';
        $graph_y_diag .= $profondeur.',';  
        $graph_temps_diag .= $temperature.',';  
        $graph_obs_diag .= "'".$obs."',"; 
    
        // Ajouter les valeurs individuelles au tableau combiné
        $combined_custom_data[] = array($temperature, $obs); 
    }   
    // Supprimer la dernière virgule des chaînes
    $graph_x_diag = rtrim($graph_x_diag, ',');
    $graph_y_diag = rtrim($graph_y_diag, ',');
    
    // Convertir le tableau combiné en JSON pour l'utiliser dans JavaScript
    $combined_custom_data_json = json_encode($combined_custom_data);

    $colorEtl = $colorGraph[$nb_diag % 18 + 1]; // Assure un cycle de couleurs entre 1 et 30

    $data_graph_diag .=
                            "
                            var data_diag_".$id_ra." = 
                            { 
                                hovermode: 'closest',

                                x: [". $graph_x_diag ."],
                                y: [". $graph_y_diag ."],  
                                customdata: ". $combined_custom_data_json .",

                                name: '".$code_station." - ".$formatted_date_ra."',
                                
                                // Format d'étiquette des données au survol
                                hovertemplate:  '<b>Conductivité</b> : %{x:.0f} &mu;S/cm<br>' + // Conductivité
                                                '<b>Profondeur</b> : %{y:.2f} m<br>' + // Profondeur
                                                '<b>Température</b> :  %{customdata[0]} °C<br>' + // Température
                                                '<b>Obs.</b> :  %{customdata[1]}' , // Observation                                                

                                mode: 'markers+lines', // type de trace (scatter plot)
                                type: 'scatter', // type de graphique
                                marker: {
                                    size: 8, 
                                    symbol: 'circle',
                                    color: '".$colorEtl."' // Couleur du marqueur
                                }, 
                                line: {
                                    color: '".$colorEtl."' // Même couleur pour la ligne
                                }  
                            };  
                        ";

    $load_data_diag .= "data_diag_".$id_ra.",";

    $nb_diag++;
}
$load_data_diag = rtrim($load_data_diag, ', ');

$layout_graph =
"
    var layout = 
    {
        xaxis: 
        {
            title: {
                text: 'Conductivité (&mu;S/cm)',
                standoff: 20, // Ajuster la distance entre le titre et l'axe
                font: {
                    family: 'roboto, arial, helvetica',
                    size: 14,
                    bold: true,
                    color: '#000000'
                }
            },
            
            tickformat: ',d', // Utilisez 'd' pour afficher les nombres en entiers sans notation abrégée
            autorange: false,
            range: [(".$xmax."*-0.1), (".$xmax."*1.1)], 
            side: 'top' // Placer l'axe x en haut du graphique
        },
        yaxis:
        {
            title: {
                text: 'Profondeur (m)',
                standoff: 25, // Ajuster la distance entre le titre et l'axe
                font: {
                    family: 'roboto, arial, helvetica',
                    size: 14,
                    bold: true,
                    color: '#000000'
                }
                    
            },
            
            tickformat: 'd', // Utilisez 'd' pour afficher les nombres en entiers sans notation abrégée                
            autorange: false,
            range: [(".$ymin."*1.1), 0], 
        },
        
        hovermode: '',
        hoverlabel: { bgcolor: '#fff', font: { size: 16, color: '#000' } },
        margin: {l: 80, r: 10, t: 60, b: 40}, // Par défault : l: 60, r: 60, t: 80, b: 60 
        showlegend: true,
        legend: 
        {
            x: 1,
            y: 1,
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
    Plotly.newPlot('plot',[".$load_data_diag."],layout,config);
    
";

$responseData = array(
    'js_graph' => $config_graph.$data_graph_diag.$layout_graph.$editGraph,
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>