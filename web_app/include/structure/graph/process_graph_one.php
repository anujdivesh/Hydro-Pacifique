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
$id_station_axe1 = $dataJson['idStationAxe1'];
$id_chron_axe1 = $dataJson['idChronAxe1'];
$sqlAxe1 = $dataJson['sqlAxe1'];
$id_station_axe2 = $dataJson['idStationAxe2'];
$id_chron_axe2 = $dataJson['idChronAxe2'];
$sqlAxe2 = $dataJson['sqlAxe2'];

$reload = $dataJson['reload'];

$date_first = DateTime::createFromFormat('d-m-Y', $dataJson['dateFirst']);
$date_end = DateTime::createFromFormat('d-m-Y', $dataJson['dateEnd']);

$y_min1 = $dataJson['yMin1'];
$y_max1 = $dataJson['yMax1'];
$y_min2 = $dataJson['yMin2'];
$y_max2 = $dataJson['yMax2'];

$color_axe1 = $dataJson['colorAxe1'];
$color_axe2 = $dataJson['colorAxe2'];

// ------------------------------------------------------            
// Récupération des données Station et Chronique dans la base

// DATA TYPE AXE 
$sql_data_type_axe = "SELECT DISTINCT id, axe, unite FROM ".TABLE_DATA_TYPE_AXE;
$data_type_axe_query = tep_db_query($sql_link,$sql_data_type_axe);
while ($data_type_axe = tep_db_fetch_array($data_type_axe_query))
{				
	$data_type_axe_array[$data_type_axe['id']] = array('axe' => html_entity_decode($data_type_axe['axe'] ?? $default_string),
														'unite' => html_entity_decode($data_type_axe['unite'] ?? $default_string)
														);
} 

if($id_station_axe1 > 0)
{
    // TABLE DATA_CHRON AXE 1 (TYPE CHRON - CI,PI, CIE, ...)
    $sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, traitement, type_graph
                        FROM ".TABLE_TYPE_DATA." 
                        WHERE id_data_type=".$id_chron_axe1;
    $type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
    $type_chron_tab = tep_db_fetch_array($type_chron_query);

    $init_type_data_axe1 = $type_chron_tab['init_type_data'];
    $nom_type_data_axe1 = $type_chron_tab['nom_type_data'];
    $axe_data_axe1 = $data_type_axe_array[$type_chron_tab['axe_data']]['axe'];
    $unite_axe1 = $type_chron_tab['unite'];
    $traitement_axe1 = $type_chron_tab['traitement'];
    $type_graph_axe1 = $type_chron_tab['type_graph'];        
    $id_eq_type_data_axe1 = $type_chron_tab['id_eq_type_data'];


    // TABLE TYPE DE DONNEES AXE 1
    $sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type 
                    FROM ".TABLE_EQ_TYPE." 
                    WHERE id_eq_type=".$id_eq_type_data_axe1;
    $eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
    $eq_type_tab = tep_db_fetch_array($eq_type_query);
    $nom_eq_type_axe1 = isset($eq_type_tab['nom_eq_type']) ? $eq_type_tab['nom_eq_type'] : '';

    // TABLE STATION AXE 1
    $sql_station = "SELECT DISTINCT id_station, nom_station, code_station, active_station, station_type
                    FROM ".TABLE_STATION." 
                    WHERE id_station=".$id_station_axe1;
    $station_query = tep_db_query($sql_link,$sql_station);
    $station_tab = tep_db_fetch_array($station_query);        
    $nom_station_axe1 = isset($station_tab['nom_station']) ? $station_tab['nom_station'] : '';
    $nom_station_axe1 = affichelettres($nom_station_axe1,18);
}

// ----------------------------------------------------------

