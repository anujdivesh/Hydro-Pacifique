<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Parti FILTRE des stations affichage html des filtres
On génére ici la structure html des filtre qui sont destiné à être intégré à la colonne de gauche
----------------------------------------
*/

//---------------------------------------------------------------
// On remplit les listbox des filtres


// Champs de recherche libre pour trouver un nom de la station
if($affiche_search)
{
    echo "<p style='float:left;width:26%;margin-top:5px;padding-top:5px;text-align:left;'>".TEXT_FILTER_SEARCH."</p>";	
                            
    echo "<img src='".DIR_WS_IMG_ICO."arrow.png' alt='rechercher' onclick='".$name_form.".submit();' style='float:right;width:20px;margin-top:8px;margin-left:5px;cursor:pointer;'/>";
    echo "<input name='search_station' type='text' value='".$search_station."' style='float:right;width:40%;margin-top:5px;'>";
    
            
    echo "<hr>\n";
}

// Type de données (Débit / Pluie / Piezo)

if($affiche_select_type)
{
    echo "<p style='float:left;margin-top:5px;width:42%;padding-top:3px;text-align:left;color: #930000;'>".TEXT_FILTER_TYPE."</p>";

    echo "<select name='select_type_data' id='select_type_data' onchange='".$name_form.".submit();' style='float:right;margin-top:5px;width:55%;'>";
            
        echo "<option value='0'>".TEXT_FILTER_ALL."</option>";
        
        $hidden_type_color = '';                                
        if(isset($eq_type_array))
        {
            foreach($eq_type_array as $key => $value)
            {
                if($key == $select_type_encours){$selected="selected";}	
                else{$selected = '';}											
                echo "<option value='".$key."' ".$selected.">".$value['nom_eq_type']."</option>";

                // C'est pour les couleurs des fiches RA
                $hidden_type_color .=  "<input type='hidden' id='type_color_border_".$key."' value=\"".$value['type_color_border']."\" />\n";
				$hidden_type_color .=  "<input type='hidden' id='type_color_background_".$key."' value=\"".$value['type_color_background']."\" />\n";
														
            }
        }
    echo "</select>";

    echo "<hr><hr>\n";	
}	
											
// Région Hydro
echo "<p style='float:left;margin-top:5px;width:42%;padding-top:3px;text-align:left;'>".TEXT_FILTER_BV."</p>";

echo "<select name='select_regionhydro' id='select_regionhydro' onchange='".$name_form.".submit();' style='float:right;margin-top:5px;width:55%;'>";
        
    echo "<option value='0'>".TEXT_FILTER_ALL."</option>";
        
    if(isset($regionhydro_array))
    {
        foreach($regionhydro_array as $key => $value)
        {
            if($key == $select_regionhydro_encours){$selected="selected";}	
            else{$selected = '';}											
            echo "<option value='".$key."' ".$selected." >".$value."</option>";
        }
    }

echo "</select>";

echo "<hr>\n";									
                                            
// Selection Territoire (Province / Ile)										
echo "<p style='float:left;width:42%;padding-top:3px;text-align:left;'>".$territoire_region."</p>";

echo "<select name='select_region' id='select_region' onchange='".$name_form.".submit();'  style='float:right;width:55%;'>";

    echo "<option value='0'>".TEXT_FILTER_ALL."</option>";
    
    if(isset($region_array))
    {
        foreach($region_array as $key => $value)
        {
            if($key == $select_region_encours){$selected="selected";}	
            else{$selected = '';}											
            echo "<option value='".$key."' ".$selected." >".$value."</option>";
        }
    }

echo "</select>";

echo "<hr>\n";

// Selection  par commune														
echo "<p style='float:left;width:42%;padding-top:3px;text-align:left;'>".TEXT_FILTER_CITY."</p>";

echo "<select name='select_commune' id='select_commune' onchange='".$name_form.".submit();'  style='float:right;width:55%;'>";
        
    echo "<option value='0'>".TEXT_FILTER_ALL."</option>";
                                    
    if(isset($commune_array))
    {
        foreach($commune_array as $key => $value)
        {
            if($key == $select_commune_encours){$selected="selected";}	
            else{$selected = '';}											
            echo "<option value='".$key."' ".$selected." >".$value."</option>";
        }
    }

echo "</select>";	

echo "<hr>\n";

