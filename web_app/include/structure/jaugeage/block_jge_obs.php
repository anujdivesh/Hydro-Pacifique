<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Ce block permet de modifier l'observation sur un point de JGE
----------------------------------------
*/

echo "<div id='box_jge_obs' class='block_view' style='width:500px;height:380px;margin-left:40%;background:transparent;'>\n"; // style='background:transparent;'

	echo "<div id='cadre_view_2' style='padding:0px;background-color:#FBF9F1;' >\n";

        echo "<div >";	

            echo "<p style='float:left;width:100%;height:40px;padding-left:8px;font-size:24px;font-weight:bold;color:#fff;margin:0px;background-color:#000;'>";
                echo htmlaccent('Observation du point de Jaugeage');
            echo "</p>\n";  

        echo "</div>\n";
	
		echo "<div style='margin: 0 5%;'>";	

        echo "<input type='hidden' id='jge_pt_nbb' name='jge_pt_nbb' value=''>\n";
        echo "<input type='hidden' id='jge_pt_row' name='jge_pt_row' value=''>\n";

            // Info pt
            echo "<div style='float:left;'>";

                // Verticale
                echo "<div style='float:left;width:32%;'>\n";
                            
                    echo "<p style='float:left;width:150px;padding-top:6px;font-size:14px;font-weight:bold;'>".htmlaccent('Numéro Verticale')."</p>\n";	
                    echo "<input style='float:left;width:60px;height:15px;font-size:14px;background:transparent;border:none;' name='jge_pt_verticale' id='jge_pt_verticale' type='text' value='' readonly>\n"; 
                            
                echo "</div>\n";

                // Distance départ
                echo "<div style='float:left;width:32%;'>\n";
                            
                    echo "<p style='float:left;width:150px;padding-top:6px;font-size:14px;font-weight:bold;'>".htmlaccent('Distance Départ [m]')."</p>\n";	
                    echo "<input style='float:left;width:60px;height:15px;font-size:14px;background:transparent;border:none;' name='jge_pt_distdepart' id='jge_pt_distdepart' type='text' value='' readonly>\n"; 
                            
                echo "</div>\n";

                // Profondeur mesure
                echo "<div style='float:left;width:32%;'>\n";
                            
                    echo "<p style='float:left;width:150px;padding-top:6px;font-size:14px;font-weight:bold;'>".htmlaccent('Prof. de la Mesure [m]')."</p>\n";	
                    echo "<input style='float:left;width:60px;height:15px;font-size:14px;background:transparent;border:none;' name='jge_pt_prof' id='jge_pt_prof' type='text' value='' readonly>\n"; 
                            
                echo "</div>\n";
            
            echo "</div>\n";

            echo "<div style='float:left;width:95%;margin-top:10px;'>";

                echo "<p style='float:left;width:150px;padding-top:6px;font-size:14px;font-weight:bold;'>".htmlaccent('Observation')."</p>\n";	
                echo "<textarea name='jge_pt_obs' id='jge_pt_obs' style='width:100%;height:80px;font-size:14px;'></textarea>\n";
                        
            echo "</div>\n";

                
            echo "<div style='float:left;margin-top:15px;'>";

                echo "<input type='submit' style='float:left;width:120px;' class='button' name='valid_pt_obs' id='valid_pt_obs' value='Valider' onClick='validObs();'>";
                        
            echo "</div>\n";

            echo "<hr>";
		
		echo "</div>\n";	
		
	echo "</div>\n";

echo "</div>\n";

?>


<script>
	
        
    // Fonction de validation des données dans le popup
    function validObs()
    {
        jgeBoxNbb = document.getElementById('jge_pt_nbb'); 
        jgeBoxRow = document.getElementById('jge_pt_row');

        nbb = jgeBoxNbb.value;
        row = jgeBoxRow.value;

        jgeBoxObs = document.getElementById('jge_pt_obs');  
        jgePtObs = document.getElementById('jge_pt_obs_'+nbb+'_'+row);         
        jgePtImg = document.getElementById('jge_pt_img_'+nbb+'_'+row); 

        jgePtObs.value = jgeBoxObs.value;

        imagePath = "<?php echo DIR_WS_IMG_ICO; ?>";

        if(jgePtObs.value !== '') {jgePtImg.src = imagePath + 'info_v.png';} 
        else {jgePtImg.src = imagePath + 'info_r.png';  }

        boxObsJGE.style.display='none';
    }
    
		  
</script>