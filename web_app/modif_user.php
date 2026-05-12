<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/

require('include/application_top.php');

$action = false;

$row = 0;
$reference = '';
$libelle = '';
$today = date('d-m-Y H:i:s'); 

$modif=false;

if(isset($_GET['ref']) && tep_not_null($_GET['ref'])){$ref_id = post_secure($sql_link,$_GET['ref']);$modif=true;}
if(isset($_POST['button_save'])){require(DIR_WS_FORMULAIRE . 'ctrl_user.php');}

$gestion_data_u=0;
$parametre_u=0;
$config_u=0;

if($modif)
{
	$sql = "SELECT DISTINCT id, login, nom, prenom, info FROM ".TABLE_USER;
	$where = " WHERE id=".$ref_id;
	$eq_query = tep_db_query($sql_link,$sql.$where);
	$user = tep_db_fetch_array($eq_query);
	
	$login =  htmlaccent(post_secure($sql_link,$user['login']));
	
	// éviter de créer un utilisateur faux... ou caché
	//if($user['admin']==1 || !tep_not_null($login)){tep_redirect('list_users.php');}
	
	$nom =  htmlaccent(post_secure($sql_link,$user['nom']));
	$prenom =  htmlaccent(post_secure($sql_link,$user['prenom']));
	$info =  htmlaccent(post_secure($sql_link,$user['info']));
	
	// droits d'accès
	$sql_acces = "SELECT DISTINCT gestion_data, parametre, config FROM ".TABLE_USER_ACCES;
	$where_acces = " WHERE id=".$ref_id;
	$acces_query = tep_db_query($sql_link,$sql_acces.$where_acces);
	$acces = tep_db_fetch_array($acces_query);

	$gestion_data_u=post_secure($sql_link,$acces['gestion_data']);
	$parametre_u=post_secure($sql_link,$acces['parametre']);
	$config_u=post_secure($sql_link,$acces['config']);
}

require(DIR_WS_STRUCTURE . 'header_web.php');

echo "<body>";

require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

echo "<div id='contour_general'>";
	
	echo "<div id='contenu_centre'>";
		
		echo "<div id='contenu_box2'>";
		
			
			echo "<h1>";
				
				echo "<span>";			
					if($modif){echo htmlaccent('Utilisateur : '.$prenom.' '.$nom);}
					else{echo htmlaccent('Nouvel utilisateur');}
				echo "</span>";
				
				echo button_return('list_users.php');
				
			echo "</h1>";
			

			if($action)
			{
				$border_info = 'border:4px solid #09886d;'; // par défaut l'action s'est bien passée bordure verte
				if(!$action_result){$border_info = 'border:4px solid #930000;';} // Si erreur dans la suppression alors bordure rouge
				
				echo "<div id='contenu_info' style='".$border_info."'>".$message_action."</div>";
			}
	
	
			//FORMULAIRE
			if($modif){$lien_form = tep_href_link('modif_user.php?ref='.$ref_id);}
			else{$lien_form = tep_href_link('modif_user.php');}
			$name_form = 'user';
			
			echo "<form name='" . $name_form . "' action='" . $lien_form . "' method='post' enctype='multipart/form-data'>";

				echo "<div id='onglet'>";
					echo "<ul id='menu_onglet'>";
					
						echo "<li onClick=\"javascript:ChangeOnglet_2(1, 2, 'onglet-', 'contenu-');\" id='onglet-1' class='actif'>".htmlaccent('Information')."</li>\n";
						echo "<li onClick=\"javascript:ChangeOnglet_2(2, 2, 'onglet-', 'contenu-');\" id='onglet-2'>".htmlaccent('Droits d\'accès')."</li>\n";
										
					echo "</ul>";
					
					echo "<div id='contenu-1' class='contenu'>";
					
						require(DIR_WS_STRUCTURE . 'form_user_1.php');
				
					echo "</div>";
					
					echo "<div id='contenu-2' class='contenu' style='display:none;'>";
					
						require(DIR_WS_STRUCTURE . 'form_user_2.php');
				
					echo "</div>";
					
				echo "</div>";
					
				echo "<br>";
				
				echo "<input type='submit' class='button' name='button_save' value=\"Enregistrer\" />";
			
			echo "</form>\n";
			
					
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
