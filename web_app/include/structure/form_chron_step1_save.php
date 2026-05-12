<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Formulaire pour sélection les stations et les données que l'on veut éditer / observer - Etape 1 : Sélection Station, Période
----------------------------------------
- La colonne de gauche propose les filtres permettant de pré-sélectionner les stations
- Les stations sont à sélectionner dans la liste au centre
- La période de données est à préciser dans la colonne de droite
*/

// Initialisation des var. pour les filtres les plus commun
$affiche_select_type = true;
$affiche_select_tournee = true;
$affiche_search = true;
$affiche_select_station = false;
$affiche_select_statut_station = true;
require(DIR_WS_FILTRE . 'filtre_stations_var.php');


// SELECT STATION
$sql_station = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station, s.active_station, s.station_type 
				FROM ".TABLE_STATION." s
                JOIN ".TABLE_STATION_TO_TOURNEE." st ON st.id_station = s.id_station
				WHERE s.id_territoire=".$territoire_id.
                $where_search.$where_and_regionhydro.$where_and_region.$where_and_commune.$where_and_riviere.$where_and_type.$where_and_tournee.
                $where_and_active.$where_and_suivi.$where_and_armee." 
				ORDER BY s.nom_station";
$station_query = tep_db_query($sql_link,$sql_station);
while ($station = tep_db_fetch_array($station_query))
{	
	$nom_eq_type =  htmlaccent(html_entity_decode($station['nom_eq_type'] ?? $default_string));	
	$nom_station =  htmlaccent(html_entity_decode($station['nom_station'] ?? $default_string));
	$code_station =  htmlaccent(html_entity_decode($station['code_station'] ?? $default_string));

    $id_station_type = $station['station_type'];
    $nom_eq_type =  $eq_type_array[$id_station_type]['nom_eq_type'];
    $type_color_border =  $eq_type_array[$id_station_type]['type_color_border'];
    $type_color_background =  $eq_type_array[$id_station_type]['type_color_background'];
	
	$station_array[] = array('id' => $station['id_station'],
							 'nom_eq_type' => $nom_eq_type,
							 'type_color_border' => $type_color_border,
							 'type_color_background' => $type_color_background,
							 'active_station' => $station['active_station'],
							 'nom_station' => $nom_station,
						   	 'code_station' => $code_station);

	$nb_stations++;
}

// --------------------------------------
// Debut page HTML

require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

