<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/
$tab_mois = array('janv.','f&eacute;v.','mars','avr.','mai','juin','juil.','ao&ucirc;t','sept.','oct.','nov.','d&eacute;c.');

for($ms=1;$ms<=12;$ms++){$tab_sum[$ms]=0;$tab_nb_day_v[$ms]=0;$tab_max[$ms]=0;$tab_min[$ms]=0;}


//echo "<div id='box_graph' class='gd'>";
			
	//echo "<h8>".htmlaccent('Limnimètre (hauteur moyenne journalière en cm)')."</h8>";
	//echo "<h3>".htmlaccent('Limnimètre (hauteur moyenne journalière en cm)')."</h3>";
					
	echo "<hr><hr><hr>";

	echo "<table id='stats_tab' cellspacing='0'>";
	
		echo "<tr>";
			echo "<td class='top'>&nbsp;</td>";
			for($m=0;$m<sizeof($tab_mois);$m++)
			{
				echo "<td class='top'>".$tab_mois[$m]."</td>";
			}
			echo "<td class='top'>&nbsp;</td>";
		echo "</tr>";
		
		echo "<tr><td colspan='14'>&nbsp;</td></tr>";
		
		for($d=1;$d<=31;$d++)
		{
			$style='';
			if($d%2==0){$style = "style='background-color:#E4E4E4;'";}
			
			$dd = $d;
			if($d<10){$dd="0".$d;}
			
			//$ddplus = $d+1;
			//if(($d+1)<10){$ddplus="0".($d+1);}
			
			//$ddmoins = $d-1;
			//if(($d-1)<10 && ($d-1)>0){$ddmoins="0".($d-1);}
			
			echo "<tr>";
				echo "<td  class='bold'>".$dd."</td>";
				for($m=0;$m<sizeof($tab_mois);$m++)
				{
					$mm=$m+1;
					if($mm<10){$mm="0".$mm;}
					
					$date_day = $year_p1."-".$mm."-".$dd;
					
					
					$style_td = $style;
					if($date_day == datefr_us($date_max)){$style_td = "style='background-color:#fa7d72;'";}
					
					echo "<td ".$style_td.">";
						
						if(isset($data_limni[$date_day]))
						{
							$tab_sum[$m+1]+=$data_limni[$date_day]['hauteur'];
							echo round($data_limni[$date_day]['hauteur'],0);
							
							if($tab_max[$m+1] < round($data_limni[$date_day]['hauteur'])){$tab_max[$m+1]=round($data_limni[$date_day]['hauteur'],0);}
							
							if($tab_min[$m+1] == 0){$tab_min[$m+1]=round($data_limni[$date_day]['hauteur'],0);}
							if($tab_min[$m+1] > round($data_limni[$date_day]['hauteur'])){$tab_min[$m+1]=round($data_limni[$date_day]['hauteur'],0);}
							
							$tab_nb_day_v[$m+1]++;
						}
						else{echo "-";}
					
					echo "</td>";
				}
				echo "<td  class='bold'>".$dd."</td>";
			echo "</tr>";
		}
		
		echo "<tr><td colspan='14'>&nbsp;</td></tr>";
		
		echo "<tr>";
			echo "<td class='top' style='background-color:#acdeab;width:50px;'>Moyenne</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					echo "<td class='top' style='background-color:#acdeab;'>".round(($tab_sum[$m]/$tab_nb_day_v[$m]),1)."</td>";
				}
				else{echo "<td class='top' style='background-color:#acdeab;'>-</td>";}	
			}
			
			echo "<td class='top' style='background-color:#acdeab;width:50px;'>Moyenne</td>";
		echo "</tr>";	
		
		
		echo "<tr>";
			echo "<td class='top' style='border-top:none;width:50px;'>Maximum</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					echo "<td class='top' style='border-top:none;'>".$tab_max[$m]."</td>";
				}
				else{echo "<td class='top' style='border-top:none;'>-</td>";}	
			}
			
			echo "<td class='top' style='border-top:none;width:50px;'>Maximum</td>";
		echo "</tr>";
		
		echo "<tr>";
			echo "<td class='top' style='border-top:none;background-color:#acdeab;width:50px;'>Minimum</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					echo "<td class='top' style='border-top:none;background-color:#acdeab;'>".$tab_min[$m]."</td>";
				}
				else{echo "<td class='top' style='border-top:none;background-color:#acdeab;'>-</td>";}	
			}
			
			echo "<td class='top' style='border-top:none;background-color:#acdeab;width:50px;'>Minimum</td>";
		echo "</tr>";
		
		
		
		
		echo "<tr><td colspan='14'>&nbsp;</td></tr>";
		
		echo "<tr>";
			echo "<td style='font-weight:bold;'>&nbsp;</td>";
			for($m=0;$m<sizeof($tab_mois);$m++)
			{
				echo "<td style='font-weight:bold;'>".$tab_mois[$m]."</td>";
			}
			echo "<td style='font-weight:bold;'>&nbsp;</td>";
		echo "</tr>";
		
		
			
			
			
	echo "</table>";

	echo "<hr>";	

	echo "<div class='affiche'>";
		echo "<span>".htmlaccent('La pluviométrie journalière maximum est indiquée en rouge.')."</span>";
	echo "</div>";		
	
	
//echo "</div>";


?>
