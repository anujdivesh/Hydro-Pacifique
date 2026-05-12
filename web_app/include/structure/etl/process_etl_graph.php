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
$firstLoad = $dataJson['firstLoad'];
$date_today = $dataJson['dateToday'];
$tabIdEtl = $dataJson['tabIdEtl'];

$min_h = $dataJson['xMin'];
$max_h = $dataJson['xMax'];
$min_q = $dataJson['yMin'];
$max_q = $dataJson['yMax'];

$min_h_fix = -2;
$max_h_fix = 100;
$min_q_fix = -1;
$max_q_fix = 5;

// Gestion de l'échelle
if($firstLoad)
{   
    $min_h = $min_h_fix;
    $max_h = $max_h_fix;
    $min_q = $min_q_fix;
    $max_q = $max_q_fix;
}

$id_station = $dataJson['idStation'];


$colorGraph = colorList();

// Initialisation des variables pour l'affichage du graph
$data_graph = '';
$data_graph_jge = '';
$load_data = '';
$load_data_jge = '';

$date_first = $date_today;


if(sizeof($tabIdEtl) > 0)
{
    $tabIdEtl = array_reverse($tabIdEtl); // Inverser l'ordre des éléments dans $tabIdEtl
    foreach ($tabIdEtl as $info_etl) 
    {
        $index_pts = '';
        $graph_x = '';
        $graph_y = '';      
        $color_pts = '';

        $nb_pts = 0;

        $tab_info_etl =  explode('_',$info_etl);
        $id_etl = $tab_info_etl[0];
        $ref_etl = $tab_info_etl[1];
        $colorEtl = $colorGraph[($ref_etl - 1) % 18 + 1]; // Assure un cycle de couleurs entre 1 et 30

        // Données de l'ETL qui doit s'afficher
        $sql_ETL_data = "SELECT DISTINCT etl.id, etl.hauteur, etl.debit, etl.code_qualite
                        FROM ".TABLE_DATA_ETL_DATA." etl
                        WHERE id_etl=".$id_etl." ORDER BY hauteur ASC";
        $ETL_data_query = tep_db_query($sql_link,$sql_ETL_data);
        while($ETL_data_tab = tep_db_fetch_array($ETL_data_query))
        {
            $id_etl_data = $ETL_data_tab['id'];
            $hauteur = $ETL_data_tab['hauteur'];            
            $debit = $ETL_data_tab['debit'];     
            $code_qualite = $ETL_data_tab['code_qualite'];

            $index_pts .= $id_etl_data.',';
            $graph_x .= $hauteur.',';
            $graph_y .= $debit.',';    

            $nb_pts++;
        }

        

        // On enlève la dernière virgule des variables
        $index_pts = substr($index_pts, 0, -1);
        $graph_x = substr($graph_x, 0, -1);
        $graph_y = substr($graph_y, 0, -1);

        if($nb_pts > 0)
        {        
            $data_graph .=
                "
                    var data_".$id_etl." = 
                    { 
                        x: [". $graph_x ."],
                        y: [". $graph_y ."],   
                        ids: [". $index_pts ."],

                        name: 'ETL - réf : ".$ref_etl."',
                        
                        // Format d'étiquette des données au survol
                        hovertemplate: '<b>Hauteur</b> : %{x:.1f} cm<br>' +
                                        '<b>Débit</b> : %{y:.3f} m³/s', // .3f : 3 chiffre après la virgule
                                                            

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
            
            $load_data .= "data_".$id_etl.",";
        }


        // -----------------------------------------
        // Chargement des pts de JGE

        // Pour l'ETL en cours on récupère date_debut, date_fin

        $sql_etl = "SELECT DISTINCT id, datetime_first, datetime_end
                    FROM ".TABLE_DATA_ETL." etl
                    WHERE id=".$id_etl."
                    ORDER BY datetime_end DESC";
        $etl_query = tep_db_query($sql_link,$sql_etl);
        $etl_tab = tep_db_fetch_array($etl_query);
        $datetime_first = $etl_tab['datetime_first'];
        $datetime_end = $etl_tab['datetime_end'];

        $nb_pts_jge = 0;
        $graph_x_jge = '';
        $graph_y_jge = '';
        $graph_date_jge = '';

        // Maintenant on récupère les pts de JGE correspondant (même station, même période)
        $sql_jge = "SELECT DISTINCT jge.id, jge.datetime, jge.depouil_hmoy, jge.depouil_q
                        FROM ".TABLE_DATA_JGE." jge
                        WHERE jge.id_station = ".$id_station." 
                        AND jge.datetime >= '".$datetime_first."'  
                        AND jge.datetime <= '".$datetime_end."'  
                        AND jge.depouil_hmoy REGEXP '^-?[0-9]+(\.[0-9]+)?$'  -- Vérifie si c'est un nombre
                        AND jge.depouil_hmoy < 9999
                        AND jge.depouil_q REGEXP '^-?[0-9]+(\.[0-9]+)?$'       -- Vérifie si c'est un nombre
                        ORDER BY jge.datetime ASC";

                        
        $jge_query = tep_db_query($sql_link,$sql_jge);
        while($jge_tab = tep_db_fetch_array($jge_query))
        {
            $h_jge = abs($jge_tab['depouil_hmoy']);
            $q_jge = abs($jge_tab['depouil_q']);
            $date_jge = $jge_tab['datetime'];
            $date_jge_formatee = date("d-m-Y H:i:s", strtotime($date_jge));  

            if($nb_pts_jge < 1){$date_first = date("d-m-Y", strtotime($date_jge));}            
            
            if($firstLoad)
            {
                if($max_h < $h_jge){$max_h = $h_jge * 1.15;}
                if($max_q < $q_jge){$max_q = $q_jge * 1.25;}
            }

            $graph_x_jge .= $h_jge.',';
            $graph_y_jge .= $q_jge.',';  
            $graph_date_jge .= "'".$date_jge_formatee."',"; 

            $nb_pts_jge++;
        }

        $graph_x_jge = substr($graph_x_jge, 0, -1);
        $graph_y_jge = substr($graph_y_jge, 0, -1);
        $graph_date_jge = substr($graph_date_jge, 0, -1);

        if($nb_pts_jge > 0)
        {        
            $data_graph_jge .=
                "
                    var data_jge_".$id_etl." = 
                    { 
                        x: [". $graph_x_jge ."],
                        y: [". $graph_y_jge ."],  
                        customdata: [". $graph_date_jge ."],

                        name: 'JGE - réf : ".$ref_etl."',
                        
                        // Format d'étiquette des données au survol
                        hovertemplate:  '<b>Date</b> : %{customdata}<br>' + // Date
                                        '<b>Hauteur</b> : %{x:.1f} cm<br>' +
                                        '<b>Débit</b> : %{y:.3f} m³/s', 

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
                ";
            
            $load_data_jge .= "data_jge_".$id_etl.",";
        }

    }
    $load_data_all = "[".substr($load_data, 0, -1).",".substr($load_data_jge, 0, -1)."]";
}
else // si aucune courbe n'est sélectionnée on affiche que les JGE de toutes la station 
{
    $id_etl = 0;

    $nb_pts_jge = 0;
    $graph_x_jge = '';
    $graph_y_jge = '';
    $graph_date_jge = '';

    // On récupère les pts de JGE correspondant (même station, même période)
    $sql_jge = "SELECT DISTINCT jge.id, jge.datetime, jge.depouil_hmoy, jge.depouil_q
                FROM ".TABLE_DATA_JGE." jge
                WHERE jge.id_station = ".$id_station." 
                AND jge.depouil_hmoy REGEXP '^-?[0-9]+(\.[0-9]+)?$'  -- Vérifie si c'est un nombre
                AND jge.depouil_hmoy < 9999
                AND jge.depouil_q REGEXP '^-?[0-9]+(\.[0-9]+)?$'       -- Vérifie si c'est un nombre
                ORDER BY jge.datetime ASC";
        
    $jge_query = tep_db_query($sql_link,$sql_jge);
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

        if($firstLoad)
        {
            if($max_h < $h_jge){$max_h = $h_jge * 1.15;}
            if($max_q < $q_jge){$max_q = $q_jge * 1.25;}
        }

        $nb_pts_jge++;
    }
    
    $graph_x_jge = substr($graph_x_jge, 0, -1);
    $graph_y_jge = substr($graph_y_jge, 0, -1);
    $graph_date_jge = substr($graph_date_jge, 0, -1);

    if($nb_pts_jge > 0)
    {        
        $data_graph_jge .=
        "
        var data_jge_".$id_etl." = 
        { 
            x: [". $graph_x_jge ."],
            y: [". $graph_y_jge ."],  
            customdata: [". $graph_date_jge ."],

            name: 'JGE',
            
            // Format d'étiquette des données au survol
            hovertemplate:  '<b>Date</b> : %{customdata}<br>' + // Date
                            '<b>Hauteur</b> : %{x:.1f} cm<br>' +
                            '<b>Débit</b> : %{y:.3f} m³/s', 
                                                

            mode: 'markers', // type de trace (scatter plot)
            type: 'scatter', // type de graphique
            marker: {size: 10, symbol: 'cross',color:'#000'}, // taille des marqueurs   
        };  
        ";

        $load_data_jge .= "data_jge_".$id_etl.",";
    }
    $load_data_all = "[".substr($load_data_jge, 0, -1)."]";
}



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
                                size: 13,
                                bold: true,
                                color: '#000000'
                            }
                        },
                        
                        autorange: false,
                        range: [".$min_h.", ".$max_h."], 
                    },
                    yaxis:
                    {
                        title: {
                            text: 'Débit (m<sup>3</sup>/s)',
                            standoff: 20, // Ajuster la distance entre le titre et l'axe
                            font: {
                                family: 'roboto, arial, helvetica',
                                size: 14,
                                bold: true,
                                color: '#000000'
                            }
                        },

                        font: {
                                family: 'roboto, arial, helvetica',
                                size: 12,
                                bold: true,
                                color: '#000000'
                        },
                                            
                        autorange: false,
                        range: [".$min_q.", ".$max_q*1.1."], 
                    },
                    
                    hovermode: '',
                    hoverlabel: { bgcolor: '#fff', font: { size: 15, color: '#000' } },
                    margin: {l: 70, r: 10, t: 20, b: 40}, // Par défault : l: 60, r: 60, t: 80, b: 60 
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
                        
                scrollZoom: true, // Zoom avec la roulette de la souris

                displaylogo: false,
                modeBarOrientation: 'v',
                displayModeBar: true,    // Affichage constant du menu de la figure
                
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
                        {
                            name: 'Export PNG',
                            icon: Plotly.Icons.camera,
                            click: function(gd) {
                                Plotly.downloadImage(gd, {format: 'png', filename: 'mon_grap'});
                            }
                        },
                        'zoom2d',
                        'pan2d',
                        'resetScale2d'
                    ]
                ],

                modeBarButtonsToRemove: ['select2d', 'lasso2d', 'autoScale2d', 'zoomIn2d', 'zoomOut2d']
            };
        ";

