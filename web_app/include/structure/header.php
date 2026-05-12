<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
*/


echo "<div id='bando' style='background-color:".$color_service.";'>";

	echo "<a href='index.php' title='".TEXT_TOP_FIRST."'><img src='".DIR_WS_IMG."header_logo_header.png' style='height:50px;'></a>";

	echo "<div id='nav_icon' style='float:left;margin-left:20px;' >";	
	
		echo "<p style='float:left;margin-top:-5px;padding:5px 0;border-top:1px solid #000;text-align:left;border-bottom:1px solid #000;text-align:left;'>";
			echo "<span style='font-weight:bold;'>".TEXT_TOP_VERSION_HP." </span>".VERSION_HP;
			echo "<br>";
			echo "<span style='font-weight:bold;'>".TEXT_TOP_DATE_HP." </span>".DATE_VERSION_HP;
		echo "</p>";
	
	echo "</div>";
	
	echo "<div id='top_center'>";	
	
		echo "<span style='font-weight:bold;'>".TEXT_TOP_COUNTRY." </span>".$territoire_nom;
		//echo $territoire_nom;
	
	echo "</hr>";	
	echo "</div>";
	
	echo "<div id='nav_icon' >";	
	
		//if($tab_session['group_id']==1){echo "<a href='gestion.php' title='gestion'><div class='gestion'></div></a>";}
		echo "<p style='float:left;margin-right:20px;margin-top:-5px;padding:5px 0;border-top:1px solid #000;border-bottom:1px solid #000;text-align:left;'>";
			echo "<span style='font-weight:bold;'>".TEXT_TOP_LOG." </span>".$prenom_user.' '.$nom_user;
			echo "<br>";
			echo "<span style='font-weight:bold;'>".TEXT_TOP_LOG_QUAL." </span>".$info_user;
		echo "</p>";
		
		if($config==1){echo "<a href='gestion.php' title='".TEXT_TOP_ADMIN."'><div class='gestion'></div></a>";}
		echo "<a href='mdp.php' title='".TEXT_TOP_PASS."'><div class='mdp'></div></a>";
		echo "<a href='logout.php' title='".TEXT_TOP_CLOSE."'><div class='close'></div></a>";
	
	echo "</div>";	
	
	
echo "</div>";



?>