<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
AFFICHE DES DONNEES PAR GRAPHIQUE
----------------------------------------
*/

// --------------------------------------
// INIT VAR
$nb_data=0;
$graph_x = '';
$graph_y = '';
$min_y = 0;
$max_y = 0;
$min_x = 0;
$max_x = 0;
$id_station_encours = 0;
$id_chron_encours = 0;

$lacune_date_first = '';
$edit_lacune_temp = '';
$nb_lacunes = 0;


// --------------------------------------
// Requête SQL

// Requête sur TYPE DE MESURE
$sql_quality = "SELECT DISTINCT id_data_qualite, init_qualite_data, nom_qualite_data, info_qualite_data, id_eq_type 
				FROM ".TABLE_DATA_QUALITE."
				WHERE init_qualite_data<>'' 
                ORDER BY init_qualite_data ASC";
$quality_query = tep_db_query($sql_link,$sql_quality);


// Génération du graphique à partir des donnes sélectionnées à la page précédente

foreach($station_chron_array as $cle_station => $typedata_array) 
{   
    ${'js_config_trace_'.$cle_station} ='';
    ${'js_load_trace_'.$cle_station} ='';
    ${'nb_chron_'.$cle_station} = count($typedata_array);

    // INIT échelle des axes - Il ne peut y avoir que 2 axes  
    ${'max_'.$cle_station} = 0;
    ${'min_'.$cle_station} = 0;

    $nb_chron = 0;
    $edit_button_chron_temp = '';

    foreach($typedata_array as $typedata_chron => $sql_chron) 
    {        
        ${'nb_data_'.$cle_station.'_'.$typedata_chron} = 0;        
        ${'nb_lacunes_'.$cle_station.'_'.$typedata_chron} = 0;
        ${'max_'.$cle_station.'_'.$typedata_chron} = 0;
        ${'min_'.$cle_station.'_'.$typedata_chron} = 0;
        ${'graph_x_'.$cle_station.'_'.$typedata_chron} = '';
        ${'graph_y_'.$cle_station.'_'.$typedata_chron} = '';
        ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} = '';
        
        $data_chron_query = tep_db_query($sql_link,$sql_chron);
        while($data_chron_tab = tep_db_fetch_array($data_chron_query))
        {    
            if(tep_not_null($lacune_date_first))
            {                
                ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= $edit_lacune_temp;

                if($data_chron_tab['valeur']>=0)
                {
                    ${'graph_x_'.$cle_station.'_'.$typedata_chron} .= "'".$data_chron_tab['dateheure']."'";
                    ${'graph_y_'.$cle_station.'_'.$typedata_chron} .= $data_chron_tab['valeur'];                    

                    if(${'nb_data_'.$cle_station.'_'.$typedata_chron}>0)
                    {
                        ${'graph_x_'.$cle_station.'_'.$typedata_chron} .= ',';
                        ${'graph_y_'.$cle_station.'_'.$typedata_chron} .= ','; 
                    }

                    if($data_chron_tab['valeur'] > ${'max_'.$cle_station.'_'.$typedata_chron}){${'max_'.$cle_station.'_'.$typedata_chron} = $data_chron_tab['valeur'];}
                    if($data_chron_tab['valeur'] < ${'min_'.$cle_station.'_'.$typedata_chron}){${'min_'.$cle_station.'_'.$typedata_chron} = $data_chron_tab['valeur'];}

                    ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= "   x1: '".$lacune_date_first."',";
                }
                else
                {
                    ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= "   x1: '".$data_chron_tab['dateheure']."',";
                }
                ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= "       y1: 1,
                                                            fillcolor: '#FF6F69',
                                                            opacity: 0.5,
                                                            line: {
                                                                width: 0
                                                            }
                                                        }";
                ${'nb_lacunes_'.$cle_station.'_'.$typedata_chron}++;                      
                $lacune_date_first=''; // réinitialisation lacune
            }
            else // pas de lacune en cours
            {
                if($data_chron_tab['valeur']>=0)
                {                    
                    ${'graph_x_'.$cle_station.'_'.$typedata_chron} .= "'".$data_chron_tab['dateheure']."',";
                    ${'graph_y_'.$cle_station.'_'.$typedata_chron} .= $data_chron_tab['valeur'].","; 

                    if(${'nb_data_'.$cle_station.'_'.$typedata_chron}>0)
                    {
                        //${'graph_x_'.$cle_station.'_'.$typedata_chron} .= ',';
                        //${'graph_y_'.$cle_station.'_'.$typedata_chron} .= ','; 
                    }             

                    if($data_chron_tab['valeur'] > ${'max_'.$cle_station.'_'.$typedata_chron}){${'max_'.$cle_station.'_'.$typedata_chron} = $data_chron_tab['valeur'];}
                    if($data_chron_tab['valeur'] < ${'min_'.$cle_station.'_'.$typedata_chron}){${'min_'.$cle_station.'_'.$typedata_chron} = $data_chron_tab['valeur'];}
                }
                else
                {
                    if(${'nb_lacunes_'.$cle_station.'_'.$typedata_chron}>0){${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= ",";}
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

            $id_station_encours = $cle_station;
            $id_chron_encours = $typedata_chron;
            
            ${'nb_data_'.$cle_station.'_'.$typedata_chron}++;
        }

        // On enlève le dernier caractère qui est une ',' dans la suite de données
        ${'graph_x_'.$cle_station.'_'.$typedata_chron} = substr(${'graph_x_'.$cle_station.'_'.$typedata_chron}, 0, -1);
        ${'graph_y_'.$cle_station.'_'.$typedata_chron} = substr(${'graph_y_'.$cle_station.'_'.$typedata_chron}, 0, -1);


        // Choix du type de graphique
        $code_type_graph = '';
        $id_eq_type = $type_chron_array[$typedata_chron]['id_eq_type_data'];
        if($eq_type_array[$id_eq_type]['type_graph']=='lines')
        {
            //$code_type_graph = "mode: 'lines+markers',";
            $code_type_graph = "mode: 'lines',";
            //mode: 'lines+markers',
            $code_type_graph .= "type: 'scatter',";
        }
        if($eq_type_array[$id_eq_type]['type_graph']=='bar')
        {$code_type_graph = "type: 'bar',";}

        // ECHELLE
        if(${'max_'.$cle_station.'_'.$typedata_chron} > ${'max_'.$cle_station}){${'max_'.$cle_station} = ${'max_'.$cle_station.'_'.$typedata_chron};}
        if(${'min_'.$cle_station.'_'.$typedata_chron} < ${'min_'.$cle_station}){${'min_'.$cle_station} = ${'min_'.$cle_station.'_'.$typedata_chron};}

        ${'js_config_trace_'.$cle_station} .=
                                                "                                                
                                                var xValues = [".${'graph_x_'.$cle_station.'_'.$typedata_chron}."]
                                                var yValues = [".${'graph_y_'.$cle_station.'_'.$typedata_chron}."]
                                                
                                                var trace_".$cle_station."_".$typedata_chron." = 
                                                { 
                                                    hovermode: 'closest',
                                                    x: [".${'graph_x_'.$cle_station.'_'.$typedata_chron}."],
                                                    //x: xValues,
                                                    y: yValues,
                                                    
                                                    ".$code_type_graph."

                                                    name: '".$type_chron_array[$typedata_chron]['init_type_data']."',

                                                    //text: [' date '+".${'graph_x_'.$cle_station.'_'.$typedata_chron}."],

                                                    hovertemplate: '%{y:.3f} (".$type_chron_array[$typedata_chron]['unite'].") : %{x|%d-%m-%Y %H:%M:%S}', // .3f : 3 chiffre après la virgule
                                                    //hovertemplate: '%{text}<extra></extra>',

                                                    marker: {
                                                        //color: 'rgb(158,202,225)',                                                        
                                                      }
                                                };
                                                ";

        ${'js_load_trace_'.$cle_station} .= "trace_".$cle_station."_".$typedata_chron.",";
    } 

    if(${'max_'.$cle_station} > $max_y){$max_y = ${'max_'.$cle_station};}
    if(${'min_'.$cle_station} < $min_y){$min_y = ${'min_'.$cle_station};}

    ${'edit_lacune_'.$cle_station.'_'.$typedata_chron}=''; // le temps de bien gérer les lacunes à enlever 
    ${'js_layout_'.$cle_station} = 
                                "
                                    var layout_".$cle_station." = 
                                    {
                                        xaxis: 
                                        {
                                            title: {
                                                //text: 'Date',
                                                standoff: 20 // Ajuster la distance entre le titre et l'axe
                                            },
                                            automargin: true, 
                                            //tickformat: '%Y-%m-%d',
                                            type: 'date',

                                            range: ['".datefr_us($date_1)."', '".datefr_us($date_2)."'],
                                                                                        
                                            titlefont: {family: 'roboto, arial, helvetica',
                                                size: 14,
                                                bold: true,
                                                color: '#000000'},
                                                
                                            tickangle: 0,
                                            ticklen: 5,
                                            showline: true,
                                            linewidth: 1,

                                            autorange: true,

                                            fixedrange: false // Désactive le panning sur l'axe des ordonnées si false                                            
                                        },
                                        yaxis:
                                        {
                                            //type: 'log',
                                            title: {
                                                text: '".$type_chron_array[$typedata_chron]['axe_nom']." (".$type_chron_array[$typedata_chron]['unite'].")',
                                                standoff: 15 // Ajuster la distance entre le titre et l'axe
                                            },
                                            automargin: true, 
                                            titlefont: {family: 'roboto, arial, helvetica',
                                                    size: 14,
                                                    bold: true,
                                                    color: '#000000'},
                                            tickformat: ',.0f',
                                            ticklen: 5,
                                            showline: true,
                                            linewidth: 1,

                                            autorange: false,
                                            range: [".(${'min_'.$cle_station}*0.9).", ".(${'max_'.$cle_station}*1.1)."],

                                            barmode: 'group', // pour affichage en bar (histogramme)
                                            bargap: 0.9,

                                            fixedrange: false // Désactive le panning sur l'axe des ordonnées si true
                                        },

                                        hovermode: 'x unified',
                                        hoverlabel: { bgcolor: '#fff', font: { size: 12, color: '#000' } },
                                        cursor: 'pointer',
                                        margin: {l: 50, r: 30, t: 0, b: 40}, // Par défault : l: 60, r: 60, t: 80, b: 60 
                                        
                                        showlegend: true,
                                        legend: 
                                        {
                                            x: 0,
                                            y: 1.2,
                                            orientation: 'h',
                                        },

                                        
                                        
                                        // Affichage lacunes
                                        shapes: [".${'edit_lacune_'.$cle_station.'_'.$typedata_chron}."],

                                        
                                    };      
                                ";  
}

// --------------------------------------
// Debut page HTML

require(DIR_WS_STRUCTURE . 'header_web.php');

echo "<body>";

require(DIR_WS_STRUCTURE . 'block_graph.php');
require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

echo "<div id='contour_general'>";

	if(tep_not_null($message_info)){echo "<div id='contenu_info'>".$message_info."</div>";}
	
	echo "<div id='contenu_centre'>";

        echo "<div id='contenu_box2'>";
		
            echo "<h1 id='h1_graph'>";
                            
                echo "<span>".htmlaccent('Correction des données')."</span>";

                echo "<br>";

                echo "<span style='font-size:16px;color:#000;'>".htmlaccent('Station : ')."</span>";
                echo "<span style='font-size:16px;font-weight:normal;color:#000;'>".$station_all_array[$id_station_encours]['code_station']." - ".$station_all_array[$id_station_encours]['nom_station']."</span>";
                echo "&nbsp;&nbsp;";
                echo "<span style='font-size:16px;color:#000;'>".htmlaccent('Chronique  : ')."</span>";
                echo "<span style='font-size:16px;font-weight:normal;color:#000;'>".$type_chron_array[$id_chron_encours]['init_type_data']." - ".$type_chron_array[$id_chron_encours]['nom_type_data']."</span>";

                
            echo "</h1>";
            // Colonne de gauche permettant d'effectuer les corrections
            echo "<div id='cadre_graph' style='float:left;width:250px;height:100vh;overflow-y: auto;'>\n"; // max-height:60%;overflow-y: auto;overflow-x: auto;'>\n";  On verra si nécessaire le scrolling

                echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding-top:10px;padding-left:10px;'>\n";

                    echo "<p>";
                        echo "<span style='font-weight: bold;font-size:13px;width:150px;'>".htmlaccent('Sélection des données')."</span>";
                    echo "</p>";
                    
                    // Date début zoom
                    echo "<div id='boite_small' class='select_date' >\n";
                            
                        echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Date de début')."</p>\n";	
                        echo "<input class='input_texte' style='width:80px;padding-bottom: 4px;' name='x1Zoom' id='x1Zoom' type='text' value='".$date_1."' onclick=\"javascript:displayCalendar(document.getElementById('x1Zoom'),'dd-mm-yyyy',this);\" readonly>\n";

                    echo "</div>\n";

                    // Date fin zoom
                    echo "<div id='boite_small' class='select_date' style='margin-right:0px;'>\n";
                            
                        echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Date de fin')."</p>\n";	
                        echo "<input class='input_texte' style='width:80px;padding-bottom: 4px;' name='x2Zoom' id='x2Zoom' type='text' value='".$date_2."' onclick=\"javascript:displayCalendar(document.getElementById('x2Zoom'),'dd-mm-yyyy',this);\" readonly>\n";
                                
                    echo "</div>\n";

                    echo "<hr>\n";
                    
                    // Bouton pour sélectionner les données Abscisses
                    echo "<button id='syncAbsc' class='zoom_graph'  style='width:100px;margin-bottom:10px;' title='".htmlaccent('Sélection des données en Abscisses')."'>".htmlaccent('Data Abs.')."</button>\n";
        
                    echo "<hr>\n";

                    // Bouton pour sélectionner les données Ordonnées
                    echo "<div id='boite_small' class='select_date'>\n";
                            
                        echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Ech. : y min')."</p>\n";	
                        echo "<input type='text' class='input_texte_60' id='y1Zoom' value='".intval($min_y)."'/>\n";
                                
                    echo "</div>\n";

                    echo "<div id='boite_small' class='select_date' style='margin-left:0px;margin-right:0px;'>\n";
                            
                        echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Ech. : y max')."</p>\n";	
                        echo "<input type='text' class='input_texte_60' id='y2Zoom' value='".intval($max_y)."'/>\n";
                                
                    echo "</div>\n";

                    echo "<hr>\n";
                    
                    // Bouton pour synchroniser le zoom axe des ordonnées
                    echo "<button id='syncOrdon' class='zoom_graph' style='width:100px;margin-bottom:10px;' title='".htmlaccent('Sélection des données en Ordonnées')."'>".htmlaccent('Data Ord.')."</button>\n";

                echo "<hr>\n";
                echo "</div>\n";

                // Correction valeur à travers une fonction linéaire
                echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;margin-top:10px;padding-top:10px;padding-left:10px;'>\n";

                    echo "<p style='margin-bottom:15px;font-weight: bold;font-size:13px;'>".htmlaccent('Correction Data')."</p>";
                    
                    /*                
                    echo "<div id='boite_small' style='width:100%;margin:0;'>\n";

                        echo "<p style='float:left;width:100%;'>".htmlaccent('Abscisse (Date)')."</p>\n";

                        // Opérateur
                        echo "<p style='float:left;width:60px;color:#428bca;padding-top:5px;'>".htmlaccent('Opérateur')."</p>\n";
                        
                        echo "<select name='operateur_x' id='operateur_x' style='float:left;width:45px;font-weight: bold;font-size:16px;'>\n";
                                            
                            echo "<option value='1' >+</option>\n";
                            echo "<option value='1' >-</option>\n"; 

                        echo "</select>\n";
                    
                        // Valeur
                        echo "<p style='float:left;width:50px;color:#428bca;margin-left:20px;padding-top:5px;'>".htmlaccent('Valeur <br> (en sec)')."</p>\n";
                        echo "<input type='text' class='input_texte_60' id='valeur_operation_x' style='float:left;' value='0'/>\n";
                    
                    echo "<hr>\n";
                    echo "</div>\n";
                    */

                    echo "<div id='boite_small' style='margin:0;'>\n";

                        echo "<p style='float:left;'>".htmlaccent('Fonction linéaire de correction <br> (Ynew = aY + b)')."</p>\n";

                        echo "<hr>\n";
                        
                        // Paramètre a
                        echo "<p style='float:left;color:#428bca;padding-top:5px;'>".htmlaccent('a = ')."</p>\n";
                        echo "<input type='text' class='input_texte_xsmall' id='valeur_a' style='float:left;margin-left:5px;margin-right:20px;' value='1'/>\n";

                        // Paramètre b
                        echo "<p style='float:left;color:#428bca;padding-top:5px;'>".htmlaccent('b = ')."</p>\n";
                        echo "<input type='text' class='input_texte_xsmall' id='valeur_b' style='float:left;margin-left:5px;' value='0'/>\n";
                    
                    echo "<hr>\n";
                    echo "</div>\n";

                    echo "<hr>\n";
                    
                    // Bouton pour sélectionner les données Abscisses

                    echo "<button id='calcul_operateur' class='inverse_axe' style='margin-bottom:10px;' onclick=\"updateChart(".$id_station_encours.",'".$type_chron_array[$id_chron_encours]['init_type_data']."');\">";
                        echo htmlaccent('Générer'); 
                    echo "</button>\n";                
                    
                echo "<hr>\n";
                echo "</div>\n";
                
            echo "<hr>\n";
            echo "</div>\n";


            // Colonne Milieu - Affichage des corrections en cours avant validation et création d'une nouvelle chronique
            echo "<div id='cadre_graph' style='float:left;width:360px;margin-left:0px;height:100vh;overflow-y: auto;'>\n";

                echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding-top:10px;padding-left:10px;'>\n";
                    
                    echo "<p style='margin-bottom:10px;'>";
                        echo "<span style='font-weight: bold;font-size:13px;width:150px;'>".htmlaccent('Liste des corrections en cours')."</span>";
                    echo "</p>";
                    
                    echo "<div id='boite_small' style='width:95%;'>\n";

                        // Tableau permettant l'affichage au fur et à mesure des différentes modifications générées
                        echo "<table id='table_tri' class='TabCorrection_Title' cellspacing='0' >\n";
                                                
                            echo "<thead>\n";
                                    //echo "<th style='width:20px;font-size:11px;'></th>\n";
                                    //echo "<th style='width:25px;font-size:11px;color:#000;text-align:center;cursor:pointer' onclick='toggleCheckboxes();'>\n";	
                                    echo "<th style='width:25px;'>&nbsp;</th>\n";	
                                    echo "<th style='width:30px;font-size:11px;'>".htmlaccent('Axe')."</th>\n";
                                    echo "<th style='width:90px;font-size:11px;'>".htmlaccent('Correction')."</th>\n";	
                                    echo "<th style='width:75px;font-size:11px;'>".htmlaccent('Début')."</th>\n";	
                                    echo "<th style='width:75px;font-size:11px;'>".htmlaccent('Fin')."</th>\n";				                                 		 
                                    echo "<th style='width:25px;'>&nbsp;</th>\n";	

                            echo "</thead>\n";

                        echo "</table>\n";	

                        echo "<table id='table_tri' class='TabCorrection' cellspacing='0'>\n";
                            
                        echo "</table>\n";	

                    echo "</div>\n";
                    
                    echo "<hr>\n";

                    echo "<p style='margin-top:30px;margin-bottom:5px;'>";
                        echo "<span style='font-weight: bold;font-size:13px;width:150px;'>".htmlaccent('Information sur la correction en cours')."</span>";
                    echo "</p>";
                    
                    echo "<div id='boite_small' style='width:95%;margin-bottom:10px;'>\n";
                    
                        // Champs Type de Chronique    
                        // Un astérix rappelle la qualification de la chronique initiale
                        
                        echo "<p style='float:left;margin-bottom:0;margin-right:25px;color:#428bca;padding-top:6px;'>".htmlaccent('Type de chronique')."</p>";	
                    
                        echo "<select name='select_type_chron' id='select_type_chron' style='float:right;width:60px;'  onchange='handleSelectChange(this);'>";
                                                    
                        $selected = '';

                            if(isset($type_chron_array))
                            {
                                foreach ($type_chron_array as $id_type_chron => $type_chron)
                                {
                                    //if(($type_chron['id_eq_type_data']==$id_eq_type) &&  ($id_type_chron != $id_chron_encours))
                                    if(($type_chron['id_eq_type_data']==$id_eq_type))
                                    {
                                        if($id_type_chron != $id_chron_encours)
                                        {
                                            echo "<option value='".$id_type_chron."' title='".$type_chron['nom_type_data']."' >".$type_chron['init_type_data']."</option>\n";
                                        }
                                        else
                                        {
                                            echo "<option value='".$id_type_chron."' title='".$type_chron['nom_type_data']." (Chronique en cours)' >".$type_chron['init_type_data']." *</option>\n";
                                        }
                                    }
                                }
                            }
                            
                        echo "</select>";


                        echo "<hr>\n";

                        // Champs Code Qualité

                        echo "<p style='float:left;margin-bottom:0;margin-right:25px;color:#428bca;padding-top:6px;'>".htmlaccent('Code Qualité')."</p>";	
                    
                        echo "<select name='select_type_chron' id='select_type_chron' style='float:right;width:60px;'  onchange='handleSelectChange(this);'>";
                                                    
                            // Requête sur les Codes Qualité. Seuls les codes qualités reliées spécifiquement au type de données, ou sans lien à aucun type, s'afficheront
                            // Cela permet d'éviter une liste trop longue
                            
                            $sql_quality = "SELECT DISTINCT id_data_qualite, init_qualite_data, nom_qualite_data, info_qualite_data, id_eq_type 
                                            FROM ".TABLE_DATA_QUALITE."
                                            WHERE init_qualite_data<>'' AND (id_eq_type=".$id_eq_type." OR id_eq_type=0) 
                                            ORDER BY init_qualite_data ASC";
                            $quality_query = tep_db_query($sql_link,$sql_quality);
                            while($quality_tab = tep_db_fetch_array($quality_query))
                            {
                                $id_data_qualite = $quality_tab['id_data_qualite'];
                                $init_qualite_data = htmlaccent(html_entity_decode($quality_tab['init_qualite_data']));
                                $nom_qualite_data = htmlaccent(html_entity_decode($quality_tab['nom_qualite_data']));

                                echo "<option value='".$id_data_qualite."' title='".$nom_qualite_data."' >".$init_qualite_data."</option>\n";
                            }
                            
                        echo "</select>";
                        
                    echo "</div>\n";

                    // Bouton pour Enregistrer les corrections de données et la création d'une nouvelle chronique
                    echo "<input type='submit' name='validCorrection' class='button_mauve' style='width:95%;margin:5px 0;' value='".htmlaccent('Enregistrer les modifications')."' onclick='return confirmUpdateETL();' />";
                    
                
                echo "<hr>\n";
                echo "</div>\n";


                echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;margin-top:10px;padding-top:10px;padding-left:10px;'>\n";

                    echo "<div id='boite_small' style='width:95%;'>\n";
                    
                        // Instruction pour la validation des corrections    
                        
                        echo "<p style='float:left;padding:15px;font-weight:normal;text-align:justify;border-radius: 5px;border: 1px solid #AEBDCA;background-color:#fff;'>";
                        
                            echo "<span style='float:left;margin-bottom:5px;'>".htmlaccent('Les corrections sont exécutées dans l\'ordre d\'affichage du tableau en commençant par la première ligne.')."</span>";
                            echo "<span style='float:left;margin-bottom:5px;'>".htmlaccent('L\'ordre des corrections peut-être modifié en cliquant sur les flèches au début de chaque ligne.')."</span>";
                            echo "<span style='float:left;margin-bottom:5px;'>".htmlaccent('Les corrections qui ne sont pas validées doivent être supprimées avant de lancer l\'enregistrement.')."</span>";                        
                            echo "<span style='float:left;margin-bottom:5px;'>--</span>";
                            echo "<span style='float:left;margin-bottom:5px;'>".htmlaccent('Les données seront enregistrées dans une nouvelle chronique.')."</span>";
                            echo "<span style='float:left;margin-bottom:5px;'>".htmlaccent('Si des données existent déjà sur la même période pour un même type de chronique, ces données seront supprimées')."</span>";
                            
                        "</p>";	
                    echo "</div>\n";
                
                echo "<hr>\n";
                echo "</div>\n";
                    

            echo "<hr>\n";
            echo "</div>\n";

            // Bloc graphique
            echo "<div id='cadre_graph' style='float:left;width:60%;height:100vh;margin-left:0px;overflow-y: auto;'>\n";

                if(isset($station_chron_array) && sizeof($station_chron_array)>0)
                {
                    // Div des graphiques
                    $cle_station = array_keys($station_chron_array);
                    foreach($cle_station as $cle)
                    {
                        echo "<div id='boxpopup' class='select' style='width:95%;margin:0;'>\n";

                            echo "<div id='button_visu' style='' onclick=\"zoom_graph('".$cle."','".$station_all_array[$cle]['code_station']."','".$station_all_array[$cle]['nom_station']."');\">\n";	
                                echo  'Agrandir'; 
                            echo "</div>\n";

                            echo "<p class='titre'>".$station_all_array[$cle]['code_station']." - ".$station_all_array[$cle]['nom_station']."</p>";
                            
                            echo "<div id='plot_".$cle."' class='graph'></div>\n";
                            
                            echo "<button id='log-button1' class='log_axe' style='float:left;'>".htmlaccent('Ech. Log - Axe 1')."</button>\n";   
                            
                        echo "<hr>\n";
                        echo "</div>\n";						
                    }
                }
                else
                {
                    echo "<div id='boxpopup' >\n";
                        echo "<p class='alert'>".htmlaccent('Aucune données n\'a été trouvée')."</p>";
                    echo "<hr>";
                    echo "</div>";
                }
            
            echo "<hr>\n";
            echo "</div>\n";

        echo "<hr>";
        echo "</div>";   
	
	echo "<hr>";
	echo "</div>";
	
echo "<hr>";
echo "</div>";
	
require('include/application_bottom.php'); 
	
echo "</body>";

echo "</html>";

?>


<script>
// Génération des graphiques

idPlotZoom = 0;

var config = 
{
    responsive: true,
    doubleClickDelay: 1000, //Delay du zoom
    
    displayModeBar: true, // Affichage constant du menu de la figure
    scrollZoom: true, // Zoom avec la roulette de la souris

    modeBarButtonsToRemove: ['select2d','lasso2d','autoScale2d','zoomIn2d','zoomOut2d'],
    modeBarOrientation: 'v',

    displaylogo: false
};



<?php 
echo $js_syncAbsc_var = '';
echo $js_syncOrdon_var = '';

$stations = array_keys($station_chron_array);
foreach($stations as $cle_station)
{
    echo ${'js_config_trace_'.$cle_station};
    echo "var data_".$cle_station." = [".substr(${'js_load_trace_'.$cle_station}, 0, -1)."];"; // substr($str, 0, -1) pour enlever la dernière virgule de la chaine de caractère

    echo ${'js_layout_'.$cle_station};

    echo "Plotly.newPlot('plot_".$cle_station."', data_".$cle_station.", layout_".$cle_station.", config);";

    echo "addLogScaleButton('plot_".$cle_station."','log-button1','yaxis');";  

    echo "setupPlotlyRelayoutListener('plot_".$cle_station."');";
        
    $js_syncAbsc_var .= "Plotly.relayout('plot_".$cle_station."', {'xaxis.range': [x1_format, x2_format]});";
    $js_syncOrdon_var .= "Plotly.relayout('plot_".$cle_station."', {'yaxis.range': [y1, y2]});";
    
}

echo "
    document.getElementById('syncAbsc').addEventListener('click', function() 
    {
        x1_value = document.getElementById('x1Zoom').value;
        x2_value = document.getElementById('x2Zoom').value;

        // Convertir les dates au format 'dd-mm-yyyy' en 'yyyy-mm-dd'
        x1_format = new Date(x1_value.split('-').reverse().join('-'));
        x2_format = new Date(x2_value.split('-').reverse().join('-'));
        
        if(x1_format && x2_format) 
        {
            ".$js_syncAbsc_var."
        }
    });

    document.getElementById('syncOrdon').addEventListener('click', function() 
    {
        y1 = document.getElementById('y1Zoom').value;
        y2 = document.getElementById('y2Zoom').value;
        
        if (y1 && y2) 
        {
            ".$js_syncOrdon_var."
        }
    });
";

?>

// Fonction pour ajouter une ligne au tableau
function ajouterLigne(axe, correction, dateDebut, dateFin,traceId) 
{
    // Récupère le tableau
    var tableaux = document.getElementsByClassName('TabCorrection');

    // Vérifie s'il y a au moins un tableau avec la classe spécifiée
    if (tableaux.length > 0) 
    {
        // Récupère le premier tableau (suppose qu'il n'y en a qu'un)
        var tableau = tableaux[0];

        // Récupère la dernière ligne existante dans le tableau
        var numLastLigne = tableau.rows.length;

        // Crée une nouvelle ligne et trois cellules
        var nouvelleLigne = tableau.insertRow(-1);

        var num_op = numLastLigne;

        // Remplit les cellules avec les données
        nouvelleLigne.innerHTML = "<tr>" +
                                    //"<td style='width:20px;height:20px;text-align:center;'><input type='checkbox' class='checkbox'></td>" +
                                    "<td style='width:25px;height:20px;text-align:center;'>" +
                                        "<img src='/image/icones/arrow_up_full.png' class='btn-up' style='width:10px;cursor:pointer;margin-bottom:1px;' onclick=\"deplacerLigne(this,'up')\" title='Up'>" +
                                        "<img src='/image/icones/arrow_down_full.png' class='btn-down' style='width:10px;cursor:pointer;' onclick=\"deplacerLigne(this,'down')\" title='Down'>" +
                                    "</td>" +
                                    "<td style='width:30px;height:20px;color:#000;'><input type='text' class='correction' id='axe_"+num_op+"' style='width:10px;' value='"+ axe +"' readonly></td>" +
                                    "<td style='width:90px;height:20px;color:#000;'><input type='text' class='correction' id='correction_"+num_op+"' style='width:75px;' value='"+ correction +"' readonly></td>" +
                                    "<td style='width:75px;height:20px;color:#000;'><input type='text' class='correction' id='dateDebut_"+num_op+"' style='width:60px;' value='"+ dateDebut +"' readonly></td>" +
                                    "<td style='width:75px;height:20px;color:#000;'><input type='text' class='correction' id='dateFin_"+num_op+"' style='width:60px;' value='"+ dateFin +"' readonly></td>" +
                                    "<td style='width:25px;height:20px;text-align:center;color:#e64f4f;cursor:pointer;' title='Supprimer l opération' onclick=\"supprimerLigne(this,'" + traceId + "')\">X</td>" +
                                "</tr>";
                                //console.log(nouvelleLigne.innerHTML);  
                                
                                
                                
    }                            
}

// Fonction pour déplacer la ligne du tableau des corrections vers le haut ou le bas
function deplacerLigne(bouton, direction) 
{
        var ligne = bouton.closest('tr');

        if (direction === 'up' && ligne.previousElementSibling) 
        {
            ligne.parentElement.insertBefore(ligne, ligne.previousElementSibling);
            //mettreAJourNumerosLignes();
        } else if (direction === 'down' && ligne.nextElementSibling) 
        {
            ligne.parentElement.insertBefore(ligne.nextElementSibling, ligne);
            //mettreAJourNumerosLignes();
        }
    }

function supprimerLigne(element,traceId) 
{
    var ligne = element.closest('tr'); // Trouver la ligne parente
    if (ligne) {
        ligne.parentNode.removeChild(ligne); // Supprimer la ligne
    }

    supprimerTraceParId(traceId);
}

function mettreAJourNumerosLignes() 
{
    var tableau = document.getElementById('tableau');
    var lignes = tableau.getElementsByTagName('tr');

    for (var i = 1; i < lignes.length; i++) 
    {
        lignes[i].cells[0].innerText = i;
    }
}

// Mise à jour du graphique avec données ajustées
// - Ajustement des dates en cas de zoom
// - Nouvelle trace sur le graphique 

function updateChart(id_station,init_chron) 
{    
    var inputX1Value = document.getElementById('x1Zoom').value;
    var inputX2Value = document.getElementById('x2Zoom').value;

    // Convertir les dates du format 'jj-mm-aaaa' en 'aaaa-mm-jj'
    var x1DateParts = inputX1Value.split('-');
    var x2DateParts = inputX2Value.split('-');

    if (x1DateParts.length === 3 && x2DateParts.length === 3) {
        inputX1Value_us = x1DateParts[2] + '-' + x1DateParts[1] + '-' + x1DateParts[0];
        inputX2Value_us = x2DateParts[2] + '-' + x2DateParts[1] + '-' + x2DateParts[0];
    } 
    
    plot_n='plot_'+id_station;
    data_n='data_'+id_station;
    layout_n='layout_'+id_station;

    var inputYaValue = 1;
    var inputYbValue = 0;

    var traceDetail = '';

    inputYaValue = parseFloat(document.getElementById('valeur_a').value); 
    inputYbValue = parseFloat(document.getElementById('valeur_b').value);  

    if(!isNaN(inputYaValue) && !isNaN(inputYbValue))
    {
        //if(!((inputYaValue === 1) && (inputYbValue === 0)))
        //{
            var newYValues = yValues.map(function (value, index) 
            {
                var xValue = window[data_n][0].x[index];
                if (xValue >= inputX1Value_us && xValue <= inputX2Value_us) 
                {   
                    return value * inputYaValue + inputYbValue;
                }                      
            });

            var newTrace = 
            {
                hovermode: 'closest',
                x: window[data_n][0].x, // Use the same X-values as the existing trace
                //x: newXValues,
                y: newYValues,
                
                <?php echo $code_type_graph; ?>
                
                name: 'Correction : '+inputYaValue+' * '+init_chron+' + '+inputYbValue, // You can change the name as needed

                hovertemplate: '%{y:.3f} (cm) : %{x|%d-%m-%Y %H:%M:%S}',
            };
            
            CorrectionEncours = inputYaValue+' * '+init_chron+' + '+inputYbValue;

            // Ajouter l'identifiant unique à la trace
            newTrace.id = 'trace_' + Date.now(); // Utilisation d'un horodatage comme identifiant unique

            ajouterLigne('Y', CorrectionEncours,inputX1Value,inputX2Value,newTrace.id); 

            // Add the new trace to the data array
            window[data_n].push(newTrace);
            
            // Update the chart with the updated data array
            Plotly.newPlot(plot_n, window[data_n], window[layout_n], config);

            setupPlotlyRelayoutListener(plot_n);

        //}
    }    



    
    /*
    var inputOperateurXValue = document.getElementById('operateur_x').value;
    var inputXValue = parseFloat(document.getElementById('valeur_operation_x').value);

    
    if (!isNaN(inputXValue) && inputXValue > 0) 
    {
        var newXValues = xValues.map(function (value, index) 
        {
            var xValue = window[data_n][0].x[index];
            if (xValue >= inputX1Value_us && xValue <= inputX2Value_us) 
            {   
                // Convertir la date en objet JavaScript Date
                var xDate = new Date(xValue);
                
                if (inputOperateurXValue === '+')
                {
                    xDate.setSeconds(xDate.getSeconds() + newXValues);
                    var formattedDate = xDate.toISOString().slice(0, 19).replace('T', ' ');
                    return formattedDate;
                }
                //else if (inputOperateurYValue === '-'){return value - inputXValue;}
                //else if (inputOperateurYValue === '*'){return value * inputXValue;}
            }                      
        });
    }
    */
    
}



// Supprimer la trace en utilisant son identifiant unique
function supprimerTraceParId(traceId) 
{
    var index = window[data_n].findIndex(function(trace) 
    {
        return trace.id === traceId;
    });

    if (index !== -1) 
    {
        window[data_n].splice(index, 1); // Supprimer la trace du tableau
        Plotly.newPlot(plot_n, window[data_n], window[layout_n], config); // Mettre à jour le graphique
    }
}


// Fonction de zoom
function zoom_graph(id_station,code_station,nom_station)
{
    document.getElementById('box_graph').style.display='block';

    document.getElementById('titre_graph').innerHTML = code_station+' - '+nom_station;

    idPlotZoom = id_station;
    
    data_v='data_'+id_station;
    layout_v='layout_'+id_station;

    Plotly.newPlot('cadre_limit', window[data_v], window[layout_v], config);

    addLogScaleButton('cadre_limit','log-button_gd_1','yaxis'); 

    setupPlotlyRelayoutListener('cadre_limit');
}

// Fonction Echelle Log
function addLogScaleButton(plotId, logButtonId, axe) 
{
    const button = document.getElementById(logButtonId);
    const graphContainer = document.getElementById(plotId);

    let logScaleEnabled = false;

    button.addEventListener('click', function () {
        const plotlyLayout = graphContainer._fullLayout;

        if (axe === 'yaxis' || axe === 'yaxis2') {
            const axis = plotlyLayout[axe];

            // Activer/désactiver l'échelle logarithmique
            const newType = logScaleEnabled ? 'linear' : 'log';

            Plotly.relayout(plotId, { [axe + '.type']: newType });
            logScaleEnabled = !logScaleEnabled; // Inverser l'état
        }
    });
}

function setupPlotlyRelayoutListener(plot)
{
    document.getElementById(plot).on('plotly_relayout', function(eventData) 
    {
        if ((eventData['xaxis.range[0]'] && eventData['xaxis.range[1]']) || (eventData['yaxis.range[0]'] && eventData['yaxis.range[1]']))
        {
            x1 = eventData['xaxis.range[0]'];
            x2 = eventData['xaxis.range[1]'];   
            y1 = eventData['yaxis.range[0]'];
            y2 = eventData['yaxis.range[1]']; 
            
            // Convertir les dates au format 'yyyy-mm-dd' en 'dd-mm-yyyy'
            x1_format = x1.split(' ')[0].split('-').reverse().join('-');
            x2_format = x2.split(' ')[0].split('-').reverse().join('-');
            
            document.getElementById('x1Zoom').value = x1_format;
            document.getElementById('x2Zoom').value = x2_format;
                            
            if(typeof y1 !== 'undefined' && !isNaN(y1)){document.getElementById('y1Zoom').value = parseInt(y1);}
            if(typeof y2 !== 'undefined' && !isNaN(y2)){document.getElementById('y2Zoom').value = parseInt(y2);}                
        }
    });
}

function getDaysInMonth(monthNumber, year) 
{
    return new Date(year, monthNumber, 0).getDate();
}



</script>