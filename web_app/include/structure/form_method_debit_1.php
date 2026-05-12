<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/


$row = 0;


echo "<div id='onglet_contenu'>\n";

	echo "<div id='boite1' class='first'>\n";

		$sql = "SELECT DISTINCT * FROM ".TABLE_DATA_METHOD_DEBIT." ORDER BY num_order, method";
		$method_tab_query = tep_db_query($sql_link,$sql);
		
		echo "<table id='table_tri' cellspacing='0'>";
		
			echo "<tr>";
				echo "<td style='color:#ccc;font-size:14px;'>".htmlaccent('Méthode')."</td>";
				echo "<td style='color:#ccc;text-align:center;font-size:14px;'>".htmlaccent('Affichage Liste')."</td>";
				echo "<td style='color:#ccc;text-align:center;font-size:14px;'>".htmlaccent('Ordre Liste')."</td>";
				echo "<td>&nbsp;</td>";
			echo "</tr>";
			
			
			while ($method_tab = tep_db_fetch_array($method_tab_query)) 
			{
				$method = post_secure($sql_link,$method_tab['method']);
				$num_order = post_secure($sql_link,$method_tab['num_order']);
				$list = post_secure($sql_link,$method_tab['list']);
				
				if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
				else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
	
				echo "<tr ".$row_l." >";
						
					echo "<td>";
						if($method_tab['modif']==1){echo "<input type='text' class='input_texte_plus' name='method_".$method_tab['id']."' value='".htmlaccent($method)."'>\n";}
						else{echo "<input type='text' class='input_texte_plus' name='method_".$method_tab['id']."' value='".htmlaccent($method)."'  onFocus='this.blur()' style='float:left;width:45%;border:0;font-size:12px;'>";}
					echo "</td>";
					
					echo "<td style='text-align:center;'>";
						if($list==0 && $method_tab['modif']==0){echo '-';}
						else
						{
							$check = 'checked';
							if($method_tab['list']==0){$check = '';}
							
							echo "<input type='checkbox' name='list_".$method_tab['id']."' ".$check.">";
						}
					echo "</td>";
					
					echo "<td style='text-align:center;'>";
						if($list==1){echo "<input type='text' class='input_texte_xsmall' name='order_".$method_tab['id']."' value='".$num_order."'>\n";}
						else{echo '-';}
					echo "</td>";
					
					// supprimer
					echo "<td class='t_icon'>";
						$lien_suppr = "gestion_method_debit.php?id=".$method_tab['id'];
						if($method_tab['modif']==1){echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:16px;cursor:pointer;' title='".htmlaccent('Supprimer')."' onClick=\"confirm_suppr('" . $lien_suppr . "','la Methode de Mesure de Débit','".$method."');\">";}
					echo "</td>\n";
					
					
				
				echo "</tr>";					
			}
			
			
			
			echo "<tr><td colspan='2' style='color:#ccc;font-size:14px;'>".htmlaccent('Ajouter une Méthode')."</td></tr>\n";
							
							
			  echo "<tr>";
				echo "<td><input type='text' class='input_texte_plus' name='method' ></td>\n";
				echo "<td style='text-align:center;'><input type='checkbox' name='list' checked></td>\n";
				echo "<td style='text-align:center;'><input type='text' class='input_texte_xsmall' name='num_order' ></td>\n";
				echo "<td>&nbsp;</td>";
			echo "<tr>";
			
		
		echo "</table>";

	echo "<hr>\n";
	echo "</div>\n";

	
echo "<hr>\n";
echo "</div>\n";
?>
