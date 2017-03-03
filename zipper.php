<?php 

ini_set('display_errors', 'on');
error_reporting(E_ALL);

$errors = '';
ob_end_clean();

$excludes = array(".zip", '.log', '.php~', '.mp4', '.css~', '.js~',  'error_log', __DIR__ . "wp-content/uploads/", 'wp-content/themes/amgoals/videolib/input/',  __DIR__ .  "/nppBackup", 'wp-content/themes/amgoals/videolib/output/', 'wp-content/themes/amgoals/videolib/user_videos/', 'wp-content/themes/amgoals/videolib/tmp/');

$logfile = fopen('zipper.log', 'w');

ini_set('max_execution_time', 1000); //300 seconds = 5 minutes
$path = __DIR__;
// Get real path for our folder
$rootPath = realpath($path);


 
//print_r($excludes);
// Initialize archive object
$zip = new ZipArchive();
$zip->open('zipper_gen.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
{
      if ($file == '.' or $file == "..") 
      		continue;
	  $fileinfo = pathinfo($file->getRealPath());
	
	  $ignore = false;
	  foreach ($excludes as $regex) {
	  	 
		  if (strpos($fileinfo['dirname'], $regex) !== false) {
		  	//echo "matched $regex : {$fileinfo['dirname']}";
			 $ignore = true;	
			 break;
		 }
	 }
	

	if ($ignore) {
		//echo "\n <br> Ignoring: $file";		
			continue;
	} 
	 
    if (isset($fileinfo['extension']))	 {
    	 
	    if (in_array("." . $fileinfo['extension'], $excludes ))  {
	    	//echo "\n <br> Ignoring: $file";	
    		continue;
    	}
    }
    		
    //echo "\n <br> Adding: $file";	
    
    // Skip directories (they would be added automatically)
    try {
        if (!$file->isDir())
        {
            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);
            
            fwrite($logfile, "\n" . $filePath);
            // Add current file to archive
            $zip->addFile($filePath, $relativePath);
        }
    } catch (Exception $e) {
        $errors .= $e->getMessage();
    } 
}
fclose($logfile);
// Zip archive will be created only after closing object
$zip->close();

header("HTTP/1.1 303 See Other"); // 303 is technically correct for this type of redirect
header("Location: zipper_gen.zip");

echo $errors;
exit;
