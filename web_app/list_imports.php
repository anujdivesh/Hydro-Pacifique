<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Affichage de la Liste des actions réalisées sur la plateforme
----------------------------------------
*/

require('include/application_top.php');

$message_suprr = '';
$row = 0;
$today = new DateTime();

$select_user_encours = 0;
$where_and_user = '';

$select_station_encours = 0;
$where_and_station = '';

$delai_encours = 31; // par défaut au chargement de la page
$having_delai = " HAVING DATEDIFF(NOW(), dateheure) <= ".$delai_encours;



//---------------------------------------------------------------
// Récupération des champs de formulaires pour sélection

// Utilisateur de la plateforme
if(isset($_POST['select_user']) && $_POST['select_user']!=0)
{
	$select_user_encours = $_POST['select_user'];
	$where_and_user = " AND id_user=".$select_user_encours; // pour la selection de l'utilisateur
}

if(isset($_POST['select_station']) && $_POST['select_station']!=0)
{
	$select_station_encours = $_POST['select_station'];
	$where_and_station = " AND id_station=".$select_station_encours; // pour la selection de l'utilisateur
}

// Tournées Délai - Délai de passage à une station
if(isset($_POST['select_delai']) && $_POST['select_delai']!=0)
{
	$delai_encours = $_POST['select_delai'];
    $having_delai = " HAVING DATEDIFF(NOW(), dateheure) <= ".$delai_encours;
}
if(isset($_POST['select_delai']) && $_POST['select_delai']==0)
{
    $delai_encours = 0;
    $having_delai = "";
}



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

// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, unite
				  FROM ".TABLE_TYPE_DATA." 
				  ORDER BY init_type_data ASC";
$type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
while ($type_chron_tab = tep_db_fetch_array($type_chron_query))
{
    $nom_type_data = htmlaccent(html_entity_decode($type_chron_tab['nom_type_data'] ?? $default_string));

	$type_chron_array[$type_chron_tab['id_data_type']] = array('init_type_data' => $type_chron_tab['init_type_data'],
															'nom_type_data' => $nom_type_data,
															'unite' => $type_chron_tab['unite'],															
															'id_eq_type_data' => $type_chron_tab['id_eq_type_data']
															);
}


//---------------------------------------------------------------
// TABLE SQL - Recupération de la liste des Actions à afficher

$nb_imports = 0;

// AND dateheure >= DATE_SUB(NOW(), INTERVAL 24 MONTH) ".

$sql_import = "SELECT DISTINCT id, id_import, file_import, dateheure, id_station, id_chron, id_user, nb_data, datetime_first, datetime_end
                        FROM ".TABLE_IMPORT_SUIVI." 
                        WHERE import=1".                        
                        $where_and_user.$where_and_station.
                        $having_delai."                       
                        ORDER BY dateheure DESC";