if($id_station_axe2 > 0)
{
    // TABLE DATA_CHRON AXE 2 (TYPE CHRON - CI,PI, CIE, ...)
    $sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, traitement, type_graph
                        FROM ".TABLE_TYPE_DATA." 
                        WHERE id_data_type=".$id_chron_axe2;
    $type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
    $type_chron_tab = tep_db_fetch_array($type_chron_query);

    $init_type_data_axe2 = $type_chron_tab['init_type_data'];
    $nom_type_data_axe2 = $type_chron_tab['nom_type_data'];
    $axe_data_axe2 = $data_type_axe_array[$type_chron_tab['axe_data']]['axe'];
    $unite_axe2 = $type_chron_tab['unite'];
    $traitement_axe2 = $type_chron_tab['traitement'];
    $type_graph_axe2 = $type_chron_tab['type_graph'];        
    $id_eq_type_data_axe2 = $type_chron_tab['id_eq_type_data'];

    // TABLE TYPE DE DONNEES AXE 2
    $sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type 
                    FROM ".TABLE_EQ_TYPE." 
                    WHERE id_eq_type=".$id_eq_type_data_axe2;
    $eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
    $eq_type_tab = tep_db_fetch_array($eq_type_query);
    $nom_eq_type_axe2 = isset($eq_type_tab['nom_eq_type']) ? $eq_type_tab['nom_eq_type'] : '';

    // TABLE STATION AXE 2
    $sql_station = "SELECT DISTINCT id_station, nom_station, code_station, active_station, station_type
                    FROM ".TABLE_STATION." 
                    WHERE id_station=".$id_station_axe2;
    $station_query = tep_db_query($sql_link,$sql_station);
    $station_tab = tep_db_fetch_array($station_query);        
    $nom_station_axe2 = isset($station_tab['nom_station']) ? $station_tab['nom_station'] : '';
    $nom_station_axe2 = affichelettres($nom_station_axe2,18);
}



// Initialisation des variables pour l'affichage du graph

$lacune_date_first = '';
$edit_lacune_temp = '';
$html_tab_lacune_temp = '';

$nb_data_axe1 = 0;        
$nb_lacunes_axe1 = 0;
$nb_data_axe2 = 0;        
$nb_lacunes_axe2 = 0;

$graph_x_axe1 = '';
$graph_y_axe1 = '';
$text_yaxis1 = '';

$graph_x_axe2 = '';
$graph_y_axe2 = '';
$text_yaxis2 = '';

//$html_tab_lacune_axe1 = '';


$data_graph = '';
$load_data = '';

$edit_lacune_axe1 = '';
$edit_lacune_axe2 = '';

