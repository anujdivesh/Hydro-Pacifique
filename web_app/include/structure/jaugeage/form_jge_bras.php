<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Onglet Jaugeage Pour le dépouillement d'un jaugeage avec graphique... Une feuille par bras
*/


// Initialisation var equation Hélice
$l1=0;$a1=0;$b1=0;$l2=0;$a2=0;$b2=0;$a3=0;$b3=0;

// Récupération des données du JGE (PTS)
// Dépouillement du jaugeage, on va récupérer les données points par points
// $nbb est la variable qui indique le num du bras


$modif_bras=false;
if(isset($jge_bras_array[$nbb])){$modif_bras=true;} // si nouveau jaugeage


$jge_pts_array = [];
$num_vert_encours = 0;
$dist_encours = 0;
$prof_max_encours = 0;
$num_pts = 1;

if($modif_bras)
{   
    if(isset($jge_bras_array[$nbb]))
    {
        $sql_jge_pts = "SELECT DISTINCT id, id_bras, num_vert, dist_depart, prof_max, prof_pts, nb_tours, tps_pts, obs, dist_calc, 
                                        debit_lam, prof_calc, vitesse_calc, vitesse_surf, vitesse_fond, vitesse_moy
                        FROM ".TABLE_DATA_JGE_PTS."
                        WHERE id_bras=".$jge_bras_array[$nbb]['id_bras']. "
                        ORDER BY dist_depart ASC, prof_pts DESC";
        
        $jge_pts_query = tep_db_query($sql_link,$sql_jge_pts);
        while ($jge_pts = tep_db_fetch_array($jge_pts_query))
        {   
            // Données Saisies        
            //$num_vert =  intval($jge_pts['num_vert']); On préfère renuméroter les verticales automatiquement
            $dist_depart =  round(floatval($jge_pts['dist_depart']),3);
            $prof_max =  round(floatval($jge_pts['prof_max']),3);
            $prof_pts =  round(floatval($jge_pts['prof_pts']),3);
            $nb_tours =  intval($jge_pts['nb_tours']);
            $tps_pts =  intval($jge_pts['tps_pts']);

            if($dist_depart > $dist_encours) // Si on est sur le premier points ou que l'on doit changer de vertical
            {
                $num_vert_encours++;            
                $dist_encours = $dist_depart;
                $prof_max_encours = $prof_max;
            }
            else
            {
                $dist_depart = $dist_encours;
                $prof_max = $prof_max_encours;
            }
            $num_vert = $num_vert_encours;


            $num_pts++;
            
            // Données Calculées
            $dist_calc =  round(floatval($jge_pts['dist_calc']),3);
            $debit_lam =  round(floatval($jge_pts['debit_lam']),3);
            $prof_calc =  round(floatval($jge_pts['prof_calc']),3);
            $vitesse_calc =  round(floatval($jge_pts['vitesse_calc']),3);
            $vitesse_surf =  round(floatval($jge_pts['vitesse_surf']),3);
            $vitesse_fond =  round(floatval($jge_pts['vitesse_fond']),3);
            $vitesse_moy =  round(floatval($jge_pts['vitesse_moy']),3);

            $obs = htmlaccent(html_entity_decode($jge_pts['obs'] ?? $default_string));    

            $jge_pts_array[$jge_pts['id']] = array('id_bras' => $jge_bras_array[$nbb]['id_bras'],
                                                'num_vert' => $num_vert,
                                                'dist_depart' => $dist_depart,
                                                'prof_max' => $prof_max,
                                                'prof_pts' => $prof_pts,
                                                'nb_tours' => $nb_tours,
                                                'tps_pts' => $tps_pts,
                                                'dist_calc' => $dist_calc,
                                                'debit_lam' => $debit_lam,
                                                'prof_calc' => $prof_calc,
                                                'vitesse_calc' => $vitesse_calc,
                                                'vitesse_surf' => $vitesse_surf,
                                                'vitesse_fond' => $vitesse_fond,
                                                'vitesse_moy' => $vitesse_moy,
                                                'obs' => $obs);
        }
    }
}

$nb_jge_pts = 0;
if(isset($jge_pts_array)){$nb_jge_pts = sizeof($jge_pts_array);}// Nb de Points de Jaugeage



// Affichage de l'onglet 



