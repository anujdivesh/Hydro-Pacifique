<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/







echo "<div id='box_graph' class='lgt_r'>";
	
	echo "<h8>".htmlaccent('Navigation')."</h8>";
	
	
	
	
	
	if($periode == 1)
	{
		echo "<table id='resume' cellspacing='0'>";
			
			echo "<tr class='grey'>";
				
				echo "<td class='bold'>".htmlaccent('Jour - Mois - Année')."</td>";
				
				echo "<td style='text-align:right;'>";
				
					//Stats Day
					echo "<div id='stats_day'>\n";
						
						echo "<div style='float:right;'>\n";	
							echo "<select name='annee_d' id='annee_d' style='width:60px;'  onchange='stats_select.submit();'>";
								$annee_temp = 0;
								$annee = 0;
								for($a=0;$a<sizeof($tab_annee);$a++)
								{
									$selected='';if($tab_annee[$a]==$year_p1){$selected='selected';}
									echo "<option value='".$tab_annee[$a]."'  ".$selected.">".$tab_annee[$a]."</option>";
								}
							echo "</select>";
						echo "</div>\n";
						
						
						echo "<div style='float:right;'>";
							echo select_mois('month_day',$month_p1,'stats_select');
						echo "</div>";
						
						
						echo "<div style='float:right;'>";
							echo "<select name='day_stats' id='day_stats' style='width:50px;'  onchange='stats_select.submit();'>";
								for($a=1;$a<=31;$a++)
								{
									$selected='';if($a==$day_p1){$selected='selected';}
									echo "<option value='".$a."' ".$selected.">".$a."</option>";
								}
							echo "</select>";
						echo "</div>";
							
					echo "</div>";
				
				echo "</td>";
			echo "</tr>";
			
			if($debit_tarage)
			{
				echo "<tr>";	
					
					echo "<td class='bold'>";
						if($type_data==3){echo htmlaccent('Consulter les données limnimétriques');}
						if($type_data==2){echo htmlaccent('Convertir en débit');}
					
					echo "</td>";
					
					echo "<td style='text-align:right;'>";
						if($type_data==3){echo "<input type='submit' class='button' name='button_stats' value='Consulter' style='float:right;width:100px;height:18px;margin-top:10px;padding:0;'>";}
						if($type_data==2){echo "<input type='submit' class='button' name='button_stats' value='Convertir' style='float:right;width:100px;height:18px;margin-top:10px;padding:0;'>";}
						
					echo "</td>";
					
				echo "</tr>";
			}
			
				
		echo "</table>";			
			
	}
	
	
	
	
	if($periode == 2)
	{
		echo "<table id='resume' cellspacing='0'>";
			
			echo "<tr class='grey'>";
				
				echo "<td class='bold'>".htmlaccent('Mois - Année')."</td>";
				
				echo "<td style='text-align:right;'>";
				
					//Stats Month
					echo "<div id='stats_month'>\n";
						echo "<div style='float:right;'>\n";	
							echo "<select name='annee_m' id='annee_m' style='width:60px;' onchange='stats_select.submit();'>";
								$annee_temp = 0;
								$annee = 0;
								for($a=0;$a<sizeof($tab_annee);$a++)
								{
									$selected='';if($tab_annee[$a]==$year_p1){$selected='selected';}
									echo "<option value='".$tab_annee[$a]."'  ".$selected.">".$tab_annee[$a]."</option>";
								}
							echo "</select>";
						echo "</div>\n";
						
						echo "<div style='float:right;'>";
							echo select_mois('month_stats',$month_p1,'stats_select');
						echo "</div>";
					
					echo "</div>\n";
					
				echo "</td>";
			echo "</tr>";
			
			if($debit_tarage)
			{
				echo "<tr>";	
					
					echo "<td class='bold'>";
						if($type_data==3){echo htmlaccent('Consulter les données limnimétriques');}
						if($type_data==2){echo htmlaccent('Convertir en débit');}
					
					echo "</td>";
					
					echo "<td style='text-align:right;'>";
						if($type_data==3){echo "<input type='submit' class='button' name='button_stats' value='Consulter' style='float:right;width:100px;height:18px;margin-top:10px;padding:0;'>";}
						if($type_data==2){echo "<input type='submit' class='button' name='button_stats' value='Convertir' style='float:right;width:100px;height:18px;margin-top:10px;padding:0;'>";}
						
					echo "</td>";
					
				echo "</tr>";
			}
			
				
		echo "</table>";			
			
	}
	
	
	
	
	if($periode == 3)
	{
		echo "<table id='resume' cellspacing='0'>";
			
			echo "<tr class='grey'>";
				
				echo "<td class='bold'>".htmlaccent('Année')."</td>";
				
				echo "<td style='text-align:right;'>";
				
					// Stats Years
					echo "<div id='stats_year'>\n";
						echo "<select name='select_year' style='width:60px;' onchange='stats_select.submit();'>";
							$annee_temp = 0;
							$annee = 0;
							for($a=0;$a<sizeof($tab_annee);$a++)
							{
								$selected='';if($tab_annee[$a]==$year_p1){$selected='selected';}
								echo "<option value='".$tab_annee[$a]."'  ".$selected.">".$tab_annee[$a]."</option>";
							}
						echo "</select>";
					echo "</div>\n";
				
				
				echo "</td>";
			echo "</tr>";
			
			
			if($debit_tarage)
			{
				echo "<tr>";	
					
					echo "<td class='bold'>";
						if($type_data==3){echo htmlaccent('Consulter les données limnimétriques');}
						if($type_data==2){echo htmlaccent('Convertir en débit');}
					
					echo "</td>";
					
					echo "<td style='text-align:right;'>";
						if($type_data==3){echo "<input type='submit' class='button' name='button_stats' value='Consulter' style='float:right;width:100px;height:18px;margin-top:10px;padding:0;'>";}
						if($type_data==2){echo "<input type='submit' class='button' name='button_stats' value='Convertir' style='float:right;width:100px;height:18px;margin-top:10px;padding:0;'>";}
						
					echo "</td>";
					
				echo "</tr>";
			}
				
		echo "</table>";		
			
	}
	
	
	
	
	
	
	
	if($periode == 4)
	{
		echo "<table id='resume' cellspacing='0'>";
			
			echo "<tr class='grey'>";
				
				echo "<td class='bold'>".htmlaccent('du')."</td>";
				
				echo "<td style='text-align:right;'>";
				
					//Stats Day
					echo "<div id='stats_perso'>\n";
						
						echo "<div style='float:right;'>\n";	
							echo "<select name='year_p1' id='year_p1' style='width:60px;'  onchange='stats_select.submit();'>";
								$annee_temp = 0;
								$annee = 0;
								for($a=0;$a<sizeof($tab_annee);$a++)
								{
									$selected='';if($tab_annee[$a]==$year_p1){$selected='selected';}
									echo "<option value='".$tab_annee[$a]."'  ".$selected.">".$tab_annee[$a]."</option>";
								}
							echo "</select>";
						echo "</div>\n";
						
						
						echo "<div style='float:right;'>";
							echo select_mois('month_p1',$month_p1,'stats_select');
						echo "</div>";
						
						
						echo "<div style='float:right;'>";
							echo "<select name='day_p1' id='day_p1' style='width:50px;'  onchange='stats_select.submit();'>";
								for($a=1;$a<=31;$a++)
								{
									$selected='';if($a==$day_p1){$selected='selected';}
									echo "<option value='".$a."' ".$selected.">".$a."</option>";
								}
							echo "</select>";
						echo "</div>";
							
					echo "</div>";
				
				echo "</td>";
			echo "</tr>";
			
			
			
			echo "<tr class='grey'>";
				
				echo "<td class='bold'>".htmlaccent('au')."</td>";
				
				echo "<td style='text-align:right;'>";
				
					//Stats Day
					echo "<div id='stats_perso'>\n";
						
						echo "<div style='float:right;'>\n";	
							echo "<select name='year_p2' id='year_p2' style='width:60px;'  onchange='stats_select.submit();'>";
								$annee_temp = 0;
								$annee = 0;
								for($a=0;$a<sizeof($tab_annee);$a++)
								{
									$selected='';if($tab_annee[$a]==$year_p2){$selected='selected';}
									echo "<option value='".$tab_annee[$a]."'  ".$selected.">".$tab_annee[$a]."</option>";
								}
							echo "</select>";
						echo "</div>\n";
						
						
						echo "<div style='float:right;'>";
							echo select_mois('month_p2',$month_p2,'stats_select');
						echo "</div>";
						
						
						echo "<div style='float:right;'>";
							echo "<select name='day_p2' id='day_p2' style='width:50px;'  onchange='stats_select.submit();'>";
								for($a=1;$a<=31;$a++)
								{
									$selected='';if($a==$day_p2){$selected='selected';}
									echo "<option value='".$a."' ".$selected.">".$a."</option>";
								}
							echo "</select>";
						echo "</div>";
							
					echo "</div>";
				
				echo "</td>";
			echo "</tr>";
			
			if($debit_tarage)
			{
				echo "<tr>";	
					
					echo "<td class='bold'>";
						if($type_data==3){echo htmlaccent('Consulter les données limnimétriques');}
						if($type_data==2){echo htmlaccent('Convertir en débit');}
					
					echo "</td>";
					
					echo "<td style='text-align:right;'>";
						if($type_data==3){echo "<input type='submit' class='button' name='button_stats' value='Consulter' style='float:right;width:100px;height:18px;margin-top:10px;padding:0;'>";}
						if($type_data==2){echo "<input type='submit' class='button' name='button_stats' value='Convertir' style='float:right;width:100px;height:18px;margin-top:10px;padding:0;'>";}
						
					echo "</td>";
					
				echo "</tr>";
			}
			
				
		echo "</table>";			
			
	}












echo "</div>";

?>
