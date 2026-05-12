<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Page permettant de lister les corrections qui sont en cours et qui ont été réalisées
----------------------------------------
*/

require('include/application_top.php');

$row = 0;
$today = new DateTime(); // Crée un objet DateTime pour la date actuelle


$tournee_delai_encours = 0;
$having_delai = '';


//---------------------------------------------------------------
// Récupération des champs de formulaires pour sélection


// SELECT POUR LE TRI
// TRI (Nom station, Code Station, Commune, Type_DATA)
$tri_encours = 1;
$tri = ""; // tri par défault 
if(isset($_POST['select_tri']))
{
	$tri_encours = $_POST['select_tri'];
	//if($_POST['select_tri'] == 1){$tri = "date_lastRa";} // tri nom station
    if($_POST['select_tri'] == 2){$tri = "s.nom_station";} // tri nom station
	if($_POST['select_tri'] == 3){$tri = "s.code_station";} // tri code station
	if($_POST['select_tri'] == 4){$tri = "s.station_type";} // tri type data (Pluie, Hydro, Piezo)
}

$tri_order_encours = 2;
$tri_order = " DESC,";
if(isset($_POST['order_tri']))
{
	$tri_order_encours = $_POST['order_tri'];
	if($_POST['order_tri'] == 1){$tri_order = " ASC,";} // tri croissant
	if($_POST['order_tri'] == 2){$tri_order = " DESC,";} // tri décroissant
}


// Initialisation des var. pour les filtres les plus commun
$affiche_select_type = true;
$affiche_select_tournee = false;
$affiche_search = true;
$affiche_select_riviere = false;
$affiche_select_station = false;
$affiche_select_statut_station = true;
require(DIR_WS_FILTRE . 'filtre_stations_var.php');




//---------------------------------------------------------------
// TABLE SQL - Recupération DATA

// TABLE USER
$sql_user_list = "SELECT DISTINCT id, id_statut, login, nom, prenom FROM ".TABLE_USER;
$user_list_query = tep_db_query($sql_link,$sql_user_list);
while ($user_list = tep_db_fetch_array($user_list_query))
{
    $id = $user_list['id'];
    $id_statut = $user_list['id_statut'];
	$login = htmlaccent(html_entity_decode($user_list['login'] ?? $default_string));
	$nom = ucfirst(strtolower(htmlaccent(html_entity_decode($user_list['nom'] ?? $default_string))));
	$prenom = ucfirst(strtolower(htmlaccent(html_entity_decode($user_list['prenom'] ?? $default_string))));

	$user_list_array[$id] = array('id_statut' => $id_statut,
                                    'login' => $login,
                                    'nom' => $nom,
                                    'prenom' => $prenom
                                    );
}


// TABLE DELAI
$sql_delai = "SELECT DISTINCT id, periode, nb_days
				FROM ".TABLE_TOURNEE_PERIODE." 
				ORDER BY nb_days ASC";
$delai_query = tep_db_query($sql_link,$sql_delai);	
while ($delai_tab = tep_db_fetch_array($delai_query))
{
    $id = $delai_tab['id'];
	$periode = htmlaccent(html_entity_decode($delai_tab['periode'] ?? $default_string));
    $nb_days = $delai_tab['nb_days'];

	$delai_array[$id] = array('periode' => $periode,'nb_days' => $nb_days);
}

// TABLE STATION
$sql_station_all = "SELECT DISTINCT id_station, nom_station, code_station, station_type, active_station
					FROM ".TABLE_STATION." 
				    ORDER BY nom_station ASC";
$station_all_query = tep_db_query($sql_link,$sql_station_all);
while ($station_all = tep_db_fetch_array($station_all_query))
{	
    $nom_station = htmlaccent(html_entity_decode($station_all['nom_station'] ?? $default_string));

	$station_all_array[$station_all['id_station']] = array('code_station' => $station_all['code_station'],
															'nom_station' => $nom_station,
															'station_type' => $station_all['station_type'],
															);
}

// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_graph 
                FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1 
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
while ($eq_type_tab = tep_db_fetch_array($eq_type_query))
{
	$eq_type_array[$eq_type_tab['id_eq_type']] = array('nom_eq_type' => htmlaccent(html_entity_decode($eq_type_tab['nom_eq_type'] ?? $default_string)),
                                                        'unite_eq_type' => $eq_type_tab['unite_eq_type'],
                                                        'valeur_data_type' => $eq_type_tab['valeur_data_type'],
														'type_graph' => $eq_type_tab['type_graph'],
                                                        'type_color_border' => $eq_type_tab['type_color_border']
                                                    );
}

// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, to_periode, id_chon_periode
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



//---------------------------------------------------------------
// TABLE SQL - Recupération de la listes des corrections pour affichage

$correction_array = [];
$nb_correction = 0;

$sql_correction = "SELECT c.id, c.id_user, c.datetime_correction, c.id_station, c.id_chron_init,
                            s.id_region, s.id_commune, s.nom_station, s.code_station, s.vallee_station, 
                            s.active_station, s.suivi, s.armee, s.station_type,
                            s.id_regionhydro, s.id_riviere
                    FROM ".TABLE_DATA_CORRECTION." c
                    LEFT JOIN ".TABLE_STATION." s ON s.id_station = c.id_station	
                    WHERE 1=1 ".$where_search.$where_and_regionhydro.$where_and_region.$where_and_commune.$where_and_riviere.$where_and_type.
                    $where_and_active.$where_and_suivi.$where_and_armee.
                    " ORDER BY c.datetime_correction DESC";

$correction_query = tep_db_query($sql_link,$sql_correction);
while($correction_tab = tep_db_fetch_array($correction_query))
{	
    $nb_correction++;

    $id_correction = $correction_tab['id'];
    $station_type = 0;
    $id_station = 0;
    $id_chron_init = 0;
    $type_mesure = '';
    $type_color_border = '';

    $login_user = $user_list_array[$correction_tab['id_user']]['login'];
    $prenom_user = $user_list_array[$correction_tab['id_user']]['prenom'];
    $nom_user = $user_list_array[$correction_tab['id_user']]['nom'];

    $datetime_correction = $correction_tab['datetime_correction'];
    $datetime_correction_tab = explode(' ',$datetime_correction);
    $datetime_correction_formated = dateus_fr($datetime_correction_tab[0]).' '.$datetime_correction_tab[1];

    $id_station = $correction_tab['id_station'];        
    $code_station = $correction_tab['code_station'];
    $nom_station = $correction_tab['nom_station'];

    $id_chron_init = $correction_tab['id_chron_init']; 
    $init_chron = $type_chron_array[$id_chron_init]['init_type_data'];

    $station_type = $correction_tab['station_type'];
    $type_mesure = $eq_type_array[$station_type]['nom_eq_type'];
    $type_color_border = $eq_type_array[$station_type]['type_color_border'];

    $graph_chron_link = $id_station."_".$station_type."_".$id_chron_init;
    $text_chron_link = DIR_WS_DATA_CORRECTIONS.$code_station."_".$init_chron."_".$id_correction.".txt";
    
    $correction_array[$id_correction] = array('login_user' => $login_user,
                                                'prenom_user' => $prenom_user,
                                                'nom_user' => $nom_user,
                                                'datetime_correction_formated' => $datetime_correction_formated,
                                                'id_station' => $id_station,                                                
                                                'code_station' => $code_station,
                                                'nom_station' => $nom_station,
                                                'graph_chron_link' => $graph_chron_link,
                                                'text_chron_link' => $text_chron_link,
                                                'type_mesure' => $type_mesure,
                                                'type_color_border' => $type_color_border          
                                            );
                            
}


//---------------------------------------------------------------
// Edition HTML


require(DIR_WS_STRUCTURE . 'header_web.php');

echo "<body>";

require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

