<?php
echo "<h2>📋 Logs d'Erreurs PHP</h2>";

$error_log_path = ini_get('error_log');
echo "<p><strong>Fichier de log:</strong> " . ($error_log_path ? $error_log_path : 'Non configuré') . "</p>";

// Afficher les derniers logs si possible
if ($error_log_path && file_exists($error_log_path)) {
    $logs = tailCustom($error_log_path, 20);
    echo "<pre>" . htmlspecialchars($logs) . "</pre>";
} else {
    echo "<p>Fichier de log non trouvé. Vérifiez la configuration PHP.</p>";
}

function tailCustom($filepath, $lines = 1) {
    if (!file_exists($filepath)) return "Fichier non trouvé";
    
    $handle = fopen($filepath, "r");
    if (!$handle) return "Impossible d'ouvrir le fichier";
    
    $linecounter = $lines;
    $pos = -2;
    $beginning = false;
    $text = array();
    
    while ($linecounter > 0) {
        $t = " ";
        while ($t != "\n") {
            if(fseek($handle, $pos, SEEK_END) == -1) {
                $beginning = true; 
                break; 
            }
            $t = fgetc($handle);
            $pos--;
        }
        $linecounter--;
        if ($beginning) rewind($handle);
        $text[$lines-$linecounter-1] = fgets($handle);
        if ($beginning) break;
    }
    fclose($handle);
    return implode("", array_reverse($text));
}
?>