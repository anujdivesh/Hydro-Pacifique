<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Block d'attente
Simplement pour lancer une boucle d'attente
----------------------------------------
*/


echo "<div id='box_wait' class='block_view' style='z-index: 2200;background-image:none;'>\n";

        echo "<div style='width:300px;margin: 0 auto;margin-top:12%;text-align:center;' \">";
        
            echo "<img src='".DIR_WS_IMG."hp100.gif' style='width:200px;margin-bottom:25px;' title='".htmlaccent('Processus en cours')."'>";
            
            echo "<p style='margin-bottom:10px;text-align:center;color:#000;font-size:22px;font-weight:bold;'>".htmlaccent('Processus en cours')."</p>";
            echo "<p style='text-align:center;font-size:18px;font-weight:bold;'>".htmlaccent('- Veuillez patienter -')."</p>";

        echo "</div>\n";    

echo "</div>";

?>
