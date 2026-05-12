
<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Affichage de la Liste des agents avec options de sélection
Procedure AJAX pour toutes les actions
----------------------------------------
*/

require('include/application_top.php');

$message_info = '';
$message_suppr_agent = '';
$row = 0;

$search_agent = '';
$where_search = '';

$nb_agents = 0;
$nb_agents_service = 0;
$nb_agents_terrain = 0;


// Recherche dans les champs de la table Agents
if(isset($_POST['search_agent']) || isset($_GET['search_agent']))
{
	if(isset($_POST['search_agent'])){$search_agent = post_secure($sql_link,$_POST['search_agent']);}
	if(isset($_GET['search_agent'])){$search_agent = post_secure($sql_link,$_GET['search_agent']);}
	
	$where_search = search_agent($search_agent,'');
}


// Requête SQL pour récupérer les données


require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

    echo "<div id='contenu_info' style='display:none;'></div><hr>";

    require(DIR_WS_STRUCTURE . 'block_wait.php'); // Block d'attente pendant les interrogations au serveur
    //require(DIR_WS_AGENT . 'block_agent_delete.php'); // Block pour permettre une confirmation de la suppression d'un RA
    //require(DIR_WS_AGENT . 'block_agent.php'); // Block pour affichage d'une fiche RA en premier plan	
    

    require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
    include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

    echo "<div id='contour_general'>";
        
        echo "<div id='contenu_centre'>";
                
            echo "<div id='contenu_box2'>";
            
                echo "<h1>";
                    
                    echo "<span>".htmlaccent('Liste des agents')."</span>";
                
                    // Boutton pour Saisir un nouvel Agent
                    echo "<div id='button_titre' onClick='loadAgent(0)'>";
                        echo htmlaccent('Nouvel Agent');
                    echo "</div>\n";

                echo "</h1>";
                

                // Champs de recherche sur la colonne de Gauche

                $lien_form = tep_href_link('list_agents.php');	
                $name_form = 'form_agents';							
                echo "<form name='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data'>";
                            
                    echo "<div id='cadre_graph' style='float:left;width:15%;height:70vh;overflow-y: auto;'>\n"; 
                                
                        echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding: 0 3%;'>\n";
                        
                            echo "<p style='float:left;width:90%;margin-top:15px;padding-top:5px;color: #609966;'>".htmlaccent('Rechercher')."</p>";					
                                
                            echo "<div id='contenu_search' style='float:left;width:90%;' >";
                            
                                echo "<input name='search_agent' type='text' value='".$search_agent."' style='float:left;width:70%;'>";
                                echo "<img src='".DIR_WS_IMG_ICO."arrow.png' alt='Rechercher' onclick='form_select_agents.submit();' style='float:left;width:28px;margin-left: 10px;'/>";
                                
                            echo "</div>";

                                    
                            echo "<div id='contenu_infos'>";
                            
                                echo "<p>";

                                    echo "<span style='font-size:13px;'>".htmlaccent('Nombre Agents : ')."</span>";
                                    echo "<input type='text' id='nb_agents' value='' readonly style='float:right;width:50px;padding:0;font-size:13px;background:none;border:none;'>";
                                    echo "<br><br>";
                                    echo "<span style='font-size:13px;'>".htmlaccent('Nombre Agents '.$service_hydro.' : ')."</span>";
                                    echo "<input type='text' id='nb_agents_service' value='' readonly style='float:right;width:50px;padding:0;font-size:13px;background:none;border:none;'>";
                                    echo "<br><br>";
                                    echo "<span style='font-size:13px;'>".htmlaccent('Nombre Agents Terrain : ')."</span>";
                                    echo "<input type='text' id='nb_agents_terrain' value='' readonly style='float:right;width:50px;padding:0;font-size:13px;background:none;border:none;'>";
                                    
                                echo "</p>";   
                            
                            echo "<hr>";
                            echo "</div>";	
                        
                        echo "<hr>";
                        echo "</div>";

                    echo "</div>";

                echo "</form>";
                
                
                // TABLEAU GENERAL RA - Permet d'afficher la liste des RA

                echo "<div id='result_listAgent' class='table-container' style='float:left;width:80%;height:78vh;margin-left:1%;'>";

                    echo "<table id='table_tri' cellspacing='0'>";
                
                        // En-tête du tableau
                        echo "<thead>";
                            echo "<tr class='header-row'>";		
                                            
                                echo "<th style='width:200px;'>".htmlaccent('Nom')."</th>";
								echo "<th style='width:200px;'>".htmlaccent('Prénom')."</th>";							
								echo "<th style='width:150px;'>".htmlaccent('Email')."</th>";
								echo "<th style='width:100px;'>".htmlaccent('Téléphone')."</th>";
								echo "<th style='width:150px;'>".htmlaccent('Institution')."</th>";						
								echo "<th style='width:150px;'>".htmlaccent('Fonction')."</th>";								
								echo "<th style='width:80px;'>".htmlaccent('Agent ').$service_hydro."</th>";	
								echo "<th style='width:80px;text-align:center;'>".htmlaccent('Agent Terrain')."</th>";	
                                echo "<th style='width:40px;text-align:center;'></th>";	
                                
                            echo "</tr>";

                            echo "<tr>";
                                echo "<td colspan='8' style='height:15px;'>&nbsp;</td>";
                            echo "</tr>";
                        echo "</thead>";		
                        
                        echo "<tbody>";
                        echo "</tbody>";
                
                    echo "</table>";

                    echo "<div id='wait' style='width:100%;height:65px;margin-top:30px;text-align:center;'>";
                        echo "<img src='".DIR_WS_IMG."hp100.gif' style='width:150px;' title='".htmlaccent('Chargement en cours ...')."'>";
                        echo "<p style='text-align:center;color:#000;'>".htmlaccent('Chargement en cours ...')."</p>";
                        echo "<p style='text-align:center;'>".htmlaccent('- Veuillez patienter -')."</p>";
                    echo "</div>\n";  
                
                echo "<hr>";
                echo "</div>";
            
            echo "<hr>";
            echo "</div>";
        
        echo "<hr>";
        echo "</div>";

    echo "<hr>";
    echo "</div>";


	require('include/application_bottom.php'); 
	
	
	
