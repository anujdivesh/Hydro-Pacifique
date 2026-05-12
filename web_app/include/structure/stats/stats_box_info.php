<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/

echo "<h8>".htmlaccent('Informations générales')."</h8>";
	
echo "<table id='resume' cellspacing='0'>";
	
	echo "<tr class='grey'>";
		echo "<td class='bold'>".htmlaccent('Île')."</td>";
		echo "<td style='text-align:right;'>".$nom_region."</td>";
	echo "</tr>";
	
	echo "<tr>";
		echo "<td class='bold'>".htmlaccent('Station')."</td>";
		echo "<td style='text-align:right;'>".$nom_station."</td>";
	echo "</tr>";
	
	echo "<tr class='grey'>";
		echo "<td class='bold'>".htmlaccent('Code Station')."</td>";
		echo "<td style='text-align:right;'>".$code_station."</td>";
	echo "</tr>";
	
	echo "<tr>";
		echo "<td class='bold'>".htmlaccent('Bassin Versant')."</td>";
		echo "<td style='text-align:right;'>".$vallee_station."</td>";
	echo "</tr>";
	
	echo "<tr class='grey'>";
		echo "<td class='bold'>".htmlaccent('Altitude (en mètre)')."</td>";
		echo "<td style='text-align:right;'>".$altitude_station."</td>";
	echo "</tr>";
	
	echo "<tr>";
		echo "<td class='bold'>".htmlaccent('Coordonnées (latitude - longitude)')."</td>";
		echo "<td style='text-align:right;'>".$latitude_station." - ".$longitude_station."</td>";
	echo "</tr>";
		
echo "</table>";



?>
