<?php
/*  
----------------------------------------
Copyright (c) 2025 - Vai-Natura
----------------------------------------
Affichage des données dans un graphique combiné (2 axes)
----------------------------------------
*/

$nom_station = '';	
$code_station = '';

$x_min = 0;
$x_max = 0;
$y_min = 0;
$y_max = 0;


//$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" onclick=\"this.className='rowSelect';\"";
$row_l="class='row1' onclick=\"this.className='rowSelect';\"";
$print_row = '';   

$titre_graph_first = "";
$data_graph_all = ""; // Variables données pour les graphiques 


// --------------------------------------


// --------------------------------------
// Debut page HTML

require(DIR_WS_STRUCTURE . 'header_web.php');

echo "<body>";

    require(DIR_WS_STRUCTURE . 'block_graph.php'); // Affichage du graphique en plein écran

    require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
    include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

    echo "<div id='contour_general'>";

	    echo "<div id='contenu_info' style='display:none;'></div>";

        echo "<div id='contenu_centre'>";

            echo "<div id='contenu_box2'>";
                
                echo "<h1>";                                
                    echo "<span style=font-weight:bold;>".htmlaccent('Graphique Combiné (2 axes)')."</span>";
                echo "</h1>";

                echo "<div style='widht:100%;'>";

                    // Colonne de gauche pour afficher la liste des chroniques pour Axe 1
                    echo "<div id='cadre_graph' style='float:left;width:260px;margin:0;margin-right:10px;height:78vh;overflow-y: auto;'>\n";

                        echo "<div id='boxpopup' class='select-top' style='width:96%;margin:0px;padding:10px 1%;'>\n";
                        
                            echo "<p style='margin-left:1%;'>";
                                echo "<span style='font-weight: bold;font-size:13px;'>".htmlaccent('Axe 1 - Données')."</span>";
                            echo "</p>";
                            
                            // Affichage du sélecteur de couleur
                            $index_color = 1;
                            include(DIR_WS_GRAPH . 'select_color.php'); 


                            echo "<p style='margin-left:1%;'>";
                                echo "<input type='checkbox' id='check_lac_axe1' checked>";
                                echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>".htmlaccent('Afficher les lacunes')."</span>";
                            echo "</p>";
                            
                            echo "<div id='cadre_data_axe1' style='width:99%;margin:0;padding:0;'>\n";
                                    
                                echo "<div class='table-container' style='height: auto;'>";

                                    echo "<table id='table_tri' cellspacing='0'>\n";
                                    
                                        echo "
                                            <thead>
                                                <tr class='header-row'>\n
                                                    <th style='width:50%;font-size:11px;'>".htmlaccent('Nom St.')."</th>\n
                                                    <th style='width:30%;font-size:11px;'>".htmlaccent('Code St.')."</th>\n
                                                    <th style='width:10%;font-size:11px;'>".htmlaccent('Chron.')."</th>\n
                                                    <th style='width:10%;'></th>\n			 
                                                </tr>\n
                                            </thead>\n
                                        ";

                                        // Radio Boutton
                                        
                                            $row = 0;
                                            if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                                            else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
                                            echo " 
                                                <tr ".$row_l.">\n

                                                    <td>
                                                    Aucun
                                                    </td>\n
                                                    <td>-</td>\n
                                                    <td style='text-align:center;' >-</td>\n

                                                    <td style='text-align:center;'>\n
                                                        <input type='radio' name='radio_axe1' value='0_0' data-typedata-sql='' >\n
                                                    </td>\n
                                                
                                                </tr>\n
                                                ";
                                            
                                            if(isset($station_chron_array) && sizeof($station_chron_array)>0)
                                            {
                                                $row = 1;
                                                foreach($station_chron_array as $cle_station => $typedata_array) 
                                                {	
                                                    foreach($typedata_array as $cle_type_data => $typedata_sql) 
                                                    {
                                                        if (!empty($cle_type_data) && !in_array($cle_type_data, array('ra', 'etl', 'jge')))
                                                        {
                                                            $check = ($row == 1) ? 'checked' : ''; // Condition pour définir checked

                                                            if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                                                            else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
                                                            
                                                            echo " 
                                                                <tr ".$row_l.">\n

                                                                    <td title='".$station_all_array[$cle_station]['nom_station']."'>".
                                                                        affichelettres($station_all_array[$cle_station]['nom_station'],18)."
                                                                    </td>\n
                                                                    <td>".$station_all_array[$cle_station]['code_station']."</td>\n
                                                                    <td style='text-align:center;' >".$type_chron_array[$cle_type_data]['init_type_data']."</td>\n

                                                                    <td style='text-align:center;'>\n
                                                                        <input type='radio' name='radio_axe1' value='".$cle_station."_".$cle_type_data."' ".$check."
                                                                            data-typedata-sql='".htmlspecialchars($typedata_sql, ENT_QUOTES, 'UTF-8')."' >\n
                                                                    </td>\n
                                                                
                                                                </tr>\n
                                                                ";

                                                            $row++;
                                                        }
                                                    }
                                                }
                                            }
                                        
                                        
                                        // CheckBoxes
                                        /*
                                            $row = 0;
                                            if (fmod($row, 2) == 0) {
                                                $row_l = "class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";
                                            } else {
                                                $row_l = "class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";
                                            }
                                            echo "
                                                <tr ".$row_l.">\n

                                                    <td>
                                                    Aucun
                                                    </td>\n
                                                    <td>-</td>\n
                                                    <td style='text-align:center;' >-</td>\n

                                                    <td style='text-align:center;'>\n
                                                        <input type='checkbox' id='axe1_none' class='axe1-item axe1-none' name='checkbox_axe1[]' value='0_0' data-typedata-sql='' >\n
                                                    </td>\n
                                                
                                                </tr>\n
                                            ";
                                            
                                            if (isset($station_chron_array) && sizeof($station_chron_array) > 0) 
                                            {
                                                $row = 1;
                                                foreach ($station_chron_array as $cle_station => $typedata_array) {
                                                    foreach ($typedata_array as $cle_type_data => $typedata_sql) {
                                                        if (!empty($cle_type_data) && !in_array($cle_type_data, array('ra', 'etl', 'jge'))) {

                                                            // Si tu veux présélectionner certains checkbox, définis ta condition ici:
                                                            // ex: $checked = ($row === 1) ? 'checked' : '';
                                                            $checked = '';
                                                            $checked = ($row === 1) ? 'checked' : '';

                                                            if (fmod($row, 2) == 0) {
                                                                $row_l = "class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";
                                                            } else {
                                                                $row_l = "class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";
                                                            }

                                                            // ID unique utile pour labels si besoin
                                                            $input_id = 'axe1_' . htmlspecialchars($cle_station . '_' . $cle_type_data, ENT_QUOTES, 'UTF-8');

                                                            echo "
                                                                <tr ".$row_l.">\n

                                                                    <td title='".$station_all_array[$cle_station]['nom_station']."'>".
                                                                        affichelettres($station_all_array[$cle_station]['nom_station'], 18)."
                                                                    </td>\n
                                                                    <td>".$station_all_array[$cle_station]['code_station']."</td>\n
                                                                    <td style='text-align:center;' >".$type_chron_array[$cle_type_data]['init_type_data']."</td>\n

                                                                    <td style='text-align:center;'>\n
                                                                        <input type='checkbox' id='".$input_id."' class='axe1-item axe1-other' name='checkbox_axe1[]'
                                                                            value='".$cle_station."_".$cle_type_data."' ".$checked."
                                                                            data-typedata-sql='".htmlspecialchars($typedata_sql, ENT_QUOTES, 'UTF-8')."' >\n
                                                                    </td>\n
                                                                
                                                                </tr>\n
                                                            ";

                                                            $row++;
                                                        }
                                                    }
                                                }
                                            }
                                        */

                                    echo "</table>";

                                echo "</div>\n";
                                
                            echo "</div>\n";
                        
                        echo "<hr>\n";
                        echo "</div>\n";

                        echo "<div id='boxpopup' class='select-top' style='width:96%;margin:0px;margin-top:10px;padding:10px 1%;'>\n";
                        
                            echo "<p style='margin-left:1%;'>";
                                echo "<span style='font-weight: bold;font-size:13px;'>".htmlaccent('Axe 2 - Données')."</span>";
                            echo "</p>";

                            // Affichage du sélecteur de couleur
                            $index_color = 2;
                            include(DIR_WS_GRAPH . 'select_color.php'); 

                            echo "<p style='margin-left:1%;'>";
                                echo "<input type='checkbox' id='check_lac_axe2' checked>";
                                echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>".htmlaccent('Afficher les lacunes')."</span>";
                            echo "</p>";
                            
                            echo "<div id='cadre_data_axe2' style='width:100%;margin:0;padding:0;'>\n";

                                echo "<div class='table-container' style='height: auto;'>";
                            
                                    echo "<table id='table_tri' cellspacing='0'>\n";
                                    
                                        echo "
                                            <thead>
                                                <tr class='header-row'>\n
                                                    <th style='width:50%;font-size:11px;'>".htmlaccent('Nom St.')."</th>\n
                                                    <th style='width:30%;font-size:11px;'>".htmlaccent('Code St.')."</th>\n
                                                    <th style='width:10%;font-size:11px;'>".htmlaccent('Chron.')."</th>\n
                                                    <th style='width:10%;'></th>\n			 
                                                </tr>\n
                                            </thead>\n
                                        ";

                                        $row = 0;
                                        if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                                        else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
                                        echo " 
                                            <tr ".$row_l.">\n

                                                <td>
                                                Aucun
                                                </td>\n
                                                <td>-</td>\n
                                                <td style='text-align:center;' >-</td>\n

                                                <td style='text-align:center;'>\n
                                                    <input type='radio' name='radio_axe2' value='0_0'
                                                        data-typedata-sql='' checked >\n
                                                </td>\n
                                            
                                            </tr>\n
                                            ";

                                        if(isset($station_chron_array) && sizeof($station_chron_array)>0)
                                        {
                                            $row = 1;
                                            foreach($station_chron_array as $cle_station => $typedata_array) 
                                            {	
                                                foreach($typedata_array as $cle_type_data => $typedata_sql) 
                                                {
                                                    if (!empty($cle_type_data) && !in_array($cle_type_data, array('ra', 'etl', 'jge')))
                                                    {
                                                        $check = ($row == 2) ? 'checked' : ''; // Condition pour définir checked

                                                        if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                                                        else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
                                                        
                                                        echo " 
                                                            <tr ".$row_l.">\n

                                                                <td title='".$station_all_array[$cle_station]['nom_station']."'>".
                                                                    affichelettres($station_all_array[$cle_station]['nom_station'],18)."
                                                                </td>\n
                                                                <td>".$station_all_array[$cle_station]['code_station']."</td>\n
                                                                <td style='text-align:center;' >".$type_chron_array[$cle_type_data]['init_type_data']."</td>\n

                                                                <td style='text-align:center;'>\n
                                                                    <input type='radio' name='radio_axe2' value='".$cle_station."_".$cle_type_data."' ".$check."
                                                                            data-typedata-sql='".htmlspecialchars($typedata_sql, ENT_QUOTES, 'UTF-8')."' >\n
                                                                </td>\n
                                                            
                                                            </tr>\n
                                                            ";

                                                        $row++;
                                                    }
                                                }
                                            }

                                            
                                        }
                                    
                                    echo "</table>";

                                echo "</div>\n";

                            echo "</div>\n";
                        
                        echo "<hr>\n";
                        echo "</div>\n";

                        echo "<div id='boxpopup' class='select-top' style='width:96%;margin:10px 0;padding:10px 1%;'>\n";
                            
                            echo "<button id='ajustCoord' class='zoom_graph' style='float:none;width:180px;margin-left:15px;' onClick='load_graph(true);'>";
                                echo 'Actualiser le graphique';
                            echo "</button>\n";

                        echo "</div>\n";

                    echo "</div>\n";

                    // -------------------------------------------------------------

                    // Block Graphique
                    echo "<div id='cadre_graph' style='float:none;width:auto;margin:0;height:78vh;overflow-y: auto;'>\n";
                        
                        echo "<div id='boxpopup' class='select' style='width:96%;margin:0;padding: 5px 10px;border:1px solid #000;'>\n";

                            // barre de gestion des zooms
                            echo "<div style='height:25px;margin-right:15px;'>";

                                echo "<div style='float:right;'>";
                                    echo "<input type='checkbox' id='check_zoom_x' checked onclick='zoomCTRL();'>";
                                    echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>".htmlaccent('Zoom / Move X')."</span>";
                                echo "</div>";

                                echo "<div style='float:right;margin-right:15px;'>";
                                    echo "<input type='checkbox' id='check_zoom_y' checked onclick='zoomCTRL();'>";
                                    echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>".htmlaccent('Zoom / Move Y')."</span>";
                                echo "</div>";

                            echo "</div>";


                            // Cadre Graph
                            
                            echo "<div id='plot_0' class='graph' style='height:46vh;display:none;'></div>\n";
                            
                            echo "<div id='wait_graph' style='width:100%;height:46vh;text-align:center;'>";
                                echo "<img src='".DIR_WS_IMG."wait.gif' style='width:50px;margin-top:10%;'>";
                                echo "<p>".htmlaccent('Chargement en cours ...')."</p>";
                            echo "</div>\n"; 

                            echo "<div style='width:100%;'>";

                                echo "<div style='float:left;width:33%;'>";

                                    echo "<div style='float:left;margin-left:30px;'>";
                                        echo "<button id='plus_axe1' class='decimal_axe'
                                                title='".htmlaccent('Ajouter une décimale')."'
                                                onCLick=\"updateDecimals('plot_0','yaxis','+');\">+</button>\n";
                                        echo "<button id='moins__axe1' class='decimal_axe'
                                                title='".htmlaccent('Enlever une décimale')."'
                                                onCLick=\"updateDecimals('plot_0','yaxis','-');\">-</button>\n";
                                    echo "</div>\n"; 
                                    echo "<hr>";
                                    echo "<button id='log-button1' class='log_axe' style='width: 110px;'>".htmlaccent('Ech. Log - Axe 1')."</button>\n";
                                    echo "<hr>";
                                    echo "<button id='reverse-button1' class='inverse_axe' >".htmlaccent('Inverser - Axe 1')."</button>\n";
                                
                                echo "</div>\n"; 

                                echo "<div style='float:left;width:33%;text-align:center;'>";
                                    
                                    /*
                                    echo "<button id='buttonFullSreen' class='b_fullsreen' style='margin-top:45px;' onclick='zoom_graph();'>";
                                        echo htmlaccent('Plein Écran');
                                    echo "</button>\n";
                                    */
                                    echo "<button id='buttonFullSreen' class='b_fullsreen' style='margin-top:20px;' onclick='zoom_graph();'>";
                                        echo 'Plein Écran';
                                    echo "</button>\n";
                                
                                echo "</div>\n"; 

                                echo "<div style='float:left;width:33%;'>";

                                    echo "<div style='float:right;margin-right:30px;'>";
                                        echo "<button id='plus_axe2' class='decimal_axe' style='margin-left:20px;' 
                                                title='".htmlaccent('Ajouter une décimale')."'
                                                onCLick=\"updateDecimals('plot_0','yaxis2','+');\">+</button>\n";
                                        echo "<button id='moins__axe1' class='decimal_axe'
                                                title='".htmlaccent('Enlever une décimale')."'
                                                onCLick=\"updateDecimals('plot_0','yaxis2','-');\">-</button>\n";
                                    echo "</div>\n"; 
                                    echo "<hr>";
                                    echo "<button id='log-button2' class='log_axe' style='float:right;width: 110px;'>".htmlaccent('Ech. Log - Axe 2')."</button>\n";
                                    echo "<hr>";
                                    echo "<button id='reverse-button2' class='inverse_axe' style='float:right;margin-right:0;'>".htmlaccent('Inverser - Axe 2')."</button>\n";

                                echo "</div>\n"; 

                            echo "</div>\n";      
                            

                        echo "<hr>\n";
                        echo "</div>\n";	

                        

                        // ---------------------------------
                        // Suivi des coordonnées
                        echo "<div id='boxpopup' class='select' style='width:96%;margin-top:10px;padding-top:10px;'>\n";

                            // Options de gestion du graphique (Contrôle dynamique du zoom et des échelles)

                            // Période
                            echo "<div style='float:left;width:260px;'>\n";

                                echo "<div style='float:left;width:50%;'>\n";
                                                        
                                    echo "<p>";
                                        echo "<span style='font-weight: bold;font-size:13px;'>";
                                            echo "Début période";
                                        echo "</span>";
                                    echo "</p>";

                                    // Date Début - X
                                    echo "<div id='boite_small'>\n";
                                            
                                        echo "<p style='width:100px;color:#428bca;'>";
                                            echo "dd-mm-aaaa";
                                        echo "</p>";	
                                        echo "<input class='input_texte' style='width:65px;padding-bottom: 4px;' name='date_min' id='date_min' type='text' value='".$date_1."' >\n";

                                    echo "</div>\n";

                                echo "</div>\n";

                                echo "<div style='float:left;width:50%;'>\n";
                                                        
                                    echo "<p>";
                                        echo "<span style='font-weight: bold;font-size:13px;width:150px;'>";
                                            echo "Fin période";
                                        echo "</span>";
                                    echo "</p>";

                                    // Date Fin - X
                                    echo "<div id='boite_small'>\n";
                                            
                                        echo "<p style='width:100px;color:#428bca;'>";
                                            echo "dd-mm-aaaa";
                                        echo "</p>";	
                                        echo "<input class='input_texte' style='width:65px;padding-bottom: 4px;' name='date_max' id='date_max' type='text' value='".$date_2."' >\n";

                                    echo "</div>\n";

                                echo "</div>\n";

                            echo "</div>\n";

                            // Axe
                            echo "<div style='float:left;width:280px;'>\n";

                                // AXE1
                                echo "<div style='float:left;width:50%;'>\n";

                                    echo "<p>";
                                        echo "<span style='font-weight: bold;font-size:13px;'>".htmlaccent('Axe 1')."</span>";
                                    echo "</p>";

                                    // Axe 1 - Min Y
                                    echo "<div style='float:left;margin-right:10%;'>\n";
                                            
                                        echo "<p style='color:#428bca;'>".htmlaccent('Ymin')."</p>\n";	
                                        echo "<input class='input_texte' style='width:40px;padding-bottom: 4px;' name='y_min_1' id='y_min_1' type='text' value='0' >\n";

                                    echo "</div>\n";

                                    // Axe 1 - Max Y
                                    echo "<div style='float:left;'>\n";
                                            
                                        echo "<p style='color:#428bca;'>".htmlaccent('Ymax')."</p>\n";	
                                        echo "<input class='input_texte' style='width:40px;padding-bottom: 4px;' name='y_max_1' id='y_max_1' type='text' value='0' >\n";

                                    echo "</div>\n";

                                echo "</div>\n";

                                // AXE2
                                echo "<div style='float:left;width:50%;'>\n";

                                    echo "<p>";
                                        echo "<span style='font-weight: bold;font-size:13px;'>".htmlaccent('Axe 2')."</span>";
                                    echo "</p>";

                                    // Axe 2 - Min Y
                                    echo "<div style='float:left;margin-right:10%;'>\n";
                                            
                                        echo "<p style='color:#428bca;'>".htmlaccent('Ymin')."</p>\n";	
                                        echo "<input class='input_texte' style='width:40px;padding-bottom: 4px;' name='y_min_2' id='y_min_2' type='text' value='0' >\n";

                                    echo "</div>\n";

                                    // Axe 1 - Max Y
                                    echo "<div style='float:left;'>\n";
                                            
                                        echo "<p style='color:#428bca;'>".htmlaccent('Ymax')."</p>\n";	
                                        echo "<input class='input_texte' style='width:40px;padding-bottom: 4px;' name='y_max_2' id='y_max_2' type='text' value='0' >\n";

                                    echo "</div>\n";

                                echo "</div>\n";

                            echo "</div>\n";


                            echo "<div style='float:left;text-align:center;'>";
                                    
                                echo "<button id='ajustCoord' class='zoom_graph' style='float:none;width:110px;margin-top:40px;' onClick='updateGraphRange();'>";
                                    echo "Ajuster échelle";
                                echo "</button>";
                                
                            echo "</div>\n"; 

                        
                        echo "</div>\n";	
                    
                    echo "<hr>\n";
                    echo "</div>\n";  



                echo "</div>";

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

    // Paramétrage général - Description des variables
    msgInfo = document.getElementById('contenu_info');

    boxGraphWait = document.getElementById('wait_graph');
    boxPlot = document.getElementById('plot_0');
    
    var idPlotZoom = 0;

    // Vairable permettant de récupérer les shapes d'affichage des lacunes
    var js_shapesLacunesAxe1 = [];
    var js_shapesLacunesAxe2 = [];
    
    checkLacAxe1 = document.getElementById('check_lac_axe1');
    checkLacAxe2 = document.getElementById('check_lac_axe2');

    dateFirst = document.getElementById('date_min');
    dateEnd = document.getElementById('date_max');

    yMin1 = document.getElementById('y_min_1');
    yMax1 = document.getElementById('y_max_1');
    yMin2 = document.getElementById('y_min_2');
    yMax2 = document.getElementById('y_max_2');

    colorAxe1 = document.getElementById('input_color_1');
    colorAxe2 = document.getElementById('input_color_2');

    // Variable pour suivre le nombre de décimales sur le graphique
    var decimalPlacesY1 = 1;
    var decimalPlacesY2 = 1;

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

    function load_graph(reload=false) 
    {
        if(!isValidDatesInput(dateFirst,dateEnd))
        {
           msgInfo.style.border = '4px solid #930000'; 
           return; // Action stoppée
        }

        // Vérifier si les valeurs sont bien des entiers
        if (!isNumber(yMin1) || !isNumber(yMax1) || !isNumber(yMin2) || !isNumber(yMax2))
        {            
            msgInfo.style.border = '4px solid #930000'; 
            return;
        }

        yMin1Value = 0;yMax1Value = 0;yMin2Value = 0;yMax2Value = 0;
        if(reload) // si on ajuste l'échelle à partir des champs des échelles
        {
            yMin1Value = parseFloat(yMin1.value);
            yMax1Value = parseFloat(yMax1.value);
            yMin2Value = parseFloat(yMin2.value);
            yMax2Value = parseFloat(yMax2.value);
        }

        boxPlot.style.display = 'none';
        boxGraphWait.style.display = 'block';

        radioButtonAxe1 = document.querySelector('input[name="radio_axe1"]:checked');
        radioButtonAxe2 = document.querySelector('input[name="radio_axe2"]:checked');
        
        if(radioButtonAxe1 && radioButtonAxe2) 
        {
            valueRadio = radioButtonAxe1.value; 
            valueInfo = valueRadio.split('_').map(Number);
            idStationAxe1 = valueInfo[0];
            idChronAxe1 = valueInfo[1];
            typedataSqlAxe1 = radioButtonAxe1.getAttribute('data-typedata-sql');

            valueRadio = radioButtonAxe2.value; 
            valueInfo = valueRadio.split('_').map(Number);
            idStationAxe2 = valueInfo[0];
            idChronAxe2 = valueInfo[1];
            typedataSqlAxe2 = radioButtonAxe2.getAttribute('data-typedata-sql');

            // Mise au format JSON des données
            // Créer un objet contenant les données à envoyer
            
            var dataToSend = {
                
                dateFirst: dateFirst.value,
                dateEnd: dateEnd.value,

                reload: reload,

                yMin1: yMin1Value,
                yMax1: yMax1Value,
                yMin2: yMin2Value,
                yMax2: yMax2Value,
                
                colorAxe1: colorAxe1.value,
                colorAxe2: colorAxe2.value,
                
                idStationAxe1: idStationAxe1,
                idChronAxe1: idChronAxe1,
                sqlAxe1: typedataSqlAxe1,
                idStationAxe2: idStationAxe2,
                idChronAxe2: idChronAxe2,
                sqlAxe2: typedataSqlAxe2,
            };

            // Convertir l'objet en JSON
            var jsonDataGraph = JSON.stringify(dataToSend);
            
            
            // Effectuer une requête AJAX asynchrone
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "include/structure/graph/process_graph_one.php", true);
            xhr.setRequestHeader("Content-Type", "application/json");

            xhr.onreadystatechange = function() 
            {
                if (xhr.readyState === 4 && xhr.status === 200) 
                {
                    boxPlot.style.display = 'block';
                    boxGraphWait.style.display = 'none';
                    
                    // Analyser la réponse JSON
                    var jsonResponse = JSON.parse(xhr.responseText);      

                    // Accéder aux données récupéré coté serveur
                    eval(jsonResponse['js_graph']); // on récupère le script généré coté serveur pour afficher les graphiques

                    // Permet de récupérer les données liées au lacunes pour l'affichage dans les graphs
                    // -----------------
                        var shapes = boxPlot.layout.shapes;
                        // Groupement des shapes par la propriété personnalisée "customType"
                        // Si shapes est bien défini et c'est un tableau, on filtre
                        if (shapes && Array.isArray(shapes)) 
                        {
                            js_shapesLacunesAxe1 = shapes.filter(shape => shape.customType === 'axe1');
                            js_shapesLacunesAxe2 = shapes.filter(shape => shape.customType === 'axe2');
                        }
                    // -----------------
                    // Activer les boutons reverse echelle et log
                    if(!reload)
                    {
                        addLogScaleButton('plot_0','log-button1','yaxis');
                        addLogScaleButton('plot_0','log-button2','yaxis2');

                        addReverseButton('plot_0','reverse-button1','yaxis');
                        addReverseButton('plot_0','reverse-button2','yaxis2');
                    }

                }
            };
            

            // Envoyer les données JSON au serveur
            xhr.send(jsonDataGraph);
        }
    }

    load_graph();

    // Options du graphique

    // ---------------------------------
    // Affichage des lacunes

        // Écouteur d'événement pour le checkbox checkLacAxe1
        checkLacAxe1.addEventListener('change', updateShapes);

        // Écouteur d'événement pour le checkbox checkLacAxe2
        checkLacAxe2.addEventListener('change', updateShapes);

        function updateShapes() {
            // Initialise un tableau vide pour les formes à afficher
            var shapesToDisplay = [];

            // Ajoute les formes de l'axe 1 si la checkbox est cochée
            if (checkLacAxe1.checked) {
                shapesToDisplay = shapesToDisplay.concat(js_shapesLacunesAxe1);
            }

            // Ajoute les formes de l'axe 2 si la checkbox est cochée
            if (checkLacAxe2.checked) {
                shapesToDisplay = shapesToDisplay.concat(js_shapesLacunesAxe2);
            }

            // Met à jour le tracé avec les formes combinées
            Plotly.relayout('plot_0', { 'shapes': shapesToDisplay });
        }

    // ---------------------------------
    // ECHELLE REVERSE
    
    // Fonction Echelle Inversion axe
    function addReverseButton(plotId, logButtonId, axe) 
    {
        const button = document.getElementById(logButtonId);
        const graphContainer = document.getElementById(plotId);

        var yReversed = false;

       
        button.addEventListener('click', function () 
        {
            const plotlyLayout = graphContainer._fullLayout;
             
            if(axe === 'yaxis' || axe === 'yaxis2') 
            {
                const axis = plotlyLayout[axe];

                // Vérifiez si l'axe existe dans le layout
                if(axis) 
                {
                    const current_range = axis.range;

                    // Inversez simplement les valeurs de la plage
                    const reversed_range = [current_range[1], current_range[0]];

                    Plotly.relayout(plotId,{[axe + '.range']: reversed_range});

                    if(axe === 'yaxis')
                    {
                        yMin1.value = parseInt(current_range[1]);
                        yMax1.value = parseInt(current_range[0]);
                    }
                    if(axe === 'yaxis2')
                    {
                        yMin2.value = parseInt(current_range[1]);
                        yMax2.value = parseInt(current_range[0]);
                    }

                    yReversed = !yReversed; // Inversez l'état
                }
            }
        });
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

    // ---------------------------------
    // Graph plein écran dans popup

    var zoom_graph_first = true;
    function zoom_graph(code_station,nom_station,type_data)
    {
        document.getElementById('box_graph').style.display='block';

        document.getElementById('titre_graph').innerHTML = 'Graphique combiné';

        // Récupérer le nom du plot
        var plotName = 'plot_0';

        // Récupérer les données et la mise en page du plot
        var plotData = window[plotName].data;
        var plotLayout = window[plotName].layout;

        Plotly.newPlot('cadre_limit', plotData, plotLayout, config);

        if(zoom_graph_first)
        {
            addLogScaleButton('cadre_limit','log-button_gd_1','yaxis'); 
            addLogScaleButton('cadre_limit','log-button_gd_2','yaxis2'); 

            addReverseButton('cadre_limit','reverse-button_gd_1','yaxis'); 
            addReverseButton('cadre_limit','reverse-button_gd_2','yaxis2'); 

            zoom_graph_first = false;
        }
        
    }


    

    // Fonction ajoutant ou enlevant des décimal sur un axe 
    function updateDecimals(plotId, axe, type) 
    {
        var newTickFormat = '';

        if(axe == 'yaxis')
        {
            if (type == '+' && decimalPlacesY1 < 6){decimalPlacesY1++;}
            if (type == '-' && decimalPlacesY1 > 0){decimalPlacesY1--;}
            newTickFormat = '.' + decimalPlacesY1 + 'f';
        }

        if(axe == 'yaxis2')
        {
            if (type == '+' && decimalPlacesY2 < 6){decimalPlacesY2++;}
            if (type == '-' && decimalPlacesY2 > 0){decimalPlacesY2--;}
            newTickFormat = '.' + decimalPlacesY2 + 'f';
        }

        Plotly.relayout(plotId, {[axe + '.tickformat']: newTickFormat});
    }


    function updateGraphRange() 
    {
        if(!isValidDatesInput(dateFirst,dateEnd))
        {
           msgInfo.style.border = '4px solid #930000'; 
           return; // Action stoppée
        }

        // Vérifier si les valeurs sont bien des entiers
        
        if (!isNumber(yMin1) || !isNumber(yMax1) || !isNumber(yMin2) || !isNumber(yMax2))
        {            
            msgInfo.style.border = '4px solid #930000'; 
            return;
        }

        // Récupérer les valeurs des champs d'entrée
        var dateFirstInput = dateFirst.value;
        var dateEndInput = dateEnd.value;

        // Convertir les dates du format 'dd-mm-yyyy' au format 'yyyy-mm-dd'
        var dateFirstParts = dateFirstInput.split('-');
        var dateEndParts = dateEndInput.split('-');

        var dateFirstFormatted = dateFirstParts[2]+'-'+dateFirstParts[1]+'-'+dateFirstParts[0];
        var dateEndFormatted = dateEndParts[2]+'-'+dateEndParts[1]+'-'+dateEndParts[0];

        // Récupérer l'état actuel des axes
        var layout = document.getElementById('plot_0')._fullLayout;


        // Vérification de l'inversion des axes
        var isXAxisValid = false;
        if (layout.xaxis) {
            isXAxisReversed = layout.xaxis.range[0] > layout.xaxis.range[1];
            isXAxisValid = true;
        }

        var isYAxisValid = false;
        if (layout.yaxis) {
            isYAxisReversed = layout.yaxis.range[0] > layout.yaxis.range[1];
            isYAxisValid = true;
        }

        var isYAxis2Valid = false;
        if (layout.yaxis2) {
            isYAxis2Reversed = layout.yaxis2.range[0] > layout.yaxis2.range[1];
            isYAxis2Valid = true;
        }

        // Mise à jour des plages des axes
        if (isYAxisValid && isYAxis2Valid) {
            Plotly.relayout('plot_0', {
                'xaxis.range': isXAxisReversed ? [dateEndFormatted, dateFirstFormatted] : [dateFirstFormatted, dateEndFormatted],
                'yaxis.range': isYAxisReversed ? [yMax1.value, yMin1.value] : [yMin1.value, yMax1.value],
                'yaxis2.range': isYAxis2Reversed ? [yMax2.value, yMin2.value] : [yMin2.value, yMax2.value]
            });
        } else if (!isYAxisValid && isYAxis2Valid) {
            Plotly.relayout('plot_0', {
                'xaxis.range': isXAxisReversed ? [dateEndFormatted, dateFirstFormatted] : [dateFirstFormatted, dateEndFormatted],
                'yaxis2.range': isYAxis2Reversed ? [yMax2.value, yMin2.value] : [yMin2.value, yMax2.value]
            });
        } else if (isYAxisValid && !isYAxis2Valid) {
            Plotly.relayout('plot_0', {
                'xaxis.range': isXAxisReversed ? [dateEndFormatted, dateFirstFormatted] : [dateFirstFormatted, dateEndFormatted],
                'yaxis.range': isYAxisReversed ? [yMax1.value, yMin1.value] : [yMin1.value, yMax1.value]
            });
        }

        
        
    }

    // Fonction Graph Full Screen
    // Pour mettre le graphique en plein écran, pas sûr que l'on utilise cette fonction
    //document.getElementById('buttonFullSreen').addEventListener('click', toggleFullScreen);
    function toggleFullScreen() 
    {
        var graphDiv = document.getElementById('plot_0');
        if (!document.fullscreenElement) 
        {
            graphDiv.classList.add("fullscreen");
            graphDiv.requestFullscreen();
        } 
        else 
        {
            document.exitFullscreen();
            graphDiv.classList.remove("fullscreen");
        }
    }


    // Fonction de gestion du zoom et du pan par l'utilisateur
    function zoomCTRL()
    {
        var checkZoomX = document.getElementById('check_zoom_x');
        var checkZoomY = document.getElementById('check_zoom_y');

        var fixedRangeX = true; // Par défaut, désactive le zoom horizontal
        var fixedRangeY = true; // Par défaut, désactive le zoom vertical
        
        if(checkZoomX.checked && checkZoomY.checked)
        {
            fixedRangeX = false; // Active le zoom horizontal
            fixedRangeY = false; // Active le zoom vertical            
        }
        else if(checkZoomX.checked)
        {
            fixedRangeX = false; // Active le zoom horizontal
            fixedRangeY = true; // Désactive le zoom vertical            
        }
        else if(checkZoomY.checked)
        {
            fixedRangeX = true; // Désactive le zoom horizontal
            fixedRangeY = false; // Active le zoom vertical
        }
        

        Plotly.relayout('plot_0', 
        {
            'xaxis.fixedrange': fixedRangeX,
            'yaxis.fixedrange': fixedRangeY,
            'yaxis2.fixedrange': fixedRangeY,
        });
    }

    // ---------------------------------
    // Fonction Vérification des Dates Heures
    
    function isValidDatesInput(date1Input,date2Input)
    {   
        // Vérifier si les dates sont valides
        if (isValidDate(date1Input.value) && isValidDate(date2Input.value))
        {
            
                // Convertir dates et heures en objets Date complets
                const date1Format = parseDate(date1Input.value); // Obtenez un objet Date à partir de la date
                const date2Format = parseDate(date2Input.value); // Obtenez un objet Date à partir de la date

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

    // Fonction pour vérifier si une valeur est un entier
    function isInteger(inputElement) 
    {
        // Vérifie si la valeur de l'élément d'entrée est un entier
        if (!Number.isInteger(Number(inputElement.value))) 
        {
            // Affiche un message d'erreur
            msgInfo.innerText = "Erreur : Les champs des Axes (1 et 2) doivent être des nombres entiers.\n";
            msgInfo.style.display = 'block';
            return false;
        }
        return true;
    }

    // Fonction pour vérifier si une valeur est un nombre (entier ou flottant)
    function isNumber(inputElement) 
    {
        // Vérifie si la valeur de l'élément d'entrée est un nombre
        const value = Number(inputElement.value);
        if (isNaN(value)) {
            // Affiche un message d'erreur
            msgInfo.innerText = "Erreur : Les champs des Axes (1 et 2) doivent être des nombres.\n";
            msgInfo.style.display = 'block';
            return false;
        }
        return true;
    }


</script>