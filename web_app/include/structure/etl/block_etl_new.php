<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Ce block permet de créer un nouvel ETL
Il est accessible à partir de la page modif_etl.php 
----------------------------------------
*/

echo "<div id='box_elt_new' class='block_view' style='width:600px;height:400px;margin-left:35%;background:transparent;'>\n"; // style='background:transparent;'

    echo "<div id='cadre_view_2' style='padding:0px;background-color:#FBF9F1;' >\n";

        echo "<div>";	

            echo "<p style='float:left;width:100%;height:30px;padding-left:8px;font-size:18px;font-weight:bold;color:#fff;margin:0px;background-color:#000;'>";
                echo htmlaccent('Créer un nouvel ETL');
            echo "</p>\n";  

        echo "</div>\n";    
	
		echo "<div style='margin: 0 5%;margin-top:55px;'>";	

            // Equation courbe (à choisir ou à saisir)
            echo "<div style='width:100%;'>";

                echo "<p style='font-size:14px;font-weight:bold;'>".htmlaccent('Type de courbe')."</p>\n";	

                echo "<table style='font-size:12px;'>";

                        echo "<tr>";

                            echo "<td style='width:280px;'>".htmlaccent('Equation')."</td>";
                            echo "<td style='width:200px;'>".htmlaccent('H0 (cm) - coord. à l\'origine')."</td>";

                        echo "</tr>";

                        echo "<tr>";

                            echo "<td>";

                                echo "<select name='new_eq_etl' id='new_eq_etl' style='width:250px;height:30px;font-size:16px;'>";
										
                                    echo "<option value='1' >".htmlaccent('Q = 10^b * H^a')."</option>";
                                    echo "<option value='2' >".htmlaccent('Q = a * H + b')."</option>";                                    
                                    echo "<option value='3' >".htmlaccent('Q = log(H)')."</option>";
								
                                echo "</select>";

                            echo "</td>";

                            echo "<td>";
                                echo "<input style='width:80px;height:25px;font-size:16px;' id='origine_h0' type='text' value='0'  >\n"; 
                            echo "</td>";
                        echo "</tr>";

                    echo "</table>";

            echo "</div>";


            // Domaine de validité et densité de points
            echo "<div style='width:100%;margin-top:25px;'>";

                echo "<div style='float:left;width:50%;'>";

                    echo "<p style='font-size:14px;font-weight:bold;'>".htmlaccent('Début période')."</p>\n";	

                    echo "<table style='font-size:12px;'>";

                        echo "<tr>";

                            echo "<td style='width:120px;'>".htmlaccent('Date (dd-mm-yyyy)')."</td>";
                            echo "<td style='width:120px;'>".htmlaccent('Heure (hh:mm:ss)')."</td>";

                        echo "</tr>";

                        echo "<tr>";
                            echo "<td>";
                                echo "<input style='width:90px;height:20px;font-size:14px;' id='new_date_debut_periode' type='text' value=''  >\n"; 
                            echo "</td>";
                            echo "<td>";
                                echo "<input style='width:90px;height:20px;font-size:14px;' id='new_heure_debut_periode' type='text' value='00:00:00'>\n"; 
                            echo "</td>";
                        echo "</tr>";

                    echo "</table>";
                            
                    echo "<p style='font-size:14px;font-weight:bold;margin-top:10px;'>".htmlaccent('Fin période')."</p>\n";	

                    echo "<table style='font-size:12px;'>";

                        echo "<tr>";

                            echo "<td style='width:120px;'>".htmlaccent('Date (dd-mm-yyyy)')."</td>";
                            echo "<td style='width:120px;'>".htmlaccent('Heure (hh:mm:ss)')."</td>";

                        echo "</tr>";

                        echo "<tr>";
                            echo "<td>";
                                echo "<input style='width:90px;height:20px;font-size:14px;' id='new_date_fin_periode' type='text' value='".$date_now."'  >\n"; 
                            echo "</td>";
                            echo "<td>";
                                echo "<input style='width:90px;height:20px;font-size:14px;' id='new_heure_fin_periode' type='text' value='00:01:00'>\n"; 
                            echo "</td>";
                        echo "</tr>";

                    echo "</table>";

                echo "</div>\n";

                // Densité des points
                echo "<div style='float:right;width:50%;'>";

                    echo "<p style='float:left;font-size:14px;font-weight:bold;'>".htmlaccent('Densité de points')."</p>\n";	
                        
                    echo "<hr>";
                    
                    echo "<table style='font-size:12px;'>";

                        echo "<tr>";

                            echo "<td style='width:80px;'>".htmlaccent('Borne inf.')."</td>";
                            echo "<td style='width:80px;'>".htmlaccent('Borne sup.')."</td>";
                            echo "<td style='width:80px;'>".htmlaccent('Interval (cm)')."</td>";

                        echo "</tr>";

                        echo "<tr>";
                            echo "<td>";
                                echo "<input style='width:50px;height:15px;font-size:14px;' id='inf_1' type='text' value='0'  >\n"; 
                            echo "</td>";
                            echo "<td>";
                                echo "<input style='width:50px;height:15px;font-size:14px;' id='sup_1' type='text' value='100'>\n"; 
                            echo "</td>";
                            echo "<td>";
                                echo "<input style='width:50px;height:15px;font-size:14px;' id='interv_1' type='text' value='10'>\n"; 
                            echo "</td>";
                        echo "</tr>";

                        echo "<tr>";
                            echo "<td>";
                                echo "<input style='width:50px;height:15px;font-size:14px;' id='inf_2' type='text' value='110'  >\n"; 
                            echo "</td>";
                            echo "<td>";
                                echo "<input style='width:50px;height:15px;font-size:14px;' id='sup_2' type='text' value='200'>\n"; 
                            echo "</td>";
                            echo "<td>";
                                echo "<input style='width:50px;height:15px;font-size:14px;' id='interv_2' type='text' value='20'>\n"; 
                            echo "</td>";
                        echo "</tr>";

                        echo "<tr>";
                            echo "<td>";
                                echo "<input style='width:50px;height:15px;font-size:14px;' id='inf_3' type='text' value='250'  >\n"; 
                            echo "</td>";
                            echo "<td>";
                                echo "<input style='width:50px;height:15px;font-size:14px;' id='sup_3' type='text' value='500'>\n"; 
                            echo "</td>";
                            echo "<td>";
                                echo "<input style='width:50px;height:15px;font-size:14px;' id='interv_3' type='text' value='50'>\n"; 
                            echo "</td>";
                        echo "</tr>";

                        echo "<tr>";
                            echo "<td>";
                                echo "<input style='width:50px;height:15px;font-size:14px;' id='inf_4' type='text' value='550'  >\n"; 
                            echo "</td>";
                            echo "<td>";
                                echo "<input style='width:50px;height:15px;font-size:14px;' id='sup_4' type='text' value='1000'>\n"; 
                            echo "</td>";
                            echo "<td>";
                                echo "<input style='width:50px;height:15px;font-size:14px;' id='interv_4' type='text' value='100'>\n"; 
                            echo "</td>";
                        echo "</tr>";

                    echo "</table>";

                echo "</div>\n";

            echo "</div>\n";
                
            echo "<div style='float:left;margin-top:25px;'>";

                echo "<input type='submit' style='float:left;width:120px;' class='button' name='new_etl' id='new_etl' value='Générer' >";

                echo "<input type='button' style='float:left;width:120px;margin-left:30px;' class='button_close'  value='Annuler' 
                        onclick=\"document.getElementById('box_elt_new').style.display='none';\" >";
                        
            echo "</div>\n";

            echo "<hr>";
		
		echo "</div>\n";	
		
	echo "</div>\n";