echo "</body>";

echo "</html>";

?>	

<script>
	
	// Initialisation des variables
	var id_user_agent = '<?php echo $id_user; ?>';

	var territoire_id = '<?php echo $territoire_id; ?>';
	var where_agents = '<?php echo $where_agents; ?>';
	var order_agents = '<?php echo $order_agents; ?>';

    var blockListAgent = document.getElementById('blockListAgent');
	var nbAgents = document.getElementById('nb_agents');
	var nbAgentsService = document.getElementById('nb_agents_service');
	var nbAgentsTerrain = document.getElementById('nb_agents_terrain');

	var boxWait = document.getElementById('box_wait'); // Attente lors des opérations sur les RA, occupe l'ensemble de la page
	var wait = document.getElementById('wait'); // Attente pour le cahrgement du tableau des RA, occupe uniquement le tableau de la liste des RA

	var box_agent = document.getElementById('box_agent');
	var tbody_info = document.querySelector("#table_tri tbody"); // Pour récupérer le contenu du tableau d'affichage des corrections en cours

	var boxDelAgent = document.getElementById('box_del_agent');

	var contenuInfo = document.getElementById('contenu_info'); // popup d'affichage d'info

	var agent_nav_json = null; // On initialise une variable générale pour récupérer ensuite le contenu du tableau dans loadRATab()


	// Fonction pour afficher la liste des RA sélectionnés par un appel vers le serveur
    function loadAgentsTab()
    {
		return new Promise((resolve, reject) => {

			contenuInfo.style.display = 'none';
			blockListAgent.style.display = 'none';
			wait.style.display = 'block';

			// Créer un objet JavaScript contenant les données à envoyer
			var dataToSend = {
								territoire_id: territoire_id,
								where_agents: where_agents,
								order_agents: order_agents
							};
			
			
			// Effectuer une requête AJAX asynchrone
			var xhr = new XMLHttpRequest();
			xhr.open("POST", "include/structure/ra/process_agents_tab.php", true);
			xhr.setRequestHeader("Content-Type", "application/json");

			xhr.onreadystatechange = function() 
			{
				if (xhr.readyState === 4 && xhr.status === 200) 
				{
					// Analyser la réponse JSON
					var jsonResponse = JSON.parse(xhr.responseText);

					nb_agents = jsonResponse['nb_agents'];
                    nb_agents_service = jsonResponse['nb_agents_service'];
                    nb_agents_terrain = jsonResponse['nb_agents_terrain'];

					nbAgents.value = nb_agents;
                    nbAgentsService.value = nb_agents;
                    nbAgentsTerrain.value = nb_agents;

					tab_html = jsonResponse['tab_html'];				
					tbody_info.innerHTML = tab_html; // Ajoute la ligne dans le tableau des fichiers importables

					agent_nav_json = jsonResponse['agent_nav_json'];

					blockListAgent.style.display = 'block';
					wait.style.display = 'none';
					
                    resolve(); // Résoudre la promesse après que tout soit fait (nécessaire pour lancer le processus de façon asynchron et s'assurer que les donneés sont chargées)
				}
			};

			// Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
			xhr.send(JSON.stringify(dataToSend));

		});
    }
	

	// ------------------------------------------

    loadRATab(); // On lance le chargement de la table RA
    // ------------------------------------------


    
</script>