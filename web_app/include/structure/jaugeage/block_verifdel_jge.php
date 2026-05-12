<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Popup pour permettre une confirmation de la suppresion d'un jaugeage
----------------------------------------
*/


$day = date('d');
$month = date('m');
$year = date('Y');

$display_box = '';

echo "<div id='box_del_jge' class='block_view' >\n";

    echo "<div id='cadre_view_del' style='width:500px;margin-top:100px;padding:0;background-color:#FBF9F1;' >";

        echo "<p style='width:100%;height:30px;padding:10px 0;text-align:center;font-size:18px;font-weight:bold;color:#fff;background-color:#000;'>";
            echo htmlaccent('Êtes vous sûr de vouloir supprimer ce Jaugeage ?');
        echo "</p>\n";  

        echo "<div style='float:left;width:80%;margin-top:10px;margin-left:10%;'>";

            echo "<p style='width:100%;margin-top:15px;font-size:18px;'>";
                echo htmlaccent('Cette action est irresversible.');
            echo  "</p>\n"; 

        echo "</div>";


        echo "<div style='float:left;width:80%;margin-top:25px;margin-left:10%;'>";
        
                echo "<div style='float:left;width:45%;'>";
                    echo "<input type='button' class='button' id='ok_valid_del' value='Valider'>";
                echo "</div>";

                echo "<div style='float:left;width:45%;'>";
                    echo "<input type='button' id='no_valid_del' class='button_close' value='Annuler'>";
                echo "</div>";
            
        echo "<hr>";
        echo "</div>";
    
    echo "<hr>";
    echo "</div>";
    

echo "</div>";

?>


<script type="text/javascript">
	
	// Récupère le popup et le bouton qui l'ouvre
	var popup_del = document.getElementById('box_del_jge');
	var button_cancel_del = document.getElementById('no_valid_del');
		
	// Ajoute un événement de clic au document
	document.addEventListener("click", function(event)	
    {
		// Vérifie si l'élément cliqué est le bouton "Annuler"
        if (event.target === button_cancel_del) 
        {
            popup_del.style.display = "none";
         
        }
    });
    

    
		  
</script>