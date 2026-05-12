<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
AFFICHE DES DONNEES PAR GRAPHIQUE - Multi Graphs
C'est avec cette page que l'on affiche les graphiques des chroniques
- Un graphique par station
- Plusieurs Chroniques possible par station donc par graphique
- Si une seule chronique alors on peut la corriger ou générer d'autres Chronique (QJ, QM, QA, ...)
- Il es tpossible d'agrandir les graphiques pour plus de visibilité
- La colonne de gauche permet de naviguer dans les graphiques plus précisément et simplement 
- Un zoom met à jour les limites de dates et de l'axe des ordonnées 
- En cliquant sur Synchroniser ou Générer, on peut ajuter tous les graphiques sur les mêmes échalles et en même temps
----------------------------------------
*/

// --------------------------------------
// INIT VAR
$nb_data=0;
$graph_x = '';
$graph_y = '';
$min_y = 0;
$max_y = 0;

$min_x = $date_2;
$max_x = $date_1;

$id_station_encours = 0;

$lacune_date_first = '';
$edit_lacune_temp = '';
$nb_lacunes = 0;

$js_syncAbsc_var = '';
$js_syncOrdon_var = '';

// --------
// tableau pour le paramétrage initial des courbes ou bars par type de chronique
$tab_param = [];
$colorIndex = 1;
$colorGraph = colorList();
$maxColors  = count($colorGraph);
$html_param = '';

    if(isset($station_chron_array) && sizeof($station_chron_array)>0)
    {
        foreach($station_chron_array as $cle_station => $typedata_array) 
        {	
            foreach($typedata_array as $typedata_chron => $sql)
            {
                if (!isset($tab_param[$typedata_chron])) 
                {
                    if ($colorIndex > $maxColors) 
                    {
                        $colorIndex = 1; // repartir au début si on dépasse
                    }

                    // Affecter la couleur courante
                    $tab_param[$typedata_chron]['color'] = $colorIndex;
                    $tab_param[$typedata_chron]['line']  = 1;

                    // Avancer dans la liste des couleurs
                    $colorIndex++;                
                }
            }
        }
    }
    

    $html_param = "<table id='table_tri' class='curve-param-table' style='width:100%;' >";

        foreach ($tab_param as $typedata_chron => $param)
        {
            if($typedata_chron != 'ra' && $typedata_chron != 'jge')
            {

                $row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";

                $idCurrentColor = $param['color'];
                $traceName = "trace_".$cle_station."_".$typedata_chron;
                $html_param .= "
                                <tr ".$row_l." >
                                    <td style='width:50px;'>
                                        ".$type_chron_array[$typedata_chron]['init_type_data']."
                                    </td>
                                    <td style='width:60px;'>

                                        <div class='color-dropdown' >
                                            <div id='selectedColor_".$typedata_chron."' class='dropdown-selected' onclick='toggleDropdownColor(".$typedata_chron.")' style='background-color:".$colorGraph[$idCurrentColor].";'></div>
                                            
                                            <div id='dropdownList_".$typedata_chron."' class='dropdown-list' >
                                            ";
                                                foreach ($colorGraph as $id => $color)
                                                {
                                                    $html_param .= "
                                                                        <div class='dropdown-item' style='background-color:".$color."' onclick=\"selectColor('".$color."',".$typedata_chron.",'".$traceName."');\"></div>
                                                                    ";
                                                }

                $html_param .= "                                            
                                            </div>
                                        </div>

                                        <input type='hidden' id='input_color_".$typedata_chron."' value='".$colorGraph[$idCurrentColor]."' />\n 

                                    </td>

                                    <td style='width:70px;'>
                                        <button type='button' class='decimal_axe' style='margin-left:10px;padding: 2px 4px;'  onclick=\"bumpLineWidth('".$typedata_chron."','".$traceName."',-0.5);\">−</button>    
                                        <button type='button' class='decimal_axe' style='margin-left:0px;padding: 2px 4px;'  onclick=\"bumpLineWidth('".$typedata_chron."','".$traceName."',0.5);\">+</button>  

                                        <span id='lineWidthDisplay_".$typedata_chron."' class='linew-display' style='display:none;'></span>
                                    </td>
                                </tr>
                                ";
            }
        }

    $html_param .= "</table>";

// --------

// --------------------------------------
// Debut page HTML

