<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/

for($ms=1;$ms<=12;$ms++){$tab_sum[$ms]=0;$tab_nb_day_v[$ms]=0;$tab_max[$ms]=0;$tab_min[$ms]=0;}

$edit_tab_html .= "<div id='box_graph' class='gd'>";
			
	if($periode == 3){$edit_tab_html .= "<h8>".htmlaccent('Tableau des précipitations journalières (mm)')."</h8>";}
	else{$edit_tab_html .= "<h8>".htmlaccent('Année '.$y.' - Précipitations journalières (mm)')."</h8>";}
					
	$edit_tab_html .= "<hr><hr><hr>";

	$edit_tab_html .= "<table id='stats_tab' cellspacing='0'>";
	
		$edit_tab_html .= "<tr>";
			$edit_tab_html .= "<td class='top' style='border-top:none;'>&nbsp;</td>";
			for($m=0;$m<sizeof($tab_mois);$m++)
			{
				$edit_tab_html .= "<td class='top' style='border-top:none;'>".$tab_mois[$m]."</td>";
			}
			$edit_tab_html .= "<td class='top' style='border-top:none;'>&nbsp;</td>";
		$edit_tab_html .= "</tr>";
		
		$edit_tab_html .= "<tr><td colspan='14'>&nbsp;</td></tr>";
		
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
						
						if(isset($data_pluvio[$date_day]))
						{
							if($verif_lac){$edit_tab_html .= "<span style='color:#0251c7;'>(";}
							
							$tab_sum[$m+1]+=$data_pluvio[$date_day]['qte_plu_lac'];
							$edit_tab_html .= round($data_pluvio[$date_day]['qte_plu_lac'],1);
							
							if($verif_lac){$edit_tab_html .= ")</span>";}
							
							if($tab_max[$m+1] < round($data_pluvio[$date_day]['qte_plu_lac'])){$tab_max[$m+1]=round($data_pluvio[$date_day]['qte_plu_lac'],1);}
	
							if($tab_min[$m+1] == 0){$tab_min[$m+1]=round($data_pluvio[$date_day]['qte_plu_lac'],1);}
							if($tab_min[$m+1] > round($data_pluvio[$date_day]['qte_plu_lac'])){$tab_min[$m+1]=round($data_pluvio[$date_day]['qte_plu_lac'],1);}

							
							$tab_nb_day_v[$m+1]++;
						}
						else
						{
							$edit_tab_html .= "-";
						}
					
					$edit_tab_html .= "</td>";
				}
				$edit_tab_html .= "<td class='bold'>".$dd."</td>";
			$edit_tab_html .= "</tr>";
		}
		
		$edit_tab_html .= "<tr><td colspan='14'>&nbsp;</td></tr>";
		
		// Cumul - Somme
		
		$edit_tab_html .= "<tr>";
			$edit_tab_html .= "<td class='top' style='background-color:#E5ECF9;width:50px;'>Cumul</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					$edit_tab_html .= "<td class='top' style='background-color:#E5ECF9;'>".round($tab_sum[$m],1)."</td>";
				}
				else{$edit_tab_html .= "<td class='top' style='background-color:#E5ECF9;'>-</td>";}	
			}
			
			$edit_tab_html .= "<td class='top' style='background-color:#E5ECF9;width:50px;'>Cumul</td>";
			
		$edit_tab_html .= "</tr>";
		
		// Cumul TabAll
		
		$edit_tab_resume_html .= "<tr>";
			$edit_tab_resume_html .= "<td class='bold'  style='width:50px;'>".$y."</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					//Cumul
					$edit_tab_resume_html .= "<td ".$style_resume.">".round($tab_sum[$m],1)."</td>";
					
					//MAx
					//$edit_tab_resume_html .= "<td ".$style_resume.">".round($tab_max[$m],1)."</td>";
				}
				else{$edit_tab_resume_html .= "<td ".$style_resume.">-</td>";}	
			}
			
			$edit_tab_resume_html .= "<td class='bold' style='width:50px;'>".$y."</td>";
			
		$edit_tab_resume_html .= "</tr>";
		
		// Moyenne by day
		
		$edit_tab_html .= "<tr>";
			$edit_tab_html .= "<td class='top' style='border-top:none;width:50px;'>Moyenne</td>";
			
			for($m=1;$m<=12;$m++)
			{
				if($tab_nb_day_v[$m] > 0)
				{
					$edit_tab_html .= "<td class='top' style='border-top:none;'>".round($tab_sum[$m]/$tab_nb_day_v[$m],1)."</td>";
				}
				else{$edit_tab_html .= "<td class='top' style='border-top:none;'>-</td>";}	
			}
			
			$edit_tab_html .= "<td class='top' style='border-top:none;width:50px;'>Moyenne</td>";
			
		$edit_tab_html .= "</tr>";	
		
		
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
			$edit_tab_html .= "<span>".htmlaccent('Les informations en bleu, entre parenthèses ou signaler par (+) ont fait l\'objet d\'une correction (lacune).')."</span>";
			$edit_tab_html .= "<br><span>".htmlaccent('La pluviométrie journalière maximum est indiquée en rouge.')."</span>";
		$edit_tab_html .= "</div>";		
	}
	else
	{
		$edit_tab_html .= "<div class='affiche'>";
			$edit_tab_html .= "<span>".htmlaccent('La pluviométrie journalière maximum est indiquée en rouge.')."</span>";
		$edit_tab_html .= "</div>";		
	}
	
	
$edit_tab_html .= "</div>";



?>