// ------------------------------------------------
// AXE 1 
if($id_station_axe1 > 0)
{
    $text_yaxis1 = $axe_data_axe1." (".$unite_axe1.")";

    $cumul = 0; // Initialisation de la variable nécessaire pour l'affichage en cumul

    // On récupère les données pour la chronique axe 1
    $data_chron_query = tep_db_query($sql_link,$sqlAxe1);
    while($data_chron_tab = tep_db_fetch_array($data_chron_query))
    {   
        // Convertir la date de la donnée en objet DateTime
        $date_chron = new DateTime($data_chron_tab['dateheure']);

        if($nb_data_axe1>0)
        {
            $graph_x_axe1 .= ',';
            $graph_y_axe1 .= ','; 
        }  
        else
        {
            if(!$reload){$date_first = $date_chron;}
        }              

        if(!$reload){$date_end = $date_chron;}
        
        $nb_data_axe1++;

        // Comparer les dates
        /*
        if(!$reload)
        {
            if ($date_chron < $date_first) {$date_first = $date_chron;}
            if ($date_chron > $date_end) {$date_end = $date_chron;}
        }
        */
        
        
        // Si on a une lacune en cours
        if(tep_not_null($lacune_date_first))
        {
            $edit_lacune_axe1 .= $edit_lacune_temp;
            //$html_tab_lacune_axe1 .= $html_tab_lacune_temp;
            
            if($data_chron_tab['valeur'] > (-8888) && $data_chron_tab['valeur'] < (99999) )
            {
                $graph_x_axe1 .= "'".$data_chron_tab['dateheure']."'";

                if($traitement_axe1 == 0){$valeur = abs($data_chron_tab['valeur']);} // données simple (valeur)
                if($traitement_axe1 == 1){$valeur += abs($data_chron_tab['valeur']);} // Cumul

                $graph_y_axe1 .= $valeur;      

                if(!$reload)
                {
                    if($valeur > $y_max1){$y_max1 = $valeur;}
                    if($valeur < $y_min1){$y_min1 = $valeur;} 
                }

                $edit_lacune_axe1 .= "   x1: '".$lacune_date_first."',";
                //$html_tab_lacune_axe1 .= "<td style='height:15px;'>".$lacune_date_first_fr."</td></tr>";
            }
            else
            {
                $graph_x_axe1 .= "'".$data_chron_tab['dateheure']."',";                    
                $graph_y_axe1 .= 'null,';
                
                $chron_dateheure_tab = explode(' ',$data_chron_tab['dateheure']); 
                $chron_dateheure_fr = dateus_fr($chron_dateheure_tab[0]).' '.$chron_dateheure_tab[1];

                $edit_lacune_axe1 .= "   x1: '".$data_chron_tab['dateheure']."',";
                //$html_tab_lacune_axe1 .= "<td style='height:15px;'>".$chron_dateheure_fr."</td></tr>";
                //${'tab_lacunes_'.$cle_station.'_'.$typedata_chron}[${'nb_lacunes_'.$cle_station.'_'.$typedata_chron}]['date_end'] = $data_chron_tab['dateheure']; 
            }

            
            $edit_lacune_axe1 .= "  y1: 1,
                                    fillcolor: '".$color_axe1."',
                                    opacity: 0.15,
                                    line: {width: 0},
                                    customType: 'axe1' // Propriété pour distinguer l'axe lié au shape (lacunes)
                                }";                

            $nb_lacunes_axe1++;                      
            $lacune_date_first=''; // réinitialisation lacune

        }
        else // pas de lacune en cours
        {
            if($data_chron_tab['valeur'] > (-8888) && $data_chron_tab['valeur'] < (99999) )
            {
                $graph_x_axe1 .= "'".$data_chron_tab['dateheure']."'";

                if($traitement_axe1 == 0){$valeur = abs($data_chron_tab['valeur']);} // données simple (valeur)
                if($traitement_axe1 == 1){$valeur += abs($data_chron_tab['valeur']);} // Cumul

                $graph_y_axe1 .= $valeur;

                if(!$reload)
                {
                    if($valeur > $y_max1){$y_max1 = $valeur;}
                    if($valeur < $y_min1){$y_min1 = $valeur;} 
                }               
            }
            else
            {
                $graph_x_axe1 .= "'".$data_chron_tab['dateheure']."'";                    
                $graph_y_axe1 .= 'null';

                $html_tab_lacune_temp = '';
                $edit_lacune_axe1 .= ","; // on ajoute une virgule entre chaque lacunes

                $edit_lacune_temp = "        
                                {
                                    type: 'rect',
                                    xref: 'x', // x-reference is assigned to the x-values                               
                                    yref: 'paper',  // y-reference is assigned to the plot paper [0,1]                           
                                    x0: '".$data_chron_tab['dateheure']."',
                                    y0: 0,
                                ";

                $lacune_date_first = $data_chron_tab['dateheure'];
            }     
        }
    }


    // Choix du type de graphique (Lignes ou Bar)
    $code_type_graph = '';
    if($type_graph_axe1=='lines')
    {
        //$code_type_graph = "mode: 'lines+markers',";
        $code_type_graph = "mode: 'lines',";
        $code_type_graph .= "type: 'scatter',";
    }
    if($type_graph_axe1=='bar'){$code_type_graph = "type: 'bar',";}


    // Paramétrage de la config générale du graphique (JS)
    $data_graph .=  "
                    var data_axe1 = 
                    { 
                        hovermode: 'closest',
                        
                        x: [".$graph_x_axe1."],
                        y: [".$graph_y_axe1."],
                        
                        ".$code_type_graph." // Bar, lines, scatter, ...
                        name: '".$nom_station_axe1." - ".$init_type_data_axe1."',

                        // Format d'étiquette des données au survol
                        hovertemplate: '<br><b>Date</b> : %{x|%d-%m-%Y %H:%M:%S}<br>' +
                                        '<b>".$axe_data_axe1."</b> : %{y:.3f} ".$unite_axe1."',
                        
                        marker: {
                            color: '".$color_axe1."',
                            line: {
                                color: '".$color_axe1."',
                                width: 2,  
                            }  
                            
                        },   
                            
                        line: {
                            color: '".$color_axe1."',
                            width: 1.5,  
                        } 
                    };
                ";
}

