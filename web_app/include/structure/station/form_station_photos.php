<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Onglet galerie photos pour la fiche station 
- Affichage des photos
- Enregistrement d'une nouvelle photo
- Suppression d'une photo
*/

$row = 0;




echo "<div id='onglet_contenu' style='overflow-y: auto;height:75vh;'>\n";

    echo "<div id='boite1' style='margin:0;margin-top:15px;margin-left:30px;'>\n";
           
        echo "<div style='
                            width:580px;
                            padding:10px;
                            border: 1px solid #e0e0e0;
                            border-radius: 8px;
                            background-color: #fff;
                            box-shadow: 5px 20px 38px -27px #232323;'>";

            echo "<p style='float:left;width:100%;margin-bottom:3px;font-weight:bold;color:#000;font-size:13px;'>";
                echo htmlaccent('Sélectionner une nouvelle photo (formats : .jpg .jpeg .png)');
                echo "<br>";   
                echo htmlaccent('La taille du fichier ne doit pas dépasser 2 Mo');
            echo "</p>";        

            echo "<div id='boite_small' style='width:350px;'>\n";
			
                echo "<h2>".htmlaccent('Description')."</h2>\n";
                echo "<input id='desc_photo' value='' class='input_texte_300' type='text'>";

            echo "</div>\n";

            echo "<div id='boite_small' style='width:200px;margin-right:0;'>\n";
			
                echo "<h2>".htmlaccent('Date de la photo - (jj-mm-aaaa)')."</h2>\n";
                echo "<input class='input_texte' style='width:80px;' id='date_photo' value='' type='text'  onclick=\"javascript:displayCalendar(document.getElementById('date_photo'),'dd-mm-yyyy',this);\") >";
            
            echo "</div>\n";

            echo "<hr>\n";

            echo "<input type='file' id='file_photo' name='file_photo' style='float:left;background-color:#fff;'>";
            
            echo "<button id='new_photo' class='zoom_graph' style='width:200px;margin-left:35px;padding:8px 5px;display:block;' >";
                echo "Enregistrer la photo";                 
            echo "</button>\n";

            echo "<button id='load_wait' class='zoom_graph' style='width:210px;margin-left:35px;padding:8px 5px;display:none;' >";            
                echo "<img src='".DIR_WS_IMG."wait.gif' style='float:left;width:15px;margin:0px;margin-left:5px;margin-right:15px;' >";    
                echo "<span style='float:left;'>Chargement en cours ...</span>";              
            echo "</button>\n";
        
        echo "<hr>\n";
        echo "</div>";

    echo "<hr>\n";
    echo "</div>\n";

    
	echo "<div id='tab_photos' style='margin-left:30px;'>\n";
    echo "<hr>\n";
	echo "</div>\n";
    	
	
echo "<hr>\n";
echo "</div>\n";

?>

<script>

var id_station = <?php echo $id_station; ?>;

var filePhoto = document.getElementById('file_photo');

var loadButton = document.getElementById('new_photo');
var waitPhoto = document.getElementById('load_wait');

var contenuInfo = document.getElementById('contenu_info');
var tab_photos = document.getElementById('tab_photos');

// On écoute l'opérateur qui permet de sélectionner un fichier
filePhoto.addEventListener('change', function(e)
{
    loadButton.style.display = 'block';
});

// Fonction permettant d'ajouter une nouvelle photo - Protocole Ajax
function new_photo(id_station)
{
    loadButton.style.display = 'none';
    waitPhoto.style.display = 'block';

    var desc_photo = document.getElementById('desc_photo').value;
    var date_photo = document.getElementById('date_photo').value;
    var file_photo = filePhoto.files[0];
    
    // Créer un objet FormData pour envoyer les données
    var formData = new FormData();
    formData.append('id_station', id_station);
    formData.append('desc_photo', desc_photo);
    formData.append('date_photo', date_photo);
    formData.append('file_photo', file_photo);

    // Effectuer une requête AJAX asynchrone
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "include/structure/station/process_newphoto.php", true);

    xhr.onreadystatechange = function() 
    {
        if (xhr.readyState === 4 && xhr.status === 200) 
        {
            contenuInfo.innerHTML  = xhr.responseText;
            contenuInfo.style.display = 'block';            
            
            load_photos(id_station);    
        }

        waitPhoto.style.display = 'none';
        loadButton.style.display = 'block';
    };

    // Envoyer les données JSON au serveur
    xhr.send(formData);
}

// Fonction de lancement de la procédure AJAX permettant de générer la galerie photo
function load_photos(id_station)
{
    // Créer un objet JavaScript contenant les données à envoyer
    var dataToSend = {
                        id_station: id_station
                    };
    
    // Effectuer une requête AJAX asynchrone
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "include/structure/station/process_loadphotos.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onreadystatechange = function() 
    {
        if (xhr.readyState === 4 && xhr.status === 200) 
        {
            // Traitement de la réponse du serveur
            tab_html = JSON.parse(xhr.responseText); 

            tab_photos.innerHTML = tab_html['tab_html']; // Ajoute la ligne dans le tableau des fichiers importables
        }
    };

    // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
    xhr.send(JSON.stringify(dataToSend));
}


// Fonction de lancement de la procédure AJAX permettant de supprimer 
function del_photos(id_photo)
{
    // Créer un objet JavaScript contenant les données à envoyer
    var dataToSend = {
                    id_photo: id_photo
                    };
    
    // Effectuer une requête AJAX asynchrone
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "include/structure/station/process_delphoto.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onreadystatechange = function() 
    {
        if (xhr.readyState === 4 && xhr.status === 200) 
        {
            contenuInfo.innerHTML  = xhr.responseText;
            contenuInfo.style.display = 'block';
            contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

            // On recharge la gallerie photo
            load_photos(id_station); 
        }
    };

    // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
    xhr.send(JSON.stringify(dataToSend));
}




// On écoute le bouton 'new_photo'. On lance la fonction new_photo qui va charger le fichier si il est valide
// On empêche égaleemnt la soumission du formulaire qui enregistre l'ensemble des données de la station
var boutonLoadPhoto = document.getElementById('new_photo');
boutonLoadPhoto.addEventListener('click', function()
{    
    event.preventDefault();// Empêcher le comportement par défaut du bouton (soumission du formulaire)
    new_photo(id_station);
});


// On lance le chargement de la gallerie des photos via une fonction qui fait appelle à une procédure Ajax
load_photos(id_station);


</script>