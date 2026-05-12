<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Page de logout
On déconnecte la session et on coupe l'accès à la BDD
*/

require('include/application_top.php');

// On su^pprime la session en cours lié à l'utilisateur
tep_db_query($sql_link,"DELETE FROM ".TABLE_SESSION." WHERE id=".$tab_session['id']);

require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

	echo "<div id='contour_general' style='width: 100%;margin:0;'>"; 
			
		echo "<div id='log_out'>";
			
			echo "<p>Vous êtes sur l'application <span style='color: #336699;'>".TITRE_SITE."</span></p>";
			
			echo "<p><span style='font-weight: bold;'>Votre session est terminée</span> </p>";
			
			echo "<p style='margin-bottom:30px;'> - <a href='login.php'>Connexion</a> - </p>";
			
		echo "</div>";

		if($autorisation){regenerer_id($sql_link);}
		tep_db_close($sql_link);
		tep_session_end();
		
			
	echo "</div>";

echo "</body>";
	
echo "</html>";

?>