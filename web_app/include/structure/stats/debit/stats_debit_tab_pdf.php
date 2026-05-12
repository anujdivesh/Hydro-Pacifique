<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/
for($ms=1;$ms<=12;$ms++){$tab_sum[$ms]=0;$tab_nb_day_v[$ms]=0;$tab_max[$ms]=0;$tab_min[$ms]=0;}


$edition .= "<div id='box_graph' class='gd'>";
			
	$edition .= "<div style='width:100%;margin-top:30px;border-bottom:1px solid #336699;'></div>";
	$edition .= "<h2>".htmlaccent('Année '.$y.' - Tableau des débits moyens journaliers (m<sup>3</sup>/s)')."</h2>";
	//$edition .= "<hr><div style='width:100%;margin-top:30px;border-bottom:1px solid #336699;'></div>";
					
	$edition .= "<hr><hr>";
	
	$edition .= "<table id='stats_tab' cellspacing='0'>";
	
		$edition .= "<tr>";
			$edition .= "<td class='top'>&nbsp;</td>";
			
			for($m=0;$m<sizeof($tab_mois);$m++)
			{
				$edition .= "<td class='top'>".$tab_mois[$m]."</td>";
			}
			
		$edition .= "</tr>";
		
		$edition .= "<tr><td>&nbsp;</td></tr>";
			
		
		$verif_lac_jb=false;
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
			
			$edition .= "<tr>";
				$edition .= "<td  class='bold'>".$dd."</td>";
				for($m=0;$m<sizeof($tab_mois);$m++)
				{
					$mm=$m+1;
					if($mm<10){$mm="0".$mm;}
					
					$date_day = $y."-".$mm."-".$dd;
					$date_day_lac = $y.$mm.$dd;
					
					// verif lacune
					$verif_lac=false;
					if($lacune_load)
					{
						
						for($dl=0;$dl<sizeof($data_list_lacunes);$dl++)
						{
							$date_deb = str_replace('-','', datefr_us($data_list_lacunes[$dl]['date_deb']));
							$date_fin = str_replace('-','', datefr_us($data_list_lacunes[$dl]['date_fin']));
							
							if($date_day_lac>=$date_deb && $date_day_lac<=$date_fin){$verif_lac=true;}
						}
					}
					
					$style_td = $style;
					if($date_day == datefr_us($date_max)){$style_td = "style='width: 15px;background-color:#fa7d72;'";}
					
					$edition .= "<td ".$style_td.">";
						
						if(isset($data_debit_tab[$date_day]))
						{
							if($verif_lac){$edition .= "<span style='color:#0251c7;'>(0)</span>";}
							else
							{
								$tab_sum[$m+1]+=$data_debit_tab[$date_day]['debit'];
								$edition .= round($data_debit_tab[$date_day]['debit'],3);
								
								if($data_debit_tab[$date_day]['debit']>0)
								{
									$tab_nb_day_v[$m+1]++;
									
									if($tab_max[$m+1] < round($data_debit_tab[$date_day]['debit'])){$tab_max[$m+1]=round($data_debit_tab[$date_day]['debit'],3);}
			
									if($tab_min[$m+1] == 0){$tab_min[$m+1]=round($data_debit_tab[$date_day]['debit'],3);}
									if($tab_min[$m+1] > round($data_debit_tab[$date_day]['debit'])){$tab_min[$m+1]=round($data_debit_tab[$date_day]['debit'],3);}
								
								}
							
							
								
							}
						}
						else
						{
							
							//if($verif_lac){$edition .= "+";}
							//else{$edition .= "-";}
							$edition .= "-";
						}
					
					$edition .= "</td>";
				}
				$edition .= "<td class='bold' style='width: 50px;'>".$dd."</td>";
			$edition .= "</tr>";
		}
		
		$edition .= "<tr><td>&nbsp;</td></tr>";
		
		// Moyenne
		
		$edition .= "<tr>";
			$edition .= "<td class='top' style='border-top:none;width:50px;'>Moy</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					$edition .= "<td class='top' style='border-top:none;'>".round($tab_sum[$m]/$tab_nb_day_v[$m],3)."</td>";
				}
				else{$edition .= "<td class='top' style='border-top:none;'>-</td>";}	
			}
			
			$edition .= "<td class='top' style='border-top:none;width:50px;'>Moy</td>";
			
		$edition .= "</tr>";	
		
		
		// Moyenne Résumée
		
		$edition_page1 .= "<tr>";
			$edition_page1 .= "<td class='bold'  style='width:50px;'>".$y."</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					$edition_page1 .= "<td ".$style_resume.">".round($tab_sum[$m]/$tab_nb_day_v[$m],3)."</td>";
				}
				else{$edition_page1 .= "<td ".$style_resume.">-</td>";}	
			}
			
			$edition_page1 .= "<td class='bold' style='width:50px;'>".$y."</td>";
			
		$edition_page1 .= "</tr>";
		
		// Maximum
		
		$edition .= "<tr>";
			$edition .= "<td class='top' style='border-top:none;background-color:#E5ECF9;width:50px;'>Max</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					$edition .= "<td class='top' style='border-top:none;background-color:#E5ECF9;'>".$tab_max[$m]."</td>";
				}
				else{$edition .= "<td class='top' style='border-top:none;background-color:#E5ECF9;'>-</td>";}	
			}
			
			$edition .= "<td class='top' style='border-top:none;background-color:#E5ECF9;width:50px;'>Max</td>";
			
		$edition .= "</tr>";
		
		
		// Minimum
		
		$edition .= "<tr>";
			$edition .= "<td class='top' style='border-top:none;width:50px;'>Min</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					$edition .= "<td class='top' style='border-top:none;'>".$tab_min[$m]."</td>";
				}
				else{$edition .= "<td class='top' style='border-top:none;'>-</td>";}	
			}
			
			$edition .= "<td class='top' style='border-top:none;width:50px;'>Min</td>";
			
		$edition .= "</tr>";
		
		
		
		$edition .= "<tr><td>&nbsp;</td></tr>";
		
		$edition .= "<tr>";
			$edition .= "<td style='font-weight:bold;'>&nbsp;</td>";
			for($m=0;$m<sizeof($tab_mois);$m++)
			{
				$edition .= "<td style='font-weight:bold;'>".$tab_mois[$m]."</td>";
			}
			
		$edition .= "</tr>";
		
		
			
			
			
	$edition .= "</table>";

	$edition .= "<hr><hr><hr>";	
	
	if($lacune_load)
	{
		$edition .= "<table style='text-align:left;'>";
			$edition .= "<tr><td>".htmlaccent('Les informations en bleu définissent les périodes de lacunes diagnostiquées.')."</td></tr>";
			$edition .= "<tr><td>".htmlaccent('Le débit moyen journalier maximum est indiquée en rouge.')."</td></tr>";
		$edition .= "</table>";		
	}
	else
	{
		$edition .= "<table style='text-align:left;'>";
			//$edition .= "<tr><td>".htmlaccent('Les informations en bleu, entre parenthèses ou signaler par + ont fait l\'objet d\'une correction (lacune).')."</td></tr>";
			$edition .= "<tr><td>".htmlaccent('Le débit moyen journalier maximum est indiquée en rouge.')."</td></tr>";
		$edition .= "</table>";		
	}
	
	
	
$edition .= "</div>";
//echo $edition;


?>
