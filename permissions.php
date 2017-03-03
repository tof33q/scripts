<?php

$ignores = array(".htaccess");
$ignore_exts = array();

function change_perm(&$item, &$perm, &$ftype) {
    global $file;
    
    switch ($perm) {
        case 'write':
            chmod($item->getPathname(), 0777);
            fwrite($file, "\n 0777: {$item->getPathname()}");    
        break;
        
        case 'read':
            chmod($item->getPathname(), 0644);
            fwrite($file, "\n 0644: {$item->getPathname()}");    
        break;
        
        case 'php':
            chmod($item->getPathname(), 0755);
            fwrite($file, "\n 0755: {$item->getPathname()}");    
        break;
        
        case 'default':
            chmod($item->getPathname(), 0755);
            fwrite($file, "\n 0755: {$item->getPathname()}");    
        break;
    }
    
    if ( $item->isDir() ) {       
         chmod_r($item->getPathname(), $perm, $ftype);
    }
}


function chmod_r($path, $perm, $ftype) {
    
    global $file, $ignores, $ignore_exts;
    
    
    
    $dir = new DirectoryIterator($path);
    
    foreach ($dir as $item) {  
        
        if (!$item->isDot()) {
            
            if (in_array($item->getBasename(), $ignores)) 
                continue;
            
            if (in_array($item->getExtension(), $ignore_exts)) 
                continue;
            
            
            
            if ($ftype == 'file') {
                if ($item->isFile()) {                   
                    change_perm($item, $perm, $ftype);
                } else {
                    chmod_r($item->getPathname(), $perm, $ftype);
                }
            } else if ($ftype == 'dir') {
                if ($item->isDir()) {
                    change_perm($item, $perm, $ftype);
                }
            } else {
                change_perm($item, $perm, $ftype);                        
            }
          
        }
    }
}



$perm = 'php';
$path = __DIR__;
$ftype = "all";
$action = 'Confirm';


if (isset($_POST['Confirm'])) {
    $path = $_POST['path'];
    $perm = $_POST['perm'];
    $ftype = $_POST['ftype'];
    echo "<p>Do you want to apply <strong>$perm</strong> permissions to following path <br /> <strong>$path</strong></p><hr />";
    $action = "Execute";
}

$log = false;

if (isset($_POST['Execute'])) {
    //chdir("..");
    $path = $_POST['path'];
    $perm = $_POST['perm'];
    $ftype = $_POST['ftype'];
    
    $action = "Confirm";
    
    if (!is_dir($path)) {
        echo "<p>invalid path: " . $path . '</p>';
    } else {
        $file = fopen('perm.log', 'w');
        chmod_r($path, $perm, $ftype);
        fclose($file);
        $log = true;
        echo "<h3>Permissions changed</h3><strong>$path</strong> to <strong>$perm</strong><hr />";
    } 
}


?>
<style>
    input[type=text] {
        padding: 4px;
        width: 90%;
        font-size: 20px;
    }
</style>
<div>
    <form method="post" action="">
        <div>
            <h4>Path</h4>
            <div>
                <input type="text" name="path" id="path" value="<?php echo $path ?>" <?php echo $action == 'Execute' ? 'readonly="readonly"' : ''?> />
            </div>
        </div>
        <h4>Permissions</h4>
        <div>
            <input type="radio" name="perm" value="php" <?php echo $perm == 'php' ? 'checked' : ($action == 'Execute' ? 'disabled' : '')?> /> PHP Recommended
            <input type="radio" name="perm" value="write" <?php echo $perm == 'write' ? 'checked' : ($action == 'Execute' ? 'disabled' : '')?> /> Write All
            <input type="radio" name="perm" value="read" <?php echo $perm == 'read' ? 'checked' : ($action == 'Execute' ? 'disabled' : '')?> /> Read only
        </div>
        <h4>Apply On</h4>
        <div>
            <input type="radio" name="ftype" value="all" <?php echo $ftype == 'all' ? 'checked' : ($action == 'Execute' ? 'disabled' : '')?> /> Dirs and Files
            <input type="radio" name="ftype" value="dir" <?php echo $ftype == 'dir' ? 'checked' : ($action == 'Execute' ? 'disabled' : '')?> /> Dirs only
            <input type="radio" name="ftype" value="file" <?php echo $ftype == 'file' ? 'checked' : ($action == 'Execute' ? 'disabled' : '')?> /> Files only
        </div>
        
        <p>
            <input type="submit" name="<?php echo $action ?>" value="<?php echo $action ?>" />
        </p>
    </form>
</div>

<?php echo $log ? '<iframe width="100%" height="100%" src="perm.log"></iframe>' : '' ?>
