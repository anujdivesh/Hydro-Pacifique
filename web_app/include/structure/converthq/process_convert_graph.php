<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export 
- Ce script permet de générer les graphs pour la page de conversion C -> Q
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


// DATA TYPE AXE 
$sql_data_type_axe = "SELECT DISTINCT id, axe, unite FROM ".TABLE_DATA_TYPE_AXE;
$data_type_axe_query = tep_db_query($sql_link,$sql_data_type_axe);
while ($data_type_axe = tep_db_fetch_array($data_type_axe_query))
{				
	$data_type_axe_array[$data_type_axe['id']] = array('axe' => $data_type_axe['axe'],
														'unite' => $data_type_axe['unite']
														);
} 

// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, 
                                    axe_data, unite, to_periode, id_chon_periode
				  FROM ".TABLE_TYPE_DATA." 
				  ORDER BY init_type_data ASC";
$type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
while ($type_chron_tab = tep_db_fetch_array($type_chron_query))
{
    $axe_nom = '';
    if(isset($data_type_axe_array[$type_chron_tab['axe_data']]['axe'])){$axe_nom = $data_type_axe_array[$type_chron_tab['axe_data']]['axe'];}

	$type_chron_array[$type_chron_tab['id_data_type']] = array('init_type_data' => $type_chron_tab['init_type_data'],
															'nom_type_data' => $type_chron_tab['nom_type_data'],
															'id_eq_type_data' => $type_chron_tab['id_eq_type_data'],
															'axe_nom' => $axe_nom,
															'unite' => $type_chron_tab['unite'],
															'to_periode' => $type_chron_tab['to_periode'],
															'id_chon_periode' => $type_chron_tab['id_chon_periode']
															);
}


// Récupération des données JSON envoyées depuis la requête AJAX
$jsonDataGraph = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataJson = json_decode($jsonDataGraph, true);

// Accéder aux données du tableau récupérer
$timezone_php = $dataJson['timezone_php'];

$station_chron = $dataJson['idStation'];

$typedata_chron_h = $dataJson['typedataChronH'];
$typedata_chron_q = $dataJson['typedataChronQ'];

$color_h = $dataJson['colorH'];
$color_q = $dataJson['colorQ'];
$check_lac_h = $dataJson['checkLacH'];
$check_lac_q = $dataJson['checkLacQ'];

$color_qcal = #28B463;


$reload = $dataJson['reload'];

$date_first = DateTime::createFromFormat('d-m-Y', $dataJson['xDateMin']);
$date_end = DateTime::createFromFormat('d-m-Y', $dataJson['xDateMax']);

$y_min1 = $dataJson['yHauteurMin'];
$y_max1 = $dataJson['yHauteurMax'];
$y_min2 = $dataJson['yDebitMin'];
$y_max2 = $dataJson['yDebitMax'];


$data_graph = '';
$load_data = '';

// ---------------------------------------------
// Chron. HAUTEUR EAU

$graph_x_h = '';
$graph_y_h = '';       

$nb_data_h = 0;
$nb_data_q = 0;

$yaxis_nom = '';
$yaxis_unite = '';
$yaxis2_nom = '';
$yaxis2_unite = '';

$condition_date = '';


