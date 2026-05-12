<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Formulaire des fiches RA
Profil piézométrique
----------------------------------------
*/
$today = date('d-m-Y'); 
$time = date('H:i');
$today_time = date('d-m-Y H:i');

$day = date('d');
$month = date('m');
$year = date('Y');

$display_box = '';

echo "<div id='box_ra_piezoprofil' class='block_view' style='background:transparent;'>\n";


	echo "<div id='cadre_view_2' style='width:800px;' >\n";
	
		echo "<div id='cadre_limit' style='height:605px;margin-top: 0px;'>";	
        
            echo "<div id='cadre_titre' style='margin-bottom:5px;'>";	 

                echo "<p style='float:left;font-size:20px;font-weight:bold;color:#000;margin:0;'>".htmlaccent('Profil en profondeur')."</p>\n";                

                echo "<input type='button' class='button_close' style='float:right;'  value=\"".htmlaccent('Retour')."\" onclick=\"document.getElementById('box_ra_piezoprofil').style.display='none';\"/>";
        
            echo "<hr>\n";
            echo "</div>\n";
        
        
            echo "<div id='boite1' class='first' style='float:left;width:39%;padding-right:20px;margin:0;border-right: 2px solid #fff;'>\n";

                echo "<table id='table_tri' cellspacing='0' >";
                
                    echo "<thead>";
                        echo "<th style='width:120px;color:#000;font-size:14px;border-bottom: 1px solid #fff;'>".htmlaccent('Profondeur<br>[m]')."</th>";				
                        echo "<th style='width:120px;color:#000;font-size:14px;border-bottom: 1px solid #fff;'>".htmlaccent('Conductivité<br>[&mu;S/cm]')."</th>";
                        echo "<th style='width:120px;color:#000;font-size:14px;border-bottom: 1px solid #fff;'>".htmlaccent('Température<br>[°C]')."</th>";
                    echo "</thead>";	

                    // ligne vide dans le tableau						
                    echo "<tr>";
                        echo "<td colspan='3' style='height:15px;'>&nbsp;</td>";
                    echo "</tr>";

                    for($i=1;$i<=15;$i++)
                    {
                        echo "<tr>";
                                
                            echo "<td>";
                                echo "<input type='text' class='input_texte_small' id='piezo_profil_prof_".$i."' name='piezo_profil_prof_".$i."' value=''>\n";
                            echo "</td>";
                            
                            echo "<td>";
                                echo "<input type='text' class='input_texte_small' id='piezo_profil_conduct_".$i."' name='piezo_profil_conduct_".$i."' value=''>\n";
                            echo "</td>";
                            
                            echo "<td>";
                                echo "<input type='text' class='input_texte_small' id='piezo_profil_temp_".$i."' name='piezo_profil_temp_".$i."' value=''>\n";
                            echo "</td>";

                        echo "</td>";
                    }

                echo "</table>";

            echo "<hr>\n";
            echo "</div>\n";
            
            
            echo "<div id='boite1' class='first' style='float:left;width:54%;margin:0px;padding:0;padding-left:30px;'>\n";

                echo "<button type='button' id='refresh' class='inverse_axe' style='margin-bottom:10px;' onclick='f_editgraph_profil();'>".htmlaccent('Rafraîchir Graph')."</button>";
                
                echo "<div id='boxpopup' class='select' style='width:100%;height:450px;margin:0;padding:0;'>\n";

                    echo "<p class='titre' style='margin-bottom:5px;'>".htmlaccent('Graphique')."</p>\n";	

                    echo "<div id='plot_profil'></div>\n";

                echo "<hr>\n";    
                echo "</div>\n";

            echo "<hr>\n";
            echo "</div>\n";
				
            // --LIGNE --------------------------------
            echo "<hr>";
           
		
		echo "</div>\n";	
		
	echo "</div>\n";


echo "</div>\n";

?>


<script type="text/javascript">
	
// Récupère le popup et le bouton qui l'ouvre
var popup2 = document.getElementById('cadre_view_2');
var box2 = document.getElementById('box_ra_piezoprofil');
    
// Ajoute un événement de clic au document
document.addEventListener("click", function(event)
{
    // Vérifie si l'élément cliqué est à l'intérieur ou à l'extérieur du popup
    if (event.target !== popup2 && event.target === box2) 
    {
    // Ferme le popup
    box2.style.display = "none";
    }
});
    
		  
</script>