// ------------------------------------------------
// AXE 2
if($id_station_axe2 > 0)
{
    $text_yaxis2 = $axe_data_axe2." (".$unite_axe2.")";

    $cumul = 0; // Initialisation de la variable nécessaire pour l'affichage en cumul

    // Pour chaque chronique on récupère les données
    $data_chron_query = tep_db_query($sql_link,$sqlAxe2);
    while($data_chron_tab = tep_db_fetch_array($data_chron_query))
    {     
        // Convertir la date de la donnée en objet DateTime
        $date_chron = new DateTime($data_chron_tab['dateheure']);
        if(($nb_data_axe2<1) && ($date_chron < $date_first) && !$reload)
        {
            $date_first = $date_chron;
        }

        if(($date_chron > $date_end) && !$reload)
        {
            $date_end = $date_chron;
        }

        if($nb_data_axe2>0)
        {
            $graph_x_axe2 .= ',';
            $graph_y_axe2 .= ','; 
        }                
        $nb_data_axe2++;
        

        
        // Si on a une lacune en cours
        if(tep_not_null($lacune_date_first))
        {
            $edit_lacune_axe2 .= $edit_lacune_temp;
            //$html_tab_lacune_axe1 .= $html_tab_lacune_temp;
            
            if($data_chron_tab['valeur'] > (-8888) && $data_chron_tab['valeur'] < (99999) )
            {
                $graph_x_axe2 .= "'".$data_chron_tab['dateheure']."'";

                if($traitement_axe2 == 0){$valeur = abs($data_chron_tab['valeur']);} // données simple (valeur)
                if($traitement_axe2 == 1){$valeur += abs($data_chron_tab['valeur']);} // Cumul

                $graph_y_axe2 .= $valeur;      

                if(!$reload)
                {
                    if($valeur > $y_max2){$y_max2 = $valeur;}
                    if($valeur < $y_min2){$y_min2 = $valeur;} 
                }

                $edit_lacune_axe2 .= "   x1: '".$lacune_date_first."',";
                //$html_tab_lacune_axe1 .= "<td style='height:15px;'>".$lacune_date_first_fr."</td></tr>";
            }
            else
            {
                $graph_x_axe2 .= "'".$data_chron_tab['dateheure']."',";                    
                $graph_y_axe2 .= 'null,';
                
                $chron_dateheure_tab = explode(' ',$data_chron_tab['dateheure']); 
                $chron_dateheure_fr = dateus_fr($chron_dateheure_tab[0]).' '.$chron_dateheure_tab[1];

                $edit_lacune_axe2 .= "   x1: '".$data_chron_tab['dateheure']."',";
                //$html_tab_lacune_axe1 .= "<td style='height:15px;'>".$chron_dateheure_fr."</td></tr>";
                //${'tab_lacunes_'.$cle_station.'_'.$typedata_chron}[${'nb_lacunes_'.$cle_station.'_'.$typedata_chron}]['date_end'] = $data_chron_tab['dateheure']; 
            }

            
            $edit_lacune_axe2 .= "  y1: 1,
                                    fillcolor: '".$color_axe2."',
                                    opacity: 0.15,
                                    line: {width: 0},
                                    customType: 'axe2' // Propriété pour distinguer l'axe lié au shape (lacunes)
                                }";                

            $nb_lacunes_axe2++;                      
            $lacune_date_first=''; // réinitialisation lacune

        }
        else // pas de lacune en cours
        {
            if($data_chron_tab['valeur'] > (-8888) && $data_chron_tab['valeur'] < (99999) )
            {
                $graph_x_axe2 .= "'".$data_chron_tab['dateheure']."'";

                if($traitement_axe2 == 0){$valeur = abs($data_chron_tab['valeur']);} // données simple (valeur)
                if($traitement_axe2 == 1){$valeur += abs($data_chron_tab['valeur']);} // Cumul

                $graph_y_axe2 .= $valeur;

                if(!$reload)
                {
                    if($valeur > $y_max2){$y_max2 = $valeur;}
                    if($valeur < $y_min2){$y_min2 = $valeur;} 
                }
            }
            else
            {
                $graph_x_axe2 .= "'".$data_chron_tab['dateheure']."'";                    
                $graph_y_axe2 .= 'null';

                $html_tab_lacune_temp = '';
                $edit_lacune_axe2 .= ","; // on ajoute une virgule entre chaque lacunes

                $edit_lacune_temp = "        
                                {
                                    type: 'rect',
                                    xref: 'x', // x-reference is assigned to the x-values                               
                                    yref: 'paper',  // y-reference is assigned to the plot paper [0,1]                           
                                    x0: '".$data_chron_tab['dateheure']."',
                                    y0: 0,
                                ";

                $lacune_date_first = $data_chron_tab['dateheure'];
            }     
        }
    }
    // Choix du type de graphique (Lignes ou Bar)
    $code_type_graph = '';
    if($type_graph_axe2=='lines')
    {
        //$code_type_graph = "mode: 'lines+markers',";
        $code_type_graph = "mode: 'lines',";
        $code_type_graph .= "type: 'scatter',";
    }
    if($type_graph_axe2=='bar'){$code_type_graph = "type: 'bar',";}

    
    // Paramétrage de la config générale du graphique (JS)
    $data_graph .=  "
                        var data_axe2 = 
                        { 
                            hovermode: 'closest',
                            
                            x: [".$graph_x_axe2."],
                            y: [".$graph_y_axe2."],
                            
                            ".$code_type_graph." // Bar, lines, scatter, ...
                            name: '".$nom_station_axe2." - ".$init_type_data_axe2."',

                            yaxis: 'y2', // Associe cette trace au second axe Y

                            // Format d'étiquette des données au survol
                            hovertemplate: '<br><b>Date</b> : %{x|%d-%m-%Y %H:%M:%S}<br>' +
                                            '<b>".$axe_data_axe2."</b> : %{y:.3f} ".$unite_axe2."',

                            
                            marker: {
                                color: '".$color_axe2."',
                                line: {
                                    color: '".$color_axe2."',
                                    width: 2,  
                                } 
                            },   
                                
                            line: {
                                color: '".$color_axe2."',
                                width: 1.5,  
                            }               
                        };
                    ";   
                    
                    
}


