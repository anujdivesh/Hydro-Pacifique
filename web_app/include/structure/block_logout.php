<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/
$today = date('d-m-Y'); 
$class_bv="style='display:block;'";


$tab_session = getAdminInfo();
if(isset($tab_session['id'])){tep_db_query($sql_link,"UPDATE ".TABLE_SESSION." SET sid = '' WHERE  id = " . $tab_session['id']);}



echo "<div id='block_view_log' class='block_view' ".$class_bv.">\n";

	echo "<p style='font-family: \"Open Sans,arial\";margin-top:100px;font-weight: 300;font-size:38px;-webkit-font-smoothing: antialiased;color: #000;background-color:#fff;padding: 20px 0;'>".htmlaccent('Vous avez bien été déconnecté de ').TITRE_SITE."</p> \n";
	
	echo "<div id='cadre_view' class='cadre_view' style='width: 335px;height:80px;margin-top: 0px;'>";
	
		echo "<div id='cadre_limit'>";
			
			echo "<div id='log' style='margin-top: 0px;'> \n";
					
				echo "<input type='submit' class='button' value='Retour' onClick=\"location.replace('login.php')\"/> \n";
			
				echo "</form> \n";
		
		echo "</div>\n";	
		
	echo "</div>\n";
	

echo "</div>\n";

?>