require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

    require(DIR_WS_STRUCTURE . 'block_wait.php'); // Block d'attente pendant les interrogations au serveur
    require(DIR_WS_STRUCTURE . 'block_graph.php'); // Affichage du graphique en plein écran
    require(DIR_WS_STRUCTURE . 'block_lacunes_info.php'); // Affichage du graphique en plein écran
    require(DIR_WS_GRAPH . 'block_stats.php'); // Block pour l'affichage des statistiques d'un chronique
    require(DIR_WS_GRAPH . 'block_tab.php'); // Block pour l'affichage des données dans un tableau d'un chronique

    require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
    include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

    echo "<div id='contour_general'>";
        
        echo "<div id='contenu_info' style='display:none;'></div>";
        
        echo "<div id='contenu_centre'>";

            echo "<div id='contenu_box2'>";		
            
                echo "<h1>";    
                    echo "<span>".htmlaccent('Visualisation des données')."</span>";
                echo "</h1>";

                // Colonne de gauche
                echo "<div id='cadre_graph' style='float:left;width:210px;margin-right:0.5%;height:calc(98vh - 140px);overflow-y: auto;'>\n";

                    echo "<div id='boxpopup' class='select-top' style='width:185px;margin:0px;padding:5px;'>\n";

                        echo "<p style='margin-left:1%;'>";
                            echo "<input type='checkbox' id='check_lac' checked>";
                            echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                echo "Afficher les lacunes";
                            echo "</span>";
                        echo "</p>";

                        echo "<div style='width:180px;margin: 10px 0;margin-left:2px;'>";

                            echo "<input type='checkbox' id='checkStatsAll' style='float:left;margin-right:8px;' onClick='visibleTraceAll();'>";
                            /*
                                echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                    echo "Afficher les lignes de Stats";
                                echo "</span>";
                            */
                            echo "<p class='toggle-graph' data-menu-graph='stats' style='font-size:12px;color:#000;padding-top:5px;'>";
                            
                                echo "<span style='font-weight:normal;font-size: 11px;'>";
                                    echo "Afficher les lignes de Stats";
                                echo "</span>";

                                echo "<span class='arrow' style='cursor:pointer;'>&#9660;</span>";
                                
                            echo "</p>";

                            // List Check Stats

                            echo "<div class='navMenuGraph' style='margin-left:10%;display:none;'>"; 
                                
                                echo "<p style=''>";
                                    echo "<input type='checkbox' id='checkStat_8' onClick='visibleTrace(8);'>";
                                    echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                        echo "Moyenne";
                                    echo "</span>";
                                echo "</p>";

                                echo "<p style=''>";
                                    echo "<input type='checkbox' id='checkStat_7' onClick='visibleTrace(7);'>";
                                    echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                        echo "Percentil (99%)";
                                    echo "</span>";
                                echo "</p>";

                                echo "<p style=''>";
                                    echo "<input type='checkbox' id='checkStat_6' onClick='visibleTrace(6);'>";
                                    echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                        echo "Percentil (90%)";
                                    echo "</span>";
                                echo "</p>";

                                echo "<p style=''>";
                                    echo "<input type='checkbox' id='checkStat_5' onClick='visibleTrace(5);'>";
                                    echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                        echo "Last Quartile (75%)";
                                    echo "</span>";
                                echo "</p>";

                                echo "<p style=''>";
                                    echo "<input type='checkbox' id='checkStat_4' onClick='visibleTrace(4);'>";
                                    echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                        echo "Médiane (50%)";
                                    echo "</span>";
                                echo "</p>";

                                echo "<p style=''>";
                                    echo "<input type='checkbox' id='checkStat_3' onClick='visibleTrace(3);'>";
                                    echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                        echo "First Quartile (25%)";
                                    echo "</span>";
                                echo "</p>";

                                echo "<p style=''>";
                                    echo "<input type='checkbox' id='checkStat_2' onClick='visibleTrace(2);'>";
                                    echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                        echo "Percentil (10%)";
                                    echo "</span>";
                                echo "</p>";

                                echo "<p style=''>";
                                    echo "<input type='checkbox' id='checkStat_1' onClick='visibleTrace(1);'>";
                                    echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>";
                                        echo "Percentil (1%)";
                                    echo "</span>";
                                echo "</p>";

                            echo "</div>";

                        echo "</div>";

                    echo "</div>\n";


                    // Options de gestion du graphique (Contrôle dynamique du zoom et des échelles)
                    echo "<div id='boxpopup' class='select-top' style='width:185px;margin:0px;margin-top:10px;padding-top:5px;padding-left:10px;'>\n";
                        
                        echo "<p>";
                            echo "<span style='font-weight: bold;font-size:13px;width:150px;'>".htmlaccent('Contrôle du Zoom')."</span>";
                        echo "</p>";

                        // Date début zoom
                        echo "<div id='boite_small' class='select_date' style='margin-right:10px;'>\n";
                                
                            echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Date début')."</p>\n";	
                            echo "<input class='input_texte' style='width:65px;padding-bottom: 4px;' name='x1Zoom' id='x1Zoom' type='text'>\n";
                            //echo "<input class='input_texte' style='width:65px;padding-bottom: 4px;' name='x1Zoom' id='x1Zoom' type='text' value='' onclick=\"javascript:displayCalendar(document.getElementById('x1Zoom'),'dd-mm-yyyy',this);\" readonly>\n";

                        echo "</div>\n";

                        // Date fin zoom
                        echo "<div id='boite_small' class='select_date' style='margin-right:0px;'>\n";
                                
                            echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Date fin')."</p>\n";	
                            echo "<input class='input_texte' style='width:65px;padding-bottom: 4px;' name='x2Zoom' id='x2Zoom' type='text'>\n";
                            //echo "<input class='input_texte' style='width:65px;padding-bottom: 4px;' name='x2Zoom' id='x2Zoom' type='text' value='' onclick=\"javascript:displayCalendar(document.getElementById('x2Zoom'),'dd-mm-yyyy',this);\" readonly>\n";
                                    
                        echo "</div>\n";

                        echo "<hr>\n";
                        
                        // Bouton pour synchroniser le zoom axe des Abscisses
                        //echo "<button id='syncAbsc' class='zoom_graph'  style='margin-bottom:10px;' title='Synchroniser les Abscisses'>Sync Abs.</button>\n";

                        echo "<hr>\n";        

                        // Echelle
                        echo "<div id='boite_small' class='select_date' style='margin-right:10px;'>\n";
                                
                            echo "<p style='width:50px;color:#428bca;' title=".htmlaccent('Echelle : y min').">".htmlaccent('Y min')."</p>\n";	
                            echo "<input type='text' style='width:45px;' id='y1Zoom' value=''/>\n";
                                    
                        echo "</div>\n";

                        echo "<div id='boite_small' class='select_date'  style='margin-right:0px;'>\n";
                                	
                            echo "<p style='width:50px;color:#428bca;' title=".htmlaccent('Echelle : y max').">".htmlaccent('Y max')."</p>\n";	
                            echo "<input type='text' style='width:45px;' id='y2Zoom' value=''/>\n";
                                    
                        echo "</div>\n";

                        echo "<hr>\n";
                        
                        // Bouton pour synchroniser le zoom axe des ordonnées
                        echo "<button id='ajustCoord' class='zoom_graph' style='width:125px;margin-top:5px;margin-bottom:10px;' title=".htmlaccent('Ajustement de l\'échelle').">";
                            echo htmlaccent('Ajuster Echelle');
                        echo "</button>";

                    echo "</div>\n";

                    // Box de navigation temporelle par année et par mois 
                    echo "<div id='boxpopup' class='select-top' style='width:185px;margin:0px;margin-top:10px;padding-top:5px;padding-left:10px;'>\n";
                        echo "<p style='width:80px;'>";
                            echo "<span style='font-weight: bold;font-size:13px;'>".htmlaccent('Période fixe')."</span>";
                        echo "</p>";
                    
                        echo "<div id='boite_small' class='list_year' style='margin-right:10px;'>\n";
                                            
                            echo "<p style='color:#428bca;'>".htmlaccent('Année')."</p>";
                            echo "<select id='select_year_zoom' name='select_year_zoom' style='width:58px;'>";
                                
                                echo "<option value='0'>-</option>";    

                                for($a=$year_2;$a>=$year_1;$a--)
                                {
                                    echo "<option value='".$a."' >".$a."</option>";
                                    
                                }
                            echo "</select>";
                                
                        echo "</div>";

                        echo "<div id='boite_small' class='list_month'>\n";
                                        
                            echo "<p style='color:#428bca;'>".htmlaccent('Mois')."</p>";

                            echo select_mois_vide('select_month_zoom');
                                
                        echo "</div>";

                        echo "<hr>\n";
                        
                        echo "<button id='zoomPeriode' class='zoom_graph' style='width:70px;margin-top:10px;margin-bottom:14px;margin-left:0px;'>".htmlaccent('Générer')."</button>";

                        echo "<button id='zoomPeriode_previous' class='zoom_graph' style='width:30px;margin-top:10px;margin-bottom:14px;margin-left:0px;'><<</button>";

                        echo "<button id='zoomPeriode_next' class='zoom_graph' style='width:30px;margin-top:10px;margin-bottom:14px;margin-left:0px;'>>></button>";

                    echo "</div>\n";

                    echo "<div id='boxpopup' class='select-top' style='width:185px;margin:0px;margin-top:10px;padding:5px 0;padding-left:10px;'>\n";

                        echo "<p class='toggle-graph' data-menu-graph='param' style='width:175px;font-size:12px;color:#000;padding-top:5px;'>";
                            echo "<span style='font-weight: bold;font-size:13px;'>";
                                echo "Configuration";
                            echo "</span>";
                            echo "<span class='arrow' style='cursor:pointer;'>&#9660;</span>";
                        echo "</p>";
                        

                        // Box pour afficher la liste des chroniques dans la graph
                        // Avec paramétrage de la couleur des courbes (et épaisseur des traits ?)                                        
                        echo "<div id='curve-param' class='navMenuGraph' style='display:none;'>"; 

                            echo "<p style='width:80px;margin-top:10px;color:#428bca;'>";
                                echo "Traces";
                            echo "</p>";

                            echo $html_param;

                        echo "<hr>\n";
                        echo "</div>";
                        

                    echo "</div>\n";
                
                echo "<hr>\n";
                echo "</div>\n";


                // Bloc graphique                
                echo "<div id='cadre_graph' style='float:none;width:auto;'>\n";
                    
                    echo "<div style='width:auto;height:calc(98vh - 140px);overflow-y: auto;'>";      

                        $load_graph_function = '';

                        if(isset($station_chron_array) && sizeof($station_chron_array)>0)
                        {
                            // Div des graphiques
                            $num_graph = 1;
                            $cle_station = array_keys($station_chron_array);

                            //foreach($cle_station as $cle)
                            foreach($station_chron_array as $cle_station => $typedata_array) 
                            {	            
                                // INIT échelle des axes - Il ne peut y avoir que 2 axes  
                                ${'max_'.$cle_station} = 0;
                                ${'min_'.$cle_station} = 0;
                                ${'nb_chron_'.$cle_station} = sizeof($station_chron_array[$cle_station]); // nbre de chronique dans chaque station

                                ${'hidden_check_chron_'.$cle_station} = '';

                                // Dimensions pour l'affichage de plusieurs graphiques
                                $width_boxGraph = 'width:49%;';
                                $marginright_boxGraph = '';
                                $margintop_boxGraph = '';
                                $height_plot = 'height:40vh;';
                                
                                if(($num_graph % 2) == 0){$marginright_boxGraph = 'margin-left:1%;';} 
                                if($num_graph > 2){$margintop_boxGraph = 'margin-top:20px;';}
                                $nom_type_data = $eq_type_array[$station_all_array[$cle_station]['type_station']]['nom_eq_type'];    
                                if(sizeof($station_chron_array) == 1) // Si un seul graphique s'affiche   
                                {
                                    $width_boxGraph = 'width:99%;';
                                    $height_plot = 'height:50vh;';
                                }             

                                echo "<div id='boxpopup' class='select' style='".$width_boxGraph."margin:0;".$marginright_boxGraph.$margintop_boxGraph."padding:0;border-radius: 2px;border:1px solid #000;'>\n";

                                    // Agrandir le graphique => Popup
                                    echo "<div id='button_visu' onclick=\"zoom_graph('".$cle_station."','".$station_all_array[$cle_station]['code_station']."','".$station_all_array[$cle_station]['nom_station']."','".$nom_type_data."');\">\n";	
                                        echo  "Agrandir"; 
                                    echo "</div>\n";

                                    echo "<div id='button_lacune_".$cle_station."' class='button_lacune' style='margin-right:8px;padding:6px 5px;display:none;' title='".htmlaccent('Tableau des lacunes')."'>";
                                        echo  "Lacunes"; 
                                    echo "</div>";
                                    
                                    echo "<div id='button_tab_".$cle_station."' style='margin-right:8px;'></div>";

                                    echo "<div id='button_stats_".$cle_station."' style='margin-right:8px;'></div>";
                                    
                                    echo "<p class='titre' style='margin-bottom:5px;padding:4px 10px;font-size:13px;'>";
                                        
                                        echo $nom_type_data;
                                        echo "<br>";
                                        echo "<a href='modif_station.php?ref=".$cle_station."' target='_blank' >";
                                            echo $station_all_array[$cle_station]['code_station']." - ".$station_all_array[$cle_station]['nom_station'];
                                        echo "</a>";
                                        
                                    echo "</p>";

                                    // barre de gestion des zooms
                                    echo "<div style='height:25px;margin-right:15px;'>";

                                        echo "<div style='float:right;'>";
                                            echo "<input type='checkbox' id='check_zoom_x_".$cle_station."' checked onclick='zoomCTRL(".$cle_station.");'>";
                                            echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>".htmlaccent('Zoom / Move X')."</span>";
                                        echo "</div>";

                                        echo "<div style='float:right;margin-right:15px;'>";
                                            echo "<input type='checkbox' id='check_zoom_y_".$cle_station."' checked onclick='zoomCTRL(".$cle_station.");'>";
                                            echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>".htmlaccent('Zoom / Move Y')."</span>";
                                        echo "</div>";

                                    echo "</div>";
                                    
                                    echo "<div id='plot_".$cle_station."' class='graph' style='".$height_plot."margin:0 1%;display:none;'></div>\n";
                                    
                                    echo "<div id='wait_".$cle_station."' style='width:100%;".$height_plot."text-align:center;'>";
                                        echo "<img src='".DIR_WS_IMG."wait.gif' style='width:50px;margin-top:15%;' title='".htmlaccent('Chargement en cours ...')."'>";
                                        echo "<p>".htmlaccent('Chargement en cours ...')."</p>";
                                    echo "</div>\n";    

                                    // Il faut envoyer $station_chron_array et $cle
                                    $load_graph_function .= "load_graph(".$cle_station.",".$station_all_array[$cle_station]['type_station']."," .json_encode($typedata_array).");"; // Appeler votre fonction JavaScript avec les valeurs
                                    $js_syncAbsc_var .= "Plotly.relayout('plot_".$cle_station."', {'xaxis.range': [x1_format, x2_format]});";
                                    $js_syncOrdon_var .= "Plotly.relayout('plot_".$cle_station."', {'yaxis.range': [y1, y2]});";

                                    echo "<div id='box_options_".$cle_station."' style='float:left;width:98%;margin-top:0;margin-left:2%;'>";

                                        echo "<div style='float:left;'>";
                                            echo "<button id='plus_".$cle_station."' class='decimal_axe' style='margin-left:10px;' 
                                                    title='".htmlaccent('Ajouter une décimale')."'
                                                    onCLick=\"updateDecimals('plot_".$cle_station."','yaxis','+');\">+</button>\n";
                                            echo "<button id='moins_".$cle_station."' class='decimal_axe'
                                                    title='".htmlaccent('Enlever une décimale')."'
                                                    onCLick=\"updateDecimals('plot_".$cle_station."','yaxis','-');\">-</button>\n";
                                            echo "<hr>";
                                            echo "<button id='log_".$cle_station."' class='log_axe' style='float:left;' title='".htmlaccent('Echelle logarithmique (base 10)')."'>";
                                                echo htmlaccent('Ech. Log');
                                            echo "</button>\n";

                                        echo "</div>";                                

                                        echo "<div style='float:left;margin-left:30%;'>";
                                            
                                            echo "<div id='button_calcul_".$cle_station."' style='float:right;border:0;margin:10px auto;'></div>";

                                        echo "</div>";

                                    echo "</div>";
                                    
                                echo "<hr>\n";
                                echo "</div>\n";	
                                
                                $num_graph++;
                            }
                        }
                        else
                        {
                            echo "<div id='boxpopup' >\n";
                                echo "<p class='alert'>".htmlaccent('Aucune données n\'a été trouvée')."</p>";
                            echo "<hr>";
                            echo "</div>";
                        }
                        echo "<hr>";
                    echo "</div>";
                       
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

    // Paramétrage général
    var msgInfo = document.getElementById('contenu_info');
    var boxWait = document.getElementById('box_wait'); // Attente lors des chaque opération, occupe l'ensemble de la page

    var boxStats = document.getElementById('box_stats'); // Box d'affichage pour les statistiques de la chronique en cours
   

    var checkLac = document.getElementById('check_lac');
    var checkStats = document.getElementById('check_stats');

    var checkZoomX = document.getElementById('check_zoom_x');
    var checkZoomY = document.getElementById('check_zoom_y');
    

    // Liste dynamique des identifiant de graphique donc de stations affichées
    var js_traceStation = [];    
    var js_shapesLacunesData = [];        
    
    // Génération des graphiques
    var idPlotZoom = 0;
    var js_syncAbsc_var = "<?php echo $js_syncAbsc_var;?>";
    var js_syncOrdon_var = "<?php echo $js_syncOrdon_var;?>";

    var min_x = '<?php echo $date_1;?>';
    var max_x = '<?php echo $date_2;?>';

    var tab_param = <?php echo json_encode($tab_param);?>;

  

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


    // Lancement de la génération de graph

    function load_graph(cle_station,type_station,typedata_array)
    {        
        //boxWait.style.display = 'block';

        // Mise au format JSON des données
        // Créer un objet contenant les données à envoyer
        let dataToSend = {            
            territoireId: <?php echo $territoire_id;?>,
            lang: '<?php echo $lang;?>',
            cle_station: cle_station,
            type_station: type_station, // Hydro, Pluvio, Piezo, ...
            typedata_array: typedata_array,
            min_x: min_x,
            max_x: max_x,
            tab_param: tab_param
        };

        // Convertir l'objet en JSON
        var jsonDataGraph = JSON.stringify(dataToSend);

        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/graph/process_graph_multi.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                document.getElementById('wait_'+cle_station).style.display = 'none';
                document.getElementById('plot_'+cle_station).style.display = 'block'; 

                document.getElementById('box_options_'+cle_station).style.display = 'block';

                document.getElementById('button_lacune_'+cle_station).style.display = 'block';
                document.getElementById('button_stats_'+cle_station).style.display = 'block';
                document.getElementById('button_tab_'+cle_station).style.display = 'block';

                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);
            
                // Accéder aux données récupéré coté serveur
                eval(jsonResponse['js_text']); // on récupère le script généré coté serveur pour afficher les graphiques

                // Permet de récupérer les données liées au lacunes pour l'affichage dans les graphs
                var plotDivId = 'plot_'+cle_station;
                var graphDiv = document.getElementById(plotDivId);
                var shapes = graphDiv.layout.shapes;
                var nbTrace = graphDiv.data.length;
                
                js_traceStation[cle_station] = nbTrace;
                js_shapesLacunesData[cle_station] = shapes;
                
                // -----------------
                // affichage du bouton de pour les statistiques
                text_button_stats = jsonResponse['text_button_stats'];
                document.getElementById('button_stats_'+cle_station).insertAdjacentHTML('beforeend', text_button_stats);
                text_button_tab = jsonResponse['text_button_tab'];
                document.getElementById('button_tab_'+cle_station).insertAdjacentHTML('beforeend', text_button_tab);

                // affichage du bouton de correction
                text_button_calcul = jsonResponse['text_button_calcul'];
                document.getElementById('button_calcul_'+cle_station).insertAdjacentHTML('beforeend', text_button_calcul);

                // affichage de la liste des lacunes
                text_lacunes = jsonResponse['text_lacunes'];
                ecoute_lacune(cle_station,text_lacunes);


                // Coordonnées
                min_x = jsonResponse['min_x'];
                min_date = min_x;

                max_x = jsonResponse['max_x'];
                max_date = max_x;

                document.getElementById('x1Zoom').value = min_date;
                document.getElementById('x2Zoom').value = max_date;

                min_y = parseInt(jsonResponse['min_y']);
                max_y = parseInt(jsonResponse['max_y']);
                document.getElementById('y1Zoom').value = min_y;
                document.getElementById('y2Zoom').value = max_y;

                document.getElementById('ajustCoord').removeEventListener('click', ajustCoord); // Pour supprimer l'écouteur d'événements
                document.getElementById('ajustCoord').addEventListener('click', ajustCoord); // Pour ajouter l'écouteur d'événements

                document.getElementById('zoomPeriode').removeEventListener('click', zoomPeriode); // Pour supprimer l'écouteur d'événements
                document.getElementById('zoomPeriode').addEventListener('click', zoomPeriode); // Pour ajouter l'écouteur d'événements

                document.getElementById('zoomPeriode_previous').removeEventListener('click', zoomPeriode_previous); // Pour supprimer l'écouteur d'événements
                document.getElementById('zoomPeriode_previous').addEventListener('click', zoomPeriode_previous); // Pour ajouter l'écouteur d'événements

                document.getElementById('zoomPeriode_next').removeEventListener('click', zoomPeriode_next); // Pour supprimer l'écouteur d'événements
                document.getElementById('zoomPeriode_next').addEventListener('click', zoomPeriode_next); // Pour ajouter l'écouteur d'événements
            }
        };

        // Envoyer les données JSON au serveur
        xhr.send(jsonDataGraph);
    }




    // Gestion de l'affichage des Lacunes
    // Construction des layouts dynamiques
    // Fonction pour mettre à jour les graphiques

    // Code pour la dynamique des listes des stats (lignes) à afficher sur le graphique
	$(document).ready(function() 
	{
        // Appliquer l'état initial des menus
        $('.toggle-graph').each(function() 
        {
            const menuId = $(this).data('menu-graph');
            const isOpen = menuStates[menuId] === 1;

            const navigation = $(this).nextAll('.navMenuGraph').first();
            const arrow = $(this).find('.arrow');

            if (isOpen) {
                navigation.show();
                arrow.html('&#9650;'); // Flèche vers le haut
            } else {
                navigation.hide();
                arrow.html('&#9660;'); // Flèche vers le bas
            }
        });


		$(document).on('click', '.toggle-graph', function()
		{   
			// Trouver le contenu associé à ce titre		
			const id_user = <?php echo json_encode($id_user); ?>;
			const navdiag = $(this).nextAll('.navMenuGraph').first();
			const menuId = $(this).data('menu-graph'); // Récupérer l'identifiant du menu
			const isOpen = navdiag.is(':visible');
			

			navdiag.slideToggle('slow', function() 
			{
				// Changer l'icône de la flèche
				const arrow = $(this).prevAll('.toggle-graph').find('.arrow');
				
				if (navdiag.is(':visible')) 
				{
					arrow.html('&#9650;'); // Flèche vers le haut
				} else 
				{
					arrow.html('&#9660;'); // Flèche vers le bas
				}		
                
                const dataToSend = {
									id_user: id_user,
									menu_id: menuId,
									is_open: !isOpen // L'état sera inversé après le slideToggle
								};

				// Convertir l'objet en JSON
				const jsonData = JSON.stringify(dataToSend);				
				
				// Effectuer une requête AJAX asynchrone
				const xhr = new XMLHttpRequest();
				xhr.open("POST", "include/structure/box/process_menu.php", true);
				xhr.setRequestHeader("Content-Type", "application/json");
				xhr.send(jsonData);

			});
		});
	});
    
    
    // Écouteur d'événement pour le checkbox Lacunes
    checkLac.addEventListener('change', function() 
    {
        checkLac = this.checked;
        for (var id_station in js_shapesLacunesData) 
        {
            var shapes = checkLac ? js_shapesLacunesData[id_station] : [];
            Plotly.relayout('plot_' + id_station, {'shapes': shapes});
        }
    });

    // Écouteur d'événement pour le checkbox des lignes de Stats (Moy, Med, Q1-25%, Q3-75%)
    /*
    checkStats.addEventListener('change', function() 
    {
        checkStats = this.checked;
        for (var id_station in js_traceStation) 
        {
            nbTrace = js_traceStation[id_station];

            Plotly.restyle('plot_'+id_station, {'visible': [checkStats, checkStats]}, [nbTrace-1, nbTrace-2, nbTrace-3, nbTrace-4, nbTrace-5]);
        }
    });
    */
    function visibleTraceAll()
    {
        idCheckStats = document.getElementById('checkStatsAll');
        checkStats = idCheckStats.checked;

        for (var id_station in js_traceStation) 
        {
            nbTrace = js_traceStation[id_station];
            numTraces = 8; // Nbre de Trace (Graph) avec des stats

            // Parcourir tous les éléments checkStat_ et les décocher si checkStatsAll est décoché
            if (!checkStats) 
            {
                console.log(nbTrace);
                for (var i = nbTrace-1; i >= (nbTrace-numTraces); i--) 
                {
                    var checkStatElement = document.getElementById('checkStat_' + i);
                    if (checkStatElement) 
                    {
                        checkStatElement.checked = false;
                        Plotly.restyle('plot_'+id_station, {'visible': [false, false]}, [i]);
                    }
                }
            }
            else
            {
                document.getElementById('checkStat_' + 6).checked = true;
                document.getElementById('checkStat_' + 4).checked = true;
                document.getElementById('checkStat_' + 2).checked = true;
                Plotly.restyle('plot_'+id_station, {'visible': [checkStats, checkStats]}, [nbTrace-6,nbTrace-4,nbTrace-2]);
            }
        }
        
    }

    function visibleTrace(numTrace)
    {
        idCheckStat = document.getElementById('checkStat_'+numTrace);
        checkStat = idCheckStat.checked;

        if(checkStat){document.getElementById('checkStatsAll').checked = true;}

        for (var id_station in js_traceStation) 
        {
            nbTrace = js_traceStation[id_station];

            Plotly.restyle('plot_'+id_station, {'visible': [checkStat, checkStat]}, [nbTrace-numTrace]);
        }
        
    }


    // Function pour la gestion des couleurs des traces
    
    function toggleDropdownColor(index) 
    {
        let dropdown = document.getElementById('dropdownList_'+index);
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    function selectColor(color, index_tdc, traceName) 
    {
        document.getElementById('selectedColor_'+index_tdc).style.backgroundColor = color;
        document.getElementById('dropdownList_'+index_tdc).style.display = 'none';
        document.getElementById('input_color_'+index_tdc).value = color;

        // Appliquer à tous les graphs dont l'id commence par "plot_"
        document.querySelectorAll("[id^='plot_']").forEach(function(plotDiv) {
            
            const data = plotDiv.data || [];
            const idxs = [];
            for (let i = 0; i < data.length; i++) 
            {       
                const tr = data[i] || {};
                const lg = tr.legendgroup;

                if (lg === ('tdc_' + index_tdc))
                {idxs.push(i);}
            }

            if (idxs.length) 
            {
                Plotly.restyle(plotDiv, {
                        'marker.color': color,      // barres, marqueurs
                        'marker.line.color': color, // contour des marqueurs
                        'line.color': color         // lignes
                    }, idxs);
            }
        });  
    }

    function bumpLineWidth(index_tdc, traceName, delta)
    {
        let anyAppliedWidth = null; // pour afficher une valeur indicative dans le <span>

        document.querySelectorAll("[id^='plot_']").forEach(function(plotDiv){
            const data = plotDiv.data || [];
            const forLines = [], lineWidths = [];
            const forBars  = [], barWidths  = [];

            for (let i=0; i<data.length; i++)
            {
                const tr = data[i] || {};
                const lg = tr.legendgroup;

                if (lg === ('tdc_' + index_tdc))
                {
                    // --- calcul des nouvelles largeurs ---
                    if ((tr.type === 'scatter' || tr.type === 'scattergl') && (tr.mode || '').includes('lines')) 
                    {
                        const current = (tr.line && typeof tr.line.width === 'number') ? tr.line.width : 2;
                        const next    = Math.max(0.1, +(current + delta).toFixed(2));
                        forLines.push(i);
                        lineWidths.push(next);
                        anyAppliedWidth = next;
                    } 
                    else if (tr.type === 'bar') 
                    {
                        const current = (tr.marker && tr.marker.line && typeof tr.marker.line.width === 'number') ? tr.marker.line.width : 0;
                        const next    = Math.max(0.1, +(current + delta).toFixed(2));
                        forBars.push(i);
                        barWidths.push(next);
                        anyAppliedWidth = next;
                    }

                }
            }   

            if (forLines.length)
            {
                // chaque valeur de lineWidths s’applique à la trace correspondante de forLines
                Plotly.restyle(plotDiv, { 'line.width': lineWidths }, forLines);
            }
            if (forBars.length)
            {
                Plotly.restyle(plotDiv, { 'marker.line.width': barWidths }, forBars);
            }

        });

        // Affichage indicatif de la dernière largeur appliquée (optionnel)
        /*
        if (anyAppliedWidth != null){
            const disp = document.getElementById('lineWidthDisplay_' + index_tdc);
            if (disp) disp.textContent = 'épaisseur: ' + anyAppliedWidth;
        }
            */
        
    }




    // fonction qui synchronise les abscisses des graphs déjà affichés
    function ajustCoord()
    {
        x1_value = document.getElementById('x1Zoom').value;
        x2_value = document.getElementById('x2Zoom').value;

        

        if(!isValidDatesInput(x1_value,x2_value))
        {
            msgInfo.style.border = '4px solid #930000'; 
            return; // Action stoppée
        }

        // Vérifier si les valeurs des ordonnées sont bien des nombres
        y1 = document.getElementById('y1Zoom').value;
        y2 = document.getElementById('y2Zoom').value;

        if (!isNumber(y1) || !isNumber(y2))
        {            
            msgInfo.style.border = '4px solid #930000'; 
            return;
        }

        // Convertir les dates au format 'dd-mm-yyyy' en 'yyyy-mm-dd'
        x1_format = new Date(x1_value.split('-').reverse().join('-'));
        x2_format = new Date(x2_value.split('-').reverse().join('-'));
        
        // Mise à jour des échelles des graphiques
        eval(js_syncAbsc_var);
        eval(js_syncOrdon_var);
    }



    function zoomCTRL(idStation)
    {
        var checkZoomX = document.getElementById('check_zoom_x_'+idStation);
        var checkZoomY = document.getElementById('check_zoom_y_'+idStation);

        var fixedRangeX = true; // Par défaut, désactive le zoom horizontal
        var fixedRangeY = true; // Par défaut, désactive le zoom vertical

        if(checkZoomX.checked && checkZoomY.checked)
        {
            fixedRangeX = false; // Active le zoom horizontal
            fixedRangeY = false; // Active le zoom vertical
        }
        else if(checkZoomX.checked )
        {
            fixedRangeX = false; // Active le zoom horizontal
            fixedRangeY = true; // Désactive le zoom vertical
        }
        else if(checkZoomY.checked )
        {
            fixedRangeX = true; // Désactive le zoom horizontal
            fixedRangeY = false; // Active le zoom vertical
        }
        
        Plotly.relayout('plot_' + idStation, 
        {
            'xaxis.fixedrange': fixedRangeX,
            'yaxis.fixedrange': fixedRangeY
        });
    }

    // fonction qui permet de synchroniser les graphiques sur un mois, ou une années spécifiques afin de les comparer
    function zoomPeriode()
    {
        year_zoom = document.getElementById('select_year_zoom').value;        
        month_zoom = document.getElementById('select_month_zoom').value;

        if (year_zoom > 0) 
        {
            if(month_zoom > 0)
            {
                numberOfDays = getDaysInMonth(month_zoom, year_zoom);
                
                if(month_zoom < 10){month='0'+month_zoom;}
                else{month=month_zoom;}

                x1_format = year_zoom+'-'+month+'-01';
                x2_format = year_zoom+'-'+month+'-'+numberOfDays;
            }
            else
            {
                x1_format = year_zoom+'-01-01';
                x2_format = year_zoom+'-12-31';
            }

            x1_format_value = x1_format.split(' ')[0].split('-').reverse().join('-');
            x2_format_value = x2_format.split(' ')[0].split('-').reverse().join('-');

            document.getElementById('x1Zoom').value = x1_format_value;
            document.getElementById('x2Zoom').value = x2_format_value;
            
            eval(js_syncAbsc_var);
        }
    }

    // fonction qui permet de faire évoluer vers le passé le zoom des grpahiques d'une année sur l'autre ou d'un moi ssur l'autre
    function zoomPeriode_previous()
    {
        year_zoom = document.getElementById('select_year_zoom').value;
        month_zoom = document.getElementById('select_month_zoom').value;

        if(year_zoom > 0) 
        {
            if(month_zoom > 0)
            {
                month=parseInt(month_zoom)-1;
                if(month==0){month=12;year=parseInt(year_zoom)-1;}
                else{year=year_zoom;}  
                nbDayMonth=getDaysInMonth(month, year);

                if(month < 10){month_string='0'+month;}
                else{month_string=month;}
                
                if(nbDayMonth < 10){nbDayMonth_string='0'+nbDayMonth;} 
                else{nbDayMonth_string=nbDayMonth;} 

                x1_format = year+'-'+month_string+'-01';
                x2_format = year+'-'+month_string+'-'+nbDayMonth_string;                
            }
            else
            {
                year=parseInt(year_zoom)-1;
                month=month_zoom;

                x1_format = year+'-01-01';
                x2_format = year+'-12-31';
            }

            x1_format_value = x1_format.split(' ')[0].split('-').reverse().join('-');
            x2_format_value = x2_format.split(' ')[0].split('-').reverse().join('-');

            document.getElementById('x1Zoom').value = x1_format_value;
            document.getElementById('x2Zoom').value = x2_format_value;

            document.getElementById('select_year_zoom').value = year;
            document.getElementById('select_month_zoom').value = month;

            eval(js_syncAbsc_var);
        }
    }

    // fonction qui permet de faire évoluer vers le future le zoom des grpahiques d'une année sur l'autre ou d'un moi ssur l'autre
    function zoomPeriode_next()
    {
        year_zoom = document.getElementById('select_year_zoom').value;
        month_zoom = document.getElementById('select_month_zoom').value;

        if(year_zoom > 0) 
        {
            if(month_zoom > 0)
            {
                month=parseInt(month_zoom)+1;
                if(month>12){month=1;year=parseInt(year_zoom)+1;}
                else{year=year_zoom;}  
                nbDayMonth=getDaysInMonth(month, year);

                if(month < 10){month_string='0'+month;}
                else{month_string=month;}
                
                if(nbDayMonth < 10){nbDayMonth_string='0'+nbDayMonth;} 
                else{nbDayMonth_string=nbDayMonth;} 

                x1_format = year+'-'+month_string+'-01';
                x2_format = year+'-'+month_string+'-'+nbDayMonth_string;                
            }
            else
            {
                year=parseInt(year_zoom)+1;
                month=month_zoom;

                x1_format = year+'-01-01';
                x2_format = year+'-12-31';
            }

            x1_format_value = x1_format.split(' ')[0].split('-').reverse().join('-');
            x2_format_value = x2_format.split(' ')[0].split('-').reverse().join('-');

            document.getElementById('x1Zoom').value = x1_format_value;
            document.getElementById('x2Zoom').value = x2_format_value;

            document.getElementById('select_year_zoom').value = year;
            document.getElementById('select_month_zoom').value = month;
            
            eval(js_syncAbsc_var);
        }
    }

    // fonction permettant de créer un point d'écoute d'un action sur le bouton Tableau des lacunes pour ouvrir le bloc permettant l'affichage d'un div en popup
    function ecoute_lacune(id_station,text_lacunes)
    {
        var bouttonLacunes = document.getElementById('button_lacune_'+id_station);
        bouttonLacunes.addEventListener('click', function() 
        {
            document.getElementById('cadre_tab_lacune').innerHTML = text_lacunes;
            document.getElementById('box_lacunes_info').style.display = 'block';
        });
    }

    <?php
    // On lance le chargement de tous les graphiques 
    echo $load_graph_function;

    ?>


    function zoom_graph(id_station,code_station,nom_station,type_data)
    {
        document.getElementById('box_graph').style.display='block';

        document.getElementById('titre_graph').innerHTML = code_station+' - '+nom_station+' - '+type_data;

        idPlotZoom = id_station;

        // Récupérer le nom du plot
        var plotName = 'plot_' + id_station;

        // Récupérer les données et la mise en page du plot
        var plotData = window[plotName].data;
        var plotLayout = window[plotName].layout;

        Plotly.newPlot('cadre_limit', plotData, plotLayout, config);

        addLogScaleButton('cadre_limit','log-button_gd_1','yaxis'); 
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

    // Fonction ajoutant ou enlevant des décimal sur un axe 

    var decimalPlaces = 1;    // Variable pour suivre le nombre de décimales sur les graphiques

    function updateDecimals(plotId, axe, type) 
    {
        if (type == '+' && decimalPlaces < 6){decimalPlaces++;}
        if (type == '-' && decimalPlaces > 0){decimalPlaces--;}

        var newTickFormat = '.' + decimalPlaces + 'f';
        Plotly.relayout(plotId, {[axe + '.tickformat']: newTickFormat});
    }

    // Function qui permet de connaitre le nombre de jours dans un mois
    function getDaysInMonth(monthNumber, year) 
    {
        return new Date(year, monthNumber, 0).getDate();
    }


    // Fonctions pour affichages des STATISTIQUES
    
    var dataToSendStats = null; // Variable globale pour stocker dataToSendStats
        
    var titleBoxStats = document.getElementById('title_box'); // Titre block_stats.php   

    var menuStats = document.getElementById('menu_stats'); 

    var waitBoxStats = document.getElementById('cadre_wait_stats');

    var generalStats = document.getElementById('general_stats'); 
    var contenuStats = document.getElementById('contenu_stats');
    var contenuStatsGraph = document.getElementById('contenu_stats_graph');


    function afficheStats(cle_station,type_station,id_typedata)
    {
        x1Zoom = document.getElementById('x1Zoom').value;
        x2Zoom = document.getElementById('x2Zoom').value;

        boxStats.style.display = 'block';        
        waitBoxStats.style.display = 'block';
        
        contenuStats.style.display = 'none';
        contenuStatsGraph.style.display = 'none';  


        // Créer un objet JavaScript contenant les données à envoyer
        dataToSendStats = {
							territoireId: '<?php echo $territoire_id; ?>',
                            lang: '<?php echo $lang;?>',
                            cle_station: cle_station,
                            type_station: type_station, // Hydro, Pluvio, Piezo, ...
                            id_typedata: id_typedata, // Chron : CI, CIE, QI, QIE, ...
                            min_x:x1Zoom,
                            max_x:x2Zoom
						};

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/graph/process_graph_stats.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {	
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				html_stats_title = jsonResponse['html_stats_title'];
				html_stats_menu = jsonResponse['html_stats_menu'];
				html_stats_general = jsonResponse['html_stats_general'];
					
				titleBoxStats.innerHTML = html_stats_title;
                menuStats.innerHTML = html_stats_menu;
                generalStats.innerHTML = html_stats_general;
                

                // Appeler statsChron et gérer la réponse de manière asynchrone
                statsChron('global');	
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSendStats));
    }

    // Fonction qui permet de calculer les statistiques de bases sur une chronique
    function statsChron(typeStat='global')
    {  
        waitBoxStats.style.display = 'block';
        contenuStats.style.display = 'none'; 
        contenuStatsGraph.style.display = 'none'; 

        bydays=false;
        
        switch (typeStat) 
        {
            case 'global':
                processFile = 'process_stats_chron_global.php';
                break;
            case 'byyear':
                processFile = 'process_stats_chron_byyear.php';
                break;
            case 'bymonth':
                processFile = 'process_stats_chron_bymonth.php';
                break;
            case 'bydays':
                processFile = 'process_stats_chron_bydays_selectyear.php';
                bydays=true;
                break;
            case 'workbymonth':
                processFile = 'process_stats_chron_global.php';
                //processFile = 'process_stats_chron_workbymonth.php';
                break;
            default:
                processFile = 'process_stats_chron_global.php';
                break;
        }

        // Mettre à jour l'état actif des boutons
        var buttons = document.querySelectorAll('.bstats');
        buttons.forEach(function(button) {button.classList.remove('active');});

        var buttonClick = document.getElementById(typeStat);
        if (buttonClick) {buttonClick.classList.add('active');}
        
        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/stats/"+processFile , true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {	
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);
                html_stats = jsonResponse['html_stats'];
                stat_graph = jsonResponse['stat_graph'];                
                html_stats_graph = jsonResponse['html_stats_graph'];	
                js_graph = jsonResponse['js_graph'];	
                
                waitBoxStats.style.display = 'none';
                contenuStats.style.display = 'block'; 
                contenuStats.innerHTML = html_stats;

                if(bydays)
                {
                    yearSelect = document.getElementById('yearSelect').value;
                    statsChronDays(yearSelect);
                }

                if(stat_graph)
                {
                    contenuStatsGraph.style.display = 'block';  
                    contenuStatsGraph.innerHTML = html_stats_graph;
                    eval(js_graph);                    
                    console.log(js_graph);
                }

                
            }
        };
        xhr.send(JSON.stringify(dataToSendStats));            
    }

    function statsChronDays(year_select)
    {          
        var contenuStatsDays = document.getElementById('contenu_stats_days');
        //contenuStatsGraph.style.display = 'none'; 

        var dataToSendStatsDays = {
                                    stats: dataToSendStats,
                                    yearSelect: year_select
                                };
                
        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/stats/process_stats_chron_bydays.php" , true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {	
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);
                html_stats = jsonResponse['html_stats'];
                
                contenuStatsDays.style.display = 'block'; 
                contenuStatsDays.innerHTML = html_stats;
            }
        };
        xhr.send(JSON.stringify(dataToSendStatsDays));            
    }

    function prevYear() 
    {
        var yearSelect = document.getElementById('yearSelect');
        var selectedIndex = yearSelect.selectedIndex;
        if (selectedIndex < yearSelect.options.length - 1) {
            yearSelect.selectedIndex = selectedIndex + 1;
            statsChronDays(yearSelect.value);
        }
    }

    function nextYear() 
    {
        var yearSelect = document.getElementById('yearSelect');
        var selectedIndex = yearSelect.selectedIndex;
        if (selectedIndex > 0) {
            yearSelect.selectedIndex = selectedIndex - 1;
            statsChronDays(yearSelect.value);
        }
    }

    // ---------------------------------
    // Fonction Vérification des Dates Heures

    function isValidDatesInput(date1Input,date2Input)
    {   
        // Vérifier si les dates sont valides
        if (isValidDate(date1Input) && isValidDate(date2Input))
        {
            
                // Convertir dates et heures en objets Date complets
                const date1Format = parseDate(date1Input); // Obtenez un objet Date à partir de la date
                const date2Format = parseDate(date2Input); // Obtenez un objet Date à partir de la date

                // Comparer les deux dates complètes
                if (date1Format < date2Format) 
                {
                    return true;
                } 
                else 
                {
                    msgInfo.innerText = "La 'Date de début' doivent être antérieures à la 'Date de fin'";
                    msgInfo.style.display = 'block';

                    return false;
                }
        } 
        else 
        {
            msgInfo.innerText = "Au moins l'une des dates saisies est invalide ou dans un mauvais format (dd-mm-yyyy : format valide)";
            msgInfo.style.display = 'block';

            return false;
        }  
    }

    // Fonction pour valider une date réelle
    function isValidDate(dateString) 
    {
        // Vérifier le format avec une regex
        const dateRegex = /^(0[1-9]|[12][0-9]|3[01])-(0[1-9]|1[0-2])-(\d{4})$/;
        if (!dateRegex.test(dateString)) 
        {
            return false; // Format invalide
        }

        // Découper la date
        const [day, month, year] = dateString.split("-").map(Number);

        // Créer une date JavaScript et vérifier sa validité
        const date = new Date(year, month - 1, day); // Mois commence à 0 en JS
        return (
            date.getFullYear() === year &&
            date.getMonth() === month - 1 &&
            date.getDate() === day
        );
    }

    // Fonction pour convertir une date (format valide) en objet Date
    function parseDate(dateString) 
    {
        [day, month, year] = dateString.split("-").map(Number);
        return new Date(year, month - 1, day);
    }

    // Fonction pour vérifier si une valeur est un nombre (entier ou flottant)
    function isNumber(inputElement) 
    {
        // Vérifie si la valeur de l'élément d'entrée est un nombre
        const value = Number(inputElement);
        if (isNaN(value)) {
            // Affiche un message d'erreur
            msgInfo.innerText = "Erreur : Les champs Ymin et Ymax doivent être des nombres.\n";
            msgInfo.style.display = 'block';
            return false;
        }
        return true;
    }



</script>