if($id_chron_axe1 == 0){$load_data = "[data_axe2]";}    
if($id_chron_axe2 == 0){$load_data = "[data_axe1]";} 
if($id_chron_axe1 == 0 && $id_chron_axe2 == 0){$load_data = "[]";}               
if($id_chron_axe1 > 0 && $id_chron_axe2 > 0){$load_data = "[data_axe1,data_axe2]";}
   
// Choix d'affichage des lacunes ou pas
$affiche_lac = "shapes: [".$edit_lacune_axe1.",".$edit_lacune_axe2."],";

$date_first_str = $date_first->format('Y-m-d');
$date_end_str = $date_end->format('Y-m-d');

// Configuration de l'échelle des axes 
$pad_y1 = max(0.5, 0.1 * ($y_max1 - $y_min1));
$y1_min_graph = $y_min1 - $pad_y1;
$y1_max_graph = $y_max1 + $pad_y1;

$pad_y2 = max(0.5, 0.1 * ($y_max2 - $y_min2));
$y2_min_graph = $y_min2 - $pad_y2;
$y2_max_graph = $y_max2 + $pad_y2;

$layout_graph =
            "var layout=
            {
                        xaxis: 
                        {
                            title: {
                                //text: 'Date',
                                standoff: 5 // Ajuster la distance entre le titre et l'axe
                            },
                            rangeselector: {
                                buttons: [
                                    {
                                        step: 'month',
                                        stepmode: 'backward',
                                        count: 1,
                                        label: '1 mois'
                                    },
                                    {
                                        step: 'month',
                                        stepmode: 'backward',
                                        count: 6,
                                        label: '6 mois'
                                    },
                                    {
                                        step: 'year',
                                        stepmode: 'backward',
                                        count: 1,
                                        label: '1 an'
                                    },
                                    {
                                        step: 'year',
                                        stepmode: 'backward',
                                        count: 10,
                                        label: '10 ans'
                                    },
                                    {
                                        step: 'all',
                                        label: 'Tout'
                                    },
                                    {
                                        step: 'year',
                                        stepmode: 'todate',
                                        count: 1,
                                        label: 'Année en cours'
                                    }
                                ],
                                font: 
                                {
                                    size: 12,
                                    color: '#fff'
                                },
                                bgcolor: '#C1D8C3',
                                activecolor: '#6A9C89',
                                y: 1.09,       // un peu au-dessus du graphe
                                x: 0.01,                                  
                            },  
                            
                            rangeslider: {
                                visible: true,
                                thickness: 0.05,
                                yaxis: {
                                            rangemode: 'fixed',
                                            range: [".($y_min1*0.75).",".($y_max1*1.25)."] 
                                        }
                            },
                            

                            type: 'date',

                            showgrid: true,      // Affiche le quadrillage
                            gridcolor: '#ddd',   // Couleur des lignes du quadrillage
                            gridwidth: 1,         // Largeur des lignes du quadrillage

                            autorange: false,
                            range: ['".$date_first_str."', '".$date_end_str."'],
                                               
                            tickfont: {size: 12}, // Taille des caractères des graduations
                            
                            titlefont: {family: 'roboto, arial, helvetica',
                                size: 1,
                                bold: true,
                                color: '#000000'},                                
                            tickangle: 0,
                            ticklen: 5,
                            showline: true,
                            linewidth: 1,
                            automargin: true, 
                            fixedrange: false                                                                     
                        },
                        yaxis:
                        {
                            title: {
                                text: '".$text_yaxis1."',
                                standoff: 15 // Ajuster la distance entre le titre et l'axe
                            },
                            
                            autorange: false,
                            range:[".($y_min1*0.75).",".($y_max1*1.25)."],
                            
                            tickfont: {size: 11}, // Taille des caractères des graduations

                            titlefont: {family: 'roboto, arial, helvetica',
                                    size: 14,
                                    bold: true,
                                    color: '#000000'},
                            tickformat: '.1f',
                            ticklen: 5,
                            showline: true,
                            linewidth: 1,

                            automargin: true,
                            fixedrange: false
                        },
                        yaxis2:
                        {
                            title: {
                                text: '".$text_yaxis2."',
                                standoff: 15 // Ajuster la distance entre le titre et l'axe
                            },
                            
                            autorange: false,
                            range:[".($y_min2*0.9).",".($y_max2*1.5)."],
                            
                            tickfont: {size: 11}, // Taille des caractères des graduations
                                        
                            titlefont: {family: 'roboto, arial, helvetica',
                                    size: 14,
                                    bold: true,
                                    color: '#000000'},
                            tickformat: '.1f',
                            ticklen: 5,
                            showline: true,
                            linewidth: 1,

                            overlaying: 'y',
                            side: 'right',

                            automargin: true,
                            fixedrange: false
                            
                        },

                        hovermode:'x unified',
                        
                        hoverlabel: { bgcolor: '#fff', font: { size: 12, color: '#000' } },
                        margin: {l: 60, r: 10, t: 10, b: 10}, // Par défault : l: 60, r: 60, t: 80, b: 60                         
                        
                        showlegend: true,
                        legend: 
                        {
                            x: 0,
                            y: 0.99,
                            orientation: 'h',
                        },

                        barmode: 'group', // Mode de groupement des barres
                        //bargap: 0.1, // Contrôle l'espacement entre les barres
                        bargroupgap: 0.9, // Contrôle l'espacement entre les groupes de barres 
                        
                        // Affichage lacunes
                        ".$affiche_lac."             
                    };
            ";