echo "</div>\n";

?>


<script>

    var box_elt_new = document.getElementById('box_elt_new'); 

    // Ajout d'un gestionnaire d'événements pour la touche Echap
	document.addEventListener("keydown", function(event) 
	{
		if (event.key === "Escape") 
		{
			// Ferme le popup et le popup d'info s'il a été ouvert
			box_elt_new.style.display = "none";
		}
    });



    newDateDebut = document.getElementById('new_date_debut_periode');
    newHeureDebut = document.getElementById('new_heure_debut_periode');
    newDateFin = document.getElementById('new_date_fin_periode');
    newHeureFin = document.getElementById('new_heure_fin_periode');

    // Fonction pour une gestion fluide des champs dans les box
    function newUpdateFieldsDate(selectedValue) 
    {
        if (!selectedValue) return;

        const idETL = selectedValue.split('_')[0];
        
        if (ETL_array[idETL]) 
        {
            newDateDebut.value = ETL_array[idETL].datetime_first.split(' ')[0];
            newHeureDebut.value = ETL_array[idETL].datetime_first.split(' ')[1];
            newDateFin.value = ETL_array[idETL].datetime_end.split(' ')[0];
            newHeureFin.value = ETL_array[idETL].datetime_end.split(' ')[1];
        } 
        else 
        {
            newDateDebut.value = '';
            newHeureDebut.value = '';
            newDateFin.value = '';
            newHeureFin.value = '';
        }
    }
		  
</script>