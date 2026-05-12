<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/

for($ms=1;$ms<=12;$ms++){$tab_sum[$ms]=0;$tab_nb_day_v[$ms]=0;$tab_max[$ms]=0;$tab_min[$ms]=0;}


$edit_tab_html .= "<div id='box_graph' class='gd'>";
			
	$edit_tab_html .= "<h8>".htmlaccent('Année '.$y.' - Débits moyens journaliers (m<sup>3</sup>/s)')."</h8>";
					
	$edit_tab_html .= "<hr><hr><hr>";

	$edit_tab_html .= "<table id='stats_tab' cellspacing='0'>";
	
		$edit_tab_html .= "<tr>";
			$edit_tab_html .= "<td class='top'>&nbsp;</td>";
			for($m=0;$m<sizeof($tab_mois);$m++)
			{
				$edit_tab_html .= "<td class='top'>".$tab_mois[$m]."</td>";
			}
		$edit_tab_html .= "</tr>";
		
		$edit_tab_html .= "<tr><td colspan='14'>&nbsp;</td></tr>";
		
		
		$verif_lac=false;
		for($d=1;$d<=31;$d++)
		{
			$style='';
			if($d%2==0){$style = "style='background-color:#E4E4E4;'";}
			
			$dd = $d;
			if($d<10){$dd="0".$d;}
			
						
			$edit_tab_html .= "<tr>";
				$edit_tab_html .= "<td  class='bold'>".$dd."</td>";
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
					if($date_day == datefr_us($date_max)){$style_td = "style='background-color:#fa7d72;'";}
					//if($date_day == datefr_us($year_tab_max[$year_p1])){$style_td = "style='background-color:#fa7d72;'";}
					
					$edit_tab_html .= "<td ".$style_td.">";
						
						
						if(isset($data_debit[$date_day]))
						{
							if($verif_lac){$edit_tab_html .= "<span style='color:#0251c7;'>(0)</span>";}
							else
							{
								$tab_sum[$m+1]+=$data_debit[$date_day]['debit'];
								$edit_tab_html .= round($data_debit[$date_day]['debit'],3);
								
								if($data_debit[$date_day]['debit']>0)
								{
									$tab_nb_day_v[$m+1]++;
									
									if($tab_max[$m+1] == 0){$tab_max[$m+1]=round($data_debit[$date_day]['debit'],3);}
									if($tab_max[$m+1] < round($data_debit[$date_day]['debit'],3)){$tab_max[$m+1]=round($data_debit[$date_day]['debit'],3);}
									
									if($tab_min[$m+1] == 0){$tab_min[$m+1]=round($data_debit[$date_day]['debit'],3);}
									if($tab_min[$m+1] > round($data_debit[$date_day]['debit'],3)){$tab_min[$m+1]=round($data_debit[$date_day]['debit'],3);}
								
								}
							}
							
							//if($data_debit_tab[$date_day]['lacune'] > 0){$edit_tab_html .= "<span style='color:#0251c7;'>(0)</span>";}
							//else{$edit_tab_html .= round($data_debit_tab[$date_day]['debit_lac'],3);}
							
						}
						else
						{
							
							$edit_tab_html .= "-";
						}
						
					
					$edit_tab_html .= "</td>";
				}
				$edit_tab_html .= "<td  class='bold'>".$dd."</td>";
			$edit_tab_html .= "</tr>";
		}
		
		$edit_tab_html .= "<tr><td colspan='14'>&nbsp;</td></tr>";
		
		// Moyenne
		
		$edit_tab_html .= "<tr>";
			$edit_tab_html .= "<td class='top' style='border-top:none;width:50px;'>Moyenne</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					$edit_tab_html .= "<td class='top' style='border-top:none;'>".round($tab_sum[$m]/$tab_nb_day_v[$m],3)."</td>";
				}
				else{$edit_tab_html .= "<td class='top' style='border-top:none;'>-</td>";}	
			}
			
			$edit_tab_html .= "<td class='top' style='border-top:none;width:50px;'>Moyenne</td>";
			
		$edit_tab_html .= "</tr>";	
		
		// Moyenne TabAll
		
		$edit_tab_resume_html .= "<tr>";
			$edit_tab_resume_html .= "<td class='bold'  style='width:50px;'>".$y."</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					$edit_tab_resume_html .= "<td ".$style_resume.">".round($tab_sum[$m]/$tab_nb_day_v[$m],3)."</td>";
				}
				else{$edit_tab_resume_html .= "<td ".$style_resume.">-</td>";}	
			}
			
			$edit_tab_resume_html .= "<td class='bold' style='width:50px;'>".$y."</td>";
			
		$edit_tab_resume_html .= "</tr>";
		
		// Maximum
		
		$edit_tab_html .= "<tr>";
			$edit_tab_html .= "<td class='top' style='border-top:none;background-color:#E5ECF9;width:50px;'>Maximum</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					$edit_tab_html .= "<td class='top' style='border-top:none;background-color:#E5ECF9;'>".$tab_max[$m]."</td>";
				}
				else{$edit_tab_html .= "<td class='top' style='border-top:none;background-color:#E5ECF9;'>-</td>";}	
			}
			
			$edit_tab_html .= "<td class='top' style='border-top:none;background-color:#E5ECF9;width:50px;'>Maximum</td>";
			
		$edit_tab_html .= "</tr>";
		
		// Minimum
		
		$edit_tab_html .= "<tr>";
			$edit_tab_html .= "<td class='top' style='border-top:none;width:50px;'>Minimum</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					$edit_tab_html .= "<td class='top' style='border-top:none;'>".$tab_min[$m]."</td>";
				}
				else{$edit_tab_html .= "<td class='top' style='border-top:none;'>-</td>";}	
			}
			
			$edit_tab_html .= "<td class='top' style='border-top:none;width:50px;'>Minimum</td>";
			
		$edit_tab_html .= "</tr>";
		
		
		
		$edit_tab_html .= "<tr><td colspan='14'>&nbsp;</td></tr>";
		
		$edit_tab_html .= "<tr>";
			$edit_tab_html .= "<td style='font-weight:bold;'>&nbsp;</td>";
			for($m=0;$m<sizeof($tab_mois);$m++)
			{
				$edit_tab_html .= "<td style='font-weight:bold;'>".$tab_mois[$m]."</td>";
			}
			
		$edit_tab_html .= "</tr>";
		
		
			
			
			
	$edit_tab_html .= "</table>";

	$edit_tab_html .= "<hr>";	

	if($lacune_load)
	{
		$edit_tab_html .= "<div class='affiche'>";
			$edit_tab_html .= "<span>".htmlaccent('Les informations en bleu définissent les périodes de lacunes diagnostiquées.')."</span>";
			$edit_tab_html .= "<br><span>".htmlaccent('Le débit moyen journalier maximum est indiquée en rouge.')."</span>";
		$edit_tab_html .= "</div>";		
	}
	else
	{
		$edit_tab_html .= "<div class='affiche'>";
			$edit_tab_html .= "<span>".htmlaccent('Le débit moyen journalier maximum est indiquée en rouge.')."</span>";
		$edit_tab_html .= "</div>";		
	}
	
	
$edit_tab_html .= "</div>";


?>
