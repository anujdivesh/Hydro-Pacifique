<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/
$today = date('d-m-Y'); 
$style_display='';
if(tep_not_null($message_info)){$style_display="display:block;";}



echo "<div id='block_view_log' class='block_view'  style='background-image:none;".$style_display."'>\n";

	
	echo "<div id='cadre_view_titre'>";

		/*
		echo "<span style='font-weight: bold;font-size:32px;'>";
			echo htmlaccent('Connect');
		echo "</span>";
		*/
		echo "<br>";
		echo TITRE_SITE;
	echo "</div> \n";
		
		//echo "<div id='cadre_view' class='cadre_view' style='top:200px;width: 335px;;margin-left: -167px;'>";
		echo "<div id='cadre_view_log'>";			
				
				echo "<div id='log' style='margin-top: 0px;'> \n";		
					
						echo "<img src='".DIR_WS_IMG_ICO."avatar.png' >";
						
						echo "<hr> \n";
										
						if(tep_not_null($message_info)){echo "<p class='label'>" . $message_info . "</p> \n";}
						
						echo "<hr> \n";
						
						$lien_login = tep_href_link('login.php');
						echo  "<form name='login' action='" . $lien_login . "' method='post' enctype='multipart/form-data'> \n";
						
							//echo "<span class='info' >Login</span><hr>\n";
							echo "<input name='login' maxlength='40' placeholder='Login' type='text'> \n";
							
							echo "<hr>\n";
							
							//echo "<span class='info'>Mot de passe</span><hr>\n";
							echo "<input name='password' maxlength='40' type='password' placeholder='Password'> \n";
							
							echo "<hr> \n";
							
							echo "<input type='submit' class='button' value='Connect' onClick='login.submit();'/> \n";
							
							echo "<hr> \n";
							
							echo "<p><a onClick=\"document.getElementById('block_view_log').style.display='none';\">".htmlaccent('Cancel')."</a></p> \n";
					
						echo "</form> \n";
				
				echo "</div>\n";
			
		echo "</div>\n";	
			
	echo "</div>\n";
	

echo "</div>\n";

?>