echo "<div id='contour_general'>";
	
	if(tep_not_null($message_info)){echo "<div id='contenu_info'>".$message_info."</div>";}
	
	echo "<div id='contenu_centre'>";

        echo "<div id='contenu_box2'>";
		
            echo "<h1>";
                            
                echo "<span>".htmlaccent('Accès aux données - Etape 1 : Sélection des stations')."</span>";
                
            echo "</h1>";
                            
            $lien_form = tep_href_link('data_chron.php');
            $name_form = 'form_filtre';			
            echo "<form name='".$name_form."' id='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data' >";	
                    
                echo "<div id='cadre_graph' style='float:left;width:17.5%;height:70vh;overflow-y: auto;'>\n";       
						
                    echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding: 0 3%;'>\n";
                    
                        require(DIR_WS_FILTRE . 'filtre_stations_html.php');
                        
                        /*            
                        // Un seul type de données (CI, CIE, PJ, PJE, ...)
                        if($select_type_encours > 0)
                        {
                            echo "<div id='boxpopup' class='select' style='width:240px;margin-top: 0px;margin-left: 10px;background-color: #f7f7f7;'>\n";
                                
                                echo "<div id='boite_small'>\n";		
                            
                                    echo "<p>".htmlaccent('Sélectionner un seul type de chronique')."</p>";	
                                
                                    echo "<select name='select_type_chron' id='select_type_chron' style='width:200px;margin-right: 20px;'>";
                                                                
                                        echo "<option value='0' >".htmlaccent('-')."</option>";
                                        
                                        $selected = '';	
                                        
                                        if(isset($type_chron_array))
                                        {
                                            foreach ($type_chron_array as $id_type_chron => $type_chron)
                                            {
                                                $selected = '';
                                                if($type_chron['id_eq_type_data']==$select_type_encours)
                                                {echo "<option value='".$id_type_chron."' ".$selected." >".$type_chron['init_type_data']." - ".$type_chron['nom_type_data']."</option>\n";}
                                            }
                                        }
                                        
                                    echo "</select>";
                                        
                                echo "</div>\n";
                                        
                            echo "<hr>\n";
                            echo "</div>\n";	
                        }
                        */
                    echo "<hr>\n";
                    echo "</div>\n";	                        
                
                echo "<hr>\n";
                echo "</div>\n";
            
            echo "</form >\n";

            $lien_form = tep_href_link('data_chron.php');
            $name_form = 'form_valid';			
            echo "<form name='".$name_form."' id='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data' target='_blank'>";	

                echo "<div id='cadre_graph' style='float:left;width:800px;height:78vh;margin-left:1%;'>\n";
                    
                    // Select Stations
                    echo "<div id='boxpopup' class='select' style='margin-top: 0px;'>\n";
                        
                        echo "<div style='float:left;'>\n";

                            echo "<h2 style='float:left;width:100%;'>".htmlaccent('Sélectionnez une ou plusieurs stations parmi ').$nb_stations.htmlaccent(' stations')."</h2>";
                            
                        echo "</div>\n";

                        echo "<select name='select_station_ref[]' id='select_station_ref' multiple='multiple'>";
                                    
                            if(isset($station_array))
                            {
                                for($s=0;$s<sizeof($station_array);$s++)
                                {
                                    echo "<option value='".$station_array[$s]['id']."' style='background-color:".$station_array[$s]['type_color_background'].";'>".$station_array[$s]['nom_eq_type']." - ".$station_array[$s]['code_station']." - ".$station_array[$s]['nom_station']."</option>";
                                }
                            }
                                                                    
                        echo "</select>";

                    echo "<hr>\n";
                    echo "</div>\n";

                echo "<hr>\n";
                echo "</div>\n";    
                
                echo "<div id='cadre_graph' style='float:left;width:17.5%;height:60vh;'>\n";

                    // Periode de données	
                    echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding: 15px 3%;'>\n";
                            
                        echo "<div id='boite_small'>\n";
                                        
                            echo "<p style='float:left;width:100px;padding-top:5px;'>";
                                echo htmlaccent('Période');
                            echo "</p>";

                            echo "<select name='select_periode' id='select_periode'  onchange='select_periode_function();' style='float:right;width:140px;'>";
                                
                                echo "<option value='1' selected>".htmlaccent('Plusieurs années')."</option>";
                                echo "<option value='2' >".htmlaccent('Plusieurs mois')."</option>";
                                echo "<option value='3' >".htmlaccent('Personnaliser...')."</option>";
                                
                            echo "</select>";	
                                
                        echo "</div>";

                        echo "<hr>\n";

                                        
                        // Date début aaaa ou mm/aa ou jj/mm/aaa
                        
                        echo "<div id='boite_small' class='list_month' style='display:none;'>\n";
                                        
                            echo "<p style='color:#428bca;'>".htmlaccent('1er mois')."</p>";
                            echo select_mois('select_month_f',1);
                                
                        echo "</div>";
                        
                        echo "<div id='boite_small' class='list_year' >\n";
                                        
                            echo "<p style='color:#428bca;'>".htmlaccent('1er année')."</p>";
                            echo "<select name='select_year_f' style='width:65px;'>";
                                $annee_temp = 0;
                                $annee = 0;
                                for($a=$l_y;$a>=$f_y;$a--)
                                {
                                    $selected='';
                                    if($a==$year_first){$selected='SELECTED';}
                                    echo "<option value='".$a."' ".$selected.">".$a."</option>";
                                    
                                }
                            echo "</select>";
                                
                        echo "</div>";

                        echo "<hr>\n";
                        
                        
                        // Date fin aaaa ou mm/aa
                        
                        echo "<div id='boite_small' class='list_month' style='display:none;'>\n";
                                        
                            echo "<p style='color:#d9534f;'>".htmlaccent('Dernier mois')."</p>";
                            echo select_mois('select_month_l',1);
                                
                        echo "</div>";
                        
                        echo "<div id='boite_small' class='list_year' style='margin-right:0;'>\n";
                                        
                            echo "<p style='color:#d9534f;'>".htmlaccent('Dernière année')."</p>";
                            echo "<select name='select_year_l' style='width:65px;'>";
                                $annee_temp = 0;
                                $annee = 0;
                                for($a=$l_y;$a>=$f_y;$a--)
                                {							
                                    $selected='';
                                    if($a==$year_today){$selected='SELECTED';}
                                    echo "<option value='".$a."' ".$selected.">".$a."</option>";
                                    
                                }
                            echo "</select>";
                                
                        echo "</div>";

                        // -----------------------

                        // Date First jj/mm/aaaa
                        echo "<div id='boite_small' class='select_date' style='display:none;'>\n";
                            
                            echo "<p style='width:80px;color:#428bca;'>".htmlaccent('Date de début')."</p>\n";	
                            echo "<input class='input_texte' style='width:80px;padding-bottom: 4px;' name='date_f' id='date_f' type='text' value='".$date_1."' onclick=\"javascript:displayCalendar(document.forms[0].date_f,'dd-mm-yyyy',this);\" >\n"; 											
                                    
                        echo "</div>\n";
                    
                        echo "<hr>\n";
                        
                        // Date Fin jj/mm/aaaa
                        echo "<div id='boite_small' class='select_date' style='display:none;'>\n";
                            
                            echo "<p style='width:80px;color:#d9534f;'>".htmlaccent('Date de fin')."</p>\n";	
                            echo "<input class='input_texte' style='width:80px;padding-bottom: 4px;' name='date_l' id='date_l' type='text' value='".$date_2."' onclick=\"javascript:displayCalendar(document.forms[0].date_l,'dd-mm-yyyy',this);\" >\n"; 											
                                    
                        echo "</div>\n";
                            

                    echo "</div>\n";

                    echo "<input type='submit' class='button' name='valid_chron_step1' value='Valider' style='width:100%;margin:20px auto;' />";	
                
                echo "<hr>\n";
                echo "</div>\n";
                        
                
            echo "</form >\n";

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

<script id="source" type="text/javascript">

	// Affichage de la barre de progression
	//document.getElementById('box_load').style.display = 'block';

    function submitForm(from,target) 
    {
        document.getElementById(from).target = target; // Définit l'attribut target du formulaire

        document.getElementById(from).target = '_SELF';
    }

</script>