echo "<div id='onglet_contenu' style='overflow-y: auto;height:78vh;'>\n";

    if($modif_bras){echo "<input type='hidden' name='id_bras_".$nbb."' id='id_bras_".$nbb."' value='".$jge_bras_array[$nbb]['id_bras']."'>";}    
    else{echo "<input type='hidden' name='id_bras_".($nb_bras_tab+1)."' id='id_bras_".($nb_bras_tab+1)."' value='0'>";}

    echo "<div id='boite1' class='first' style='margin-top:10px;margin-bottom:10px;'>\n";

        echo "<div style='float:left;width:13%;' >\n";

            // Heure début du Jaugeage        
            echo "<div id='boite_small' style='width:90%'>\n";
                            
                echo "<p style='float:left;font-weight: bold;color: #000;margin-top:5px;margin-bottom:10px;'>";
                    echo htmlaccent('Heure Début');
                echo "</p>";
                        
                $value = '';
                if($modif_bras){$value = $jge_bras_array[$nbb]['heure_first'];}
                echo "<input type='text' style='float:right;width:50px;' id='heure_first_".$nbb."' name='heure_first_".$nbb."' value='".$value."'>\n";
                        
            echo "</div>\n";

            // Hauteur d'échelle début        
            echo "<div id='boite_small' style='width:90%'>\n";
                            
                echo "<p style='float:left;font-weight: bold;color: #000;margin-top:5px;margin-bottom:10px;'>";
                    echo htmlaccent('Echelle Début [cm]');
                echo "</p>";
                        
                $value = '';
                if($modif_bras){$value = $jge_bras_array[$nbb]['h_ech_first'];}
                echo "<input type='text' style='float:right;width:30px;' id='h_ech_first_".$nbb."' name='h_ech_first_".$nbb."' value='".$value."'>\n";
                        
            echo "</div>\n";

            // Heure fin du Jaugeage        
            echo "<div id='boite_small' style='width:90%'>\n";
                            
                echo "<p style='float:left;font-weight: bold;color: #000;margin-top:5px;margin-bottom:10px;'>";
                    echo htmlaccent('Heure Fin');
                echo "</p>";
                        
                $value = '';
                if($modif_bras){$value = $jge_bras_array[$nbb]['heure_end'];}
                echo "<input type='text' style='float:right;width:50px;' id='heure_end_".$nbb."' name='heure_end_".$nbb."' value='".$value."'>\n";
                        
            echo "</div>\n";

            // Hauteur d'échelle fin        
            echo "<div id='boite_small' style='width:90%'>\n";
                            
                echo "<p style='float:left;font-weight: bold;color: #000;margin-top:5px;margin-bottom:10px;'>";
                    echo htmlaccent('Echelle Fin [cm]');
                echo "</p>";
                        
                $value = '';
                if($modif_bras){$value = $jge_bras_array[$nbb]['h_ech_end'];}
                echo "<input type='text' style='float:right;width:30px;' id='h_ech_end_".$nbb."' name='h_ech_end_".$nbb."' value='".$value."'>\n";
                        
            echo "</div>\n";

        echo "</div>\n";
         
        echo "<div style='float:left;width:20%;' >\n";

            // Fond du lit
            echo "<div id='boite_small' style='width:90%;'>\n";
                        
                echo "<p style='font-weight: bold;color: #000;margin-top:5px;margin-bottom:0px;'>";
                    echo htmlaccent('Fond du lit');
                echo "</p>";

                echo "<div style='width:100%'>\n";

                    $value = '';
                    if($modif_bras){$value = $jge_bras_array[$nbb]['fond_text'];}

                    if (isset($data_jge_fondlit_array)) 
                    {
                        foreach ($data_jge_fondlit_array as $key => $value_fond) 
                        {
                            $checked = (strpos($value, $value_fond['titre']) !== false) ? 'checked' : '';
                
                            echo "<div style='float:left;'>";
                                echo "<input type='checkbox' id='check_fondlit_".$nbb."_".$key."' name='check_fondlit_".$nbb."_".$key."' value='".$key."' data-value='".$value_fond['titre']."' onchange='updateSelectedFond(".$nbb.");' ".$checked."> ";
                                echo "<span style='font-size:10px;'>".htmlaccent($value_fond['titre'])."</span>";
                            echo "</div>";
                        }
                    }
                echo "</div>\n";
                
                echo "<input type='text' style='width:100%;' id='fond_text_".$nbb."' name='fond_text_".$nbb."' value='".$value."'>\n";

                
            echo "</div>\n";

            

            // Berge de départ
            echo "<div id='boite_small' style='width:90%;margin-top:10px;'>\n";

                echo "<p style='float:left;margin:0;font-weight: bold;color: #000;margin-top:5px;'>";
                        echo htmlaccent('Berge de départ');
                echo "</p>";

                echo "<select name='select_berge1_".$nbb."' id='select_berge1_".$nbb."' style='float:right;width:100px;' >";

                    $selected = '';
                    if($modif_bras && $jge_bras_array[$nbb]['berge_depart'] == 1){$selected="selected";}	
                    echo "<option value='1' ".$selected." >".htmlaccent('Rive Gauche')."</option>";
                    if($modif_bras && $jge_bras_array[$nbb]['berge_depart'] == 2){$selected="selected";}	
                    echo "<option value='2' ".$selected." >".htmlaccent('Rive Droite')."</option>";
                    
                echo "</select>";                       
                
            echo "</div>\n";
                    
        echo "</div>\n";

        echo "<div style='float:left;width:15%;' >\n";

            // Moulinet
            echo "<div id='boite_small' style='width:90%'>\n";

                echo "<p style='float:left;font-weight: bold;color: #000;margin-top:5px;margin-bottom:10px;'>";
                    echo htmlaccent('Moulinet');
                echo "</p>";
                
                echo "<select name='select_moulinet_".$nbb."' id='select_moulinet_".$nbb."' style='float:right;width:100px;' >";
                                                
                    echo "<option value='0'>-</option>";
                        
                    $selected = '';								 
                    if(isset($moulinet_array))
                    {
                        foreach ($moulinet_array as $key => $value)
                        {
                            if($modif_bras && $key == $jge_bras_array[$nbb]['id_moulinet']){$selected="selected";}	
                            else{$selected = '';}											
                            echo "<option value='".$key."' ".$selected." >".$moulinet_array[$key]['num']."</option>";
                        }
                    }
                
                echo "</select>";
                        
            echo "</div>\n";

            // Helice
            echo "<div id='boite_small' style='width:90%'>\n";

                echo "<p style='float:left;font-weight: bold;color: #000;margin-top:5px;margin-bottom:10px;'>";
                    echo htmlaccent('Helice');
                echo "</p>";
                            
                echo "<select name='select_helice_".$nbb."' id='select_helice_".$nbb."' style='float:right;width:100px;' onChange='helice_eq(".$nbb.");'>";
                                                
                    echo "<option value='0'>-</option>";
                        
                    $selected = '';		
                    if(isset($helice_array))
                    {
                        foreach ($helice_array as $key => $value)
                        {
                            if($modif_bras && $key == $jge_bras_array[$nbb]['id_helice']){$selected="selected";}	
                            else{$selected = '';}											
                            echo "<option value='".$key."' ".$selected." >".$helice_array[$key]['num']." - ".$helice_array[$key]['fabricant']."</option>";
                        }
                    }

                echo "</select>";

                if($modif_bras)
                {
                    $id_helice_encours = $jge_bras_array[$nbb]['id_helice'];
                        
                    // Permet d'initialiser les données de l'équation Hélice si on est dans une modif_bras des jaugeages
                    if(isset($helice_array[$id_helice_encours]))
                    {
                        $l1 = $helice_array[$id_helice_encours]['l1'];
                        $a1 = $helice_array[$id_helice_encours]['a1']; 
                        $b1 = $helice_array[$id_helice_encours]['b1'];
                        $l2 = $helice_array[$id_helice_encours]['l2'];
                        $a2 = $helice_array[$id_helice_encours]['a2']; 
                        $b2 = $helice_array[$id_helice_encours]['b2']; 
                        $a3 = $helice_array[$id_helice_encours]['a3']; 
                        $b3 = $helice_array[$id_helice_encours]['b3']; 
                    }
                }
                else
                {
                    $l1 = '';
                    $a1 = ''; 
                    $b1 = '';
                    $l2 = '';
                    $a2 = ''; 
                    $b2 = ''; 
                    $a3 = ''; 
                    $b3 = ''; 
                }
                    
            echo "</div>\n";

            
            // Diamètre de la perche
            echo "<div id='boite_small' style='width:90%;margin-top:5px;'>\n";

                echo "<p style='float:left;font-weight: bold;color: #000;margin-top:7px;margin-bottom:0px;' title='".htmlaccent('Diamètre de la perche')."'>";
                    echo htmlaccent('Diam. perche [mm]');
                echo "</p>";
                    
                $value = '';
                if($modif_bras){$value = $jge_bras_array[$nbb]['perche_diam'];}
                echo "<input type='text' style='float:right;width:30px;' id='perche_diam_".$nbb."' name='perche_diam_".$nbb."' value='".$value."'>\n";
                
            echo "</div>\n";
                
        echo "</div>\n";  
        
        echo "<div style='float:left;width:45%;'>\n";

            // Tableau Equation permettant de préciser l'équation en cours pour la conversion des tours d'hélice en vitesse
            $hidden = "style='visibility: hidden;'";
            if(($l2 > 0) && ($l2 < 99.99)){$hidden = "style='visibility: visible;'";}       

            echo "<div id='boite_small' style='float:left;margin-right:10px ;padding:5px;border: 1.5px solid #000;'>\n";	
                    
                echo "<h2 style='margin:0;color:#000;'>".htmlaccent('Equations de vitesse :')."</h2>\n";
                
                echo "<table>";
                    
                    echo "<tr>";

                        echo "<td style='width:35px;'>&nbsp;</td>";
                        echo "<td style='width:35px;'><span style='font-weight:bold;'>".htmlaccent('<=')."</td>";
                        echo "<td style='width:35px;'>";
                            echo "<input type='text' style='width:35px;padding:0;font-size:12px;border:0px;background:none;' name='l1_bras_".$nbb."' id='l1_bras_".$nbb."' value='".$l1."' readonly>";
                        echo "</td>";

                        echo "<td style='width:10px;'>&nbsp;</td>";
                                
                        echo "<td style='width:22px;'><span style='font-weight:bold;'>".htmlaccent('v =')."</td>";
                        echo "<td style='width:38px;'>";
                            echo "<input type='text' style='width:38px;padding:0;font-size:12px;border:0px;background:none;' name='a1_bras_".$nbb."' id='a1_bras_".$nbb."' value='".$a1."' readonly>";
                        echo "</td>";
                        echo "<td style='width:25px;'><span style='font-weight:bold;'>".htmlaccent(' * n + ')."</td>";
                        echo "<td style='width:35px;'>";
                            echo "<input type='text' style='width:35px;padding:0;font-size:12px;border:0px;background:none;' name='b1_bras_".$nbb."' id='b1_bras_".$nbb."' value='".$b1."' readonly>";
                        echo "</td>";

                    echo "</tr>";
                    
                    echo "<tr>";
                        
                        echo "<td style='width:35px;'>";
                            echo "<input type='text' style='width:35px;padding:0;font-size:12px;border:0px;background:none;' name='l1_inf_bras_".$nbb."' id='l1_inf_bras_".$nbb."' value='".$l1."' readonly>"; 
                        echo "</td>";                         
                        
                        if(($l2 > 0) && ($l2 < 99.99))
                        {
                            echo "<td style='width:35px;'>";
                                echo "<input type='text' style='width:35px;padding:0;font-weight:bold;font-size:12px;border:0px;background:none;' name='lsign_".$nbb."' id='lsign_".$nbb."' value='".htmlaccent('< n <=')."' readonly>";
                            echo "</td>";
                            echo "<td style='width:35px;'>";
                                echo "<input type='text' style='width:35px;padding:0;font-size:12px;border:0px;background:none;' name='l2_bras_".$nbb."' id='l2_bras_".$nbb."' value='".$l2."' readonly>";
                            echo "</td>";
                        }
                        else
                        {
                            echo "<td style='width:35px;'>";
                                echo "<input type='text' style='width:35px;padding:0;font-weight:bold;font-size:12px;border:0px;background:none;' name='lsign_".$nbb."' id='lsign_".$nbb."' value='".htmlaccent('< n')."' readonly>";
                            echo "</td>";
                            echo "<td style='width:35px;'>";
                                echo "<input type='text' style='width:35px;padding:0;font-size:12px;border:0px;background:none;' name='l2_bras_".$nbb."' id='l2_bras_".$nbb."' value='' readonly>";
                            echo "</td>";
                        }
                        
                        echo "<td style='width:10px;'>&nbsp;</td>";
                                
                        echo "<td style='width:22px;'><span style='font-weight:bold;'>".htmlaccent('v =')."</span></td>";
                        echo "<td style='width:38px;'>";
                            echo "<input type='text' style='width:38px;padding:0;font-size:12px;border:0px;background:none;' name='a2_bras_".$nbb."' id='a2_bras_".$nbb."' value='".$a2."' readonly>";
                        echo "</td>";
                        echo "<td style='width:25px;'><span style='font-weight:bold;'>".htmlaccent('* n +')."</span></td>";
                        echo "<td style='width:35px;'>";
                            echo "<input type='text' style='width:35px;padding:0;font-size:12px;border:0px;background:none;' name='b2_bras_".$nbb."' id='b2_bras_".$nbb."' value='".$b2."' readonly>";
                        echo "</td>";

                    echo "</tr>";
                
                    echo "<tr class='hidden_helice' ".$hidden.">";

                        echo "<td style='width:35px;'>";
                            echo "<input type='text' style='width:35px;padding:0;font-size:12px;border:0px;background:none;' name='l2_inf_bras_".$nbb."' id='l2_inf_bras_".$nbb."' value='".$l2."' readonly>";
                        echo "</td>";                         
                        echo "<td style='width:35px;'><span style='font-weight:bold;'>".htmlaccent('< n')."</span></td>";
                        echo "<td style='width:35px;'>&nbsp;</td>";  

                        echo "<td style='width:10px;'>&nbsp;</td>";
                                
                        echo "<td style='width:22px;'><span style='font-weight:bold;'>".htmlaccent('v =')."</span></td>";
                        echo "<td style='width:38px;'>";
                            echo "<input type='text' style='width:38px;padding:0;font-size:12px;border:0px;background:none;' name='a3_bras_".$nbb."' id='a3_bras_".$nbb."' value='".$a3."' readonly>";
                        echo "</td>";
                        echo "<td style='width:25px;'><span style='font-weight:bold;'>".htmlaccent('* n +')."</span></td>";
                        echo "<td style='width:35px;'>";
                            echo "<input type='text' style='width:35px;padding:0;font-size:12px;border:0px;background:none;' name='b3_bras_".$nbb."' id='b3_bras_".$nbb."' value='".$b3."' readonly>";
                        echo "</td>";

                    echo "</tr>";
                    
                    
                echo "</table>";
            
            echo "</div>\n";

           // Cadre d'information des équations des hélices
            echo "<div id='boite_small' style='float:left;margin:0;padding:5px;border:1.5px solid #000;'>\n";

                echo "<h2 style='width:200px;margin:0;color:#000;'>";
                    echo htmlaccent('Formule d\'étalonnage de l\'hélice')."</span>";
                    echo "<br>".htmlaccent('v = k * n + a');
                echo "</h2>\n";

                echo "<table>";
                
                    echo "<tr style='height:15px;'>";
                        echo "<td style='font-size:10px;'><span style='font-weight:bold;'>v</span>".htmlaccent(' : vitesse du courant [m/s}')."</span></td>";
                    echo "</tr>";
                    echo "<tr style='height:15px;'>";
                        echo "<td style='font-size:10px;><span style='font-weight:bold;'>k</span>".htmlaccent(' : pas hydraulique Hélice [m]')."</span></td>";
                    echo "</tr>";
                    echo "<tr style='height:15px;'>";
                        echo "<td style='font-size:10px;><span style='font-weight:bold;'>n</span>".htmlaccent(' : vitesse rotation Hélice [tr/s]')."</span></td>";
                    echo "</tr>";
                    echo "<tr style='height:15px;'>";
                        echo "<td style='font-size:10px;><span style='font-weight:bold;'>a</span>".htmlaccent(' : constante de frottements [m/s]')."</span></td>";
                    echo "</tr>";
                    
                echo "</table>";

            
            echo "</div>\n";

        echo "</div>\n"; 
        
        
        
        echo "<div style='width:45%;'>\n";

            //Description / Observation						
            echo "<div id='boite_small' style='width:100%'>\n";
                
                echo "<p style='float:left;width:140px;font-weight: bold;color: #000;margin:0;'>";
                    echo htmlaccent('Observation');
                echo "</p>";
                        
                $value = '';
                if($modif_bras){$value = $jge_bras_array[$nbb]['bras_obs'];}
                echo "<textarea name='bras_obs_".$nbb."' id='bras_obs_".$nbb."' style='width:100%;height:40px;'>".$value."</textarea>\n";
                            
            echo "</div>\n";
        
        echo "</div>\n";

    echo "<hr>\n";
    echo "</div>\n";
    
    // Saut Ligne
    echo "<div style='width:100%;border-bottom:2px solid #176B87;'></div>\n";

    // ---------------------------------------------------------------------------
    // Dépouillement du Jaugeage
    // ---------------------------------------------------------------------------

    echo "<div id='boite1' style='margin-top:10px;float:left;width:97%;'>\n";

        echo "<div style='float:left;width:40%;margin-right:2%;' >\n";

            echo  "<div style='float:left;width:100%;margin-bottom:5px;'>\n";
            
                echo  "<div style='float:left;width:30%;'>\n";
                    echo "<h2 style='font-size:14px;'>";
                        echo htmlaccent('Dépouillement du JGE');
                    echo "</h2>\n";
                echo "</div>";

                echo  "<div style='float:right;'>\n";
                    echo "<span style='margin-right:5px;font-size:12px;font-weight:bold;' >";
                        echo htmlaccent('Données à saisir :');
                    echo "</span>";

                    echo "<select name='select_saisie_".$nbb."' id='select_saisie_".$nbb."' style='width:180px;margin-top:-6px;' onchange='toggleFields(".$nbb.");'>";// onchange='form_jge.submit();' >";
                                                    
                        echo "<option value='1'>".htmlaccent('Nbre de tours d\'hélice (TOPs)')."</option>";
                        echo "<option value='2'>".htmlaccent('Nbre de tours d\'hélice par seconde (TOPs/sec)')."</option>";
                        echo "<option value='3'>".htmlaccent('Vitesse mesurée')."</option>";

                    echo "</select>";
                echo "</div>";

            echo "</div>";
                        
            echo "<div style='float:left;width:100%;' >\n";

                echo "<div style='float:left;width:100%;'>\n";

                    echo "<table id='table_tri' cellspacing='0' >";
                        
                        echo "<thead>";
                            echo "<tr class='header-row'>";
                                echo "<th style='width:40px;color:#000;font-size:11px;border-bottom: 1px solid #fff;' title='".htmlaccent('Numéro de la verticale')."'>".htmlaccent('Num<br>Vert.')."</th>"; 
                                echo "<th style='width:60px;color:#000;font-size:11px;border-bottom: 1px solid #fff;' title='".htmlaccent('Distance du départ')."'>".htmlaccent('Dist.<br>départ [m]')."</th>";				
                                echo "<th style='width:60px;color:#000;font-size:11px;border-bottom: 1px solid #fff;' title='".htmlaccent('Profondeur totale de la verticale')."'>".htmlaccent('Prof. Tot.<br>[m]')."</th>";                    		
                                echo "<th style='width:70px;color:#000;font-size:11px;border-bottom: 1px solid #fff;' title='".htmlaccent('Profondeur de la mesure')."'>".htmlaccent('Prof. <br>Mesure [m]')."</th>";
                                echo "<th style='width:40px;color:#000;font-size:11px;border-bottom: 1px solid #fff;' title='".htmlaccent('Nombre de tours fait par l\'hélice')."'>";
                                    echo htmlaccent('TOPs');
                                echo "</th>";                      
                                echo "<th style='width:40px;color:#000;font-size:11px;border-bottom: 1px solid #fff;' title='".htmlaccent('Temps d\'enregistrement')."'>";
                                    echo htmlaccent('Temps<br>[s]');
                                echo "</th>";   
                                echo "<th style='width:70px;color:#000;font-size:11px;border-bottom: 1px solid #fff;' title='".htmlaccent('Nombre de tours d\'hélice par seconde')."'>";
                                    echo htmlaccent('TOPs<br>/sec');
                                echo "</th>";                    
                                echo "<th style='width:70px;color:#000;font-size:11px;border-bottom: 1px solid #fff;' title='".htmlaccent('Vitesse')."'>";
                                    echo htmlaccent('Vitesse<br>[m/s]');                                
                                echo "</th>";                    
                                echo "<th style='width:30px;color:#000;font-size:11px;border-bottom: 1px solid #fff;' >";
                                    echo "&nbsp;";
                                echo "</th>";
                            echo "</tr>";	

                        
                        echo "</thead>";

                    echo "</table>";
                
                echo "</div>";

                echo "<div style='float:left;width:100%;margin-top:5px;overflow-y:auto;height:45vh;'>\n";

                    echo "<table id='table_tri' cellspacing='0' >";

                        $row=0;

                        if(isset($jge_pts_array))
                        {
                            foreach($jge_pts_array as $key => $value)
                            {
                                $nb_tour_sec = ($value['tps_pts'] == 0) ? '0' : (round($value['nb_tours'] / $value['tps_pts'],3));
                        
                                if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                                else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";}

                                echo "<tr ".$row_l.">";
                                        
                                    echo "<td class='small' style='width:40px;'>";
                                        echo "<input type='text' style='width:20px;height:10px;' id='jge_bra_vert_".$nbb."_".$row."' name='jge_bra_vert_".$nbb."_".$row."' value='".$value['num_vert']."'>\n";
                                    echo "</td>";
                                    
                                    echo "<td class='small' style='width:60px;'>";
                                        echo "<input type='text' style='width:30px;height:10px;' id='jge_bra_dist_".$nbb."_".$row."' name='jge_bra_dist_".$nbb."_".$row."' value='".$value['dist_depart']."'>\n";
                                    echo "</td>";
                                    
                                    echo "<td class='small' style='width:60px;'>";
                                        echo "<input type='text' style='width:40px;height:10px;' id='jge_bra_profmax_".$nbb."_".$row."' name='jge_bra_profmax_".$nbb."_".$row."' value='".$value['prof_max']."'>\n";
                                    echo "</td>";
                                    
                                    echo "<td class='small' style='width:70px;'>";
                                        echo "<input type='text' style='width:40px;height:10px;' id='jge_bra_profmesure_".$nbb."_".$row."' name='jge_bra_profmesure_".$nbb."_".$row."' value='".$value['prof_pts']."'>\n";
                                    echo "</td>";

                                    echo "<td class='small' style='width:40px;'>";
                                        echo "<input type='text' style='width:30px;height:10px;' id='jge_bra_nbtour_".$nbb."_".$row."' name='jge_bra_nbtour_".$nbb."_".$row."' value='".$value['nb_tours']."'>\n";
                                    echo "</td>";

                                    echo "<td class='small' style='width:40px;'>";
                                        echo "<input type='text' style='width:30px;height:10px;' id='jge_bra_tps_".$nbb."_".$row."' name='jge_bra_tps_".$nbb."_".$row."' value='".$value['tps_pts']."'>\n";
                                    echo "</td>";

                                    echo "<td class='small' style='width:70px;'>";
                                        echo "<input type='text' style='width:40px;height:10px;' id='jge_bra_tourssec_".$nbb."_".$row."' name='jge_bra_tourssec_".$nbb."_".$row."' value='".$nb_tour_sec."'>\n";
                                    echo "</td>";
                                    
                                    echo "<td class='small' style='width:70px;'>";
                                        echo "<input type='text' style='width:45px;height:10px;border:0px;' id='jge_bra_vitesse_".$nbb."_".$row."' name='jge_bra_vitesse_".$nbb."_".$row."' value='".$value['vitesse_calc']."' >\n";
                                    echo "</td>";

                                    echo "<td class='small' style='width:30px;'>";
                                        $ico_obs = 'info_r.png';
                                        if(tep_not_null($value['obs'])){$ico_obs = 'info_v.png';}
                                        echo "<img src='".DIR_WS_IMG_ICO.$ico_obs."' id='jge_pt_img_".$nbb."_".$row."' style='width:15px;cursor:pointer;' title='".$value['obs']."' onClick='obsJGE(".$nbb.",".$row.");'>";
                                        echo "<input type='hidden' id='jge_pt_obs_".$nbb."_".$row."' name='jge_pt_obs_".$nbb."_".$row."' value='".$value['obs']."'>\n";
                                    echo "</td>";

                                echo "</tr>";

                                $row++;
                            }
                        }

                        while($row < 150)
                        {
                            if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
                            else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";}

                            echo "<tr ".$row_l.">";

                                echo "<td class='small' style='width:40px;'>";
                                    echo "<input type='text' style='width:20px;' id='jge_bra_vert_".$nbb."_".$row."' name='jge_bra_vert_".$nbb."_".$row."' value=''>\n";
                                echo "</td>";
                                
                                echo "<td class='small' style='width:60px;'>";
                                    echo "<input type='text' style='width:30px;' id='jge_bra_dist_".$nbb."_".$row."' name='jge_bra_dist_".$nbb."_".$row."' value=''>\n";
                                echo "</td>";
                                
                                echo "<td class='small' style='width:60px;'>";
                                    echo "<input type='text' style='width:40px;' id='jge_bra_profmax_".$nbb."_".$row."' name='jge_bra_profmax_".$nbb."_".$row."' value=''>\n";
                                echo "</td>";
                                
                                echo "<td class='small' style='width:70px;'>";
                                    echo "<input type='text' style='width:40px;' id='jge_bra_profmesure_".$nbb."_".$row."' name='jge_bra_profmesure_".$nbb."_".$row."' value=''>\n";
                                echo "</td>";

                                echo "<td class='small' style='width:40px;'>";
                                    echo "<input type='text' style='width:30px;' id='jge_bra_nbtour_".$nbb."_".$row."' name='jge_bra_nbtour_".$nbb."_".$row."' value=''>\n";
                                echo "</td>";

                                echo "<td class='small' style='width:40px;'>";
                                    echo "<input type='text' style='width:30px;' id='jge_bra_tps_".$nbb."_".$row."' name='jge_bra_tps_".$nbb."_".$row."' value=''>\n";
                                echo "</td>";

                                echo "<td class='small' style='width:70px;'>";
                                    echo "<input type='text' style='width:40px;height:10px;' id='jge_bra_tourssec_".$nbb."_".$row."' name='jge_bra_tourssec_".$nbb."_".$row."' value=''>\n";
                                echo "</td>";
                                
                                echo "<td class='small' style='width:70px;'>";
                                    echo "<input type='text' style='width:45px;border:0px;' id='jge_bra_vitesse_".$nbb."_".$row."' name='jge_bra_vitesse_".$nbb."_".$row."' value='' >\n";
                                echo "</td>";

                                echo "<td class='small' style='width:30px;'>";
                                    $ico_obs = 'info_r.png';
                                    echo "<img src='".DIR_WS_IMG_ICO.$ico_obs."' id='jge_pt_img_".$nbb."_".$row."'  style='width:15px;cursor:pointer;' title='' onClick='obsJGE(".$nbb.",".$row.");'>";                                    
                                    echo "<input type='hidden' id='jge_pt_obs_".$nbb."_".$row."' name='jge_pt_obs_".$nbb."_".$row."' value='' >\n";
                                echo "</td>";
                            
                            echo "</tr>";

                        
                            $row++;
                        }

                    echo "</table>";

                echo "</div>";
            
            echo "</div>\n";

        echo "</div>\n";

        echo "<div style='float:left;width:58%;' >\n";
            
            echo "<div style='float:left;' >\n";
            
                echo "<div style='float:left;width:20%' >\n";

                     // Débit - Q       
                     echo "<div id='boite_small' style='float:left;width:130px;margin-bottom:3px;'>\n";
                                    
                        echo "<p style='float:left;font-weight:bold;color:#930000;margin:0;margin-top:5px;' title='".htmlaccent('Débit Instantannée')."'>";
                            echo htmlaccent('Débit (Q) [m3/s]');
                        echo "</p>";

                        $value = '';
                        if($modif_bras){$value = $jge_bras_array[$nbb]['depouil_bras_q'];}
                        echo "<input type='text' style='float:right;width:30px;border:0px;background-color:#FFFFDD;' id='depouil_bras_q_".$nbb."' name='depouil_bras_q_".$nbb."' value='".$value."' >\n";
                                
                    echo "</div>\n";
                    
                    
                    // Echelle Moyenne    
                    echo "<div id='boite_small' style='float:left;width:130px;margin:0;'>\n";
                                    
                        echo "<p style='float:left;font-weight: bold;color:#000;margin:0;margin-top:5px;' title='".htmlaccent('Echelle Moyenne')."'>";
                            echo htmlaccent('Ech. Moy. [cm]');
                        echo "</p>";
                        
                        $value = '';
                        if($modif_bras){$value = $jge_bras_array[$nbb]['depouil_bras_hmoy'];}
                        echo "<input type='text' style='float:right;width:30px;border:0px;background-color:#FFFFDD;' id='depouil_bras_hmoy_".$nbb."' name='depouil_bras_hmoy_".$nbb."' value='".$value."' >\n";
                                
                    echo "</div>\n";

                echo "</div>\n";

                echo "<div style='float:left;width:22%' >\n";

                    // Vitesse Moyenne       
                    echo "<div id='boite_small' style='float:left;width:140px;margin-bottom:3px;'>\n";
                                    
                        echo "<p style='float:left;font-weight:bold;color:#000;margin:0;margin-top:5px;' title='".htmlaccent('Vitesse Moyenne')."'>";
                            echo htmlaccent('Vit. Moy. [m/s]');
                        echo "</p>";

                        $value = '';
                        if($modif_bras){$value = $jge_bras_array[$nbb]['depouil_bras_vmoy'];}
                        echo "<input type='text' style='float:right;width:30px;border:0px;background-color:#FFFFDD;' id='depouil_bras_vmoy_".$nbb."' name='depouil_bras_vmoy_".$nbb."' value='".$value."' >\n";
                                
                    echo "</div>\n";

                    // Vitesse Surface      
                    echo "<div id='boite_small' style='float:left;width:140px;'>\n";
                                    
                        echo "<p style='float:left;font-weight: bold;color: #000;margin:0;margin-top:5px;' title='".htmlaccent('Vitesse Moyenne en Surface')."' >";
                            echo htmlaccent('Vit. Moy. Surf. [m/s]');
                        echo "</p>";

                        $value = '';
                        if($modif_bras){$value = $jge_bras_array[$nbb]['depouil_bras_vsurf'];}
                        echo "<input type='text' style='float:right;width:30px;border:0px;background-color:#FFFFDD;' id='depouil_bras_vsurf_".$nbb."' name='depouil_bras_vsurf_".$nbb."' value='".$value."' >\n";
                                
                    echo "</div>\n";

                echo "</div>\n";

                echo "<div style='float:left;width:22%' >\n";

                    // Surface Mouillée      
                    echo "<div id='boite_small' style='float:left;width:140px;margin-bottom:3px;'>\n";
                                    
                        echo "<p style='float:left;font-weight: bold;color: #000;margin:0;margin-top:5px;' title='".htmlaccent('Surface Mouillée')."'>";
                            echo htmlaccent('Surf. Mouillée [m2]');
                        echo "</p>";

                        $value = '';
                        if($modif_bras){$value = $jge_bras_array[$nbb]['depouil_bras_surfmouil'];}
                        echo "<input type='text' style='float:right;width:30px;border:0px;background-color:#FFFFDD;' id='depouil_bras_surfmouil_".$nbb."' name='depouil_bras_surfmouil_".$nbb."' value='".$value."' >\n";
                                
                    echo "</div>\n";

                    // Périmètre mouillé       
                    echo "<div id='boite_small' style='float:left;width:140px;margin:0;'>\n";
                                    
                        echo "<p style='float:left;font-weight: bold;color: #000;margin:0;margin-top:5px;' title='".htmlaccent('Périmètre Mouillé')."'>";
                            echo htmlaccent('Périm. Mouillé [m]');
                        echo "</p>";

                        $value = '';
                        if($modif_bras){$value = $jge_bras_array[$nbb]['depouil_bras_perimouil'];}
                        echo "<input type='text' style='float:right;width:30px;border:0px;background-color:#FFFFDD;' id='depouil_bras_perimouil_".$nbb."' name='depouil_bras_perimouil_".$nbb."' value='".$value."' >\n";
                                
                    echo "</div>\n";

                echo "</div>\n";

                echo "<div style='float:left;width:20%' >\n";

                    // Profondeur Moyenne      
                    echo "<div id='boite_small' style='float:left;width:130px;margin-bottom:3px;'>\n";
                                    
                        echo "<p style='float:left;font-weight: bold;color: #000;margin:0;margin-top:5px;' title='".htmlaccent('Profondeur Moyenne')."'>";
                            echo htmlaccent('Prof. Moy. [cm]');
                        echo "</p>";

                        $value = '';
                        if($modif_bras){$value = $jge_bras_array[$nbb]['depouil_bras_profmoy'];}
                        echo "<input type='text' style='float:right;width:30px;border:0px;background-color:#FFFFDD;' id='depouil_bras_profmoy_".$nbb."' name='depouil_bras_profmoy_".$nbb."' value='".$value."' >\n";
                                
                    echo "</div>\n";

                    // Largeur Totale     
                    echo "<div id='boite_small' style='float:left;width:130px;margin:0;'>\n";
                                    
                        echo "<p style='float:left;font-weight: bold;color: #000;margin:0;margin-top:5px;' title='".htmlaccent('Largeur Totale')."'>";
                            echo htmlaccent('Largeur Tot. [m]');
                        echo "</p>";

                        $value = '';
                        if($modif_bras){$value = $jge_bras_array[$nbb]['depouil_bras_distmax'];}
                        echo "<input type='text' style='float:right;width:30px;border:0px;background-color:#FFFFDD;' id='depouil_bras_distmax_".$nbb."' name='depouil_bras_distmax_".$nbb."' value='".$value."' >\n";
                                
                    echo "</div>\n";

                echo "</div>\n";

                echo "<div style='float:left;width:15%;'>\n";

                    // Rayon Hydraulique       
                    echo "<div id='boite_small' style='float:left;width:100px;margin-bottom:3px;'>\n";
                                    
                        echo "<p style='float:left;font-weight:bold;color:#000;margin:0;margin-top:5px;' title='".htmlaccent('Rayon Hydraulique')."' >";
                            echo htmlaccent('Ray. Hyd.');
                        echo "</p>";

                        $value = '';
                        if($modif_bras){$value = $jge_bras_array[$nbb]['depouil_bras_rh'];}
                        echo "<input type='text' style='float:right;width:30px;border:0px;background-color:#FFFFDD;' id='depouil_bras_rh_".$nbb."' name='depouil_bras_rh_".$nbb."' value='".$value."' >\n";
                                
                    echo "</div>\n";

                echo "</div>\n";

            echo "</div>\n";    

            
            // Block pour affichage du graphique
            echo "<div id='boxpopup' class='select' style='width:100%;margin:0;margin-top:10px;padding:0;'>\n";

                echo "<div id='button_visu' 
                            style='float:left;width:120px;margin-left:5px;padding:4px 5px;'
                            title='".htmlaccent('Lancer le calcul des vitesses et du débit')."'
                            onClick='calcul_jge(".$nbb.");'>\n";	
                        echo  htmlaccent('Calculer le débit'); 
                echo "</div>\n";

                echo "<p class='titre' style='height:15px;'></p>\n";	
                

                /*
                echo "<p class='titre' style='height:15px;'>";
                    echo "<span style='font-size:14px;color:#EA1179;font-weight:bold;cursor:pointer;' title='".htmlaccent('Calculer les vitesses...')."' onClick='calcul_jge(".$nbb.");'>";
                        echo htmlaccent('| Calculer le Débit |');
                    echo "</span>";
                echo "</p>\n";	*/ 

                echo "<div id='plot_jge_bras_".$nbb."' style='height:35vh;padding:10px;padding-bottom:0px;'></div>\n";

            echo "</div>\n";

        
        echo "</div>\n";

    echo "</div>\n";

