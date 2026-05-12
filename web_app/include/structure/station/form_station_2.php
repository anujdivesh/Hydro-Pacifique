<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Second onglet de la page station 
Essentiellement affichage des MétaDonnées de la Station sélectionnée
----------------------------------------
*/
$row=0;



$color_type = '';
if(tep_not_null($type_color_border)){$color_type = 'color:'.$type_color_border.';';}

echo "<div id='onglet_contenu' style='overflow-y: auto;height:75vh;'>\n";

	echo "<div id='boite1' class='first' style='margin-bottom:0px;margin-right: 5px;'>\n";
		
		
		echo "<p class='titre_box'>".
				"Métadonnées de la station".
			"</p>\n";	
			
		// Infos très générales
		echo "<div id='boxpopup' style='height:100px;padding:10px;margin-right: 25px;' >\n";
					
			echo "<p style='font-weight:normal;font-size:14px;'>";
				echo "<span style='font-weight:bold;'>".htmlaccent('Type de mesure : ')."</span>";
				echo "<span style='".$color_type."'>".$nom_data_type."</span>";
			echo "</p>";

			
			if(isset($commune_array[$id_commune]))
			{
				echo "<p style='margin:15px 0;'>".$region_array[$id_region]." - ".htmlaccent('Commune de '.$commune_array[$id_commune])."</p>";
			}

			echo "<p style='font-weight:normal;'><span style='font-weight:bold;'>".htmlaccent('Date d\'installation : ')."</span>".$date_installation_station."</p>";
			echo "<p style='font-weight:normal;'><span style='font-weight:bold;'>".htmlaccent('Date de démontage : ')."</span>".$date_fermeture_station."</p>";
			
		echo "</div>\n";

		// Etat de la station
		echo "<div id='boxpopup' style='height:100px;padding:10px;margin-right: 25px;' >\n";
			
			$text_active = htmlaccent('Historique (fermée)');
			if($active_station > 0){$text_active = htmlaccent('Active');}
			echo "<p style='font-weight:normal;'>";
				echo "<span style='font-weight:bold;'>".htmlaccent('Statut : ')."</span>";
				echo $text_active;
			echo "</p>";
			$text_suivi = htmlaccent('Mesures ponctuelles');
			if($suivi_station > 0){$text_suivi = htmlaccent('Mesures continues');}
			echo "<p style='font-weight:normal;'>";
				echo "<span style='font-weight:bold;'>".htmlaccent('Suivi : ')."</span>";
				echo $text_suivi;
			echo "</p>";
			$text_armee = htmlaccent('non');
			if($armee_station > 0){$text_armee = htmlaccent('oui');}
			echo "<p style='font-weight:normal;'>";
				echo "<span style='font-weight:bold;'>".htmlaccent('Equipement en panne : ')."</span>";
				echo $text_armee;
			echo "</p>";
			
		echo "</div>\n";
		
				
		// Infos sur les RA de la station
		/*
			echo "<div id='boxpopup' style='height:100px;padding:10px;' >\n";
				
				
				echo "<p style='font-weight:normal;'>";
					echo "<span style='font-weight:bold;'>".htmlaccent('Nombre de RA validés : ')."</span>";
					//echo $nb_ra_valide;
				echo "</p>";
				echo "<p style='font-weight:normal;'>";
					echo "<span style='font-weight:bold;'>".htmlaccent('Date du dernier RA validé : ')."</span>";
					if($nb_ra_valide > 0)
					{
						$first_RA_valide = reset($ra_valide_array);
						echo $first_RA_valide['date_ra'] ;
					}
					else{echo '';}
					
				echo "</p>";

				echo "<br>";

			echo "</div>\n";
		*/

		/*

		// Nbre de fichiers et de données
		/*
			echo "<div id='boxpopup' style='height:115px;' >\n";
						
				echo "<p style='font-weight:normal;'><span style='font-weight:bold;'>".htmlaccent('Nombre de fichiers de données importés : ')."</span>".number_format($nb_type_meta, 0, '.', ' ')."</p>";
				echo "<p style='font-weight:normal;'><span style='font-weight:bold;'>".htmlaccent('Nombre total de données : ')."</span>".number_format($nb_data_all, 0, '.', ' ')."</p>";
				
			echo "</div>\n";
		*/

		// Liens
		echo "<div id='boxpopup' style='height:100px;padding:10px;margin-right: 25px;' >\n";

			echo "<p style='font-weight:normal;margin-bottom:10px;'><span style='font-weight:bold;'>".htmlaccent('Liens : ')."</span></p>";
			
			echo "<p style='margin-bottom8px;'>";
				echo "<a href='data_chron.php?id_st=".$id_station."' style='font-size:12px;' target='_blank' >";
					echo ">> Données de la station";
				echo "</a>";
			echo "</p>";

			// RA

			$tabForm = [
							['name' => 'search_station', 'value' => $code_station],
							['name' => 'select_periode', 'value' => 0]
						];
			$tabFormJson = json_encode($tabForm);
			$tabFormJson = htmlspecialchars($tabFormJson, ENT_QUOTES, 'UTF-8');

			echo "<p style=''>";
				echo "<a href='#' style='font-size:12px;' onclick=\"event.preventDefault();linkSubmitForm('list_ra.php', ".$tabFormJson.");\">";
					echo ">> Derniers Rapports d'Activité";
				echo "</a>";
			echo "</p>";



			if($id_eq_type == 11)
			{
				$sql_ETL = "SELECT DISTINCT id
							FROM ".TABLE_DATA_ETL." 
							WHERE id_station=?
							LIMIT 1";
				$stmt_ETL = mysqli_prepare($sql_link, $sql_ETL);
				mysqli_stmt_bind_param($stmt_ETL, "i", $id_station);
				mysqli_stmt_execute($stmt_ETL);
				$ETL_query = mysqli_stmt_get_result($stmt_ETL);

				$sql_JGE = "SELECT DISTINCT id
							FROM ".TABLE_DATA_JGE." 
							WHERE id_station=?
							LIMIT 1";
				$stmt_JGE = mysqli_prepare($sql_link, $sql_JGE);
				mysqli_stmt_bind_param($stmt_JGE, "i", $id_station);
				mysqli_stmt_execute($stmt_JGE);
				$JGE_query = mysqli_stmt_get_result($stmt_JGE);

				if(mysqli_num_rows($JGE_query) > 0)
				{
					$tabForm = [
									['name' => 'search_station', 'value' => $code_station],
									['name' => 'select_periode', 'value' => 0]
								];
					$tabFormJson = json_encode($tabForm);
					$tabFormJson = htmlspecialchars($tabFormJson, ENT_QUOTES, 'UTF-8');

					echo "<p style=''>";
						echo "<a href='#' style='font-size:12px;' onclick=\"event.preventDefault();linkSubmitForm('data_jge.php', ".$tabFormJson.");\">";
							echo ">> Liste des Jaugeages";
						echo "</a>";
					echo "</p>";
				}

				

				if(mysqli_num_rows($ETL_query) > 0 || mysqli_num_rows($JGE_query) > 0)
				{
					$tabForm = [
									['name' => 'st', 'value' => $id_station]
								];
					$tabFormJson = json_encode($tabForm);
					$tabFormJson = htmlspecialchars($tabFormJson, ENT_QUOTES, 'UTF-8');

					echo "<p style=''>";
						echo "<a href='#' style='font-size:12px;' onclick=\"event.preventDefault();linkSubmitForm('modif_etl.php', ".$tabFormJson.");\">";
							echo ">> Relation d´Etalonnage";
						echo "</a>";
					echo "</p>";
				}


			}

		echo "</div>\n";

		echo "<div id='boxpopup' style='height:100px;padding:10px;' >\n";

			echo "<div id='button_pdf' style='float:left;width:150px;'>";

				echo "<img src='".DIR_WS_IMG_ICO."pdf.png' style='float:left;width:22px;margin-top:3px;margin-right:10px;'>"; 
				echo "<p style='margin-top:12px;' id='textPDF' >";
					echo "<span style='font-size:13px;'>";
						echo "Générer le PDF";
					echo "</span>";	
				echo "</p>";							
				echo "<img src='".DIR_WS_IMG."wait.gif' style='width:25px;margin-top:5px;display: none;' id='waitPDF'>"; 

			echo "</div>";

			echo "<hr>";

			echo "<div id='button_xls' style='float:left;width:150px;margin-top:5px;'>";

				echo "<img src='".DIR_WS_IMG_ICO."xls.png' style='float:left;width:22px;margin-top:3px;margin-right:10px;'>"; 
				echo "<p style='margin-top:12px;' 
							title='"."Télécharger les données de la Station"."'		
							onClick=\"downloadStation_xls('".$id_station."');\">";
					
					echo "<span style='font-size:13px;'>";
						echo "Export Xls";
					echo "</span>";	

				echo "</p>";							
				//echo "<img src='".DIR_WS_IMG."wait.gif' style='width:25px;margin-top:5px;display: none;' id='waitPDF'>"; 

			echo "</div>";

		echo "</div>\n";
	
		
	echo "<hr>\n";
	echo "</div>\n";

	// Affichages du résumé des données liées à la station
		
	echo "<div id='boite1' class='first' style='float:left;width:98%;margin-bottom:0;margin-right:0;'>\n";
	
		echo "<p class='titre_box' style='margin-bottom:10px;'>".htmlaccent('Données disponibles sur cette station')."</p>\n";	

		echo "<div style='float:left;width:330px;margin-right:2%;'>";

			echo "<div id='cadre_data_station' style='width:none;border: 1px solid #e0e0e0;'>\n";
				
				echo "<div id='cadre_wait_tab' style='width:100%;height:50px;margin-top:10px;text-align:center;'>";	   
					echo "<img src='".DIR_WS_IMG."wait.gif' style='width:40px;'>";                 
				echo "</div>";
			
			echo "</div>\n";

			echo "<hr>\n";

			echo "<div style='width:100%;margin-top:15px;margin-left:15px;'>\n";

				echo "<img src='".DIR_WS_IMG_ICO."info.png' style='float:left;width:20px;margin-left:5px;margin-right:10px;'>";    
				echo "<p style='float:left;margin:0;margin-top:3px;'>";
					echo "<a onClick='afficheBlockInfoChron();'>";
						echo "<span style='font-size:13px;font-weight:bold;'>".htmlaccent('Détails sur les chroniques')."</span>";
					echo "</a>\n";
				echo "</p>\n";
			
			
			echo "<hr>";
			echo "</div>\n";

			echo "<div style='width:100%;margin-top:15px;margin-left:15px;'>\n";

				echo "<img src='".DIR_WS_IMG_ICO."list.png' style='float:left;width:20px;margin-left:5px;margin-right:10px;'>";    
				echo "<p style='float:left;margin:0;margin-top:3px;'>";
					echo "<a onClick='afficheBlockHistoryChron();'>";
						echo "<span style='font-size:13px;font-weight:bold;'>".htmlaccent('Historique des modifications')."</span>";
					echo "</a>\n";
				echo "</p>\n";
			
			echo "<hr>";
			echo "</div>\n";


		echo "</div>\n";  

		echo "<div id='cadre_graph' style='float:none;overflow-y: auto;padding-bottom:30px;padding-right:0px;'>\n";
	
			echo "<div id='boxpopup' class='select' 
						style='width:97%;margin:0;padding:0;padding-top:20px;padding-bottom:25px;
								box-shadow: 5px 10px 38px -27px #232323;'>\n";
		
				echo "<div id='cadre_wait_graph' style='width:100%;height:50px;margin-top:10px;text-align:center;'>";	   
						echo "<img src='".DIR_WS_IMG."wait.gif' style='width:40px;'>";                 
				echo "</div>";

				echo "<div id='plot' style='float:left;width:80%;'></div>\n";

				echo "<div id='cadre_code_qual' style='float:right;width:15%;margin-top:0px;'>\n";
				echo "</div>\n";	

			echo "</div>\n";
			
		echo "</div>\n";
	
	echo "</div>\n";
	
	
