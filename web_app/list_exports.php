<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Affichage de la Liste des Exports réalisées sur la plateforme
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

$delai_encours = 0;
$having_delai = '';



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
	$where_and_action = " AND type=".$select_action_encours; // pour la selection commune	
}

// Tournées Délai - Délai de passage à une station
if(isset($_POST['select_delai']) && $_POST['select_delai']!=0)
{
	$delai_encours = $_POST['select_delai'];
    // cette ligne permet de générer une partie de la requête sql, pour ne considérer que les actions dont la date est inférieure à 'select_delai'
	$having_delai = " HAVING DATEDIFF(NOW(), dateheure) <= ".$delai_encours;
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

$sql_action = "SELECT DISTINCT id, id_user, type_action, info, dateheure, file_export
				FROM ".TABLE_ACTIONS."
				WHERE type_action=36
                AND dateheure >= DATE_SUB(NOW(), INTERVAL 24 MONTH)              
				ORDER BY dateheure DESC";

$action_query = tep_db_query($sql_link,$sql_action);
while ($action_tab = tep_db_fetch_array($action_query))
{	
	$id =  $action_tab['id'];
    $id_user =  $action_tab['id_user'];    
    //$type_action =  $action_tab['type_action'];
	$info = htmlaccent(html_entity_decode($action_tab['info'] ?? $default_string));
    
    $file_export =  $action_tab['file_export'];
    $file_info = strstr($file_export, '.', true).'.txt'; // true pour récupérer la partie avant le premier point

    $file_exist = false;
    if(file_exists(DIR_WS_DATA_EXPORT.$action_tab['file_export'])){$file_exist = true;}

    $file_exist_txt = false;
    if(file_exists(DIR_WS_DATA_EXPORT.$file_info)){$file_exist_txt = true;}

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
	$action_array[$id] = array('id_user' => $id_user,
                                'info' => $info,
                                'dateheure' => $dateheure_formatted,
                                'file_export' => $file_export,
                                'file_exist' => $file_exist,
                                'file_info' => $file_info,
                                'file_exist_txt' => $file_exist_txt        
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
				
				echo "<span>".htmlaccent('Suivi des derniers Exports de données - 24 mois')."</span>";

			echo "</h1>";		
            
            // Bloc haut d'information
            echo "<div id='cadre_graph' style='float:left;width:18%;height:70vh;overflow-y: auto;padding: 2px;'>\n"; 
            
                //echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding: 3%;'>\n";

                    echo "<div style='float:left;background-color:#fff;margin:1%;padding: 3% 5%;box-shadow: 1px 1px 6px #555;'>";

                        echo "<img src='".DIR_WS_IMG_ICO."time.png' style='float:left;width:50px;margin-top:0px;'>";
                        
                        echo "<p style='float:left;width:75%;margin-left:5%;text-align:left;font-size:14px;font-weight:bold;'>";

                            echo htmlaccent('Les fichiers de données sont disponibles au téléchargement pendant 1 mois');

                        echo "</p>";    

                    echo "</div>";	
                        
                //echo "</div>";	
            
            echo "</div>";
        
			
			// Affichage Tableau
			if(isset($action_array) && ($nb_actions>0))
			{
                echo "<div class='table-container' style='float:left;width:60%;height:75vh;margin-left:2%;'>";

                    echo "<table id='table_tri' cellspacing='0'>";
                
                        echo "<thead>";
                            echo "<tr class='header-row'>";		
                                echo "<th style='width:90px;font-size:12px;padding-left:20px;'>".htmlaccent('Login')."</th>";						
                                echo "<th style='width:150px;font-size:12px;'>".htmlaccent('Prénom Nom')."</th>";         
                                echo "<th style='width:150px;font-size:12px;'>".htmlaccent('Date')."</th>";          
                                echo "<th style='width:100px;font-size:12px;text-align: center;'>".htmlaccent('Détails')."</th>";
                                echo "<th style='width:150px;font-size:12px;'>".htmlaccent('Fichier à télécharger')."</th>";

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

                                echo "<td style='width:90px;padding-left:20px;' >".$user_list_array[$value['id_user']]['login']."</td>\n"; 							
                                echo "<td style='width:150px;'>".$user_list_array[$value['id_user']]['prenom']." ".$user_list_array[$value['id_user']]['nom']."</td>\n";								
                                echo "<td style='width:150px;' >".$value['dateheure']."</td>\n";
                                echo "<td style='width:100px;text-align: center;'>";
                                    if($value['file_exist_txt'])
                                    {
                                        echo "<a href='".DIR_WS_DATA_EXPORT.$value['file_info']."' target='blank_' >"; 
                                            echo "<img src='".DIR_WS_IMG_ICO."detail.png' style='width:20px;cursor:pointer;'>";							
                                        echo "</a>";
                                    }
                                    else{echo '-';}  
                                echo "</td>\n"; 
                                echo "<td style='width:300px;'>";
                                    if($value['file_exist']){echo "<a href='".DIR_WS_DATA_EXPORT.$value['file_export']."' download>".$value['file_export']."</td>\n";}
                                    else{echo '-';}                             
                                
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
