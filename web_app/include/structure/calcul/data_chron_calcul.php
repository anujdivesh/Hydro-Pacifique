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

$min_y = 0;
$max_y = 0;
$min_x = '';
$max_x = '';

$id_correction = 0;
$id_station_encours = 0;
$id_typedata_encours = 0;
$id_chron_encours = 0;

$id_eq_type = $typedata_encours; // Var. renseigné dans data_chron.php
$id_typedata_encours = $typedata_encours; 

$lacune_date_first = '';
$edit_lacune_temp = '';
$nb_lacunes = 0;

$js_syncAbsc_var = '';
$js_syncOrdon_var = '';

// --------------------------------------
// Récupération de l'identifiant de correction s'il existe
if(isset($_POST['id_correction'])){$id_correction = $_POST['id_correction'];}

// --------------------------------------
// Gestion des couleurs d'affichage pour chaque courbe
$colorMapping = [
    'data_init' => '#2471a3', // Bleu
    'calcul' => '#000', // Noir
    'decalage_date' => '#2ca02c', // Vert
    'lissage' => '#ff7f0e',   // Orange
    'lacune' => '#EA1179',     // Rose
    'calcul_pastemps' => '#9467bd', // Violet
    'calcul_chron_new' => '#d62728',  // Rouge
];

// On récupère ici les informations générales de la station qui est en jeux 
/*
if(isset($station_chron_array) && sizeof($station_chron_array)>0)
{
    foreach($station_chron_array as $cle_station => $typedata_array) 
    {	
        $id_station_encours = $cle_station;
        $id_typedata_encours = $station_all_array[$cle_station]['type_station'];                            
        foreach($typedata_array as $typedata_chron => $sql_chron) 
        {                            

            echo "<span style='margin: 0 10px;'>&#x25CF</span>";
            echo htmlaccent('Station : ').$station_all_array[$cle_station]['code_station']." - ".$station_all_array[$cle_station]['nom_station'];
            echo "<span style='margin: 0 10px;'>&#x25CF</span>";
            echo htmlaccent('Chronique : ').$type_chron_array[$typedata_chron]['init_type_data']." - ".$type_chron_array[$typedata_chron]['nom_type_data'];
            
        }                  
    }
    
}
    */

// --------------------------------------
// Debut page HTML

require(DIR_WS_STRUCTURE . 'header_web.php');