echo "<hr>\n";
echo "</div>\n";
?>


<script>

	var cadreData = document.getElementById('cadre_data_station');
	var cadreGraph = document.getElementById('cadre_data_station'); 
	var cadreCodeQual = document.getElementById('cadre_code_qual'); 
	
	var waitBoxTab = document.getElementById('cadre_wait_tab');
	var waitBoxGraph = document.getElementById('cadre_wait_graph');

	var idStation = <?php echo $id_station; ?>; 
	var idEqType = <?php echo $id_eq_type; ?>; 

	loadData();
	
	function loadData()
    {        
        waitBoxTab.style.display = 'block';
		waitBoxGraph.style.display = 'block';

        // Mise au format JSON des données
        // Créer un objet contenant les données à envoyer
        var dataToSend = {
            idStation: idStation,
			idEqType: idEqType
        };

        // Convertir l'objet en JSON
        var jsonData = JSON.stringify(dataToSend);

        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/station/process_loaddata.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);
            
				// Tableau en html
				nb_chron = jsonResponse['nb_chron'];

				if(nb_chron > 1)
				{
					html_tab_data = jsonResponse['js_tab_data'];
					
					
					// Edition du graphique
					var plotDiv = document.getElementById('plot');
					plotDiv.style.height = '42vh'; 
					
					js_graph = jsonResponse['js_graph'];
					eval(js_graph); 

					html_tab_code_cal = jsonResponse['js_tab_code_cal'];
					cadreCodeQual.innerHTML = html_tab_code_cal;
				}
				else
				{
					cadreGraph.style.display='none';
					html_tab_data = "Aucune donnée n'est enregistrée sur cette station";
				}


				cadreData.innerHTML = html_tab_data;
				waitBoxTab.style.display = 'none';
				waitBoxGraph.style.display = 'none';
				

                
            }
        };

        // Envoyer les données JSON au serveur
        xhr.send(jsonData);
    }	


	
</script>