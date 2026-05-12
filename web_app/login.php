<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
*/

require('include/application_top.php');

//---------------------------------------------

$message_info = '';

//Verif si session en cours avec même numéro
$tab_session = getAdminInfo($sql_link);
if(isset($tab_session['admin_id']) && tep_not_null($tab_session['admin_id'])){tep_redirect('index.php');}

if(isset($_POST['login']) && isset($_POST['password']))
{
	if(ip_login_enforce($sql_link))
	{	
		$message_info = "!!! Tentative d'accés protégé en force !!!<br><br>";	
		tep_redirect('error.html');
		tep_db_close($sql_link);
		die();
	}
	
	$login = mysqli_real_escape_string($sql_link,trim(addslashes($_POST['login'])));
	$password = mysqli_real_escape_string($sql_link,trim(addslashes($_POST['password'])));
	
	// recuperation des donnees de l'administrateur*/
  	$user_query = tep_db_query($sql_link,"SELECT id, password FROM ".TABLE_USER." WHERE active=1 AND login = '".$login."'");
  	$user = tep_db_fetch_array($user_query);
  	
  	
  	if(tep_not_null($user))
    {	
    	/* validation pass */
		if (tep_validate_password($password, $user['password'])) 
		{
			if(!double_connexion($sql_link,$user['id'],$login))
			{
				tep_db_query($sql_link,"UPDATE " . TABLE_USER . " SET last_log = now(), nb_log = nb_log + 1 WHERE id = '" . $user['id'] . "'");
		 		tep_db_query($sql_link,"INSERT INTO " . TABLE_SESSION . " (sid,". 
		 								"admin_id," .
		 								"date_connect," .
		 								"heure_connect," .
		 								"last_access," .
		 								"ip," .
		 								"browser)" .
		 								" VALUES('" . session_id() .
		 									 "'," . $user['id'] . 
		 									 ",now()" .
		 									 ",current_time()" .
		 									 "," . time() .	 
		 									 ",'" . getIP() .
		 									 "','" . getUser_agent() . "')");
		 		tep_redirect('index.php');						 
		 	}
		 	else
			{
				tep_db_query($sql_link,"UPDATE ".TABLE_SESSION." SET sid='' WHERE admin_id=".$user['id']);
				$message_info = htmlaccent('Une connexion en cours est détectée avec cet identifiant');
				$message_info .= htmlaccent('<br>Par mesure de protection la session a été fermée.');
				$message_info .= htmlaccent('<br>Veuillez vous reconnecter.');
			}
		} 
		else{$message_info = 'Les informations de connexion sont incorrectes';}
	  
	}
	else{$message_info = 'Les informations de connexion sont incorrectes';}
}

//---------------------------------------------


require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body style='background-color: #000;'>";


	echo "<div style='margin-bottom:20px;height:30px;padding: 5px 0;background-color:".$color_service.";'>";
		echo "<p style='float:right;margin-right:5%;font-size:24px;'>";	
			echo htmlaccent(TITRE_T);
		echo "</p>";				
	echo "</div>";	

	echo "<div id='fond_log_img'>";

		echo "<img src='".DIR_WS_IMG."fond_index.jpg' style='width:100%;'>";

	echo "</div>";
		
	echo "<div style='margin-left:43%;'>";

		echo "<a href='#' onClick=\"document.getElementById('block_view_log').style.display='block';\">";
			echo "<div id='index_bconnect'>".htmlaccent('Log in')."</div>";
		echo "</a>";
	
	echo "</div>";	

	echo "<div id='fond_log_img' style='margin-top:15%;'>";

		echo "<img src='".DIR_WS_IMG."fond_poweredvn.jpg' style='width:100%;'>";

	echo "</div>";
	
	// Redirection Logout
	if(isset($_GET['log']) && $_GET['log']=='out')
	{		
		require(DIR_WS_STRUCTURE . 'block_logout.php');
	}
	else{require(DIR_WS_STRUCTURE . 'block_login.php');}
	

	require('include/application_bottom.php'); 
	
echo "</body>";

echo "</html>";


?>