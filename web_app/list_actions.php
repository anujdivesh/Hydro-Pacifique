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

$select_action_encours = 0;
$where_and_action = '';




//---------------------------------------------------------------
// Récupération des champs de formulaires pour sélection

// Utilisateur de la plateforme
if(isset($_POST['select_user']) && $_POST['select_user']!=0)
{
	$select_user_encours = $_POST['select_user'];
	$where_and_user = " AND id_user=".$select_user_encours; // pour la selection de l'utilisateur
}

// Actions réalisées
if(isset($_POST['select_action']) && $_POST['select_action']!=0)
{    
	$select_action_encours = $_POST['select_action'];	
	$where_and_action = " AND type_action=".$select_action_encours; // pour la selection commune	
}

// Tournées Délai - Délai de passage à une station
$delai_encours = 182;
if(isset($_POST['select_delai']))
{
	$delai_encours = $_POST['select_delai'];
    // cette ligne permet de générer une partie de la requête sql, pour ne considérer que les actions dont la date est inférieure à 'select_delai'
}
$having_delai = '';
if($delai_encours > 0){$having_delai = " HAVING DATEDIFF(NOW(), dateheure) <= ".$delai_encours;}

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


// TABLE ACTIONS TYPE
$sql_action_type = "SELECT DISTINCT id, type FROM ".TABLE_ACTIONS_TYPE." ORDER BY type ASC";
$action_type_query = tep_db_query($sql_link,$sql_action_type);
while ($action_type = tep_db_fetch_array($action_type_query))
{
    $id = $action_type['id'];
	$type = htmlaccent(html_entity_decode($action_type['type'] ?? $default_string));

	$action_type_array[$id] = $type;
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



//---------------------------------------------------------------
// TABLE SQL - Recupération de la liste des Actions à afficher

$nb_actions = 0;

$sql_action = "SELECT DISTINCT id, id_user, type_action, info, dateheure
				FROM ".TABLE_ACTIONS."
				WHERE 1=1".$where_and_user.$where_and_action. 
                $having_delai."                     
				ORDER BY dateheure DESC";

$action_query = tep_db_query($sql_link,$sql_action);
while ($action_tab = tep_db_fetch_array($action_query))
{	
	$id =  $action_tab['id'];
    $id_user_action =  $action_tab['id_user'];    
    $type_action =  $action_tab['type_action'];
	$info = htmlaccent(html_entity_decode($action_tab['info'] ?? $default_string));

    // Crée un objet DateTime pour la dernière date valide
    $dateheure = new DateTime($action_tab['dateheure']);
    // Formatte la date au format "d-m-Y HH:mm:ss"
    $dateheure_formatted = $dateheure->format('d-m-Y H:i:s');
	
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
  
    
    // Tableau station avec toutes les données	
	$action_array[$id] = array('id_user' => $id_user_action,
                                'type_action' => $type_action,
                                'info' => $info,
                                'dateheure' => $dateheure_formatted,
                                'delai_action' => $delai_action,
                                'text_delai' => $text_delai           
                                );
}
if(isset($action_array)){$nb_actions = sizeof($action_array);}


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
				
				echo "<span>".htmlaccent('Suivi des actions sur la Plateforme - HydroPacifique')."</span>";

			echo "</h1>";		
            
            $lien_form = tep_href_link('list_actions.php');			
            echo "<form name='form_actions' action='".$lien_form."' method='post' enctype='multipart/form-data' >";
			
                // Bloc haut pour filtrer la liste des actions
                echo "<div id='cadre_graph' style='float:left;width:14%;height:70vh;overflow-y: auto;'>\n"; 
                
                    echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding: 0 3%;'>\n";

                        // Filtre Utilisateur de la plateforme
                        echo "<p style='float:left;width:70px;margin-top:15px;padding-top:5px;'>".htmlaccent('Utilisateur')."</p>";

                        echo "<select name='select_user' id='select_user' onchange='form_actions.submit();' style='float:right;width:120px;margin-top:15px;'>";
                                                
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

                        // Filtre Action
                        echo "<p style='float:left;width:70px;padding-top:5px;'>".htmlaccent('Action')."</p>";

                        echo "<select name='select_action' id='select_action' onchange='form_actions.submit();' style='float:right;width:120px;'>";
                                                
                            echo "<option value='0'>-</option>";
                            
                            $selected = '';                 
                            if(isset($action_type_array))
                            {
                                foreach($action_type_array as $key => $value)
                                {
                                    if($key == $select_action_encours){$selected="selected";}	
                                    else{$selected = '';}											
                                    echo "<option value='".$key."' ".$selected.">".$value."</option>";
                                }
                            }
                        echo "</select>";

                        echo "<hr>\n";	                            
                        
                        // Filtre Délai
                        echo "<p style='float:left;width:70px;padding-top:5px;'>".htmlaccent('Délai')."</p>";
                                            
                        echo "<select name='select_delai' id='select_delai' onchange='form_actions.submit();' style='float:right;width:120px;'>";
                                
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


                        // Affichage nombre d'actions
                        echo "<div id='contenu_infos'>";
												
                            echo "<p>";
                                echo "<span style='margin:0px;'>".htmlaccent('Nombre d\'actions : ').number_format($nb_actions,0,'.',' ')."</span>";
                            echo "</p>";

                        echo "</div>";

						echo "<hr>";
                            
                    echo "</div>";	
                
                echo "</div>";
            
            echo "</form>";
			
			
			// Affichage Tableau
			if(isset($action_array) && ($nb_actions>0))
			{
                echo "<div class='table-container' style='float:left;width:80%;height:75vh;margin-left:1%;'>";

                    echo "<table id='table_tri' cellspacing='0'>";
                
                        echo "<thead>";
                            echo "<tr class='header-row'>";		
                                echo "<th style='width:90px;font-size:12px;padding-left:20px;'>".htmlaccent('Login')."</th>";						
                                echo "<th style='width:150px;font-size:12px;'>".htmlaccent('Prénom Nom')."</th>";                                    				
                                echo "<th style='width:130px;font-size:12px;'>".htmlaccent('Type')."</th>";                            
                                echo "<th style='width:90px;font-size:12px;'>".htmlaccent('Délai (en j.)')."</th>";
                                echo "<th style='width:150px;font-size:12px;'>".htmlaccent('Date de l\'action')."</th>";
                                echo "<th style='width:500px;font-size:12px;' >".htmlaccent('Détail')."</th>";                        
                            echo "</tr>";
                        echo "</thead>";
                
                        //ligne vide dans le tableau	
                        echo "<tr>";
                            echo "<td colspan='6' style='height:15px;'>&nbsp;</td>";
                        echo "</tr>";	
                            
                        foreach($action_array as $key => $value)
                        {	
                            if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                            else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
                            
                            echo "<tr ".$row_l." style='height:20px;'>";

                                echo "<td style='padding-left:20px;' >".$user_list_array[$value['id_user']]['login']."</td>\n"; 							
                                echo "<td>".$user_list_array[$value['id_user']]['prenom']." ".$user_list_array[$value['id_user']]['nom']."</td>\n";		

                                if(isset($action_type_array[$value['type_action']]))
                                {
                                    echo "<td>".$action_type_array[$value['type_action']]."</td>\n";						
                                }
                                else{echo "<td>-</td>";}	
                                
                                echo "<td style='padding-left:25px;'>".$value['delai_action']."</td>\n";					
                                echo "<td>".$value['dateheure']."</td>\n";
                                echo "<td>".$value['info']."</td>\n";                            
                                
                            echo "</tr>\n";
                            
                            $row++;
                        }
                        
                    echo "</table>";
                    
                echo "</div>";
			}
			else
			{
				echo "<div id='boxpopup' style='margin-left: 1%;'>\n";
					echo "<p class='alert'>".htmlaccent('Aucune - Action - n\'a été trouvée')."</p>";
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
