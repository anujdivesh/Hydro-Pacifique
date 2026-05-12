<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Ce block permet d'afficher la listes des lacunes qui vont s'afficher sur un graphique
Cette fenêtre s'affiche quand on clique sur un boutton Tableau des Lacunes dans l'en-tête des graphiques
----------------------------------------
*/

echo "<div id='box_lacunes_info' class='block_view' >\n"; // style='background:transparent;'

	echo "<div id='cadre_view_2' style='float:left;width:68%;max-height:80%;margin-top:20px;margin-left:18%;padding:0px;background-color:#FBF9F1;' >\n";

        echo "<p style='float:left;width:100%;height:30px;padding:5px 0;color:#fff;background-color:#000;'>";
            echo "<span style='font-size:20px;font-weight:bold;margin-left:5px;'>";
                echo htmlaccent('Liste des lacunes relevées sur la station');
            echo "</span>";
            echo "<span style='float:right;font-size:24px;margin-right:5px;cursor:pointer;' onCLick=\"document.getElementById('box_lacunes_info').style.display='none';\" title='Fermer'>X</span>";
        echo "</p>\n";  

	
		echo "<div id='cadre_tab_lacune' style='margin-top:0px;padding:0px;padding-right:5px;'>";	
		
		echo "</div>\n";	
		
	echo "</div>\n";

echo "</div>\n";

?>