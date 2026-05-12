<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Popup pour permettre une confirmation de la suppresion de données
----------------------------------------
*/


echo "<div id='box_verif_deletedata' class='block_view' >\n";

    echo "<div id='cadre_view_del' style='width:500px;margin-top:100px;padding:0;background-color:#FBF9F1;' >";

        echo "<p style='width:100%;height:30px;padding:10px 0;text-align:center;font-size:18px;font-weight:bold;color:#fff;background-color:#000;'>";
            echo htmlaccent('Êtes vous sûr de vouloir supprimer les données ?');
        echo "</p>\n";  

        echo "<div id='detail_del' style='float:left;width:80%;margin-top:10px;margin-left:10%;display:none;'>";

            echo "<p style='width:100%;'>";
                echo "<input type='text' id='detail_del_text' style='width:100%;font-size:16px;border:none;background:none;' readonly>";
            echo  "</p>\n"; 

        echo "</div>";

        echo "<div style='float:left;width:80%;margin-left:10%;'>";

            echo "<p style='width:100%;margin-top:15px;font-size:18px;'>";
                echo htmlaccent('Cette action est irréversible.');
            echo  "</p>\n"; 

        echo "</div>";


        echo "<div style='float:left;width:80%;margin-top:25px;margin-left:10%;'>";
        
                echo "<div style='float:left;width:45%;'>";
                    echo "<input type='button' class='button' id='ok_valid_deletedata' value='Valider'>";
                echo "</div>";

                echo "<div style='float:left;width:45%;'>";
                    echo "<input type='button' id='no_valid_deletedata' class='button_close' value='Annuler'>";
                echo "</div>";
            
        echo "<hr>";
        echo "</div>";
    
    echo "<hr>";
    echo "</div>";
    

echo "</div>";

?>


<script type="text/javascript">
	
	// Récupère le popup et le bouton qui l'ouvre
	var popup_del = document.getElementById('cadre_view_del');
	var box_del = document.getElementById('box_del_ra');
		
	// Ajoute un événement de clic au document
	document.addEventListener("click", function(event)
	{
		// Vérifie si l'élément cliqué est à l'intérieur ou à l'extérieur du popup
		if (event.target !== popup_del && event.target === box_del) 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			box_del.style.display = "none";
		}
	});

		  
</script>