$import_query = tep_db_query($sql_link,$sql_import);
while ($import_tab = tep_db_fetch_array($import_query))
{	
	$id =  $import_tab['id'];
    $id_import =  $import_tab['id_import'];
    
    $file_import =  $import_tab['file_import'];

    $dateheure = new DateTime($import_tab['dateheure']); // Crée un objet DateTime pour la dernière date valide
    $dateheure_formatted = $dateheure->format('d-m-Y H:i:s'); // Formatte la date au format "d-m-Y HH:mm:ss"
	$delai_action = $today->diff($dateheure)->days;
        
    // Parcourir le tableau des délais
    $text_delai = '';
    foreach ($delai_array as $key => $value) 
    {
        // Vérifier si le nombre de jours se trouve dans la plage de ce délai
        if ($delai_action <= $value['nb_days']) 
        {
            $text_delai = htmlaccent('moins de '.$value['periode']);
            break; // Sortir de la boucle dès qu'on trouve la première correspondance
        }
        $text_delai = htmlaccent('plus de '.$value['periode']);
    }
      
    $id_station =  $import_tab['id_station'];  
    if(isset($station_all_array[$id_station]))
    {
        $code_station = $station_all_array[$id_station]['code_station'];    
        $nom_station = $station_all_array[$id_station]['nom_station'];
        $station_type = $station_all_array[$id_station]['station_type'];

        $id_chron =  $import_tab['id_chron'];  

        $init_chron = '';
        $nom_chron = '';
        if(isset($type_chron_array[$id_chron]))
        {    
            $init_chron = $type_chron_array[$id_chron]['init_type_data'];   
            $nom_chron = $type_chron_array[$id_chron]['nom_type_data'];   
        }

        $graph_chron_link = $id_station."_".$station_type."_".$id_chron;

        $id_user =  $import_tab['id_user'];   
        $login_user = $user_list_array[$id_user]['login']; 
        $nom_user = $user_list_array[$id_user]['nom'];     
        $prenom_user = $user_list_array[$id_user]['prenom']; 

        $nb_data =  $import_tab['nb_data'];  
        $datetime_first = new DateTime($import_tab['datetime_first']); // Crée un objet DateTime pour la dernière date valide
        $date_first_formatted = $datetime_first->format('d-m-Y'); // Formatte la date au format "d-m-Y"
        $datetime_first_formatted = $datetime_first->format('d-m-Y H:i:s'); // Formatte la date au format "d-m-Y HH:mm:ss"

        $datetime_end = new DateTime($import_tab['datetime_end']); // Crée un objet DateTime pour la dernière date valide
        $date_end_formatted = $datetime_end->format('d-m-Y'); // Formatte la date au format "d-m-Y"
        $datetime_end_formatted = $datetime_end->format('d-m-Y H:i:s'); // Formatte la date au format "d-m-Y HH:mm:ss"  

        $file_exist_txt = false;
        $file_info_link = DIR_WS_DATA_IMPORT.$import_tab['id_import'].'_'.$init_chron.'.txt';
        if(file_exists($file_info_link)){$file_exist_txt = true;}
        
        // Tableau station avec toutes les données	
        $import_array[$id] = array('id_import' => $id_import,
                                    'file_import' => $file_import,
                                    'dateheure_formatted' => $dateheure_formatted,
                                    'text_delai' => $text_delai,
                                    'code_station' => $code_station,
                                    'nom_station' => $nom_station,
                                    'graph_chron_link' => $graph_chron_link,
                                    'init_chron' => $init_chron,
                                    'nom_chron' => $nom_chron,
                                    'login_user' => $login_user,
                                    'nom_user' => $nom_user,
                                    'prenom_user' => $prenom_user,
                                    'nb_data' => $nb_data,
                                    'date_first_formatted' => $date_first_formatted,
                                    'datetime_first_formatted' => $datetime_first_formatted,
                                    'date_end_formatted' => $date_end_formatted,
                                    'datetime_end_formatted' => $datetime_end_formatted,
                                    'file_exist_txt' => $file_exist_txt,
                                    'file_info_link' => $file_info_link                            
                                    );
    }                                    
}
if(isset($import_array)){$nb_imports = sizeof($import_array);}


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
				
				echo "<span>".htmlaccent('Suivi des dernières importations de données - 24 mois')."</span>";

			echo "</h1>";		
            
            $lien_form = tep_href_link('list_imports.php');			
            echo "<form name='form_imports' action='".$lien_form."' method='post' enctype='multipart/form-data' >";
			
                // Bloc haut pour filtrer la liste des actions
                echo "<div id='cadre_graph' style='float:left;width:230px;margin-left:1%;height:70vh;overflow-y: auto;'>\n"; 
                
                    echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding: 0 3%;'>\n";

                        // Filtre Utilisateur de la plateforme
                        echo "<p style='float:left;width:60px;margin-top:15px;padding-top:5px;'>".htmlaccent('Utilisateur')."</p>";

                        echo "<select name='select_user' id='select_user' onchange='form_imports.submit();' style='float:right;width:140px;margin-top:15px;'>";
                                                
                            echo "<option value='0'>-</option>";
                            
                            $selected = '';	                
                            if(isset($user_list_array))
                            {
                                foreach($user_list_array as $key => $value)
                                {
                                    if($key == $select_user_encours){$selected="selected";}	
                                    else{$selected = '';}											
                                    echo "<option value='".$key."' ".$selected.">".$value['prenom']." ".$value['nom']."</option>";
                                }
                            }
                        echo "</select>";

                        echo "<hr>\n";	

                        // Filtre Station
                        /*
                        echo "<p style='float:left;width:60px;padding-top:5px;'>".htmlaccent('Station')."</p>";

                        echo "<select name='select_station' id='select_station' onchange='form_imports.submit();' style='float:right;width:140px;'>";
                                                
                            echo "<option value='0'>-</option>";
                            
                            $selected = '';                 
                            if(isset($station_all_array))
                            {
                                foreach($station_all_array as $key => $value)
                                {
                                    if($key == $select_station_encours){$selected="selected";}	
                                    else{$selected = '';}											
                                    echo "<option value='".$key."' ".$selected.">".$value['code_station']." - ".$value['nom_station']."</option>";
                                }
                            }
                        echo "</select>";
                        */

                        echo "<hr>\n";	                            
                        
                        // Filtre Délai
                        echo "<p style='float:left;width:60px;padding-top:5px;'>".htmlaccent('Délai')."</p>";
                                            
                        echo "<select name='select_delai' id='select_delai' onchange='form_imports.submit();' style='float:right;width:140px;'>";
                                
                            echo "<option value='0'>-</option>";
                            
                            $selected = '';	

                            if(isset($delai_array))
                            {
                                foreach($delai_array as $key => $value)
                                {   
                                    if($value['nb_days'] == $delai_encours){$selected="selected";}	
                                    else{$selected = '';}											
                                    echo "<option value='".$value['nb_days']."' ".$selected.">".htmlaccent('moins de ').$value['periode']."</option>";
                                }
                            }
                        echo "</select>";

						echo "<hr>";
                            
                    echo "</div>";	
                
                echo "</div>";
            
            echo "</form>";
            
        
			
			// Affichage Tableau
			if(isset($import_array) && ($nb_imports>0))
			{
                echo "<div class='table-container' style='float:none;width:auto;height:75vh;'>";

                    echo "<table id='table_tri' cellspacing='0'>";
                
                        echo "<thead>";
                            echo "<tr class='header-row'>";		
                                echo "<th style='width:90px;font-size:12px;padding-left:5px;'>".htmlaccent('Login')."</th>";						
                                echo "<th style='width:150px;font-size:12px;'>".htmlaccent('Prénom Nom')."</th>";         
                                echo "<th style='width:170px;font-size:12px;'>".htmlaccent('Date')."</th>";                           
                                echo "<th style='width:150px;font-size:12px;'>".htmlaccent('Ficher importé')."</th>";
                                echo "<th style='width:250px;font-size:12px;'>".htmlaccent('Station')."</th>";
                                echo "<th style='width:240px;font-size:12px;'>".htmlaccent('Chronique')."</th>";               
                                echo "<th style='width:90px;font-size:12px;'>".htmlaccent('Nb data')."</th>";                                             
                                echo "<th style='width:170px;font-size:12px;'>".htmlaccent('Date début')."</th>";      
                                echo "<th style='width:170px;font-size:12px;'>".htmlaccent('Date fin')."</th>";   
                                echo "<th style='width:80px;font-size:12px;text-align: center;'>".htmlaccent('Détails')."</th>";                                 
                                echo "<th style='width:80px;font-size:12px;text-align: center;'>".htmlaccent('Consulter')."</th>";   
                            echo "</tr>";
                        echo "</thead>";
                
                        //ligne vide dans le tableau	
                        echo "<tr>";
                            echo "<td colspan=11' style='height:15px;'>&nbsp;</td>";
                        echo "</tr>";	
                            
                        foreach($import_array as $key => $value)
                        {	
                            $tabForm = [
                                            ['name' => 'graph_chron', 'value' => $value['graph_chron_link']],
                                            ['name' => 'date1_encours', 'value' => $value['date_first_formatted']],
                                            ['name' => 'date2_encours', 'value' => $value['date_end_formatted']]
                                        ];
                            $tabFormJson = json_encode($tabForm);
                            $tabFormJson = htmlspecialchars($tabFormJson, ENT_QUOTES, 'UTF-8');

                            $consult_import = "<img src='".DIR_WS_IMG_ICO."graph.png' style='width:15px;cursor:pointer;' 
                                                title='"."Consulter les données importées"."' 
                                            onclick=\"event.preventDefault();linkSubmitForm('data_chron.php', ".$tabFormJson.");\">";



                            if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                            else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
                            
                            echo "<tr ".$row_l." style='height:20px;'>";

                                echo "<td style='padding-left:5px;' >".$value['login_user']."</td>\n"; 							
                                echo "<td>".$value['prenom_user']." ".$value['nom_user']."</td>\n";								
                                echo "<td>".$value['dateheure_formatted']."</td>\n";                                								
                                echo "<td>".$value['file_import']."</td>\n";                                                              								
                                echo "<td title='".$value['nom_station']."'>".$value['code_station'].' - '.affichemots($value['nom_station'],2)."</td>\n";            								
                                echo "<td>".$value['init_chron'].' - '.$value['nom_chron']."</td>\n";                   								
                                echo "<td>".$value['nb_data']."</td>\n";   
                                echo "<td>".$value['datetime_first_formatted']."</td>\n";   
                                echo "<td>".$value['datetime_end_formatted']."</td>\n"; 

                                echo "<td style='text-align: center;'>";
                                    if($value['file_exist_txt'])
                                    {
                                        echo "<a href='".$value['file_info_link']."' target='blank_' >"; 
                                            echo "<img src='".DIR_WS_IMG_ICO."detail.png' style='width:20px;cursor:pointer;'>";							
                                        echo "</a>";
                                    }
                                    else{echo '-';}  
                                echo "</td>\n"; 

                                echo "<td style='text-align: center;'>";
                                    echo $consult_import;
                                echo "</td>\n"; 

                            echo "</tr>\n";
                            
                            $row++;
                        }
                        
                    echo "</table>";
                    
                echo "</div>";
			}
			else
			{
				echo "<div id='boxpopup' style='margin-left: 1%;'>\n";
					echo "<p class='alert'>".htmlaccent('Aucune - Importation - n\'a été trouvée')."</p>";
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