echo "<body>";

    echo "<div id='contenu_info' style='display:none;'></div>";
    
    require(DIR_WS_BOX . 'block_info_chron.php'); // Block pour affichage des informations sur les Chroniques
    require(DIR_WS_CALCUL . 'block_calcul_options.php'); // Block pour affichage des options de calcul
      
    require(DIR_WS_STRUCTURE . 'block_wait.php'); // Block d'attente pendant les interrogations au serveur
    require(DIR_WS_BOX . 'block_verif_savedata_calc.php'); // Block pour permettre une confirmation de l'enregistrement des données
    require(DIR_WS_STRUCTURE . 'block_graph.php'); // Affichage du graphique en plein écran
    require(DIR_WS_STRUCTURE . 'block_lacunes_info.php'); // Affichage de la liste des lacunes dans un PopUp

    require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
    include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

    echo "<div id='contour_general'>";
        
        echo "<div id='contenu_centre'>";

            echo "<div id='contenu_box2'>";	
            
                echo "<h1 id='h1_graph'>";                            
                    echo "<span>".htmlaccent('Correction')."</span>";

                    if(isset($station_chron_array) && sizeof($station_chron_array)>0)
                    {
                        foreach($station_chron_array as $cle_station => $typedata_array) 
                        {	
                            $id_station_encours = $cle_station;
                            $id_typedata_encours = $station_all_array[$cle_station]['type_station'];                            
                            foreach($typedata_array as $typedata_chron => $sql_chron) 
                            {                            
                                echo "<span style='margin: 0 10px;'>&#x25CF</span>";
                                echo htmlaccent('Station : ').$station_all_array[$cle_station]['code_station']." - ".$station_all_array[$cle_station]['nom_station'];
                                echo "<span style='margin: 0 10px;'>&#x25CF</span>";
                                echo htmlaccent('Chronique : ').$type_chron_array[$typedata_chron]['init_type_data']." - ".$type_chron_array[$typedata_chron]['nom_type_data'];
                                
                            }                  
                        }
                        
                    }

                echo "</h1>";

                //echo "<div style='float:left;width:100%;'>\n";

                    // Colonne de gauche permettant d'effectuer les corrections
                    echo "<div id='cadre_graph' style='float:left;width:260px;margin-right:0.5%;height:78vh;overflow-y: auto;'>\n";

                        echo "<div style='float:left;width:90%;margin-top:8px;margin-bottom:15px;'>";

                            echo "<img src='".DIR_WS_IMG_ICO."info.png' style='float:left;width:20px;margin-left:5px;margin-right:10px;'>";    
                            echo "<p style='float:left;margin-top:3px;'>";
                                echo "<a onClick='afficheBlockInfoChron();'>";
                                    echo "<span style='font-size:13px;font-weight:bold;'>".htmlaccent('Détails sur les chroniques')."</span>";
                                echo "</a>\n";
                            echo "</p>\n";

                        echo "</div>\n";    

                        // -- PERIODE A CORRIGER
                        echo "<div id='boxpopup' class='select-top' style='width:88%;margin:0px;padding:10px;'>\n";

                            echo "<p>";
                                echo "<span style='font-weight: bold;font-size:13px;width:150px;'>".htmlaccent('Période à corriger')."</span>";
                            echo "</p>";
                            
                            echo "<div style='float:left;width:100%;margin-bottom:10px;'>\n";
                                
                                // Date début zoom - Abscisses
                                echo "<div id='boite_small' class='select_date' style='margin-right:1%;'>\n";
                                        
                                    echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Date de début')."</p>\n";	
                                    echo "<input class='input_texte' style='width:65px;padding-bottom: 4px;' name='x1Zoom' id='x1Zoom' type='text' value='' >\n";
                                                //onclick=\"javascript:displayCalendar(document.getElementById('x1Zoom'),'dd-mm-yyyy',this);\" >\n";

                                echo "</div>\n";

                                // Heure début zoom - Abscisses
                                echo "<div id='boite_small' class='select_date' style='margin-right:0;'>\n";
                                        
                                    echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Heure')."</p>\n";	
                                    echo "<input class='input_texte' style='width:50px;padding-bottom: 4px;' name='x1Zoom_h' id='x1Zoom_h' type='text' value='' >\n";

                                echo "</div>\n";

                                // Y Min - Ordonnées
                                echo "<div id='boite_small' class='select_date' style='margin-right:0;'>\n";
                                        
                                    echo "<p style='float:left;width:35px;color:#006A67;'>".htmlaccent('Y min')."</p>\n";	
                                    echo "<br>";
                                    echo "<input type='text' class='input_texte_xsmall' id='y1Zoom' style='' value=''/>\n";
                                            
                                echo "</div>\n";

                            echo "</div>\n"; 


                            echo "<div style='float:left;width:100%;margin-bottom:0px;'>\n";

                                // Date fin zoom - Abscisses
                                echo "<div id='boite_small' class='select_date' style='margin-right:1%;'>\n";
                                        
                                    echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Date de fin')."</p>\n";	
                                    echo "<input class='input_texte' style='width:65px;padding-bottom: 4px;' name='x2Zoom' id='x2Zoom' type='text' value='' >\n";
                                                // onclick=\"javascript:displayCalendar(document.getElementById('x2Zoom'),'dd-mm-yyyy',this);\" >\n";
                                            
                                echo "</div>\n";

                                // Heure fin zoom - Abscisses
                                echo "<div id='boite_small' class='select_date' style='margin-right:0;'>\n";
                                        
                                    echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Heure')."</p>\n";	
                                    echo "<input class='input_texte' style='width:50px;padding-bottom: 4px;' name='x2Zoom_h' id='x2Zoom_h' type='text' value='' >\n";

                                echo "</div>\n";

                                // Y Max - Ordonnées
                                echo "<div id='boite_small' class='select_date' style='margin-right:0;'>\n";
                                        
                                    echo "<p style='float:left;width:35px;color:#006A67;'>".htmlaccent('Y max')."</p>\n";	
                                    echo "<br>";	
                                    echo "<input type='text' class='input_texte_xsmall' id='y2Zoom' style='' value=''/>\n";
                                            
                                echo "</div>\n";
                            
                            echo "</div>\n";   


                            echo "<div style='float:left;'>\n";
                                echo "<button id='ajustCoord' class='zoom_graph' style='float:none;width:110px;margin-top:10px;margin-bottom:0px;' onClick='updateGraphRange();'>".htmlaccent('Ajuster l\'échelle')."</button>\n";
                            echo "</div>\n"; 
                            /*
                            echo "<div style='float:right;'>\n";
                            
                                // Bouton pour sélectionner les données Abscisses
                                echo "<button id='syncAbsc' class='zoom_graph'  style='width:70px;margin-top:50px;' title='".htmlaccent('Sélection des données en Abscisses')."'>";
                                    echo htmlaccent('Absc. (X)');
                                echo "</button>\n";
                            
                            echo "</div>\n";  
                            */

                            
                            /*
                            echo "<div style='float:right;'>\n";

                                // Bouton pour synchroniser le zoom axe des ordonnées
                                echo "<button id='syncOrdon' class='zoom_graph' style='width:70px;margin-top:0px;' title='".htmlaccent('Sélection des données en Ordonnées')."'>".htmlaccent('Ord. (Y)')."</button>\n";

                            echo "</div>\n"; 
                            */

                        
                        echo "</div>\n";

                        // ---------------------------------------------------------------                        
                        
                        // -- ACCES POPUP MODIFICATION ET CORRECTION
                        echo "<div id='boxpopup' class='select-top' style='width:88%;margin-top:10px;padding:10px;'>\n";

                            echo "<div style='float:left;width:70%;'>\n";

                                echo "<p style='float:left;width:100%;padding-top:5px;font-size:13px;'>";
                                    echo "Options de correction";
                                echo "</p>\n";
                                
                            echo "</div>\n";  
                            
                            echo "<div style='float:right;'>\n";
                                    
                                echo "<button id='popup_modif' class='inverse_axe' style='width:40px;height:30px;text-align:center;color:".$colorMapping['calcul'].";' 
                                    title='"."Ouvrir les options de correction"."'
                                    onClick='affiche_options_calcul();'>";
                                    echo "<img src='".DIR_WS_IMG_ICO."calcul.png' style='float:left;width:20px;margin-left:5px;margin-right:10px;' >";  
                                echo "</button>\n"; 

                            echo "</div>\n";

                        echo "</div>\n";

                        // ---------------------------------------------------------------                        
                        
                        // -- MODIFICATION ET CORECTION
                        echo "<div id='boxpopup' class='select-top' style='width:88%;margin-top:10px;padding:10px;'>\n";


                            // DUPLIQUER LA CHRON : 1*X+0
                            echo "<div style='float:left;width:100%;padding-bottom:10px;border-bottom:1px solid #ddd;'>\n";
                            
                                echo "<div style='float:left;width:70%;'>\n";

                                    echo "<p style='float:left;width:100%;padding-top:5px;font-size:13px;'>";
                                        echo "Dupliquer la chronique";
                                    echo "</p>\n";
                                    
                                echo "</div>\n";  
                                
                                echo "<div style='float:right;'>\n";
                                        
                                    echo "<button id='calcul_copy' class='inverse_axe' style='width:40px;padding:0;color:".$colorMapping['calcul'].";' title=".htmlaccent('Calcul').">";
                                        echo "<span style='font-size:22px;margin:0;'>&#x25CF></span>"; 
                                    echo "</button>\n"; 

                                echo "</div>\n";

                            echo "</div>\n";


                            // --- GENERETION PAR PAS DE TEMPS
                            echo "<div style='float:left;width:100%;margin-top:10px;padding-bottom:10px;border-bottom:1px solid #ddd;' >\n";

                                echo "<div style='float:left;width:75%;' >\n";
                                
                                    echo "<p style='float:left;width:100%;padding-top:5px;font-size:13px;'>";
                                        echo "Pas de temps";
                                    echo "</p>\n";

                                        // Interval
                                        echo "<div>\n";

                                            echo "<p style='float:left;margin-right:5px;padding-top:5px;font-size:11px;color:#428bca;'>";
                                                echo "Interval (minutes)";
                                            echo "</p>\n";

                                            echo "<input type='text' class='input_texte_xsmall' id='input_pastemps' style='float:right;' value='5'/>\n";

                                                /*       
                                                echo "<select name='select_pastemps' id='select_pastemps' style='float:left;width:100px;'>\n";

                                                    // TABLE DELAI                                        
                                                    $sql_option_pastemps = "SELECT DISTINCT id, interval_min, info FROM ".TABLE_OPTION_PASTEMPS." 
                                                                                ORDER BY interval_min ASC";
                                                    $option_pastemps_query = tep_db_query($sql_link,$sql_option_pastemps);	
                                                    while ($option_pastemps = tep_db_fetch_array($option_pastemps_query))
                                                    {
                                                        $info = htmlaccent(html_entity_decode($option_pastemps['info'] ?? $default_string));
                                                        echo "<option value='".$option_pastemps['interval_min']."' >".$info."</option>\n";
                                                    }

                                                echo "</select>\n";
                                                */

                                        // Mode de Calcul
                                        echo "</div>\n";

                                        echo "<div>\n";
                                        
                                            echo "<p style='float:left;margin-top:5px;margin-right:5px;padding-top:8px;font-size:11px;color:#428bca;'>";
                                                echo "Mode de calcul";
                                            echo "</p>\n";

                                            echo "<select name='select_pastemps_mode' id='select_pastemps_mode' style='float:right;width:85px;margin-top:5px;'>\n";
                                                
                                                echo "<option value='moy' >"."Moyenne"."</option>\n";
                                                echo "<option value='cumul' >"."Cumul"."</option>\n";
                                            
                                            echo "</select>\n";

                                        echo "</div>\n";

                                echo "</div>\n";
                                
                                echo "<div style='float:right;' >\n";
                                
                                    // Bouton
                                    echo "<button id='create_chron_min' class='inverse_axe' style='width:40px;padding:0;margin-top:54px;color:".$colorMapping['calcul_pastemps'].";' title=\"".htmlaccent('Générer une nouvelle Chronique')."\">";
                                        echo "<span style='font-size:22px;margin:0;'>&#x25CF></span>"; 
                                    echo "</button>\n"; 

                                echo "</div>\n";
                            
                            echo "</div>\n";

                            // REGROUPEMENT TEMPOREL
                            echo "<div style='float:left;width:100%;margin-top:10px;padding-bottom:10px;border-bottom:1px solid #ddd;display:none;' id='bloc_create_chron'>\n";
                                
                                echo "<div style='float:left;width:70%;'>\n";

                                    echo "<p style='float:left;width:100%;padding-top:5px;font-size:13px;'>";
                                        echo "Regroupement Temporel";
                                    echo "</p>\n";

                                    // Valeur                               
                                    echo "<input type='hidden' id='id_create_chron' name='id_create_chron'>\n";
                                    echo "<input type='text' id='text_create_chron' name='text_create_chron' style='float:left;width:80%;border:0;' readonly >\n";
                                    
                                echo "</div>\n";  
                                
                                echo "<div style='float:right;'>\n";
                                        
                                    echo "<button id='create_chron_dmy' class='inverse_axe' style='width:40px;margin-top:22px;padding:0;color:".$colorMapping['calcul_chron_new'].";' title=".htmlaccent('Nouvelle Chronique').">";
                                        echo "<span style='font-size:22px;margin:0;'>&#x25CF></span>"; 
                                    echo "</button>\n"; 

                                echo "</div>\n";

                            echo "</div>\n";


                            /*

                                echo "<div class='calc-toggle-header' data-menu-id='correct' >";
                                    echo "<span style='float:left;width:80%;font-weight: bold;color:#640D6B;'>";
                                        echo htmlaccent('Corrections à appliquer');
                                    echo "</span>";
                                    echo "<span class='arrow'>&#9660;</span>"; // Flèche vers le bas
                                echo "</div>\n";

                                //echo "<hr>";
                                    
                                //echo "<div style='float:left;width:100%;margin:5px 0;border-bottom:2px solid #176B87;'></div>";

                                echo "<div class='calc-navigation' style='float:left;width:100%;'>";

                                    // --- Correction par fonction linéaire

                                    echo "<div style='float:left;padding-bottom:10px;border-bottom:1px solid #ddd;'>\n";

                                        echo "<div style='float:left;width:82%'>\n";

                                            echo "<div id='boite_small' style='margin:0;'>\n";

                                                echo "<p style='float:left;width:100%;font-size:13px;'>".htmlaccent('Fonction (Ynew = aY + b)')."</p>\n";
                                                
                                                // Paramètre a
                                                echo "<p style='float:left;color:#428bca;padding-top:5px;'>".htmlaccent('a = ')."</p>\n";
                                                echo "<input type='text' class='input_texte_xsmall' id='valeur_a' style='float:left;margin-left:5px;margin-right:20px;' value='1'/>\n";

                                                // Paramètre b
                                                echo "<p style='float:left;color:#428bca;padding-top:5px;'>".htmlaccent('b = ')."</p>\n";
                                                echo "<input type='text' class='input_texte_xsmall' id='valeur_b' style='float:left;margin-left:5px;' value='0'/>\n";
                                            
                                            echo "</div>\n";

                                        echo "</div>\n";
                                        
                                        echo "<div style='float:left;'>\n";
                                        
                                            // Bouton pour sélectionner les données Abscisses

                                            echo "<button id='calcul_valeur' class='inverse_axe' style='width:40px;padding:0;margin-top:20px;color:".$colorMapping['calcul'].";' title=".htmlaccent('Générer la correction').">";
                                                echo "<span style='font-size:22px;margin:0;'>&#x25CF></span>"; 
                                            echo "</button>\n"; 

                                        echo "</div>\n";
                                    
                                    echo "</div>\n";

                                    // --- Décalage temporelle
                                    // Il faudra revoir avec un algorithme plus complexe

                                    echo "<div style='float:left;margin-top:10px;padding-bottom:10px;border-bottom:1px solid #ddd;'>\n";

                                        echo "<div style='float:left;width:82%;'>\n";

                                            echo "<div id='boite_small' style='margin:0;'>\n";

                                                echo "<p style='float:left;width:100%;font-size:13px;'>".htmlaccent('Décalage temporel (Abscisse X)')."</p>\n";
                                                
                                                // Opérateur
                                                echo "<select name='operateur_x' id='operateur_x' style='float:left;width:45px;font-weight: bold;font-size:16px;'>\n";
                                                                    
                                                    echo "<option value='+' >+</option>\n";
                                                    echo "<option value='-' >-</option>\n"; 

                                                echo "</select>\n";
                                            
                                                // Valeur                               
                                                echo "<input type='text' class='input_texte_xsmall' id='valeur_operation_x' style='float:left;' value='0'/>\n";
                                                echo "<p style='float:left;width:50px;color:#428bca;margin-left:5px;padding-top:7px;'>".htmlaccent('secondes')."</p>\n";
                                                
                                            echo "</div>\n";

                                        echo "</div>\n";
                                        
                                        
                                        echo "<div style='float:left;width:10%;'>\n";
                                        
                                            // Bouton
                                            echo "<button id='calcul_date' class='inverse_axe' style='width:40px;padding:0;margin-top:19px;color:".$colorMapping['decalage_date'].";' title=".htmlaccent('Générer la correction').">";
                                                echo "<span style='font-size:22px;margin:0;'>&#x25CF></span>"; 
                                            echo "</button>\n"; 

                                        echo "</div>\n";
                                    
                                    echo "</div>\n";
                                                            
                                    
                                    // --- Mise en lacunes

                                    echo "<div style='float:left;margin-top:10px;padding-bottom:10px;border-bottom:1px solid #ddd;'>\n";

                                        echo "<div style='float:left;width:82%;'>\n";

                                            echo "<p style='float:left;width:100%;font-size:13px;'>".htmlaccent('Mise en lacune')."</p>\n";

                                            echo "<input type='text' id='periode_lacune' name='periode_lacune' style='float:left;width:195px;padding:10px 0;border:0;' readonly >\n";
                                            
                                        echo "</div>\n";   

                                        echo "<div style='float:left;'>\n";
                                        
                                            // Bouton
                                            echo "<button id='calcul_lacune' class='inverse_axe' style='width:40px;padding:0;margin-top:19px;' title=".htmlaccent('Générer la lacune').">";
                                                echo "<span style='font-size:22px;margin:0;'>&#x25CF></span>"; 
                                            echo "</button>\n";
                                        
                                        echo "</div>\n"; 
                                    
                                    echo "</div>\n";

                                    // --- Lissage
                                    // Il faudra ajouter des algorithmes de lissage plus complexe
                                    // ne fonctionne que sur les chroniques en lignes
                                    $display = '';
                                    if($type_chron_array[$typedata_chron]['type_graph'] != 'lines'){$display = 'display:none;';}


                                    echo "<div style='float:left;margin-top:10px;".$display."'>\n";

                                        echo "<div style='float:left;width:82%;'>\n";

                                            echo "<div id='boite_small' style='margin:0;'>\n";

                                                echo "<p style='float:left;width:100%;font-size:13px;'>".htmlaccent('Lissage de la chronique')."</p>\n";
                                                
                                                // Opérateur
                                                echo "<select name='lissage' id='lissage' style='float:left;width:70%;'>\n";
                                                                    
                                                    echo "<option value='1' >".htmlaccent('Variation faible')."</option>\n";

                                                echo "</select>\n";

                                                echo "<hr>";

                                                // Valeur                               
                                                echo "<p style='float:left;width:50px;color:#428bca;margin-left:5px;padding-top:7px;'>".htmlaccent('Seuil (%) : ')."</p>\n";
                                                echo "<input type='text' id='seuil_liss' style='float:left;width:25px;' value='0'/>\n";
                                                

                                            echo "</div>\n";

                                        echo "</div>\n";                            

                                        echo "<div style='float:left;'>\n";
                                        
                                            // Bouton
                                            echo "<button id='calcul_lissage' class='inverse_axe' style='width:40px;padding:0;margin-top:19px;color:".$colorMapping['lissage'].";' title=".htmlaccent('Générer la lacune').">";
                                                echo "<span style='font-size:22px;margin:0;'>&#x25CF></span>"; 
                                            echo "</button>\n";
                                        
                                        echo "</div>\n";
                                    
                                    echo "</div>\n";
                            
                                echo "</div>\n";

                            */

                        echo "</div>\n";

                        // -- GENERATION DE NOUVELLES CHRONIQUES
                        /*
                        echo "<div id='boxpopup' class='select-top' style='width:88%;margin-top:10px;padding:0 10px;'>\n";
                                                    
                            echo "<div class='calc-toggle-header' >";
                                echo "<span style='float:left;width:80%;font-weight: bold;color:#640D6B;' >";
                                    echo htmlaccent('Nouvelles Chroniques');
                                echo "</span>";
                            echo "</div>\n";
                            

                            echo "<div class='calc-navigation' style='float:left;width:100%;'>";

                                
                                // Génération d'une chronique sur pas de temps définis plus large utilisé pour les statistiquess (Jour, Mois, Années )
                                echo "<div style='float:left;margin-top:10px;' >\n";

                                    echo "<div id='bloc_create_chron' style='display:none;'>\n";

                                        echo "<div style='float:left;width:82%;' >\n";

                                            echo "<div id='boite_small' style='margin:0;'>\n";

                                                echo "<p style='float:left;width:100%;font-size:12px;'>".htmlaccent('Création (Jour, Mois, Année)')."</p>\n";

                                                // Valeur                               
                                                echo "<input type='hidden' id='id_create_chron' name='id_create_chron'>\n";
                                                echo "<input type='text' id='text_create_chron' name='text_create_chron' style='float:left;width:195px;border:0;' readonly >\n";
                                            
                                            echo "</div>\n";
                                    
                                        echo "</div>\n";
                                                            
                                        echo "<div style='float:left;width:10%;margin:0;' >\n";
                                        
                                            // Bouton (dmy - day, month, year)
                                            echo "<button id='create_chron_dmy' class='inverse_axe' style='width:40px;padding:0;margin-top:19px;color:".$colorMapping['calcul_chron_new'].";' title=\"".htmlaccent('Générer une nouvelle Chronique')."\">";
                                                echo "<span style='font-size:22px;margin:0;'>&#x25CF></span>"; 
                                            echo "</button>\n"; 

                                        echo "</div>\n";

                                    echo "</div>\n";
                                
                                echo "</div>\n";

                            echo "</div>\n";

                        echo "</div>\n";
                        */
                        
                    echo "<hr>\n";
                    echo "</div>\n";


                    // Bloc graphique
                    echo "<div id='cadre_graph' style='float:none;width:auto;height:85vh;overflow-y: auto;'>\n";

                        $load_graph_function = '';
                        
                        if(isset($station_chron_array) && sizeof($station_chron_array)>0)
                        {
                            foreach($station_chron_array as $cle_station => $typedata_array) 
                            {	
                                $id_station_encours = $cle_station;
                                foreach($typedata_array as $typedata_chron => $sql_chron) 
                                {                            
                                    $id_chron_encours = $typedata_chron;
                                    $id_typedata_encours = $station_all_array[$cle_station]['type_station'];
                                    $nom_type_data = $eq_type_array[$station_all_array[$cle_station]['type_station']]['nom_eq_type'];    
                                    $to_periode_encours = $type_chron_array[$typedata_chron]['to_periode'];
                                    $id_create_chron_encours = $type_chron_array[$typedata_chron]['id_chon_periode'];

                                    $text_chron_encours = $type_chron_array[$typedata_chron]['init_type_data']." - ".$type_chron_array[$typedata_chron]['nom_type_data'];

                                    echo "<input type='hidden' id='text_chron_".$id_chron_encours."' value='".$text_chron_encours."'>";

                                    echo "<div id='boxpopup' class='select' style='width:99%;margin:0;padding:0;border-radius: 2px;border:1px solid #000;'>\n";

                                        echo "<div id='button_visu' style='' onclick=\"zoom_graph('".$cle_station."','".$station_all_array[$cle_station]['code_station']."','".$station_all_array[$cle_station]['nom_station']."','".$nom_type_data."');\">\n";	
                                            echo  'Agrandir'; 
                                        echo "</div>\n";

                                        echo "<div id='button_lacune_".$cle_station."' class='button_lacune' style='margin-right:5px;padding:4px 5px;display:none;' title='".htmlaccent('Tableau des lacunes')."'>";
                                            echo  htmlaccent('Lacunes'); 
                                        echo "</div>";

                                        if($id_typedata_encours == 11) // Si station Hydro
                                        {
                                            echo "<div id='button_hq_".$cle_station."' class='button_HQ' 
                                                    onclick=\"window.open('convert_hq.php?st=" . $id_station_encours . "', '_blank');\" 
                                                    style='float:right;width:50px;margin-right:5px;padding:4px 5px;' title='".htmlaccent('Conversion des Hauteurs (Côtes) en Débits')."'>";
                                                echo  htmlaccent('H -> Q'); 
                                            echo "</div>";
                                        }

                                        echo "<p class='titre' 
                                                    style='margin-bottom:5px;padding:4px 10px;font-size:13px;
                                                            border-bottom:2px solid #000;
                                                            background-color:#FAEAB1;color:#000;'>";
                                            
                                            echo "<span style='color:#930000;'>"."En cours de correction"."</span>";

                                            echo "<br>";

                                            echo "Station : ".$station_all_array[$cle_station]['code_station']." - ".$station_all_array[$cle_station]['nom_station'];
                                            echo "<span style='margin: 0 10px;'>&#x25CF</span>";
                                            echo "Chronique : ".$text_chron_encours;
                                        
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
                                        
                                        echo "<div id='plot_".$cle_station."' class='graph' style='height:50vh;margin:0 10px;'></div>\n";
                                        
                                        echo "<div id='wait_".$cle_station."' style='width:100%;height:50vh;text-align:center;'>";
                                            echo "<img src='".DIR_WS_IMG."wait.gif' style='width:50px;margin-top:10%;' title='".htmlaccent('Chargement en cours ...')."'>";
                                            echo "<p>".htmlaccent('Chargement en cours ...')."</p>";
                                        echo "</div>\n";    

                                        // Il faut envoyer $station_chron_array et $cle
                                        $js_syncAbsc_var .= "Plotly.relayout('plot_".$cle_station."', {'xaxis.range': [x1_format, x2_format]});";
                                        $js_syncOrdon_var .= "Plotly.relayout('plot_".$cle_station."', {'yaxis.range': [y1, y2]});";
                                        
                                        echo "<div id='box_options_".$cle_station."' style='float:left;margin-left:25px;'>";

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
                                        
                                    echo "<hr>\n";
                                    echo "</div>\n";	
                                }
                            }	
                        }      
                        else
                        {
                            echo "<div id='boxpopup' >\n";
                                echo "<p class='alert'>".htmlaccent('Aucune données n\'a été trouvée')."</p>";
                            echo "<hr>";
                            echo "</div>";
                        }

                        
                        // CADRE pour affichage des corrections en cours dans un tableau

                        echo "<div id='block_valid_correction' style='float:left;width:100%;margin-top:10px;'>\n";
                        
                            echo "<div style='float:left;width:45%;border: 1px solid #000;border-radius: 8px;' >\n";

                                echo "<p style='margin:10px;'>";
                                    echo "<span style='font-weight: bold;font-size:14px;'>";
                                        echo "Liste des corrections en cours";
                                    echo "</span>";
                                echo "</p>";

                                // Tableau permettant l'affichage au fur et à mesure des différentes modifications générées
                                echo "<table id='table_info_correction' cellspacing='0' style='float:left;margin: 0 10px;margin-bottom:10px;font-size:12px;'>\n";
                                                                    
                                    echo "<thead>\n";	                                
                                        
                                        //echo "<th style='width:80px;'>".htmlaccent('Axe')."</th>\n";
                                        echo "<th style='width:150px;'>".htmlaccent('Type')."</th>\n";	
                                        echo "<th style='width:130px;'>".htmlaccent('Début')."</th>\n";	
                                        echo "<th style='width:130px;'>".htmlaccent('Fin')."</th>\n";	                              		 
                                        echo "<th style='width:100px;text-align:center;'>&nbsp;</th>\n";	// Cellule pour le checkbox - Sélection de la correction 
                                        echo "<th style='width:50px;text-align:center;'>&nbsp;</th>\n";	// Cellule pour permettre le téléchargement de la chronique générée
                                        echo "<th style='width:30px;text-align:center;'>&nbsp;</th>\n";	// Cellule pour la suppression de la correction	ou l'affichage de la chronique modifiée        
                                    echo "</thead>\n";
                                    echo "<tbody>";
                                    echo "</tbody>";

                                echo "</table>\n";

                            echo "</div>\n";


                            echo "<div style='float:left;width:45%;margin-left:3%;' >\n";

                                echo "<div id='boite_small' style='width:95%;'>\n";

                                    // Boutons
                                    echo "<div style='float:left;'>\n";

                                        echo "<img src='".DIR_WS_IMG."wait.gif' style='width:40px;display:none;' id='wait_valid_save' title='".htmlaccent('Données en cours de traitement')."'>";
                                        echo "<button id='button_save' class='valid' style='width:100px;'
                                                onCLick='saveCorrection(false);'
                                                title='"."Enregistrer dans la même chronique"."'>";
                                            echo "Enregistrer"; 
                                        echo "</button>\n"; 

                                    echo "</div>\n";


                                    echo "<div style='float:left;margin-left:3%;'>\n";

                                        echo "<img src='".DIR_WS_IMG."wait.gif' style='width:40px;display:none;' id='wait_valid_saveas' title='".htmlaccent('Données en cours de traitement')."'>";
                                        echo "<button id='button_saveas' class='validunder' style='width:160px;'
                                                onCLick='saveCorrection(true);'
                                                title='"."Enregistrer dans une autre chronique"."'>";
                                            echo "Enregistrer sous ..."; 
                                        echo "</button>\n"; 

                                    echo "</div>\n";

                                    
                                echo "</div>\n";
                            
                            echo "</div>\n";
                            
                        echo "<hr>\n";
                        echo "</div>\n";

                    echo "<hr>\n";
                    echo "</div>\n";
                    
                //echo "</div>";

            echo "</div>";
        
        echo "</div>";
        
    echo "</div>";
        
    require('include/application_bottom.php'); 
	
