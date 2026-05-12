<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Page d'affichage de la liste des utilisateurs de la plateforme
*/

require('include/application_top.php');

$action = false;

$row = 0;


if(isset($_GET['del']) && tep_not_null($_GET['del'])){require(DIR_WS_SUPPRIMER . 'suppr_user.php');}
if(isset($_POST['button_save'])){require(DIR_WS_FORMULAIRE . 'ctrl_user_active.php');}


/* requête sql pour récupérer les données articles */

$sql_tech = "SELECT DISTINCT id, login, nom, prenom, date_creation, last_log, nb_log, active FROM ".TABLE_USER." WHERE admin=0 ORDER BY login";
$user_query = tep_db_query($sql_link,$sql_tech);
while ($user = tep_db_fetch_array($user_query))
{		
	$login =  htmlaccent(post_secure($sql_link,$user['login']));
	$nom =  htmlaccent(post_secure($sql_link,$user['nom']));
	$prenom =  htmlaccent(post_secure($sql_link,$user['prenom']));
	$date_creation =  post_secure($sql_link,$user['date_creation']);
	$last_log =  post_secure($sql_link,$user['last_log']);
	$nb_log =  post_secure($sql_link,$user['nb_log']);
	$active =  post_secure($sql_link,$user['active']);
	
	
	$user_array[] = array('id' => $user['id'],
						 'login' => $login,
						 'nom' => $nom,
						 'prenom' => $prenom,
						 'date_creation' => $date_creation,
						 'last_log' => $last_log,
						 'active' => $active,
						 'nb_log' => $nb_log);
	
}



require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

echo "<div id='contour_general'>";
	
	echo "<div id='contenu_centre'>";
			
		echo "<div id='contenu_box2'>";
		
			if($action)
			{
				$border_info = 'border:4px solid #09886d;'; // par défaut l'action s'est bien passée bordure verte
				if(!$action_result){$border_info = 'border:4px solid #930000;';} // Si erreur dans la suppression alors bordure rouge
				
				echo "<div id='contenu_info' style='".$border_info."'>".$message_action."</div>";
			}


			
			$lien_form = tep_href_link('list_users.php');
			$name_form = 'user';
			
			echo "<form name='" . $name_form . "' action='" . $lien_form . "' method='post' enctype='multipart/form-data'>";
			
				echo "<h1>";
					
					echo "<span>".htmlaccent('Modifications et droits des utilisateurs')."</span>";	

					echo "<input type='submit' class='button' name='button_save' value='Enregistrer' style='float:right;margin-left:30px;;'/>";
					echo button_return('gestion.php');
					
				echo "</h1>";

				echo "<table id='table_tri' cellspacing='0'>";
			
					echo "<thead>";
						echo "<tr>";
															
							echo "<th>".htmlaccent('Login')."</th>";
							echo "<th>".htmlaccent('Nom')."</th>";
							echo "<th>".htmlaccent('Prénom')."</th>";
							echo "<th>".htmlaccent('Date de création')."</th>";
							echo "<th>".htmlaccent('Date de dernière connection')."</th>";
							echo "<th>".htmlaccent('Nb connections')."</th>";
							echo "<th style='text-align:center;'>".htmlaccent('Actif')."</th>";
							echo "<th style='text-align:center;'>&nbsp;</th>";

					   echo "</tr>";
					echo "</thead>";
			
							
						if(isset($user_array))
						{
							for($c=0;$c<sizeof($user_array);$c++)
							{	
								if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
								else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
								
								
								echo "<tr ".$row_l." >";
								
									$lien_modif = "modif_user.php?ref=".$user_array[$c]['id'];	
									echo "<td class='t_cont_m' onClick=\"location.href='".$lien_modif."';\" style='cursor:pointer;'>" . $user_array[$c]['login'] . "</td>\n";
									echo "<td class='t_cont_m onClick=\"location.href='".$lien_modif."';\" style='cursor:pointer;''>" . $user_array[$c]['nom'] . "</td>\n";
									echo "<td class='t_cont_m' onClick=\"location.href='".$lien_modif."';\" style='cursor:pointer;'>" . $user_array[$c]['prenom'] . "</td>\n";
									
									echo "<td class='t_cont_l' onClick=\"location.href='".$lien_modif."';\" style='cursor:pointer;'>" . $user_array[$c]['date_creation'] . "</td>\n";
									echo "<td class='t_cont_l' onClick=\"location.href='".$lien_modif."';\" style='cursor:pointer;'>" . $user_array[$c]['last_log'] . "</td>\n";
									echo "<td class='t_cont'  onClick=\"location.href='".$lien_modif."';\" style='width:100px;text-align:center;cursor:pointer;'>" . $user_array[$c]['nb_log'] . "</td>\n";
									echo "<td class='t_cont' style='width:100px;text-align:center;'>";
										$check = '';
										if($user_array[$c]['active'] == 1){$check = 'checked';}
										echo "<input type='checkbox' name='active_".$user_array[$c]['id']."' id='active_".$user_array[$c]['id']."' ".$check.">";
									echo "</td>\n";
									
									// supprimer
									echo "<td class='t_icon' style='width:100px;text-align:center;'>";
										$lien_suppr = "list_users.php?del=".$user_array[$c]['id'];
										echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:16px;cursor:pointer;' title='".htmlaccent('Supprimer')."' onClick=\"confirm_suppr('" . $lien_suppr . "','l‘utilisateur','".$user_array[$c]['nom']." ".$user_array[$c]['prenom']."');\">";
									echo "</td>\n";
								
								echo "</tr>\n";

								$row++;
							}
						}
				
				echo "</table>";


				//Boutton
				echo "<hr>";
				echo "<input type='submit' class='button' name='button_save' value='Enregistrer' style='margin-top:30px;'/>";
			
			echo "</form>";
	
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
