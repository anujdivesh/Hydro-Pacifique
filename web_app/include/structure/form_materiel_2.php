<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/


echo "<div id='onglet_contenu'>\n";

	echo "<div id='boite1' class='first'>\n";
				
					
		echo "<div id='boite_small'>\n";
			
			echo "<h2>".htmlaccent('Format de fichier - extension')."</h2>\n";
				
			if($modif){echo tep_draw_input_field('ext_file',$ext_file,'class=\'input_texte_m\'');}
			else{echo tep_draw_input_field('ext_file','','class=\'input_texte_m\'');}
						
		echo "</div>\n";
	
		$style_display=''; 
		if($interval_type == 0){$style_display="style='display:none;'";}
		
		echo "<div id='boite_qte_eau_plu' class='boite_small' ".$style_display.">\n";
			
			echo "<h2>".htmlaccent('Quantité d\'eau à chaque basculement (en mm)')."</h2>\n";
				
			if($modif){echo tep_draw_input_field('qte',$qte,'class=\'input_texte_m\'');}
			else{echo tep_draw_input_field('qte',0,'class=\'input_texte_m\'');}
						
		echo "</div>\n";
		
		
		echo "<div id='boite_small'>\n";
			
			echo "<h2>".htmlaccent('Champ de saisi "Date d\'initialisation"')."</h2>\n";
			
			$check = '';
			if($champ_datefirst==1){$check = 'checked';}
			echo "<input type='checkbox' name='champ_datefirst' id='champ_datefirst' ".$check.">";			
		
		echo "</div>\n";	
		
		echo "<div id='boite_small'>\n";
			
			echo "<h2>".htmlaccent('Champ de saisi "Péridode des données à enregistrer"')."</h2>\n";
			
			$check = '';
			if($champ_dateend==1){$check = 'checked';}
			echo "<input type='checkbox' name='champ_dateend' id='champ_dateend' ".$check.">";			
		
		echo "</div>\n";	
				
	
	echo "<hr>\n";
	echo "</div>\n";
	
	
	
	echo "<div id='boite1'>\n";
				
		echo "<h2>".htmlaccent('Algorithme de lecture et d\'enregistrement du ficher')."</h2>\n";
				
		if($modif){echo "<textarea style='width:70%;height:400px;' name='format' id='format'>".$format_eq."</textarea>\n";}
		else{echo "<textarea style='width:70%;height:400px;' name='format' id='format'></textarea>\n";}
	
	echo "<hr>\n";
	echo "</div>\n";
	
	
echo "<hr>\n";
echo "</div>\n";
?>