// Implémentation des fonctions dynamiques des graphiques 
$textGraphFonction = 
        "
            // Écoute des événements de zoom
            document.getElementById('plot').on('plotly_relayout', function(eventData) 
            {
                if (eventData['xaxis.range[0]'] !== undefined) 
                {
                    // Mettre à jour les inputs pour les valeurs des axes
                    xMin.value = parseFloat(eventData['xaxis.range[0]']).toFixed(1);
                    xMax.value = parseFloat(eventData['xaxis.range[1]']).toFixed(1);
                    yMin.value = parseFloat(eventData['yaxis.range[0]']).toFixed(1);
                    yMax.value = parseFloat(eventData['yaxis.range[1]']).toFixed(1);
                }
            });

            // Écoute de l'événement de double-clic (reset zoom)
            document.getElementById('plot').on('plotly_doubleclick', function() {
                
                // Mettre à jour les inputs pour les valeurs initiales des axes
                xMin.value = parseFloat(".$min_h.").toFixed(1);
                xMax.value = parseFloat(".$max_h.").toFixed(1);
                yMin.value = parseFloat(".$min_q.").toFixed(1);
                yMax.value = parseFloat(".$max_q.").toFixed(1);
            });

            // Permet d'ajuster le zoom du graphique à partir des données potentiellement rentrée dans les inputs de coordonnées
            document.getElementById('ajustCoord').addEventListener('click', function() {
                const xMin_zoom = parseFloat(xMin.value);
                const xMax_zoom = parseFloat(xMax.value);
                const yMin_zoom = parseFloat(yMin.value);
                const yMax_zoom = parseFloat(yMax.value);

                if (!isNaN(xMin_zoom) && !isNaN(xMax_zoom) && !isNaN(yMin_zoom) && !isNaN(yMax_zoom)) 
                {
                    Plotly.relayout('plot', {
                        'xaxis.range': [xMin_zoom, xMax_zoom],
                        'yaxis.range': [yMin_zoom, yMax_zoom]
                    });
                }
            });
        ";         

// Préparation des données à renvoyer coté Client
$editGraph = 
    "
        Plotly.newPlot('plot',".$load_data_all.",layout,config);
        actionPtsGraph('plot');
    ";

$responseData = array(
    'js_text' => $config_graph.$data_graph.$data_graph_jge.$layout_graph.$editGraph.$textGraphFonction,
    'date_first' => $date_first,
    'min_h' => $min_h,
    'max_h' => $max_h,
    'min_q' => $min_q,
    'max_q' => $max_q
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>