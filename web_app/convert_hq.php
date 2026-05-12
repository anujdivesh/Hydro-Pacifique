<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Conversion H -> Q
Affichage d'une chronique de hauteur d'eau
Affichage d'une chronique de Débit si elle existe
Fonction de conversion de H -> Q à partir des relations d'étalonnages
----------------------------------------
*/

require('include/application_top.php');

// --------------------------------------
// INIT VAR
$message_info = '';
$valid=false;

$nom_station = '';	
$code_station = '';

$x_date_min = 0;
$x_date_max = 0;
$y_hauteur_min = 0;
$y_hauteur_max = 0;
$y_debit_min = 0;
$y_debit_max = 0;

$id_etl = 0;
$id_etl_first = 0;

//$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" onclick=\"this.className='rowSelect';\"";
$row_l="class='row1' onclick=\"this.className='rowSelect';\"";
$print_row = '';   

$titre_graph_first = "";
$data_graph_all = ""; // Variables données pour les graphiques 


// --------------------------------------

if(isset($_GET['st']))
{
    $id_station = mysqli_real_escape_string($sql_link,trim(addslashes($_GET['st'])));
    
     $sql_station = "SELECT DISTINCT s.id_station, s.nom_station, s.nom_court, s.code_station, 
									s.id_region, s.id_commune
					FROM ".TABLE_STATION." s 
					WHERE s.id_station=".$id_station;
	
	$station_query = tep_db_query($sql_link,$sql_station);
	$station = tep_db_fetch_array($station_query);

    if(isset($station['id_station']))
    {
        $nom_station = htmlaccent($station['nom_station']);	
        $code_station = htmlaccent($station['code_station']);
        $valid=true;
    }
    else{$message_info .= htmlaccent('La station n\'est pas identifiée.');}
}
else{$message_info .= htmlaccent('L\'identifiant de la station n\'est pas fourni. L\'url de la page n\'est pas reconnu.');}

// Requête SQL
if($valid)
{
    // -------------------------------------------------
    // On supprime d'abord toutes les données de conversion qui ont été générée dans les tables CORRECTION
    
    // On récupère les IDs à supprimer
    $sql_meta_del = "SELECT DISTINCT dm.id
                    FROM " . TABLE_DATA_META_CORRECTION . " dm
                    WHERE dm.id_station = " . (int)$id_station . "
                    AND dm.source = 'Conversion'";
    $meta_del_query = tep_db_query($sql_link,$sql_meta_del); 
    
    // On prépare une liste des IDs à supprimer
    $ids_to_delete = [];
    while ($meta_del = tep_db_fetch_array($meta_del_query)) 
    {
        $ids_to_delete[] = (int)$meta_del['id'];
    }
    if (!empty($ids_to_delete)) 
    {
        // Conversion des IDs en une chaîne pour la requête SQL
        $ids_list = implode(',', $ids_to_delete);
    
        tep_db_query($sql_link, "START TRANSACTION");

            // Suppression des données associées dans les deux tables
            tep_db_query($sql_link, "DELETE FROM " . TABLE_DATA_ALL_CORRECTION . " WHERE id_meta IN ($ids_list)");
            tep_db_query($sql_link, "DELETE FROM " . TABLE_DATA_META_CORRECTION . " WHERE id IN ($ids_list)");
        
        tep_db_query($sql_link, "COMMIT");
    }

    // Extraction des types de données de côtes susceptible d'être convertis (ta.id=2 -> hauteur ou côte) 
    // !!! Attention si on change l'identifiant d cet axe ça ne fonctionne plus. Il faut que je fixe ce pb (25-04-2025)
    
    $sql_cote = "SELECT DISTINCT td.id_data_type, td.init_type_data, td.nom_type_data, ta.axe, ta.unite
                    FROM ".TABLE_DATA_META." dm
                    JOIN ".TABLE_TYPE_DATA." td ON td.id_data_type=dm.id_typedata
                    JOIN ".TABLE_DATA_TYPE_AXE." ta ON ta.id=td.axe_data
                    WHERE dm.id_station = ".$id_station."
                    AND ta.id=1
                    ORDER BY td.init_type_data DESC";

    $cote_query = tep_db_query($sql_link,$sql_cote);
    while($cote_tab = tep_db_fetch_array($cote_query))
    {
        $id_data_type = $cote_tab['id_data_type'];
        $init_type_data = htmlaccent(html_entity_decode($cote_tab['init_type_data'] ?? ''));
        $nom_type_data = htmlaccent(html_entity_decode($cote_tab['nom_type_data'] ?? ''));
        $axe = $cote_tab['axe'];
        $unite = $cote_tab['unite'];

        $cote_array[$id_data_type] = array('init_type_data' => $init_type_data,
                                        'nom_type_data' => $nom_type_data,
                                        'axe' => $axe,
                                        'unite' => $unite,
                                        );
    }


    // Extraction des types de données de débit susceptible d'être sélectionnées après conversion (ta.id=5 -> débit)
    // On ne sélectionne que les type de chronique instantannée que l'on différencie des autres par td.to_periode=1 (byDay)
    // Elles peuvent être converties en moyenne par jour 
    $sql_debit = "SELECT DISTINCT td.id_data_type, td.init_type_data, td.nom_type_data, ta.axe, ta.unite
                    FROM ".TABLE_TYPE_DATA." td
                    JOIN ".TABLE_DATA_TYPE_AXE." ta ON ta.id=td.axe_data
                    WHERE ta.id=5
                    AND td.to_periode=1
                    ORDER BY td.init_type_data DESC";

    $debit_query = tep_db_query($sql_link,$sql_debit);
    while($debit_tab = tep_db_fetch_array($debit_query))
    {
        $id_data_type = $debit_tab['id_data_type'];
        $init_type_data = htmlaccent(html_entity_decode($debit_tab['init_type_data'] ?? ''));
        $nom_type_data = htmlaccent(html_entity_decode($debit_tab['nom_type_data'] ?? ''));
        $axe = $debit_tab['axe'];
        $unite = $debit_tab['unite'];

        $debit_array[$id_data_type] = array('init_type_data' => $init_type_data,
                                        'nom_type_data' => $nom_type_data,
                                        'axe' => $axe,
                                        'unite' => $unite,
                                        );
    }

}




