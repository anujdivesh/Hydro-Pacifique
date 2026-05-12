<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/




echo "<div id='onglet_contenu'>\n";

	// Importation
	echo "<div id='gestion_droit'>\n";
			
		echo "<h3>".htmlaccent('Gestion des droits et des autorisations')."</h3>"; 
		
		echo "<table>";
		
			echo "<tr>";
				echo "<td>";
					$check = '';
					if($gestion_data_u==1){$check = 'checked';}
					echo "<input type='checkbox' name='gestion_data' id='gestion_data' ".$check.">";
				echo "</td>";
				echo "<td>".htmlaccent('Gestion des données')."</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
					$check = '';
					if($parametre_u==1){$check = 'checked';}
					echo "<input type='checkbox' name='parametre' id='parametre' ".$check.">";
				echo "</td>";
				echo "<td>".htmlaccent('Paramétrages')."</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td>";
					$check = '';
					if($config_u==1){$check = 'checked';}
					echo "<input type='checkbox' name='config' id='config' ".$check.">";
				echo "</td>";
				echo "<td>".htmlaccent('Configuration de l\'application')."</td>";
			echo "</tr>";
			
		
		echo "</table>";
		
		
	echo "<hr>";
	echo "</div>";
	
	
echo "<hr>\n";
echo "</div>\n";
?>