if(tep_not_null($typedata_chron_h))
{
    $lacune_date_first = '';    
    $edit_lacune_h = '';

    $yaxis_nom = $type_chron_array[$typedata_chron_h]['axe_nom'];
    $yaxis_unite = " (".$type_chron_array[$typedata_chron_h]['unite']." )";

    // Données de la chronique de hauteur d'eau qui doit s'afficher
    $sql_chron_h = "SELECT da.dateheure, da.valeur, dm.id_station, dm.id, dm.id_typedata, dm.id_codequal
                    FROM ".TABLE_DATA_ALL." da
                    JOIN ".TABLE_DATA_META." dm ON da.id_meta=dm.id
                    WHERE dm.id_typedata = ".$typedata_chron_h."
                    AND dm.id_station = ".$station_chron.
                    $condition_date."
                    ORDER BY da.dateheure ASC";

    $chron_h_query = tep_db_query($sql_link,$sql_chron_h);
    
    while($chron_h_tab = tep_db_fetch_array($chron_h_query))
    {
        $valeur = $chron_h_tab['valeur'];            
        $dateheure = $chron_h_tab['dateheure'];  
                
        $datetime_h = DateTime::createFromFormat('Y-m-d H:i:s', $chron_h_tab['dateheure']);

        if(!$date_first){$date_first = $datetime_h;}
        if(!$date_end){$date_end = $datetime_h;}

        // Comparer les dates
        if(!$reload)
        {
            if ($datetime_h < $date_first) {$date_first = $datetime_h;}
            if ($datetime_h > $date_end) {$date_end = $datetime_h;}
        }

        
        // Si il y a une lacune en cours
        if(tep_not_null($lacune_date_first))
        {                
            $edit_lacune_h .= $edit_lacune_temp;

            if(($valeur > -8888) && ($valeur < 99999) ) // fin de lacune
            {
                $graph_x_h .= "'".$chron_h_tab['dateheure']."',";
                $graph_y_h .= $valeur.",";    

                if(!$reload)
                {
                    if($valeur > $y_max1){$y_max1 = $valeur;}
                    if($valeur < $y_min1){$y_min1 = $valeur;} 
                } 
                
                $nb_data_h++;      
            }  
            else 
            {
                $graph_x_h .= "'".$dateheure."',";
                $graph_y_h .= "null,";

                $edit_lacune_h .= "   x1: '".$dateheure."',";
            }

            $edit_lacune_h .= "       y1: 1,
                                    fillcolor: '".$color_h."',
                                    opacity: 0.15,
                                    line: {
                                        width: 0
                                    }
                                }";      

            $lacune_date_first=''; // réinitialisation début lacune en cours 

        }   
        else // pas de lacune en cours
        {
            if(($valeur > -8888) && ($valeur < 99999) ) // pas une lacune
            {
                $graph_x_h .= "'".$dateheure."',";
                $graph_y_h .= $valeur.",";  

                if(!$reload)
                {
                    if($valeur > $y_max1){$y_max1 = $valeur;}
                    if($valeur < $y_min1){$y_min1 = $valeur;} 
                }               
                
                $nb_data_h++;      
            }  
            else // on rencontre une lacune
            {
                $graph_x_h .= "'".$dateheure."',";
                $graph_y_h .= "null,";

                $edit_lacune_h .= ","; // on ajoute une virgule entre chaque lacunes

                $edit_lacune_temp = "        
                                        {
                                            type: 'rect',
                                            xref: 'x', // x-reference is assigned to the x-values                               
                                            yref: 'paper',  // y-reference is assigned to the plot paper [0,1]                           
                                            x0: '".$dateheure."',
                                            y0: 0,
                                        ";

                $lacune_date_first = $dateheure;                                        

            }
        }  
    }
    // On enlève la dernière virgule des variables
    $graph_x_h = substr($graph_x_h, 0, -1);
    $graph_y_h = substr($graph_y_h, 0, -1);   


    // ---------------------------------------------
    // Chron. DEBIT EXISTANT

    $graph_x_q = '';
    $graph_y_q = ''; 

    $edit_lacune_q = '';

    $yaxis2_titre = $type_chron_array[$typedata_chron_q]['axe_nom']." (".$type_chron_array[$typedata_chron_q]['unite'];

    // Données de la chronique de débit qui doit s'afficher
    $sql_chron_q = "SELECT da.dateheure, da.valeur, dm.id_station, dm.id, dm.id_typedata, dm.id_codequal
                    FROM ".TABLE_DATA_ALL." da
                    JOIN ".TABLE_DATA_META." dm ON da.id_meta=dm.id
                    WHERE dm.id_typedata = ".$typedata_chron_q."
                    AND dm.id_station = ".$station_chron.
                    $condition_date."
                    ORDER BY da.dateheure ASC";


    $chron_q_query = tep_db_query($sql_link,$sql_chron_q);    
    while($chron_q_tab = tep_db_fetch_array($chron_q_query))
    {
        $valeur = $chron_q_tab['valeur'];            
        $dateheure = $chron_q_tab['dateheure'];     
        $datetime_q = DateTime::createFromFormat('Y-m-d H:i:s', $chron_q_tab['dateheure']); 

        // Si il y a une lacune en cours
        if(tep_not_null($lacune_date_first))
        {                
            $edit_lacune_q .= $edit_lacune_temp;

            if(($valeur > -8888) && ($valeur < 99999) )
            {
                $graph_x_q .= "'".$dateheure."',";
                $graph_y_q .= $valeur.",";    

                if(!$reload)
                {
                    if($valeur > $y_max2){$y_max2 = $valeur;}
                    if($valeur < $y_min2){$y_min2 = $valeur;} 
                }             
                
                $nb_data_q++;     
            }          
            else 
            {
                $graph_x_q .= "'".$dateheure."',";
                $graph_y_q .= "null,";

                $edit_lacune_q .= "   x1: '".$dateheure."',";                
            }

            $edit_lacune_q .= "       y1: 1,
                                    fillcolor: '".$color_q."',
                                    opacity: 0.15,
                                    line: {
                                        width: 0
                                    }
                                }";      

            $lacune_date_first=''; // réinitialisation début lacune en cours 
        }   
        else // pas de lacune en cours
        {
            if(($valeur > -8888) && ($valeur < 99999) ) // pas une lacune
            {
                $graph_x_q .= "'".$dateheure."',";
                $graph_y_q .= $valeur.",";    

                if(!$reload)
                {
                    if($valeur > $y_max2){$y_max2 = $valeur;}
                    if($valeur < $y_min2){$y_min2 = $valeur;} 
                }
                
                $nb_data_q++;      
            }  
            else // on rencontre une lacune
            {
                $graph_x_q .= "'".$dateheure."',";
                $graph_y_q .= "null,";

                $edit_lacune_q .= ","; // on ajoute une virgule entre chaque lacunes

                $edit_lacune_temp = "        
                                        {
                                            type: 'rect',
                                            xref: 'x', // x-reference is assigned to the x-values                               
                                            yref: 'paper',  // y-reference is assigned to the plot paper [0,1]                           
                                            x0: '".$dateheure."',
                                            y0: 0,
                                        ";

                $lacune_date_first = $dateheure;       
            }
        }
    }
    // On enlève la dernière virgule des variables
    $graph_x_q = substr($graph_x_q, 0, -1);
    $graph_y_q = substr($graph_y_q, 0, -1); 

    // ---------------------------------------------
    // Chron. DEBIT NEW

    $graph_x_q_n = '';
    $graph_y_q_n = ''; 

    $nb_data_q_n = 0;

    
    $yaxis2_nom = $type_chron_array[$typedata_chron_q]['axe_nom'];
    $yaxis2_unite = " (".$type_chron_array[$typedata_chron_q]['unite']." )";
    
    // Données de la chronique de débit qui doit s'afficher
    $sql_chron_q_n = "SELECT da.dateheure, da.valeur, dm.id_station, dm.id, dm.id_typedata, dm.id_codequal
                    FROM ".TABLE_DATA_ALL_CORRECTION." da
                    JOIN ".TABLE_DATA_META_CORRECTION." dm ON da.id_meta=dm.id
                    AND dm.id_typedata = ".$typedata_chron_q."
                    AND dm.id_station = ".$station_chron.
                    $condition_date."
                    AND dm.source='Conversion'
                    ORDER BY da.dateheure ASC";


    $chron_q_n_query = tep_db_query($sql_link,$sql_chron_q_n);    
    while($chron_q_n_tab = tep_db_fetch_array($chron_q_n_query))
    {
        $valeur = $chron_q_n_tab['valeur'];            
        $dateheure = $chron_q_n_tab['dateheure'];     
        $datetime_q_n = DateTime::createFromFormat('Y-m-d H:i:s', $chron_q_n_tab['dateheure']); 

        if(($valeur > -8888) && ($valeur < 99999) )
        {
            $graph_x_q_n .= "'".$dateheure."',";
            $graph_y_q_n .= $valeur.",";
            
            if($valeur > $y_max2){$y_max2 = $valeur;}
            if($valeur < $y_min2){$y_min2 = $valeur;} 
           
            $nb_data_q_n++;     
        }         
        else 
        {
            $graph_x_q_n .= "'".$dateheure."',";
            $graph_y_q_n .= "null,";              
        }
    }
    // On enlève la dernière virgule des variables
    $graph_x_q_n = substr($graph_x_q_n, 0, -1);
    $graph_y_q_n = substr($graph_y_q_n, 0, -1); 
}


    if($nb_data_h > 0)
    {        
        $data_graph .=
            "
                var data_h = 
                { 
                    x: [". $graph_x_h ."],
                    y: [". $graph_y_h ."],   

                    name: '".$type_chron_array[$typedata_chron_h]['init_type_data']." - ".$type_chron_array[$typedata_chron_h]['nom_type_data']."',
                    
                    yaxis: 'y',

                    // Format d'étiquette des données au survol
                    hovertemplate:  '<b>Date</b> : %{x|%d-%m-%Y %H:%M:%S}<br>' +
                                    '<b>".$type_chron_array[$typedata_chron_h]['axe_nom']."</b> : %{y:.3f} ".$type_chron_array[$typedata_chron_h]['unite']."<extra></extra>',


                    mode: 'lines', // type de trace (scatter plot)
                    line: {color: '".$color_h."'},
                    type: 'scatter', // type de graphique
                }; 
            ";
        $load_data .= "data_h,";
    }

    if($nb_data_q > 0)
    {        
        $data_graph .=
            "
                var data_q = 
                { 
                    x: [". $graph_x_q ."],
                    y: [". $graph_y_q ."],   

                    name: '".$type_chron_array[$typedata_chron_q]['init_type_data']." - ".$type_chron_array[$typedata_chron_q]['nom_type_data']."',
                    
                    yaxis: 'y2',

                    // Format d'étiquette des données au survol
                    hovertemplate:  '<br>' +
                                    '<b>Date</b> : %{x|%d-%m-%Y %H:%M:%S}<br>' +
                                    '<b>".$type_chron_array[$typedata_chron_q]['axe_nom']."</b> : %{y:.3f} ".$type_chron_array[$typedata_chron_q]['unite']."<extra></extra>',
                                    

                    mode: 'lines', // type de trace (ligne)  
                    line: {color: '".$color_q."'},
                    type: 'scatter', // type de graphique
                }; 
            ";
        $load_data .= "data_q,";
    }

    if($nb_data_q_n > 0)
    {        
        $data_graph .=
            "
                var data_q_n = 
                { 
                    x: [". $graph_x_q_n ."],
                    y: [". $graph_y_q_n ."],   

                    name: '".$type_chron_array[$typedata_chron_q]['init_type_data']." - En cours de validation',
                    
                    yaxis: 'y2',

                    // Format d'étiquette des données au survol
                    hovertemplate:  '<br>' +
                                    '<b>Date</b> : %{x|%d-%m-%Y %H:%M:%S}<br>' +
                                    '<b>".$type_chron_array[$typedata_chron_q]['axe_nom']."</b> : %{y:.3f} ".$type_chron_array[$typedata_chron_q]['unite']."<extra></extra>',
                                    

                    mode: 'lines', // type de trace (ligne)  
                    line: {color: '".$color_qcal."'},
                    type: 'scatter', // type de graphique
                }; 
            ";
        $load_data .= "data_q_n,";
    }

    $load_data = substr($load_data, 0, -1); 
    $load_data_all = "[".$load_data."]";
    