echo "</body>";

echo "</html>";

?>


<script>

    // ---------------------------
    // Scripts qui permettent de gérer les corrections
    // - Création de la correction : function correctionData()
    // - Affichage de la correction : function afficheCorrection()
    // - Supression de la correction : function delCorrection()
    // - Validation de la correction et enregistrement dans la base : function validCorrection() 
    // Il y a 4 écouteurs de bouttons qui permettent de lancer les corrections . Chaque boutton correespond à un type de correction
    // Les scripts suivants permettent de mettre à jour le graphique avec les différentes fonctions pour l'utilisation des graphiques : Zoom, récupération des coordonnées du grpahiques

    var territoire_id = "<?php echo $territoire_id;?>";
    var timezone_php = "<?php echo $timezone_php;?>";
    var territoire_lang = "<?php echo $territoire_lang;?>";

    // JS SCRIPT CORRECT DATA
    var boxWait = document.getElementById('box_wait'); // Attente lors des chaque opération, occupe l'ensemble de la page

    var id_correction = <?php echo $id_correction; ?>;
    var id_user = <?php echo $id_user; ?>;
    var id_station_encours = "<?php echo $id_station_encours;?>";
    var id_type_station_encours = "<?php echo $id_typedata_encours;?>";
    var id_chron_encours = "<?php echo $id_chron_encours;?>";
    var to_periode_encours = <?php echo $to_periode_encours;?>;
    var id_create_chron_encours = <?php echo $id_create_chron_encours;?>;
    var tab_type_data_array = <?php echo json_encode($typedata_array);?>;
    var colorTab = <?php echo json_encode($colorMapping); ?>;
    var text_create_chron_encours = "<?php 
                                        if($id_create_chron_encours > 0)
                                        {
                                            echo $type_chron_array[$id_create_chron_encours]['init_type_data'].' - '.$type_chron_array[$id_create_chron_encours]['nom_type_data'];
                                        }
                                        else{echo "";}
                                    ?>";


    var tbody_info = document.querySelector("#table_info_correction tbody"); // Pour récupérer le contenu du tableau d'affichage des corrections en cours
    var contenuMsg = document.getElementById('contenu_info');
    
    var blockValidCorrection = document.getElementById('block_valid_correction'); // cadre de validation des corrections
    var buttonSave = document.getElementById('button_save'); // bouton de validation des corrections
    var buttonSaveAs = document.getElementById('button_saveas'); // bouton de validation des corrections
    var waitValidSave = document.getElementById('wait_valid_save'); // icone wait valide

    // Variable pour suivre le nombre de décimales
    var decimalPlaces = 1;

    if(id_create_chron_encours>0) // si la chronique peut être transformée en une autre type CI -> CIE 
    {
        document.getElementById('bloc_create_chron').style.display = 'block';
        document.getElementById('id_create_chron').value = id_create_chron_encours;
        document.getElementById('text_create_chron').value = text_create_chron_encours;
    }

    // Fonction pour générer la correction - par un appel vers le serveur
    function correctionData(id_station,id_chron,type_correction,calcul_correction,axe_correction,pastemps=0,modecalcul='none') 
    {
        //boxWait.style.display = 'block';

        document.getElementById('wait_'+id_station).style.display = 'block';
        document.getElementById('plot_'+id_station).style.display = 'none';        
        //document.getElementById('box_options_'+id_station).style.display = 'none';

        // On récupère les données de date de la partie de la chronique à corriger
        
        datetime_first = document.getElementById('x1Zoom').value+' '+document.getElementById('x1Zoom_h').value;
        datetime_end = document.getElementById('x2Zoom').value+' '+document.getElementById('x2Zoom_h').value;

        
        // Mise au format JSON des données
        // Créer un objet contenant les données à envoyer    

        var dataToSend = {
            id_user: id_user,
            id_correction: id_correction,
            id_station: id_station,
            id_chron: id_chron,
            datetime_first:datetime_first,
            datetime_end:datetime_end,
            type_correction:type_correction,
            calcul_correction:calcul_correction,
            axe_correction:axe_correction,
            pastemps:pastemps,
            modecalcul:modecalcul,
            to_periode_encours:to_periode_encours,
            id_create_chron_encours:id_create_chron_encours
        };
        
        // Convertir l'objet en JSON
        var jsonDataCalcul = JSON.stringify(dataToSend);

        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/calcul/process_chron_calcul.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                //document.getElementById('wait_'+id_station).style.display = 'none';
                //document.getElementById('plot_'+id_station).style.display = 'block';        
                
                //document.getElementById('box_options_'+id_station).style.display = 'block';

                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);                

                contenuMsg.innerHTML  = jsonResponse['msg_newCorrection'];
                contenuMsg.style.border = '4px solid #09886d'; // bordure en vert
            
                id_correction = jsonResponse['id_correction'];
                afficheCorrection(id_correction);   

                contenuMsg.style.display = 'block';
            }
        };

        // Envoyer les données JSON au serveur
        xhr.send(jsonDataCalcul);
    }

    

    // Fonction de lancement de la procédure AJAX permettant de supprimer une correction
    function delCorrection(id_meta)
    {
        // Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
                            id_meta: id_meta
                        };
        
        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/calcul/process_chron_calcul_del.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

                contenuMsg.innerHTML  = jsonResponse['msg_del'];
                contenuMsg.style.border = '4px solid #09886d'; // bordure en vert

                id_correction = jsonResponse['id_correction'];
                afficheCorrection(id_correction); 
                
                contenuMsg.style.display = 'block';
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
    }

    // Fonction pour afficher la correction - par un appel vers le serveur
    // d'abord dans le tableau et après on affiche le graphique en appelant la fonction load_graph
    function afficheCorrection(id_correction)
    {
        // Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
            id_correction: id_correction
        };
        
        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/calcul/process_chron_calcul_view.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

                tab_html = jsonResponse['tab_html'];
                tbody_info.innerHTML = tab_html; // Ajoute la ligne dans le tableau des fichiers importables

                id_meta =  jsonResponse['id_meta'];
                if(id_meta > 0) 
                {
                    blockValidCorrection.style.display = 'block';
                    buttonSave.style.display = 'block';
                    buttonSaveAs.style.display = 'block';
                }
                else
                {
                    blockValidCorrection.style.display = 'none';
                    buttonSave.style.display = 'none';
                    buttonSaveAs.style.display = 'none';
                }

                load_graph(id_station_encours,id_type_station_encours,tab_type_data_array);
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
    }

    // Fonction de lancement de la procédure AJAX permettant de valider une correction
    function validCorrection(tabIdMeta)
    {   
        // Récupération du choix du type de chronique et du code qualité pour la mise à jour des données
        idTypeChron = document.getElementById('id_modif_chron').value; 
        idCodeQual = document.getElementById('select_qual_chron').value; 
        obsUser = document.getElementById('obs_user').value; 

        buttonSave.style.display = 'none';
        buttonSaveAs.style.display = 'none';
        waitValidSave.style.display = 'block';

        // Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
                            territoire_id: territoire_id,
                            timezone_php: timezone_php,
                            territoire_lang: territoire_lang,
                            id_correction: id_correction,
                            tabIdMeta: tabIdMeta,
                            idTypeChron: idTypeChron,
                            idCodeQual: idCodeQual,
                            obsUser: obsUser
                        };
        
        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/calcul/process_chron_calcul_valid.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

                
                waitValidSave.style.display = 'none';
                buttonSave.style.display = 'block';
                buttonSaveAs.style.display = 'block';
                
                contenuMsg.innerHTML  = jsonResponse['msg_valid'];
                contenuMsg.style.border = '4px solid #09886d'; // bordure en vert
                
                afficheCorrection(id_correction); 

                contenuMsg.style.display = 'block';                
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
    }

    // ---------------------------------------
    // Function pour le téléchargement d'une chronique
    function download_chron(id_meta_correct)
    {
        // Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
                            id_meta_correct: id_meta_correct
                        };
        
        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/calcul/process_chron_calcul_download.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4)
            {
                if (xhr.status === 200) 
                {
                    // Analyser la réponse JSON
                    var jsonResponse = JSON.parse(xhr.responseText);  

                    if(jsonResponse['statut'])
                    {
                        // Créer un lien invisible pour déclencher le téléchargement
                        var downloadLink = document.createElement('a');
                        downloadLink.href = 'data/export/temp/'+jsonResponse['csvFile']; // URL du fichier CSV
                        downloadLink.download = jsonResponse['csvFile']; // Nom du fichier
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                    }
                    else 
                    {
                        contenuMsg.innerHTML  = 'Erreur lors de la génération du fichier.';
                        contenuMsg.style.border = '4px solid #930000'; // bordure en rouge
                        contenuMsg.style.display = 'block';
                    }

                } 
                else 
                {
                    contenuMsg.innerHTML  = 'Erreur lors de la requête au serveur.';
                    contenuMsg.style.border = '4px solid #930000'; // bordure en rouge
                    contenuMsg.style.display = 'block';
                }
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
    }



    
    // ----------------------------------------------------------------------------------
    // Bouton correction valide
    // C'est le processus pour enregistrer une correction dans une chronique

    function saveCorrection(saveas)
    {
        contenuMsg.style.display = 'none';

        let checkboxes;
        checkboxes = document.querySelectorAll('input[name="checkCorrection[]"]'); // On récupère la liste des checkboxes   

        tabCheckData = []; // Vider complètement le tableau

        if(checkboxes.length > 0) // Vérifier si checkboxes n'est pas vide
        {    
            checkboxes.forEach(checkbox => { // Parcourir les cases à cocher pour trouver celles qui sont cochées

                if(checkbox.checked) // Si la case est cochée
                { 
                    tab_check = checkbox.value.split('_'); // checkbox.value = 'meta_n' et on récupére n  
                    tabCheckData.push(tab_check[1]); // Ajouter la valeur au tableau  
                }
            });        
        }

        if(tabCheckData.length > 0) // Si au moins une correction est cochée
        {            
            // Afficher le popup de confirmation
            let popup_verif_savedata = document.getElementById('box_verif_savedata');
            let cadre_modif_chron = document.getElementById('cadre_modif_chron');
            popup_verif_savedata.style.display = 'block';
            cadre_modif_chron.style.display = 'block';

            let selectChron = document.getElementById('select_type_chron');
            selectChron.style.display = 'none';
            if(saveas){selectChron.style.display = 'block';}

            // Pour récupèrer le texte de la chronique sélectionnée  
            let input_text_chron = document.getElementById('text_chron_'+id_chron_encours).value;
            // Remplir les champs du popup permettant de vérifier si l'utilisateur souhaite bien enregistrer les corrections et où il souhaite les enregistrer
            textSelectChron.value = input_text_chron;
            idSelectChron.value = id_chron_encours;


            // On vérifie d'abord si les listeners existent déjà avant de les ajouter
            let okButton = document.getElementById('ok_valid_savedata');
            let noButton = document.getElementById('no_valid_savedata');

            // Vérifier si l'événement "Oui" n'a pas déjà été ajouté
            if (!okButton.dataset.listenerAdded) 
            {
                // Gérer le bouton "Oui"
                okButton.addEventListener('click', function () 
                {
                    popup_verif_savedata.style.display = 'none'; // Fermer le popup
                    
                    validCorrection(tabCheckData); // Lancer la validation des corrections
                    
                    // Marquer que l'événement a été ajouté pour éviter qu'il ne soit ajouté plusieurs fois
                    okButton.dataset.listenerAdded = true;
                });
            }

            if (!noButton.dataset.listenerAdded) 
            {
                // Gérer le bouton "Non"
                noButton.addEventListener('click', function () 
                {
                    popup_verif_savedata.style.display = 'none'; // Fermer le popup sans rien faire
                });   
                
                // Marquer que l'événement a été ajouté pour éviter qu'il ne soit ajouté plusieurs fois
                okButton.dataset.listenerAdded = true;
            }    
        }
        else
        {
            contenuMsg.innerHTML  = 'Vous devez sélectionner au moins une correction en cours.';
            contenuMsg.style.border = '4px solid #930000'; // bordure en rouge
            contenuMsg.style.display = 'block';
        }
    }




    // ---------------------------------------
    // Ecoute des bouttons pour actionner les corrections 
    // ---------------------------------------

        // On écoute le bouton 'calcul_operateur' . S'il est activé on lance une projection de la correction souhaitée
        var boutonCalculValeur = document.getElementById('calcul_valeur');
        boutonCalculValeur.addEventListener('click', function()
        {
            valeur_a = document.getElementById('valeur_a').value;
            valeur_b = document.getElementById('valeur_b').value;
            // il faudra vérifier si ces 2 valeurs sont des nombres
            
            axe_correction = "Ord. (Y)";
            type_correction = "calcul";
            calcul_correction = valeur_a+'Y + '+valeur_b;  
            correctionData(id_station_encours,id_chron_encours,type_correction,calcul_correction,axe_correction);
        });

        // On écoute le bouton 'calcul_copy' . S'il est activé on lance une projection de la duplication de la chronique sur la période sélectionnée
        var boutonCalculCopy = document.getElementById('calcul_copy');
        boutonCalculCopy.addEventListener('click', function()
        {
            valeur_a = 1;
            valeur_b = 0;
            
            axe_correction = "Ord. (Y)";
            type_correction = "calcul";
            calcul_correction = valeur_a+'Y + '+valeur_b; 
            correctionData(id_station_encours,id_chron_encours,type_correction,calcul_correction,axe_correction);
        });
        

        
        // Lier à la correction temporel - L'algorithme pourra être amélioré avec une projection plus complexe
        var boutonCalculDate = document.getElementById('calcul_date');
        boutonCalculDate.addEventListener('click', function()
        {
            operateur_x = document.getElementById('operateur_x').value;
            valeur_operation_x = document.getElementById('valeur_operation_x').value;
            // il faudra vérifier si ces 2 valeurs sont des nombres
            
            axe_correction = "Ord. (X)";
            type_correction = "decalage_date";
            calcul_correction = operateur_x + valeur_operation_x;
            
            correctionData(id_station_encours,id_chron_encours,type_correction,calcul_correction,axe_correction);
        });

        var boutonLissage = document.getElementById('calcul_lissage');
        boutonLissage.addEventListener('click', function()
        {
            seuilLiss = document.getElementById('seuil_liss').value;
            // il faudra vérifier si cette valeurs sont des nombres
            
            axe_correction = "Ord. (Y)";
            type_correction = "lissage";
            calcul_correction = seuilLiss;
            
            correctionData(id_station_encours,id_chron_encours,type_correction,calcul_correction,axe_correction);
        });
        
        // permet de mettre à jour le champ de définition de la lacune
        document.getElementById('x1Zoom').addEventListener('change', function() 
        {
            x1_value = document.getElementById('x1Zoom').value;
            x1h_value = document.getElementById('x1Zoom_h').value;
            x2_value = document.getElementById('x2Zoom').value;
            x2h_value = document.getElementById('x2Zoom_h').value;
        
            if(isValidDate(x1_value) && isValidDate(x2_value) && isValidTime(x1h_value) && isValidTime(x2h_value))
            {
                text_periode_lacune = 'du '+x1_value+' à '+x1h_value;
                document.getElementById('periode_lacune_first').value = text_periode_lacune;
                text_periode_lacune = 'au '+x2_value+' à '+x2h_value;
                document.getElementById('periode_lacune_end').value = text_periode_lacune;
            };
        });

        document.getElementById('x2Zoom').addEventListener('change', function() 
        {
            x1_value = document.getElementById('x1Zoom').value;
            x1h_value = document.getElementById('x1Zoom_h').value;
            x2_value = document.getElementById('x2Zoom').value;
            x2h_value = document.getElementById('x2Zoom_h').value;
        
            if(isValidDate(x1_value) && isValidDate(x2_value) && isValidTime(x1h_value) && isValidTime(x2h_value))
            {
                text_periode_lacune = 'du '+x1_value+' à '+x1h_value;
                document.getElementById('periode_lacune_first').value = text_periode_lacune;
                text_periode_lacune = 'au '+x2_value+' à '+x2h_value;
                document.getElementById('periode_lacune_end').value = text_periode_lacune;
            }

        });

        var boutonLacune = document.getElementById('calcul_lacune');
        boutonLacune.addEventListener('click', function()
        {
            axe_correction = "-";
            type_correction = "lacune";
            calcul_correction = "";
            
            correctionData(id_station_encours,id_chron_encours,type_correction,calcul_correction,axe_correction);
        });

        // On écoute le bouton 'create_chron_min' . Pour générer une chronique avec un nouveau pas de temps (en minutes < 24h)
        var boutonChronMin = document.getElementById('create_chron_min');
        boutonChronMin.addEventListener('click', function()
        {
            selectPastempsMode = document.getElementById('select_pastemps_mode').value;input_pastemps
            inputPastemps = document.getElementById('input_pastemps').value; // en minutes
            
            // var selectedOptionIndex = select_pastemps.selectedIndex; // Récupérer l'index de l'option sélectionnée
            // var selectedText_pastemps = select_pastemps.options[selectedOptionIndex].text; // Récupérer le texte affiché de l'option sélectionnée
            
            axe_correction = "Abs. (X)";
            type_correction = "calcul_pastemps";
            // calcul_correction = 'Création Chronique '+selectedText_pastemps;
            calcul_correction = 'Génération Chronique <br> pas de temps ('+selectPastempsMode+') : '+inputPastemps;  
            correctionData(id_station_encours,id_chron_encours,type_correction,calcul_correction,axe_correction, inputPastemps,selectPastempsMode);
        });

        // On écoute le bouton 'create_chron_dmy' . Pour générer une chronique avec un nouveau pas de temps (par jour, par mois ou par année)
        var boutonChronMin = document.getElementById('create_chron_dmy');
        boutonChronMin.addEventListener('click', function()
        {
            axe_correction = "Abs. (X)";
            type_correction = "calcul_chron_new";
            calcul_correction = 'Création Chronique : '+text_create_chron_encours;  
            correctionData(id_station_encours,id_chron_encours,type_correction,calcul_correction,axe_correction);
        });



    // ---------------------------------------
    // ---------------------------------------
    // AFFICHAGE DES DONNEES ET GESTION DU GRAPHIQUE
    // JS SCRIPT GRAPH

    // Génération des graphiques
    var idPlotZoom = 0;
    var js_syncAbsc_var = "<?php echo $js_syncAbsc_var;?>";
    var js_syncOrdon_var = "<?php echo $js_syncOrdon_var;?>";

    var min_x = '<?php echo $date_2;?>';
    var max_x = '<?php echo $date_1;?>';

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
                'toImage',
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

        document.getElementById('plot_'+cle_station).style.display = 'none'; 
        document.getElementById('wait_'+cle_station).style.display = 'block';
            
        //document.getElementById('box_options_'+cle_station).style.display = 'none';
        
        // Mise au format JSON des données
        // Créer un objet contenant les données à envoyer
        var dataToSend = {
            territoireId: <?php echo $territoire_id;?>,
            lang: '<?php echo $lang;?>',
            cle_station: cle_station,
            type_station: type_station, // Hydro, Pluvio, Piezo, ...
            typedata_array: typedata_array,
            colorTab: colorTab,
            min_x:min_x,
            max_x:max_x,
            id_correction:id_correction
        };

        // Convertir l'objet en JSON
        var jsonDataGraph = JSON.stringify(dataToSend);

        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/calcul/process_chron_calcul_graph.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                document.getElementById('wait_'+cle_station).style.display = 'none';
                document.getElementById('plot_'+cle_station).style.display = 'block';        
                //document.getElementById('box_options_'+cle_station).style.display = 'block';
                document.getElementById('button_lacune_'+cle_station).style.display = 'block';

                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);
            
                // Accéder aux données individuelles
                eval(jsonResponse['js_text']); // on récupère le script généré coté serveur pour afficher les graphiques
                
                // affichage de la liste des lacunes
                text_lacunes = jsonResponse['text_lacunes'];
                
                ecoute_lacune(cle_station,text_lacunes);

                // Synchronisation des valeur des ordonnées dans le input date et time
                
                min_x = jsonResponse['min_x'];
                min_date_time = min_x.split(' ');
                min_date = min_date_time[0];
                min_time = min_date_time[1];

                max_x = jsonResponse['max_x'];
                max_date_time = max_x.split(' ');
                max_date = max_date_time[0];
                max_time = max_date_time[1];

                document.getElementById('x1Zoom').value = min_date;
                document.getElementById('x1Zoom_h').value = min_time;
                document.getElementById('x2Zoom').value = max_date;
                document.getElementById('x2Zoom_h').value = max_time;

                text_periode_lacune = 'du '+min_date+' à '+min_time;
                document.getElementById('periode_lacune_first').value = text_periode_lacune;
                text_periode_lacune = 'au '+max_date+' à '+max_time;
                document.getElementById('periode_lacune_end').value = text_periode_lacune;

                min_y = parseInt(jsonResponse['min_y']);
                max_y = parseInt(jsonResponse['max_y']);
                document.getElementById('y1Zoom').value = min_y;
                document.getElementById('y2Zoom').value = max_y;
                
                /*
                document.getElementById('syncAbsc').removeEventListener('click', syncAbsc); // Pour supprimer l'écouteur d'événements
                document.getElementById('syncAbsc').addEventListener('click', syncAbsc); // Pour ajouter l'écouteur d'événements
                */

                //boxWait.style.display = 'none';
            }
        };

        // Envoyer les données JSON au serveur
        xhr.send(jsonDataGraph);
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

    // ------------------------------------------

    afficheCorrection(id_correction); // On lance le chargement initial du graphique ('data_init')

    // ------------------------------------------
    // FOnction de paramétrage du zoom et du pan sur le graphique 
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

    // Fonction de zoom
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
    function updateDecimals(plotId, axe, type) 
    {
        if (type == '+' && decimalPlaces < 6){decimalPlaces++;}
        if (type == '-' && decimalPlaces > 0){decimalPlaces--;}

        var newTickFormat = '.' + decimalPlaces + 'f';
        Plotly.relayout(plotId, {[axe + '.tickformat']: newTickFormat});
    }


    function getDaysInMonth(monthNumber, year) 
    {
        return new Date(year, monthNumber, 0).getDate();
    }

    // --------------------------------------------------
    // Fonctions de contrôles de date et time

    
    // Fonctions qui synchronise les coordonnées du graph à partir des champs de saisie
    function updateGraphRange()
    {
        x1_value = document.getElementById('x1Zoom').value;
        x1h_value = document.getElementById('x1Zoom_h').value;
        x2_value = document.getElementById('x2Zoom').value;
        x2h_value = document.getElementById('x2Zoom_h').value;
        
        if(!isValidDatesInput(x1_value,x2_value,x1h_value,x2h_value))
        {
            contenuMsg.style.border = '4px solid #930000'; 
            return; // Action stoppée
        }

        // Vérifier si les valeurs des ordonnées sont bien des nombres
        y1 = document.getElementById('y1Zoom').value;
        y2 = document.getElementById('y2Zoom').value;

        if (!isNumber(y1) || !isNumber(y2))
        {            
            contenuMsg.style.border = '4px solid #930000'; 
            return;
        }

        // Convertir les dates au format 'dd-mm-yyyy' en 'yyyy-mm-dd'
        x1_format = new Date(x1_value.split('-').reverse().join('-'));
        x2_format = new Date(x2_value.split('-').reverse().join('-'));
        
        // Mise à jour des échelles des graphiques
        eval(js_syncAbsc_var);
        eval(js_syncOrdon_var);
    }



    function isValidDatesInput(date1Input,date2Input,heure1Input,heure2Input)
    {   
        // Vérifier si les dates sont valides
        if (isValidDate(date1Input) && isValidDate(date2Input))
        {
            // Vérifier si les heures sont valides
            if (isValidTime(heure1Input) && isValidTime(heure2Input)) 
            {
                // Convertir dates et heures en objets Date complets
                const date1Format = parseDate(date1Input); // Obtenez un objet Date à partir de la date
                const [hour1, minute1, second1] = parseTime(heure1Input); // Extraire l'heure
                date1Format.setHours(hour1, minute1, second1); // Ajouter l'heure à la date
                
                const date2Format = parseDate(date2Input); // Obtenez un objet Date à partir de la date
                const [hour2, minute2, second2] = parseTime(heure2Input); // Extraire l'heure
                date2Format.setHours(hour2, minute2, second2); // Ajouter l'heure à la date

                // Comparer les deux dates complètes
                if (date1Format < date2Format) 
                {
                    return true;
                } 
                else 
                {
                    contenuMsg.innerText = "'Date et Heure de début' doivent être antérieures à 'Date et Heure de fin'";
                    contenuMsg.style.display = 'block';

                    return false;
                }
            } 
            else 
            {
                contenuMsg.innerText = "Au moins l'une des heures saisies est invalide ou dans un mauvais format (HH:MM ou HH:MM:SS : formats valides)";
                contenuMsg.style.display = 'block';

                return false;
            }
        } 
        else 
        {
            contenuMsg.innerText = "Au moins l'une des dates saisies est invalide ou dans un mauvais format (dd-mm-yyyy : format valide)";
            contenuMsg.style.display = 'block';

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

    // Fonction pour valider et normaliser une heure
    function isValidTime(timeString) 
    {
        // Vérifier le format avec une regex (HH:MM ou HH:MM:SS)
        const timeRegex = /^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/;
        if (!timeRegex.test(timeString)) 
        {
            return false; // Format invalide
        }

        // Découper les composants de l'heure
        const [hours, minutes, seconds] = timeString.split(":").map(Number);

        // Vérifier les limites des heures, minutes et secondes
        if (
            hours < 0 || hours > 23 ||
            minutes < 0 || minutes > 59 ||
            (seconds !== undefined && (seconds < 0 || seconds > 59))
        ) 
        {
            return false; // Valeur hors des limites
        }

        // Ajouter "00" pour les secondes si elles ne sont pas présentes
        const formattedTime = seconds !== undefined
            ? `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
            : `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:00`;

        return formattedTime;
    }


    // Fonction pour convertir une date (format valide) en objet Date
    function parseDate(dateString) 
    {
        [day, month, year] = dateString.split("-").map(Number);
        return new Date(year, month - 1, day);
    }

    // Fonction pour analyser une heure au format HH:MM:SS ou HH:MM
    function parseTime(timeString) 
    {
        const [hours, minutes, seconds = 0] = timeString.split(":").map(Number);
        return [hours, minutes, seconds];
    }

    // Fonction pour vérifier si une valeur est un nombre (entier ou flottant)
    function isNumber(inputElement) 
    {
        // Vérifie si la valeur de l'élément d'entrée est un nombre
        const value = Number(inputElement);
        if (isNaN(value)) {
            // Affiche un message d'erreur
            contenuMsg.innerText = "Erreur : Les champs Ymin et Ymax doivent être des nombres.\n";
            contenuMsg.style.display = 'block';
            return false;
        }
        return true;
    }



</script>