
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
	$where_search = search_agent($search_agent,'');
}


// Requête SQL pour récupérer les données


require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

    echo "<div id='contenu_info' style='display:none;'></div>";
    
	require(DIR_WS_AGENT . 'block_agent_delete.php'); // Block pour permettre une confirmation de la suppression d'un RA
    require(DIR_WS_AGENT . 'block_agent.php'); // Block pour affichage d'une fiche Agent en premier plan	

    require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
    include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

    echo "<div id='contour_general'>";
        
        echo "<div id='contenu_centre'>";
                
            echo "<div id='contenu_box2'>";
            
                echo "<h1>";
                    echo "<span>".htmlaccent('Liste des agents')."</span>";
                echo "</h1>";
                

                // Champs de recherche sur la colonne de Gauche

                $lien_form = tep_href_link('list_agents.php');	
                $name_form = 'form_agents';							
                echo "<form name='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data'>";
                            
                    echo "<div id='cadre_graph' style='float:left;width:250px;margin-right:1%;height:70vh;overflow-y: auto;'>\n"; 
                    
                        echo "<div id='boxpopup' class='select-top' style='width:92%;padding:10px 3%;margin-bottom:10px;'>\n";

                            // Boutton pour Saisir un nouvel Agent
                            echo "<div id='button_titre' style='margin-left:19%;' onClick='loadFicheAgent(0)'>";
                                echo htmlaccent('Nouvel Agent');
                            echo "</div>\n";

                        echo "</div>";
                                
                        echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding: 0 3%;'>\n";
                        
                            echo "<p style='float:left;width:90%;margin-top:15px;padding-top:5px;color: #609966;'>".htmlaccent('Rechercher')."</p>";					
                                
                            echo "<div id='contenu_search' style='float:left;width:90%;' >";
                            
                                echo "<input name='search_agent' type='text' value='".$search_agent."' style='float:left;width:70%;'>";
                                echo "<img src='".DIR_WS_IMG_ICO."arrow.png' alt='Rechercher' onclick='form_agents.submit();' style='float:left;width:28px;margin-left: 10px;'/>";
                                
                            echo "</div>";

                                    
                            echo "<div id='contenu_infos'>";
                            
                                echo "<p>";

                                    echo "<span style='font-size:12px;'>".htmlaccent('Nombre Agents : ')."</span>";
                                    echo "<input type='text' id='nb_agents' value='' readonly style='float:right;width:50px;padding:0;font-size:12px;background:none;border:none;'>";
                                    echo "<br><br>";
                                    echo "<span style='font-size:12px;'>".htmlaccent('Nombre Agents '.$service_hydro.' : ')."</span>";
                                    echo "<input type='text' id='nb_agents_service' value='' readonly style='float:right;width:50px;padding:0;font-size:12px;background:none;border:none;'>";
                                    echo "<br><br>";
                                    echo "<span style='font-size:12px;'>".htmlaccent('Nombre Agents Terrain : ')."</span>";
                                    echo "<input type='text' id='nb_agents_terrain' value='' readonly style='float:right;width:50px;padding:0;font-size:12px;background:none;border:none;'>";
                                    
                                echo "</p>";   
                            
                            echo "<hr>";
                            echo "</div>";	
                        
                        
                        echo "</div>";

                    echo "</div>";

                echo "</form>";
                
                
                // TABLEAU GENERAL RA - Permet d'afficher la liste des RA

                echo "<div id='result_listAgents' class='table-container' style='float:none;width:auto;height:80vh;'>";

                    echo "<div style='width:95%;height:78vh;overflow-y: auto;'>";
                
                        echo "<table id='table_tri' cellspacing='0'>";
                    
                            // En-tête du tableau
                            echo "<thead>";
                                echo "<tr class='header-row'>";		
                                                
                                    echo "<th style='width:130px;'>".htmlaccent('Nom')."</th>";
                                    echo "<th style='width:130px;'>".htmlaccent('Prénom')."</th>";							
                                    echo "<th style='width:180px;'>".htmlaccent('Email')."</th>";
                                    echo "<th style='width:110px;'>".htmlaccent('Téléphone')."</th>";
                                    echo "<th style='width:180px;'>".htmlaccent('Institution')."</th>";						
                                    echo "<th style='width:200px;'>".htmlaccent('Fonction')."</th>";								
                                    echo "<th style='width:20px;text-align:center;'>".htmlaccent('Agent ').$service_hydro."</th>";	
                                    echo "<th style='width:20px;text-align:center;'>".htmlaccent('Agent Terrain')."</th>";	
                                    echo "<th style='width:40px;text-align:center;'></th>";	
                                    
                                echo "</tr>";

                                echo "<tr>";
                                    echo "<td colspan='8' style='height:15px;'>&nbsp;</td>";
                                echo "</tr>";
                            echo "</thead>";		
                            
                            echo "<tbody>";
                            echo "</tbody>";
                    
                        echo "</table>";

                    echo "</div>";


                    // Bloc pour attente de chargement

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
	var where_agents = '<?php echo $where_search; ?>';

    var blockListAgent = document.getElementById('result_listAgents');
	var nbAgents = document.getElementById('nb_agents');

	var wait = document.getElementById('wait'); // Attente pour le cahrgement du tableau des agents, occupe uniquement le tableau de la liste des agents

	var tbody_info = document.querySelector("#table_tri tbody"); // Pour récupérer le contenu du tableau d'affichage des corrections en cours

	var boxAgent = document.getElementById('box_agent');
    var formAgent = document.forms['formAgent'];

	var boxDelAgent = document.getElementById('box_del_agent');

	var contenuInfo = document.getElementById('contenu_info'); // popup d'affichage d'info


	// Fonction pour afficher la liste des RA sélectionnés par un appel vers le serveur
    function loadAgentsTab()
    {
        contenuInfo.style.display = 'none';
        blockListAgent.style.display = 'none';
        wait.style.display = 'block';

        // Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
                            territoire_id: territoire_id,
                            where_agents: where_agents
                        };
        
        
        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/agent/process_agents_tab.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

                nb_agents = jsonResponse['nb_agents'];
                //nb_agents_service = jsonResponse['nb_agents_service'];
                //nb_agents_terrain = jsonResponse['nb_agents_terrain'];

                nbAgents.value = nb_agents;
                //nbAgentsService.value = nb_agents;
                //nbAgentsTerrain.value = nb_agents;

                tab_html = jsonResponse['tab_html'];				
                tbody_info.innerHTML = tab_html; // Ajoute la ligne dans le tableau des fichiers importables

                hidden_html_agent = jsonResponse['hidden_html_agent'];
                formAgent.insertAdjacentHTML('afterbegin', hidden_html_agent);

                blockListAgent.style.display = 'block';
                wait.style.display = 'none';
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
    }
	
	// ------------------------------------------

    loadAgentsTab(); // On lance le chargement de la table RA
    // ------------------------------------------

    function saveAgent(event)
	{
		//boxWait.style.display = 'block';

		event.preventDefault(); // Empêche la soumission par défaut du formulaire

		//var form = document.getElementById('formAgent'); // Récupérer l'élément du formulaire		
		var formData = new FormData(formAgent); // Créer un objet FormData à partir du formulaire

		// Ajouter des données supplémentaires à envoyer
		formData.append('territoire_id', territoire_id);
		formData.append('id_user_agent', id_user_agent);

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/agent/process_agent_save.php", true);

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                //box_ra.style.display = 'none';
				
				// Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

				erreur = jsonResponse['erreur'];
				id_agent = jsonResponse['id_agent'];
				msg_info = jsonResponse['msg_info'];

				if(!erreur)
				{
					loadAgentsTab();
                    //loadFicheAgent(id_agent);
					contenuInfo.innerHTML = msg_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert
				}
				else
				{
					contenuInfo.innerHTML = msg_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
				}

				//boxWait.style.display = 'none';
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(formData);
	}


    function verifDelAgent(id_agent)
	{
		// Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
                            id_agent: id_agent
						};

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/agent/process_agent_verifdelete.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

				tab_html = jsonResponse['tab_html'];

				boxDelAgent.innerHTML = tab_html; // On met à jour les champs du block DelRA
				boxDelAgent.style.display = 'block';								
            }
        };

		// Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
		xhr.send(JSON.stringify(dataToSend));
	}

    function delAgent(id_agent)
	{
		// Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
							id_agent: id_agent,
							id_user_agent: id_user_agent
						};

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/agent/process_agent_delete.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
				loadAgentsTab(); // On recharge d'abord la liste des RA
             
				// Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

				boxDelAgent.style.display = 'none';

				msg_info = jsonResponse['msg_info'];
				del = jsonResponse['del'];
					
				contenuInfo.innerHTML = msg_info;						
                contenuInfo.style.display = 'block';
				
				if(del){contenuInfo.style.border = '4px solid #09886d';}
				else{contenuInfo.style.border = '4px solid #930000';}
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
	}




    // FONCTION POUR MODIFIER LES CHAMPS DES FICHES ACGENTS

    // Fonction pour afficher la fiche agent
    function loadFicheAgent(id_agent)
    {
        boxAgent.style.display = 'block';
     
        if(id_agent > 0)
        {
            nomAgent = document.getElementById('nom_'+id_agent).value;
            prenomAgent = document.getElementById('prenom_'+id_agent).value;

            document.getElementById('titre_fiche_agent').value = 'Fiche Agent : '+nomAgent+' '+prenomAgent;

            document.getElementById('id_agent_fiche').value = id_agent;

            document.getElementById('nom').value = nomAgent;
            document.getElementById('nom_marital').value = document.getElementById('nom_marital_'+id_agent).value;        
            document.getElementById('prenom').value = prenomAgent;

            document.getElementById('raisonsociale').value = document.getElementById('raisonsociale_'+id_agent).value;
            document.getElementById('numinscription').value = document.getElementById('numinscription_'+id_agent).value;        
            document.getElementById('fonction').value = document.getElementById('fonction_'+id_agent).value;

            document.getElementById('adresse').value = document.getElementById('adresse_'+id_agent).value;
            document.getElementById('lieudit').value = document.getElementById('lieudit_'+id_agent).value;        
            document.getElementById('bp').value = document.getElementById('bp_'+id_agent).value;
            document.getElementById('codepostal').value = document.getElementById('codepostal_'+id_agent).value;        
            
            select_commune = document.getElementById('id_commune_'+id_agent).value;
            listCommune = document.getElementById('select_commune');
            listCommune.selectedIndex = 0;
            // Parcourir les options du select
            for (var i = 0; i < listCommune.options.length; i++) 
            {
                // Vérifier si la valeur de l'option correspond à l'id_commune
                if (listCommune.options[i].value == select_commune) 
                {
                    // Définir l'option comme sélectionnée
                    listCommune.selectedIndex = i;
                    break;
                }
            }

            document.getElementById('tel').value = document.getElementById('tel_'+id_agent).value;
            document.getElementById('mobile').value = document.getElementById('mobile_'+id_agent).value;        
            document.getElementById('fax').value = document.getElementById('fax_'+id_agent).value;
            document.getElementById('email').value = document.getElementById('email_'+id_agent).value;     
            document.getElementById('siteweb').value = document.getElementById('siteweb_'+id_agent).value;     

            check_agent_service_hydro = document.getElementById('service_hydro_'+id_agent).value;
            document.getElementById('check_service_hydro').checked = false;
            if(check_agent_service_hydro > 0){document.getElementById('check_service_hydro').checked = true;}

            check_agent_terrain = document.getElementById('terrain_'+id_agent).value;
            document.getElementById('check_terrain').checked = false;
            if(check_agent_terrain > 0){document.getElementById('check_terrain').checked = true;}

        }
        else
        {
            document.getElementById('titre_fiche_agent').value = 'Création d\'une nouvelle Fiche Agent';
        }
    }
    
</script>