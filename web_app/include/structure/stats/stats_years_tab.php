<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/
$tab_mois = array('janv.','f&eacute;v.','mars','avr.','mai','juin','juil.','ao&ucirc;t','sept.','oct.','nov.','d&eacute;c.');
$tab_mois_nb_days = array(31,28,31,30,31,30,31,31,30,31,30,31);


for($ms=1;$ms<=12;$ms++){$tab_sum[$ms]=0;$tab_nb_years[$ms]=0;}


echo "<table id='stats_tab' cellspacing='0'>";

	echo "<tr>";
		echo "<td class='top'>&nbsp;</td>";
		for($m=0;$m<sizeof($tab_mois);$m++)
		{
			echo "<td class='top'>".$tab_mois[$m]."</td>";
		}
		
		echo "<td class='top'  style='background-color:#fde9d1;'>".htmlaccent('Cumul')."</td>";
		echo "<td class='top'  style='background-color:#fde9d1;'>".htmlaccent('Moyenne')."</td>";
	echo "</tr>";
	
	echo "<tr><td colspan='13'>&nbsp;</td></tr>";
	
	
	for($y=$year_first;$y<=$year_last;$y++)
	{
		$sum_an=0;
		$moy_an=0;
		
		$style='';
		if($y%2==0){$style = "style='background-color:#E4E4E4;'";}
		
		echo "<tr>";
		 	echo "<td  class='bold'>".$y."</td>";
		  	$nb_mois=0;
			for($m=0;$m<sizeof($tab_mois);$m++)
			{
				echo "<td ".$style.">";
					
					if(isset($data_array[$y.'-'.($m+1)]))
					{
						$nb_mois++;
						$sum_an+=$data_array[$y.'-'.($m+1)]['cumul_pluvio'];
						
						$tab_nb_years[$m+1]+=1;
						$tab_sum[$m+1]+=$data_array[$y.'-'.($m+1)]['cumul_pluvio'];
						
						echo $data_array[$y.'-'.($m+1)]['cumul_pluvio'];
					}
					else{echo "-";}
				
				echo "</td>";
			}
			if($sum_an>0){echo "<td  style='background-color:#fde9d1;' class='bold'>".$sum_an."</td>";}
			else{echo "<td  style='background-color:#fde9d1;' class='bold'>-</td>";}
			
			
			if($sum_an>0)
			{
				$moy_an=round($sum_an/$nb_mois,1);
				echo "<td  style='background-color:#fde9d1;' class='bold'>".$moy_an."</td>";
			}
			else{echo "<td  style='background-color:#fde9d1;' class='bold'>-</td>";}
			
		echo "</tr>";
	}
	
	echo "<tr><td colspan='13'>&nbsp;</td></tr>";
	
	echo "<tr>";
		echo "<td class='top' style='background-color:#fde9d1;' >Moyenne</td>";
		
		for($m=1;$m<=12;$m++)
		{
			
			
			if($tab_sum[$m] > 0)
			{
				$tab_moy[$m]=$tab_sum[$m]/$tab_nb_years[$m];
				echo "<td class='top' style='background-color:#fde9d1;'>".round($tab_moy[$m],1)."</td>";
			}
			else{echo "<td class='top' style='background-color:#fde9d1;'>-</td>";}	
		}
		
		echo "<td class='top' style='background-color:#fde9d1;'>-</td>";
		echo "<td class='top' style='background-color:#fde9d1;'>-</td>";
		
	echo "</tr>";	
		
		
echo "</table>";

echo "<hr>";	




?>

