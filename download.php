<?php

@ini_set('max_execution_time', 15000); //300 seconds = 5 minutes

set_time_limit(0);

$url = rawurldecode($_GET['url']);
 

if ($fp = fopen($url, 'rb')) {
    
    $file = fopen($_GET['file'], 'w');
    $lines = 0;
    $bits = 0;
 
    
    while ($line = fread($fp, 1024)) {
        $lines ++;
        $bits += 1024;        
        fwrite($file, $line);
    }
    
    if (($bits / 1024 / 1024) > 1) {
        $mb = ($bits / 1024 / 1024) . 'Mb'; 
    } else {
        $mb = ($bits / 1024) . 'Kb'; 
    }
    
    echo "<p>No of lines : $lines</p>
        <p>filesize: $mb </p>
    ";
    fclose($file);
    
} else {
    echo 'error';
}
