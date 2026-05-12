<?php
/*  
----------------------------------------
Copyright (c) 2025 - Vai-Natura
----------------------------------------
Popup pour l'affichage d'information liée aux Chroniques de Données
----------------------------------------
*/
// Requête sur TYPE DE MESURE (Hydrométrie, Pluviométrie, Piézométrie, ...)
$sql_eq_type_boxinfo = "SELECT DISTINCT id_eq_type, nom_eq_type FROM ".TABLE_EQ_TYPE." WHERE active_eq_type=1 ORDER BY order_eq_type ASC";
$eq_type_boxinfo_query = tep_db_query($sql_link,$sql_eq_type_boxinfo);
while ($eq_type_boxinfo = tep_db_fetch_array($eq_type_boxinfo_query))
{				
	$eq_type_boxinfo_array[$eq_type_boxinfo['id_eq_type']] = $eq_type_boxinfo['nom_eq_type'];
} 


echo "<div id='box_info_chron' class='block_view' >\n"; // style='background:transparent;'

	echo "<div id='cadre_view_2' style='float:left;width:40%;margin-top:20px;margin-left:30%;padding:0px;' >\n";

        echo "<p style='float:left;width:100%;height:30px;padding:5px 0;color:#fff;background-color:#000;'>";
            echo "<span id='title_info_chron'style='font-size:20px;font-weight:bold;margin-left:5px;'>";
            echo "</span>";

            echo "<span id='button_close' style='float:right;font-size:20px;font-weight:bold;margin-right:15px;cursor:pointer;' title='Fermer'>X</span>";
        echo "</p>\n";  

		/*
		echo "<div style='float:left;margin:20px;'>";
			echo "<p style='float:left;margin-right:20px;padding-top:5px;color:#000;font-size:14px;font-weight:bold;'>";
				echo 'Sélectionner : ';
			echo "</p>";

			echo "<select name='chron_filter' id='chron_filter' style='float:left;width:150px;' onchange='selectTypeData()'>";
								
				echo "<option value='0'>-</option>";
				
				$selected = '';		
				if(isset($eq_type_boxinfo_array))
				{
					foreach($eq_type_boxinfo_array as $key => $value)
					{																			
						echo "<option value='".$key."' ".$selected." >".$value."</option>";
					}
				}
			
			echo "</select>";
		echo "</div>";
		*/

		echo "<div id='cadre_limit_info_chron' style='margin-top:50px;'>";	

            // Cadre d'affichage
            echo "<div id='cadre_info_chron_cell' style='width:95%;margin:0 2.5%;'>";	
            
                echo "<div id='cadre_wait_info_chron' style='width:100%;height:80px;margin-top:20px;text-align:center;'>";	   
                    echo "<img src='".DIR_WS_IMG."wait.gif' style='width:50px;'>";                 
                echo "</div>";
            
            echo "</div>";

		echo "<hr>\n";
		echo "</div>\n";	
		
	echo "</div>\n";

echo "</div>\n";

?>


<script>
	
	var box_infoChron = document.getElementById('box_info_chron');
	var titleBox_infoChron = document.getElementById('title_info_chron');
	var contenuBox_infoChron = document.getElementById('cadre_info_chron_cell');
	var waitBox_infoChron = document.getElementById('cadre_wait_info_chron');
		
	// Ajoute un événement de clic au document
	document.addEventListener("click", function(event)
	{
		// Vérifie si l'élément cliqué est le bouton de fermeture
        if (event.target.id === 'button_close') 
		{
            // Ferme le popup et le popup d'info s'il a été ouvert
            box_infoChron.style.display = "none";
        } 

		// Vérifie si l'élément cliqué est à l'intérieur ou à l'extérieur du popup
		if (event.target === box_info_chron) 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			box_infoChron.style.display = "none";
		}
	});

    // Ajout d'un gestionnaire d'événements pour la touche Echap
	document.addEventListener("keydown", function(event) 
	{
		if (event.key === "Escape") 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			box_infoChron.style.display = "none";
		}
    });


    function affiche_info_chron() 
	{
		titleBox_infoChron.textContent = 'Détails sur les Chroniques';
		waitBox_infoChron.style.display = 'block';

		// Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
							idTypeData: '<?php echo $id_eq_type; ?>'
						};

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/box/process_info_chron.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {	
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);
				js_html = jsonResponse['js_html'];
					
				contenuBox_infoChron.innerHTML = js_html;
				waitBox_infoChron.style.display = 'none';
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
	};


	function afficheBlockInfoChron() 
    {
        box_infoChron.style.display = 'block';
		affiche_info_chron();
    }

		  
</script>