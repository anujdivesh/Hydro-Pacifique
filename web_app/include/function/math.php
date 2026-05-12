<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Toutes les fonctions liées aux calculs mathématiques de série dans des tableaux
*/

// Moyenne
function mean(array $values)
 {
    $count = count($values);
    if ($count === 0) 
    {
        return 0; // Éviter une division par zéro
    }
    return array_sum($values) / $count;
}

// Variance
function variance(array $values) 
{
    $count = count($values);
    if ($count === 0) 
    {
        return 0; // Éviter une division par zéro
    }
    $mean = mean($values);
    $squared_differences = array_map(function($x) use ($mean) {
                                        return ($x - $mean) ** 2;
                                    }, $values);
    return array_sum($squared_differences) / $count;
}

// Covariance
function covariance(array $x_values, array $y_values) 
{
    $count = count($x_values);
    if ($count === 0 || $count !== count($y_values)) 
    {
        return 0; // Éviter une division par zéro ou des tailles incompatibles
    }
    $mean_x = mean($x_values);
    $mean_y = mean($y_values);

    $covariances = 0;
    for ($i = 1; $i <= $count; $i++) 
    {
        $covariances += ($x_values[$i] - $mean_x) * ($y_values[$i] - $mean_y);
    }
    return $covariances / $count;
}


// Fonction de calcul pour Médiane et Quartiles
function calculerPercentile($data, $percentile) 
{
    
    sort($data);
    
    $count = count($data);
    
    $index = ($percentile / 100) * ($count - 1);

    $floor = floor($index);
    $fraction = $index - $floor;
    

    if ($floor + 1 < $count) 
    {
        return $data[$floor] + ($data[$floor + 1] - $data[$floor]) * $fraction;
    } 
    else 
    {
        return $data[$floor];
    }
}

// Fonction pour une régression linéaire
function linearTrendPhp($knownYs, $knownXs, $newXs) 
{
    // Calcul du nombre de points de données
    $n = count($knownYs);

    // Vérification que les tableaux sont de la même longueur
    if ($n != count($knownXs)) 
    {
        return null; // Erreur : les tableaux doivent être de la même longueur
    }

    // Calcul des sommes nécessaires
    $sumX = array_sum($knownXs);
    $sumY = array_sum($knownYs);
    $sumXY = 0;
    $sumX2 = 0;

    for ($i = 0; $i < $n; $i++) {
        
        $sumXY += $knownXs[$i] * $knownYs[$i];
        $sumX2 += $knownXs[$i] * $knownXs[$i];
    }

    // Calcul de la pente (a) et de l"ordonnée à l"origine (b)
    $a = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
    $b = ($sumY - $a * $sumX) / $n;

    // Prédictions pour les nouvelles valeurs de X
    $newYs = [];
    foreach ($newXs as $newX) 
    {
        $newYs[] = $a * $newX + $b;
    }

    return $newYs; // Retourne les nouvelles valeurs de Y prédites
}



?>