echo "</div>\n";

?>

<script type="text/javascript">
	
    var boxObsJGE = document.getElementById('box_jge_obs'); // popup d'affichage de Obs PT

    function obsJGE(nbb,row)
    {
        jgeBoxNbb = document.getElementById('jge_pt_nbb'); 
        jgeBoxRow = document.getElementById('jge_pt_row'); 

        jgeBoxVert = document.getElementById('jge_pt_verticale'); 
        jgePtVert = document.getElementById('jge_bra_vert_'+nbb+'_'+row); 

        jgeBoxDistdepart = document.getElementById('jge_pt_distdepart'); 
        jgePtDistdepart = document.getElementById('jge_bra_dist_'+nbb+'_'+row); 

        jgeBoxProf = document.getElementById('jge_pt_prof'); 
        jgePtProf = document.getElementById('jge_bra_profmesure_'+nbb+'_'+row); 

        jgeBoxObs = document.getElementById('jge_pt_obs'); 
        jgePtObs = document.getElementById('jge_pt_obs_'+nbb+'_'+row); 
        
        jgeBoxNbb.value = nbb;
        jgeBoxRow.value = row;

        jgeBoxVert.value = jgePtVert.value;
        jgeBoxDistdepart.value = jgePtDistdepart.value;
        jgeBoxProf.value = jgePtProf.value;
        jgeBoxObs.value = jgePtObs.value;

        boxObsJGE.style.display = 'block';
    }
    
    // Fonctions permettant de gérer la saisie des caractéristiques du fond du lit
	function updateSelectedFond(bras) 
    {
        // Récupère toutes les cases à cocher correspondant au sélecteur donné
        var checkboxes = Array.from(document.querySelectorAll('input[type="checkbox"][name^="check_fondlit_'+bras+'"]'));

        // Récupère les valeurs des cases à cocher sélectionnées
        var selectedValues = checkboxes
            .filter(function(checkbox) {
                return checkbox.checked;
            })
            .map(function(checkbox) {
                return checkbox.getAttribute('data-value').trim();
            });

        // Récupère et filtre le texte manuel, en excluant les doublons et les valeurs déjà cochées
        var currentText = document.getElementById('fond_text_'+bras).value;        
        var manualText = currentText
            .split(' / ')
            .map(function(value) {
                return value.trim();
            })
            .filter(function(value) {
                return value !== '' &&
                    !selectedValues.includes(value) &&
                    !checkboxes.some(function(chk) {
                        return chk.getAttribute('data-value').trim() === value;
                    });
            });

        // Combine le texte manuel filtré et les valeurs cochées, puis met à jour le champ d'entrée
        var combinedText = manualText.concat(selectedValues).join(' / ');

        // Met à jour le champ d'entrée avec le texte combiné
        document.getElementById('fond_text_' + bras).value = combinedText;
    }

    function toggleFields(bras) 
    {
        selectSaisie = document.getElementById('select_saisie_' + bras);
        selectedValue = selectSaisie.value;

        // Cibler les champs par leur préfixe
        const topsFields = document.querySelectorAll("input[name^='jge_bra_nbtour']");
        const tempsFields = document.querySelectorAll("input[name^='jge_bra_tps']");
        const topsSecFields = document.querySelectorAll("input[name^='jge_bra_tourssec_']");
        const vitesseFields = document.querySelectorAll("input[name^='jge_bra_vitesse_']");

        // Fonction pour activer/désactiver un groupe de champs
        function setFieldsState(fields, isDisabled) 
        {
            fields.forEach(field => {
                field.readOnly = isDisabled;
                field.style.backgroundColor = isDisabled ? '#e0e0e0' : '';
                field.style.opacity = isDisabled ? '0.6' : '1';
            });
        }

       // Logique conditionnelle en fonction de la sélection
        if (selectedValue === '3') 
        {
            // Cas Vitesse mesurée
            setFieldsState([...topsFields, ...tempsFields, ...topsSecFields], true);
            setFieldsState(vitesseFields, false);
        } 
        else if (selectedValue === '2') 
        {
            // Cas TOPs/sec
            setFieldsState([...topsSecFields], false);
            setFieldsState([...topsFields, ...tempsFields, ...vitesseFields], true);
        } 
        else if (selectedValue === '1') 
        {
            // Cas Nombre de tours d'hélice (TOPs)
            setFieldsState([...topsFields, ...tempsFields], false);
            setFieldsState([...topsSecFields, ...vitesseFields], true);
        } 
        
    }


    // Appliquer la règle au chargement de la page si l'option "Vitesse mesurée" est déjà sélectionnée
    toggleFields(<?php echo $nbb; ?>);

    // Editer le graphique du JGE   
    f_editgraph_jge(<?php echo $nbb; ?>);

		  
</script>