// Selection  par Rivière	
if($affiche_select_riviere)
{							
    echo "<p style='float:left;width:42%;padding-top:3px;text-align:left;'>".TEXT_FILTER_RIVER."</p>";

    echo "<select name='select_riviere' id='select_riviere' onchange='".$name_form.".submit();'  style='float:right;width:55%;'>";
            
        echo "<option value='0'>".TEXT_FILTER_ALL."</option>";
                                        
        $selected = '';		
        if(isset($riviere_array))
        {
            foreach($riviere_array as $key => $value)
            {
                if($key == $select_riviere_encours){$selected="selected";}	
                else{$selected = '';}											
                echo "<option value='".$key."' ".$selected." >".$value."</option>";
            }
        }

    echo "</select>";	

    echo "<hr>\n";
}

// Tournée

if($affiche_select_tournee)
{
    echo "<p style='float:left;width:42%;padding-top:3px;text-align:left;'>".TEXT_FILTER_ROUND."</p>";

    echo "<select name='select_tournee' id='select_tournee' onchange='".$name_form.".submit();' style='float:right;width:55%;'>";
            
        echo "<option value='0'>".TEXT_FILTER_ALL."</option>";
            
        $selected = '';									
        if(isset($tournee_array))
        {
            foreach($tournee_array as $key => $value)
            {
                if($key == $select_tournee_encours){$selected="selected";}	
                else{$selected = '';}											
                echo "<option value='".$key."' ".$selected." >".$value."</option>";
            }
        }		

    echo "</select>";
}

echo "<hr><hr>\n";


// Type de données (Débit / Pluie / Piezo)

if($affiche_select_station)
{
    echo "<p style='float:left;width:42%;padding-top:3px;text-align:left;color: #930000;'>".htmlaccent('Station')."</p>";

    echo "<select name='select_station' id='select_station' onchange='".$name_form.".submit();' style='float:right;width:55%;'>";
            
        echo "<option value='0'>".TEXT_FILTER_ALL."</option>";
        
                                        
        if(isset($station_array))
        {
            foreach($station_array as $key => $value)
            {
                if($key == $select_station_encours){$selected="selected";}	
                else{$selected = '';}											
                echo "<option value='".$key."' ".$selected.">".$value['nom_station']."</option>";
            }
        }
    echo "</select>";

    echo "<hr>\n";	
}	


// -----------------------------------------------------------------------

if($affiche_select_statut_station)
{
    // Station active
    echo "<p style='float:left;width:42%;padding-top:3px;margin-top:5px;text-align:left;color: #006A67;'>".TEXT_FILTER_STATUT."</p>";

    echo "<select name='select_active' id='select_active' onchange='".$name_form.".submit();' style='float:right;width:55%;margin-top:5px;'>";
        
        $selected = ($select_active_encours == 0) ? "selected" : "";
        echo "<option value='0' ".$selected.">".TEXT_FILTER_ALL."</option>";
        $selected = ($select_active_encours == 1) ? "selected" : "";
        echo "<option value='1' ".$selected.">".htmlaccent('Active')."</option>";
        $selected = ($select_active_encours == 2) ? "selected" : "";
        echo "<option value='2' ".$selected.">".htmlaccent('Historique (Fermée)')."</option>";
            
    echo "</select>";

    echo "<hr>\n";

    // Station Suivi (Mesure continu / Ponctuelle)
    echo "<p style='float:left;width:42%;padding-top:3px;text-align:left;color: #006A67;'>".TEXT_FILTER_SUIVI."</p>";

    echo "<select name='select_suivi' id='select_suivi' onchange='".$name_form.".submit();' style='float:right;width:55%;'>";
            
        $selected = ($select_suivi_encours == 0) ? "selected" : "";
        echo "<option value='0' ".$selected.">".TEXT_FILTER_ALL."</option>";
        $selected = ($select_suivi_encours == 1) ? "selected" : "";
        echo "<option value='1' ".$selected.">".htmlaccent('Mesures en continu')."</option>";
        $selected = ($select_suivi_encours == 2) ? "selected" : "";
        echo "<option value='2' ".$selected.">".htmlaccent('Mesures ponctuelles')."</option>";
            
    echo "</select>";

    echo "<hr>\n";

    // Station En panne
    echo "<p style='float:left;width:42%;padding-top:3px;text-align:left;color: #006A67;'>".TEXT_FILTER_ETATEQ."</p>";

    echo "<select name='select_armee' id='select_armee' onchange='".$name_form.".submit();' style='float:right;width:55%;'>";
            
        $selected = ($select_armee_encours == 0) ? "selected" : "";
        echo "<option value='0' ".$selected.">".TEXT_FILTER_ALL."</option>";
        $selected = ($select_armee_encours == 1) ? "selected" : "";
        echo "<option value='1' ".$selected.">".TEXT_FILTER_ETATPANNE."</option>";
            
    echo "</select>";
}




?>