// --------------------------------------
// Debut page HTML

require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

echo "<div id='contour_general'>";
 
    echo "<div id='contenu_info' style='display:none;'></div>";
	
	echo "<div id='contenu_centre'>";

        echo "<div id='contenu_box2'>";
            
            echo "<h1 id='h1_graph'>";
                            
                echo "<span style='font-weight:bold;'>".htmlaccent('Conversion : Hauteur -> Débit : ')."</span>";
                echo "<span style='color:#000;'>".htmlaccent('Station hydrométrique : '.$code_station.' - '.$nom_station)."</span>";
                
            echo "</h1>";


            if($valid)
            {
                // Colonne de gauche pour afficher la liste des ETL de la station
                echo "<div id='cadre_graph' style='float:left;width:230px;margin-right:10px;height:75vh;overflow-y: auto;'>\n";

                    echo "<div id='boxpopup' class='select-top' style='width:195px;padding:10px;'>\n";
                        
                        echo "<p style='margin-left:1%;'>";
                            echo "<span style='font-weight: bold;font-size:13px;'>".htmlaccent('Chronique à Convertir')."</span>";
                        echo "</p>";

                        // Affichage du sélecteur de couleur
                        $index_color = 1;
                        include(DIR_WS_GRAPH . 'select_color.php'); 
                        
                        echo "<select name='select_chron_h' id='select_chron_h'  style='width:95%;margin-top:3px;margin-left:1%;'>";
                                
                            $selected = '';									
                            if(isset($cote_array))
                            {
                                foreach($cote_array as $key => $value)
                                {
                                    echo "<option value='".$key."' ".$selected." >".$value['init_type_data']." - ".$value['nom_type_data']."</option>";
                                }
                            }		
                    
                        echo "</select>";

                        // Afficher les lacunes
                        echo "<p style='margin-left:1%;'>";
                            echo "<input type='checkbox' id='check_lac_axe1' >";
                            echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>".htmlaccent('Afficher les lacunes')."</span>";
                        echo "</p>";

                        // ----------------------------------

                        echo "<p style='margin-top:15px;margin-left:1%;'>";
                            echo "<span style='font-weight: bold;font-size:13px;'>".htmlaccent('Chronique à Calculer')."</span>";
                        echo "</p>";

                        // Affichage du sélecteur de couleur
                        $index_color = 2;
                        include(DIR_WS_GRAPH . 'select_color.php'); 
                        
                        echo "<select name='select_chron_q' id='select_chron_q'  style='width:95%;margin-top:3px;margin-left:1%;'>";
                                
                            $selected = '';									
                            if(isset($debit_array))
                            {
                                foreach($debit_array as $key => $value)
                                {
                                    echo "<option value='".$key."' ".$selected." >".$value['init_type_data']." - ".$value['nom_type_data']."</option>";
                                }
                            }		
                    
                        echo "</select>";

                        // Afficher les lacunes
                        echo "<p style='margin-left:1%;'>";
                            echo "<input type='checkbox' id='check_lac_axe2' >";
                            echo "<span style='margin-left:5px;font-size:11px;font-weight:normal;'>".htmlaccent('Afficher les lacunes')."</span>";
                        echo "</p>";


                        if(isset($cote_array))
                        {
                            echo "<div id='button_convert' style='float:left;width:90%;margin-top:15px;margin-left:1%;padding:4px 5px;' title='".htmlaccent('Lancer la conversion')."'>";
                            echo  "<span>".htmlaccent('Convertir : H -> Q')."</span>"; 
                            echo "</div>\n";

                            echo "<div id='button_modif' style='float:left;width:90%;margin-top:15px;padding:4px 5px;display:none;' title='".htmlaccent('Lancer la conversion')."'>";
                            echo  "<span>".htmlaccent('Valider la conversion')."</span>"; 
                            echo "</div>\n";

                            echo "<div id='convert_wait' style='float:left;width:90%;margin-top:15px;padding:4px 5px;display:none;' title='".htmlaccent('Lancer la conversion')."'>";
                                echo "<img src='".DIR_WS_IMG."wait.gif' style='float:left;width:25px;' id='convert_wait'>";
                                echo  "<span style='float:left;margin-left:15px;'>".htmlaccent('Conversion en cours <br> Veuillez patienter')."</span>"; 
                            echo "</div>\n";
                        }

                        
                    
                    echo "</div>\n";

                    // ---------------------------------
                    // Suivi des coordonnées
                    echo "<div id='boxpopup' class='select-top' style='width:195px;padding:10px;margin-top:10px;'>\n";
                        
                        echo "<p>";
                            echo "<span style='font-weight: bold;font-size:13px;width:150px;'>".htmlaccent('Contrôle du Zoom')."</span>";
                        echo "</p>";

                        // Date début zoom
                        echo "<div id='boite_small' class='select_date'>\n";
                                
                            echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Date de début')."</p>\n";	
                            echo "<input class='input_texte' style='width:70px;padding-bottom: 4px;' name='x_date_min' id='x_date_min' type='text' value='' >\n";

                        echo "</div>\n";

                        // Date fin zoom
                        echo "<div id='boite_small' class='select_date' style='margin-right:0px;'>\n";
                                
                            echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Date de fin')."</p>\n";	
                            echo "<input class='input_texte' style='width:70px;padding-bottom: 4px;' name='x_date_max' id='x_date_max' type='text' value='' >\n";
                                    
                        echo "</div>\n";

                        echo "<hr>\n";

                        echo "<div style='display:block;'>";

                            // Echelle Y (hauteur)
                            echo "<div id='boite_small' class='select_date' style='float: left;'>\n";
                                    
                                echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Min Hauteur')."</p>\n";	
                                echo "<input type='text' class='input_texte_small' style='width:50px;' id='y_hauteur_min' value='0'/>\n";
                                        
                            echo "</div>\n";

                            echo "<div id='boite_small' class='select_date' style='float: left;margin-left:0px;margin-right:0px;'>\n";
                                    
                                echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Max Hauteur')."</p>\n";	
                                echo "<input type='text' class='input_texte_small' style='width:50px;'  id='y_hauteur_max' value='0'/>\n";
                                        
                            echo "</div>\n";

                            echo "<hr>\n";

                            // Echelle Y2 (Débit)
                            echo "<div id='boite_small' class='select_date' style='float: left;'>\n";
                                    
                                echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Min Débit')."</p>\n";	
                                echo "<input type='text' class='input_texte_small' style='width:50px;'  id='y_debit_min' value='0'/>\n";
                                        
                            echo "</div>\n";

                            echo "<div id='boite_small' class='select_date' style='float: left;margin-left:0px;margin-right:0px;'>\n";
                                    
                                echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Max Débit')."</p>\n";	
                                echo "<input type='text' class='input_texte_small' style='width:50px;'  id='y_debit_max' value='0'/>\n";
                                        
                            echo "</div>\n";

                            echo "<hr>\n";

                        echo "</div>\n";
                        
                        // Bouton pour ajuster l'échelle
                        echo "<button id='ajustCoord' class='zoom_graph' style='width:120px;margin-top:5px;margin-bottom:5px;text-align:left;'>";
                            echo htmlaccent('Ajuster l\'échelle');
                        echo "</button>\n";

                    echo "</div>\n";
                

                echo "<hr>\n";
                echo "</div>\n";


                // Block Graphique
                echo "<div id='cadre_graph' style='float:none;width:auto;height:75vh;overflow-y: auto;'>\n";
                    
                    echo "<div id='boxpopup' class='select' style='width:99%;margin:0;padding:0;border:1px solid #000;'>\n";

                        echo "<p id='titre_graph' class='titre' style='height:1px;'></p>";

                        // Cadre Graph                        
                        echo "<div id='plot_0' class='graph' style='height:50vh;margin:0 5px;display:none;'></div>\n";
                        
                        echo "<div id='wait_graph' style='width:100%;height:65px;text-align:center;display:none;'>";
                            echo "<img src='".DIR_WS_IMG."wait.gif' style='width:50px;'>";
                            echo "<p>".htmlaccent('Chargement en cours ...')."</p>";
                        echo "</div>\n"; 

                        echo "<div id='barre_progress' class='bar_create_all' style='width:200px;margin: 10px auto;display:none;'>";
                            echo"<div id='pourcentage_compil' style='width:0%;'></div>";
                        echo "</div>";

                        echo "<div id='plot_etl' class='graph' style='width:95%;height:10vh;margin:0 5px;margin-top:15px;'></div>\n";
                        

                    echo "<hr>\n";
                    echo "</div>\n";	
                    
                echo "<hr>\n";
                echo "</div>\n";  
                
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
	
	echo "<hr>";
	echo "</div>";
	
echo "<hr>";
echo "</div>";
	
require('include/application_bottom.php'); 
	
echo "</body>";

echo "</html>";

?>


<script>

    // ---------------------------------------------
    // Procédure Ajax pour mise à jour du graphique
    // ---------------------------------------------

    // Paramétrage général
    msgInfo = document.getElementById('contenu_info');

    selectH = document.getElementById('select_chron_h');
    selectQ = document.getElementById('select_chron_q');

    boxGraphWait = document.getElementById('wait_graph');
    boxPlot = document.getElementById('plot_0');

    checkLacH = document.getElementById('check_lac_axe1');
    checkLacH.addEventListener('click', function() {load_graph(true);});
    checkLacQ = document.getElementById('check_lac_axe2');
    checkLacQ.addEventListener('click', function() {load_graph(true);});

    xDateMin = document.getElementById('x_date_min');
    xDateMax = document.getElementById('x_date_max');
    yHauteurMin = document.getElementById('y_hauteur_min');
    yHauteurMax = document.getElementById('y_hauteur_max');
    yDebitMin = document.getElementById('y_debit_min');
    yDebitMax = document.getElementById('y_debit_max');

    bConvert = document.getElementById('button_convert');
    bValid = document.getElementById('button_modif');

    convertWait = document.getElementById('convert_wait');
    barreProgress = document.getElementById('barre_progress');
    pourcentageCompil = document.getElementById('pourcentage_compil');
    pourcentage = 0;nb_data_all=0;

    typedataChronH = selectH.value;
    typedataChronQ = selectQ.value;

    colorH = document.getElementById('input_color_1');
    colorQ = document.getElementById('input_color_2');


    id_meta_correction = 0;
    offSet = 0; // Compteur pour la conversion des données par lots 

    // Lancement de la génération de graph

    function load_graph(reload=false)
    {        
        boxPlot.style.display = 'none';
        boxGraphWait.style.display = 'block';
        msgInfo.style.display = 'none';

        var plot_info = '';

        typedataChronH = selectH.value;
        typedataChronQ = selectQ.value;

        // Mise au format JSON des données
        // Créer un objet contenant les données à envoyer
        var dataToSend = {
            timezone_php : '<?php echo $timezone_php ?>',
            idStation: <?php echo $id_station; ?>,
            typedataChronH: typedataChronH,
            typedataChronQ: typedataChronQ,
            colorH: colorH.value,
            colorQ: colorQ.value,            
            checkLacH: checkLacH.checked,
            checkLacQ: checkLacQ.checked,
            reload: reload,
            xDateMin: xDateMin.value,
            xDateMax: xDateMax.value,
            yHauteurMin: parseFloat(yHauteurMin.value),
            yHauteurMax: parseFloat(yHauteurMax.value),
            yDebitMin: parseFloat(yDebitMin.value),
            yDebitMax: parseFloat(yDebitMax.value)
        };

        // Convertir l'objet en JSON
        var jsonDataGraph = JSON.stringify(dataToSend);
        

        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/converthq/process_convert_graph.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {            
                boxPlot.style.display = 'block';

                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

                // Accéder aux données récupéré coté serveur
                //console.log(jsonResponse['js_text']);
                nb_data_all = jsonResponse['nb_data_all'];
                eval(jsonResponse['js_text']); // on récupère le script généré coté serveur pour afficher les graphiques

                load_graph_etl();
                boxGraphWait.style.display = 'none';
                barreProgress.style.display = 'none';
            }
        };

        // Envoyer les données JSON au serveur
        xhr.send(jsonDataGraph);        
    }

    // Charge le graphique de projeciton des ETL dans le temps
    function load_graph_etl()
    {   
        // Mise au format JSON des données
        // Créer un objet contenant les données à envoyer
        var dataToSend = {
            timezone_php : '<?php echo $timezone_php ?>',
            xDateMin: xDateMin.value,
            xDateMax: xDateMax.value,
            idStation: <?php echo $id_station; ?>
        };

        // Convertir l'objet en JSON
        var jsonDataGraph = JSON.stringify(dataToSend);
        

        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/converthq/process_convert_graph_etl.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {         
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

                // Accéder aux données récupéré coté serveur
                //console.log(jsonResponse['js_text']);
                eval(jsonResponse['js_text']); // on récupère le script généré coté serveur pour afficher les graphiques
            }
        };

        // Envoyer les données JSON au serveur
        xhr.send(jsonDataGraph);
        
    }
    if(typedataChronH > 0){load_graph();}    
    

    // ---------------------------------------------
    // Action Select
    // ---------------------------------------------

    selectH.addEventListener('change', function() {load_graph(true);});
    selectQ.addEventListener('change', function() {load_graph(true);});

    // ---------------------------------------------
    // Conversion C -> Q
    // ---------------------------------------------

    function convertCQ()
    {
        msgInfo.style.display = 'none';

        bConvert.style.display = 'none';
        convertWait.style.display = 'block';   
        
        boxPlot.style.display = 'none';
        boxGraphWait.style.display = 'block'; 
        barreProgress.style.display = 'block';

        typedataChronH = selectH.value;
        typedataChronQ = selectQ.value;

        // Mise au format JSON des données
        // Créer un objet contenant les données à envoyer
        var dataToSend = {
            timezone_php : '<?php echo $timezone_php ?>',
            offSet: offSet,
            typedataChronH: typedataChronH,
            typedataChronQ: typedataChronQ,
            xDateMin: xDateMin.value,
            xDateMax: xDateMax.value,
            idStation: <?php echo $id_station; ?>,
            id_user: <?php echo $id_user; ?>,
            id_meta_correction: id_meta_correction
        };

        // Convertir l'objet en JSON
        var jsonDataGraph = JSON.stringify(dataToSend);

        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/converthq/process_convert_data.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {     
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

                if(jsonResponse['remaining']) // s'il il reste des données à convertir
                {
                    offSet = jsonResponse['nextOffset']; // Met à jour l'offset
                    id_meta_correction = jsonResponse['id_meta_correction'];

                    // Mise à jour de la par d'avancement
                    pourcentage = offSet / nb_data_all;                
                    pourcentageCompil.style.width = (pourcentage*100)+'%';
                    console.log('pourcentage'+pourcentage+' offSet'+offSet+' nb_data_all'+nb_data_all);

                    convertCQ(); // Appel du lot suivant
                }
                else
                {
                    // Accéder aux données récupéré coté serveur
                    id_meta_correction = jsonResponse['id_meta_correction'];
                    
                    load_graph();

                    bValid.style.display = 'block';
                    convertWait.style.display = 'none';
                }
            }
        };

        // Envoyer les données JSON au serveur
        xhr.send(jsonDataGraph);
    }

    if(bConvert){bConvert.addEventListener('click', function() {convertCQ();});}

    // ---------------------------------------------
    // Validation et enregistrement de la Conversion H -> Q
    // ---------------------------------------------

    function convertValid()
    {
        msgInfo.style.display = 'none';
        bValid.style.display = 'none';
        
        boxPlot.style.display = 'none';
        convertWait.style.display = 'block';
        

        typedataChronQ = selectQ.value;

        // Mise au format JSON des données
        // Créer un objet contenant les données à envoyer
        var dataToSend = {
            timezone_php : '<?php echo $timezone_php ?>',
            typedataChronQ: typedataChronQ,
            xDateMin: xDateMin.value,
            xDateMax: xDateMax.value,
            idStation: <?php echo $id_station; ?>,
            id_user: <?php echo $id_user; ?>,
            id_meta_correction: id_meta_correction
        };
        
        // Convertir l'objet en JSON
        var jsonDataGraph = JSON.stringify(dataToSend);    

        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/converthq/process_convert_valid.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {     
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

                // Accéder aux données récupérées coté serveur

                load_graph(true);

                convertWait.style.display = 'none';
                bConvert.style.display = 'block';

                msgInfo.innerText = "Les nouvelles données de débit ont bien été enregistrées";                
                msgInfo.style.border = '4px solid #09886d'; // bordure en vert
                msgInfo.style.display = 'block';

                id_meta_correction = 0;
                offSet = 0;
                
            }
        };

        // Envoyer les données JSON au serveur
        xhr.send(jsonDataGraph);
    
    }

    if(bConvert){bValid.addEventListener('click', function() {convertValid();});}


    // Permet de recharger le graphique à partir des données potentiellement rentrée dans les inputs de coordonnées
    document.getElementById('ajustCoord').addEventListener('click', function() {
                
        if(!isValidDatesInput()){return;}    

        y1Hauteur = parseFloat(yHauteurMin.value);
        y2Hauteur = parseFloat(yHauteurMax.value);
        y1Debit = parseFloat(yDebitMin.value);
        y2Debit = parseFloat(yDebitMax.value);

        xMinInput = xDateMin.value.trim();
        xMaxInput = xDateMax.value.trim();

        // Convertir les dates
        xMinZoom = xMinInput.split('-').reverse().join('-');
        xMaxZoom = xMaxInput.split('-').reverse().join('-');

        // Valider les autres champs
        if (isNaN(y1Hauteur) || isNaN(y2Hauteur) || isNaN(y1Debit) || isNaN(y2Debit)){return;}
        if (y1Hauteur > y2Hauteur || y1Debit > y2Debit){return;}

        Plotly.relayout('plot_0', {
                'xaxis.range': [xMinZoom, xMaxZoom],
                'yaxis.range': [y1Hauteur, y2Hauteur],
                'yaxis2.range': [y1Debit, y2Debit]
            });
    });


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


    function isValidDatesInput()
    {   
        if(isValidDate(xDateMin.value) && isValidDate(xDateMax.value))
        {
            date1Format = parseDate(xDateMin.value);
            date2Format = parseDate(xDateMax.value);

            if(date1Format < date2Format){return true;}
            else
            {
                msgInfo.innerText = "La Date de début doit être antérieur à la Date de fin";
                msgInfo.style.display = 'block';

                return false;
            }
        }
        else
        {
            msgInfo.innerText = "Au moins l'une des dates saisies est invalide ou dans un mauvais format (dd-mm-yyy : format valide)";
            msgInfo.style.display = 'block';

            return false;
        }

    }

</script>