echo "<div id='contour_general'>";	
	
	echo "<div id='contenu_centre'>";
		
		echo "<div id='contenu_box2'>";
		
			echo "<h1>";
				
				echo "<span>".htmlaccent('Suivi des corrections des Chroniques de données')."</span>";

                //echo button_pdf(htmlaccent('Editer une prochaine tournée'),'pdf_tournee.php');

			echo "</h1>";
            
            // ----------------------------------------------------------------------------------------
            // FORMULAIRE DE SELECTION - Cadre en-tête de la page
            // Ce bloc contient les champs formulaire en liste qui permettent de sélectionner les RA en fonction de différents critères
    
                // Balise indiquant le début du formulaire
                $lien_form = tep_href_link('corrections.php');
                $name_form = 'form_correction';			
                echo "<form name='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data'>";			
			
                    echo "<div id='cadre_graph' style='float:left;width:250px;margin-right:1%;height:75vh;overflow-y: auto;'>\n"; 
						
                        echo "<div id='boxpopup' class='select-top' style='width:90%;margin:0px;padding: 10px;'>\n";
                    
                           require(DIR_WS_FILTRE . 'filtre_stations_html.php');
                            
                            echo "<hr>";

                            // TRI DE LA TABLE
                            echo "<div style='width:100%;border-bottom:2px solid #176B87;margin-top:15px;'></div>";

                            echo "<p style='float:left;width:auto;padding-top:5px;color:#186F65;margin-top:15px;'>".htmlaccent('TRIER PAR')."</p>";

                            echo "<select name='select_tri' id='select_tri' onchange='".$name_form.".submit();' style='float:right;width:130px;margin-top:15px;'>";
                                
                                $selected = ($tri_encours == 1) ? "selected" : "";
                                echo "<option value='1' ".$selected.">".htmlaccent('Date de Correction')."</option>";
                                $selected = ($tri_encours == 2) ? "selected" : "";
                                echo "<option value='2' ".$selected.">".htmlaccent('Nom de la station')."</option>";
                                $selected = ($tri_encours == 3) ? "selected" : "";
                                echo "<option value='3' ".$selected.">".htmlaccent('Code de la station')."</option>";
                                $selected = ($tri_encours == 4) ? "selected" : "";
                                echo "<option value='4' ".$selected.">".htmlaccent('Type de données')."</option>";
                                    
                            echo "</select>";

                            echo "<hr>";

                            echo "<div style='float:right;'>";

                                // Déterminer la valeur de l'attribut "checked" en fonction de $tri_order_encours
                                $asc_checked = ($tri_order_encours == 1) ? "checked" : "";
                                $desc_checked = ($tri_order_encours == 2) ? "checked" : "";

                                echo "<p style='float:left;width:55px;padding-top:3px;'>".htmlaccent('Croissant')."</p>";
                                echo "<input type='radio' id='asc' name='order_tri' value='1' style='float:left;' ".$asc_checked." onchange='".$name_form.".submit();' >";

                                echo "<p style='float:left;width:65px;margin-left:10px;padding-top:3px;'>".htmlaccent('Décroissant')."</p>";
                                echo "<input type='radio' id='desc' name='order_tri' value='2' style='float:left;' ".$desc_checked." onchange='".$name_form.".submit();' >";

                            echo "</div>";
                        
                            
                            // Affichage nombre de stations ; nbre stations activse ; nbre stations suivies - Cadre jaune
                             echo "<div id='contenu_infos' style='width:auto;'>";
                                                    
                                echo "<p>";
                                    echo "<span style='margin:0px;'>".htmlaccent('Nombre de correction : ').number_format($nb_correction,0,'.',' ')."</span>";
                                echo "</p>";

                            echo "</div>";

                            echo "<hr>";
                                
                        echo "</div>";	
                    
                    echo "</div>";
                    
                echo "</form>"; // Fin du formulaire
                
                
                // Affichage Tableau
                if(isset($correction_array))
                {
                    echo "<div class='table-container' style='float:none;width:auto;height:80vh;'>";

                        echo "<table id='table_tri' cellspacing='0' >";
                    
                            echo "<thead>";
                                echo "<tr class='header-row'>";
                                                                        
                                    echo "<th style='width:90px;font-size:12px;padding-left:5px;'>".htmlaccent('Login')."</th>";						
                                    echo "<th style='width:150px;font-size:12px;'>".htmlaccent('Prénom Nom')."</th>";         
                                    echo "<th style='width:150px;' title='".htmlaccent('Date')."'>".htmlaccent('Date')."</th>";
                                    echo "<th style='width:100px;'>".htmlaccent('Code station')."</th>";						
                                    echo "<th style='width:350px;'>".htmlaccent('Nom station')."</th>";    
                                    echo "<th style='width:100px;'>".htmlaccent('Type data')."</th>";                        
                                    echo "<th style='width:80px;text-align: center;' >".htmlaccent('Détails')."</th>";                            
                                    echo "<th style='width:80px;text-align: center;' >".htmlaccent('Consulter')."</th>";
                                    
                                echo "</tr>";
                            echo "</thead>";
                    
                            //ligne vide dans le tableau		
                                
                            echo "<tr>";
                                echo "<td colspan='9' style='height:15px;'>&nbsp;</td>";
                            echo "</tr>";	  
                                
                            foreach($correction_array as $key => $value)
                            {	
                                if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                                else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
                                
                                $color_type = '';
                                if(tep_not_null($value['type_color_border'])){$color_type = 'color:'.$value['type_color_border'].';';}

                                $detail_text = "-";
                                $consult_text = "-";                                
                                
                                //$valid_textCorrection = "<img src='".DIR_WS_IMG_ICO."check.png' style='width:15px;' title='".htmlaccent('Correction(s) appliquée(s)')."'>";
                                if(file_exists($value['text_chron_link']))
                                {
                                    $detail_text = "<a href='".$value['text_chron_link']."' target='blank_' >
                                                        <img src='".DIR_WS_IMG_ICO."detail.png' style='width:20px;cursor:pointer;'>
                                                    </a>";
                                }

                                $tabForm = [
                                                ['name' => 'graph_chron', 'value' => $value['graph_chron_link']],
                                                ['name' => 'button_calcul', 'value' => true],
                                                ['name' => 'id_correction', 'value' => $key]
                                            ];
                                $tabFormJson = json_encode($tabForm);
                                $tabFormJson = htmlspecialchars($tabFormJson, ENT_QUOTES, 'UTF-8');

                                $consult_text = "<img src='".DIR_WS_IMG_ICO."graph.png' style='width:15px;cursor:pointer;' 
                                                    title='"."Consulter la correction"."' 
                                                onclick=\"event.preventDefault();linkSubmitForm('data_chron.php', ".$tabFormJson.");\">";
                                                
                                                

                              
                                
                                echo "<tr ".$row_l." style='height:20px;'>";

                                    echo "<td style='padding-left:5px;'>".$value['login_user']."</td>\n";	
                                    echo "<td>".$value['prenom_user']." ".$value['nom_user']."</td>\n";	
                                    echo "<td>".$value['datetime_correction_formated']."</td>\n";						
                                    echo "<td >".$value['code_station']."</td>\n";						
                                    echo "<td title='".$value['nom_station']."'>".affichelettres($value['nom_station'],40)."</td>\n";	
                                    echo "<td style='".$color_type."' >".$value['type_mesure']."</td>\n";                                    
                                    echo "<td style='text-align: center;'>".$detail_text."</td>";
                                    echo "<td style='text-align: center;'>".$consult_text."</td>";
                                    
                                echo "</tr>\n";

                                $row++;
                            }
                            
                        echo "</table>";
                    
                    echo "</div>";
                }
                else
                {
                    echo "<div id='boxpopup' style='margin-left: 1%;'>\n";
                        echo "<p class='alert'>".htmlaccent('Aucune - Correction - n\'a été trouvée')."</p>";
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