// Préparation des données à renvoyer coté Client
$editGraph = "Plotly.newPlot('plot_0',".$load_data.",layout,config);";

$actionGraph = "
    dateFirst.value = '".$date_first->format('d-m-Y')."';
    dateEnd.value   = '".$date_end->format('d-m-Y')."';
    yMin1.value     = parseInt(".$y1_min_graph.");
    yMax1.value     = parseInt(".$y1_max_graph.");
    yMin2.value     = parseInt(".$y2_min_graph.");
    yMax2.value     = parseInt(".$y2_max_graph.");

    var gd = document.getElementById('plot_0');

    gd.on('plotly_relayout', function(eventData) 
    {
        var x1 = eventData['xaxis.range[0]'];
        var x2 = eventData['xaxis.range[1]'];

        // Support du format array renvoyé par le rangeslider
        if ((x1 === undefined || x2 === undefined) && Array.isArray(eventData['xaxis.range'])) {
            x1 = eventData['xaxis.range'][0];
            x2 = eventData['xaxis.range'][1];
        }

        // Reset autorange X
        if (eventData['xaxis.autorange'] === true) {
            x1 = '".$date_first->format('Y-m-d')."';
            x2 = '".$date_end->format('Y-m-d')."';
        }

        // Fallback sur le layout si pas trouvé
        if ((x1 === undefined || x2 === undefined) && gd.layout && gd.layout.xaxis && Array.isArray(gd.layout.xaxis.range)) {
            x1 = gd.layout.xaxis.range[0];
            x2 = gd.layout.xaxis.range[1];
        }

        var y1   = eventData['yaxis.range[0]'];
        var y2   = eventData['yaxis.range[1]'];
        var y1_2 = eventData['yaxis2.range[0]'];
        var y2_2 = eventData['yaxis2.range[1]'];

        if ((y1 === undefined || y2 === undefined) && Array.isArray(eventData['yaxis.range'])) {
            y1 = eventData['yaxis.range'][0];
            y2 = eventData['yaxis.range'][1];
        }
        if ((y1_2 === undefined || y2_2 === undefined) && Array.isArray(eventData['yaxis2.range'])) {
            y1_2 = eventData['yaxis2.range'][0];
            y2_2 = eventData['yaxis2.range'][1];
        }

        // Reset autorange Y1/Y2
        if (eventData['yaxis.autorange'] === true && gd.layout && gd.layout.yaxis && Array.isArray(gd.layout.yaxis.range)) {
            y1 = gd.layout.yaxis.range[0];
            y2 = gd.layout.yaxis.range[1];
        }
        if (eventData['yaxis2.autorange'] === true && gd.layout && gd.layout.yaxis2 && Array.isArray(gd.layout.yaxis2.range)) {
            y1_2 = gd.layout.yaxis2.range[0];
            y2_2 = gd.layout.yaxis2.range[1];
        }

        // Conversion X en dd-mm-yyyy
        if (x1 && typeof x1 === 'string') {
            dateFirst.value = x1.split(' ')[0].split('-').reverse().join('-');
        }
        if (x2 && typeof x2 === 'string') {
            dateEnd.value = x2.split(' ')[0].split('-').reverse().join('-');
        }

        if (typeof y1 !== 'undefined' && !isNaN(y1)) {
            yMin1.value = parseInt(y1);
        }
        if (typeof y2 !== 'undefined' && !isNaN(y2)) {
            yMax1.value = parseInt(y2);
        }
        if (typeof y1_2 !== 'undefined' && !isNaN(y1_2)) {
            yMin2.value = parseInt(y1_2);
        }
        if (typeof y2_2 !== 'undefined' && !isNaN(y2_2)) {
            yMax2.value = parseInt(y2_2);
        }
    });

    // --- Réactivité en temps réel pendant le drag (X + Y1 + Y2) ---
    gd.on('plotly_relayouting', function(eventData) 
    {
        // X (rangeslider, pan/zoom X, molette…) -> MAJ immédiate
        var xr = eventData['xaxis.range'] || [eventData['xaxis.range[0]'], eventData['xaxis.range[1]']];
        if (Array.isArray(xr) && xr[0] !== undefined && xr[1] !== undefined) {
            if (typeof xr[0] === 'string') {
                dateFirst.value = xr[0].split(' ')[0].split('-').reverse().join('-');
            }
            if (typeof xr[1] === 'string') {
                dateEnd.value = xr[1].split(' ')[0].split('-').reverse().join('-');
            }
        }

        // Y1
        var yr1 = eventData['yaxis.range'] || [eventData['yaxis.range[0]'], eventData['yaxis.range[1]']];
        if (Array.isArray(yr1) && yr1[0] !== undefined && yr1[1] !== undefined) {
            if (!isNaN(yr1[0])) { yMin1.value = parseInt(yr1[0]); }
            if (!isNaN(yr1[1])) { yMax1.value = parseInt(yr1[1]); }
        }

        // Y2
        var yr2 = eventData['yaxis2.range'] || [eventData['yaxis2.range[0]'], eventData['yaxis2.range[1]']];
        if (Array.isArray(yr2) && yr2[0] !== undefined && yr2[1] !== undefined) {
            if (!isNaN(yr2[0])) { yMin2.value = parseInt(yr2[0]); }
            if (!isNaN(yr2[1])) { yMax2.value = parseInt(yr2[1]); }
        }
    });

    // Double-clic reset
    gd.on('plotly_doubleclick', function() 
    { 
        dateFirst.value = '".$date_first->format('d-m-Y')."';
        dateEnd.value   = '".$date_end->format('d-m-Y')."';
        yMin1.value     = parseInt(".$y1_min_graph.");
        yMax1.value     = parseInt(".$y1_max_graph.");
        yMin2.value     = parseInt(".$y2_min_graph.");
        yMax2.value     = parseInt(".$y2_max_graph.");

        // Optionnel: réappliquer les plages Y par défaut au reset
        Plotly.relayout(gd, {
            'yaxis.autorange': false,
            'yaxis.range': [".$y1_min_graph.", ".$y1_max_graph."],
            'yaxis2.autorange': false,
            'yaxis2.range': [".$y2_min_graph.", ".$y2_max_graph."]
        });
    });
";


$responseData = array(
    'js_graph' => $data_graph.$layout_graph.$editGraph.$actionGraph
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>