// Choix d'affichage des lacunes ou pas
$affiche_lac = '';
if($check_lac_h && $check_lac_q){$affiche_lac = "shapes: [".$edit_lacune_h.",".$edit_lacune_q."],";}
if($check_lac_h && !$check_lac_q){$affiche_lac = "shapes: [".$edit_lacune_h."],";}
if(!$check_lac_h && $check_lac_q){$affiche_lac = "shapes: [".$edit_lacune_q."],";}



$date_first_str = '';$date_end_str = '';
if ($date_first !== false && $date_end !== false) 
{
    $date_first_str = $date_first->format('Y-m-d');
    $date_end_str = $date_end->format('Y-m-d');
}

$layout_graph =
            "
                var layout = 
                {
                    xaxis: 
                    {
                        title: {
                            text: 'Date',
                            standoff: 5, // Ajuster la distance entre le titre et l'axe
                        },  
                        autorange: false,
                        range: ['".$date_first_str."', '".$date_end_str."'],
                                  
                        tickfont: {size: 11}, // Taille des caractères des graduations
                        titlefont: {family: 'roboto, arial, helvetica',
                                    size: 14,
                                    bold: true,
                                    color: '#000000'},                                
                        tickangle: 0,
                        ticklen: 5,
                        showline: true,
                        linewidth: 1,
                        automargin: true,                  
                    },
                    yaxis:
                    {
                        title: {
                            text: '".$yaxis_nom.$yaxis_unite."',
                            standoff: 10, // Ajuster la distance entre le titre et l'axe
                        },
                            
                        autorange: false,
                        range:[".($y_min1).",".($y_max1*1.1)."],
                        tickfont: {size: 11}, // Taille des caractères des graduations
                        titlefont: {family: 'roboto, arial, helvetica',
                                size: 14,
                                bold: true,
                                color: '#000000'},
                        tickformat: '.0f',
                        ticklen: 5,
                        showline: true,
                        linewidth: 1,

                        automargin: true,                    
                    },

                    yaxis2:
                    {
                        title: {
                            text: '".$yaxis2_nom.$yaxis2_unite."',
                            standoff: 15, // Ajuster la distance entre le titre et l'axe
                        },
                            
                        autorange: false,
                        range:[".($y_min2).",".($y_max2*1.1)."],
                        
                        tickfont: {size: 11}, // Taille des caractères des graduations
                        titlefont: {family: 'roboto, arial, helvetica',
                                size: 14,
                                bold: true,
                                color: '#000000'},
                        tickformat: '.0f',
                        ticklen: 5,
                        showline: true,
                        linewidth: 1,

                        overlaying: 'y',
                        side: 'right',

                        automargin: true,
                    },
                    
                    hovermode: 'x unified',
                    hoverlabel: {font: {size: 11} },
                    /*hoverlabel: {bgcolor: '#fff', font: {size: 14, color: '#000'} },*/
                    margin: {l: 50, r: 10, t: 0, b: 40}, // Par défault : l: 60, r: 60, t: 80, b: 60 
                    showlegend: true,
                    legend: 
                    {
                        x: 0,
                        y: 1.05,
                        orientation: 'h',
                    },


                    // Affichage lacunes
                    ".$affiche_lac."
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

// Implémentation des fonctions dynamiques des graphiques 
$textGraphFonction = "

            xDateMin.value = '".$date_first->format('d-m-Y')."';
            xDateMax.value = '".$date_end->format('d-m-Y')."';
            yHauteurMin.value = parseInt(".$y_min1.");
            yHauteurMax.value = parseInt(".$y_max1.");
            yDebitMin.value = parseInt(".$y_min2.");
            yDebitMax.value = parseInt(".$y_max2.");
            
            xMin = 0;xMax = 0;

            // Écoute des événements de zoom
            boxPlot.on('plotly_relayout', function(eventData) 
            {
                if ((eventData['xaxis.range[0]'] && eventData['xaxis.range[1]']) ||
                    (eventData['yaxis.range[0]'] && eventData['yaxis.range[1]']) ||
                    (eventData['yaxis2.range[0]'] && eventData['yaxis2.range[1]'])) 
                {

                    var x1 = eventData['xaxis.range[0]'];
                    var x2 = eventData['xaxis.range[1]'];
                    var y1 = eventData['yaxis.range[0]'];
                    var y2 = eventData['yaxis.range[1]'];
                    var y1_2 = eventData['yaxis2.range[0]'];
                    var y2_2 = eventData['yaxis2.range[1]'];

                    xMin = x1;
                    xMax = x2;

                    // Convertir les dates au format 'yyyy-mm-dd' en 'dd-mm-yyyy'
                    var x1_format = x1.split(' ')[0].split('-').reverse().join('-');
                    var x2_format = x2.split(' ')[0].split('-').reverse().join('-');

                    xDateMin.value = x1_format;
                    xDateMax.value = x2_format;

                    if (typeof y1 !== 'undefined' && !isNaN(y1)) {
                        yHauteurMin.value = parseInt(y1);
                    }
                    if (typeof y2 !== 'undefined' && !isNaN(y2)) {
                        yHauteurMax.value = parseInt(y2);
                    }
                    if (typeof y1_2 !== 'undefined' && !isNaN(y1_2)) {
                        yDebitMin.value = parseInt(y1_2);
                    }
                    if (typeof y2_2 !== 'undefined' && !isNaN(y2_2)) {
                        yDebitMax.value = parseInt(y2_2);
                    }
                }


                // Petit boucle pour compter le nombre de données qu'il y a dans l'affichage du zoom
                // Ca va permettre de bien agrémenter le compteur d'avancement en cas de conversion 
                var visibleData = [];
                boxPlot.data.forEach((trace) => 
                {
                    if (trace.x && trace.y)
                    {
                        for (let i = 0; i < trace.x.length; i++) 
                        {
                            if (trace.x[i] >= xMin && trace.x[i] <= xMax) 
                            {
                                visibleData.push({
                                    x: trace.x[i],
                                    y: trace.y[i]
                                });
                            }
                        }
                    }
                });
                nb_data_all = visibleData.length;
            });

           
            // Écoute de l'événement de double-clic (reset zoom)
            boxPlot.on('plotly_doubleclick', function() 
            {   
                var layout = boxPlot.layout;

                // Récupérer les plages des axes après le dézoom
                var x1 = layout['xaxis.range[0]'];
                var x2 = layout['xaxis.range[1]'];
                var y1 = layout['yaxis.range[0]'];
                var y2 = layout['yaxis.range[1]'];
                var y1_2 = layout['yaxis2.range[0]'];
                var y2_2 = layout['yaxis2.range[1]'];

                if (x1 && x2) 
                {
                    // Convertir les dates au format 'yyyy-mm-dd' en 'dd-mm-yyyy'
                    var x1_format = x1.split(' ')[0].split('-').reverse().join('-');
                    var x2_format = x2.split(' ')[0].split('-').reverse().join('-');

                    // Mettre à jour les champs avec les nouvelles plages des axes
                    xDateMin.value = x1_format;
                    xDateMax.value = x2_format;
                }


                if (typeof y1 !== 'undefined' && !isNaN(y1)) {
                    yHauteurMin.value = parseInt(y1);
                }
                if (typeof y2 !== 'undefined' && !isNaN(y2)) {
                    yHauteurMax.value = parseInt(y2);
                }
                if (typeof y1_2 !== 'undefined' && !isNaN(y1_2)) {
                    yDebitMin.value = parseInt(y1_2);
                }
                if (typeof y2_2 !== 'undefined' && !isNaN(y2_2)) {
                    yDebitMax.value = parseInt(y2_2);
                }
            });
        ";         


        // Préparation des données à renvoyer coté Client
$editGraph = 
    "
        Plotly.newPlot('plot_0',".$load_data_all.",layout,config);        
    ";

$responseData = array(
    'js_text' =>  $config_graph.$data_graph.$layout_graph.$editGraph.$textGraphFonction,
    'nb_data_all' => $nb_data_h
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>