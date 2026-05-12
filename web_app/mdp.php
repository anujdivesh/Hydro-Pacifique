<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Formulaire pour changement de mot de passe de l'utilisateur
*/
require('include/application_top.php');

$message_info = '';


if(isset($_POST['id']) && tep_not_null($_POST['id'])){require(DIR_WS_FORMULAIRE . 'ctrl_mdp.php');}


// données admin
$admin_query = tep_db_query($sql_link,"SELECT * FROM ".TABLE_USER." WHERE id=".$tab_session['admin_id']);
$admin = tep_db_fetch_array($admin_query);



require(DIR_WS_STRUCTURE . 'header_web.php');

echo "<body>";

require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

echo "<div id='contour_general'>";
	
	echo "<div id='contenu_centre'>";
		
		echo "<div id='contenu_box2'>";
		
			echo "<h1><span>".htmlaccent('Modification de mon mot de passe')."</span></h1>"; 
			
			if(tep_not_null($message_info)){echo "<div id='contenu_info'>".$message_info."</div>";}
			
			echo "<hr>";
			
			echo "<div id='onglet_contenu' class='first'>\n";
								
				echo "<div id='box_result' style='padding-top:10px;'>\n";
				
					//FORMULAIRE
					$lien_form = tep_href_link('mdp.php');
					$name_form = 'mdp';
		
			
					echo "<form name='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data'>";
					echo tep_draw_hidden_field('id',$tab_session['admin_id'],'class=\'input_texte\'');
					echo tep_draw_hidden_field('old_pass_table', $admin['password'],'class=\'input_texte\'');
						
						echo "<div id='boite1'>";			
							echo "<h2>Ancien mot de passe</h2>";		
							echo  tep_draw_password_field('old_pass','','class=\'titre\'');			
						echo "<hr>";
						echo "</div>";
						
						echo "<div id='boite1'>";			
							echo "<h2>Nouveau mot de passe</h2>";		
								echo "<input name='new_pass' maxlength='40' class='titre' type='password' onKeyUp='evalpass(this.value);'><br>";	
								echo "<ul class='quality_pass'>";
									echo "<li id='faible' class='li_mdp'>faible</li>";
									echo "<li id='moyen' class='li_mdp'>moyen</li>";
									echo "<li id='fort' class='li_mdp'>fort</li>";
								echo "</ul>";	
						echo "<hr>";
						echo "</div>";
						
						echo "<div id='boite1'>";			
							echo "<h2>Retaper le nouveau mot de passe </h2>";		
							echo tep_draw_password_field('new_pass_confirm','','class=\'titre\'');			
						echo "<hr>";
						echo "</div>";
				
				echo "<hr>\n";
				echo "</div>\n";		
				
				echo "<hr>\n";
											
				//BOUTON
				echo "<input type='button' class='button' value='Enregistrer' onClick='".$name_form.".submit();' />";
				echo "</form>";
			
			echo "<hr>\n";
			echo "</div>\n";		
			
		echo "<hr>\n";
		echo "</div>\n";
	
	echo "<hr>";
	echo "</div>";
		
	
echo "<hr>";
echo "</div>";
	
	require('include/application_bottom.php'); 
	
echo "</body>";

echo "</html>";




?>
