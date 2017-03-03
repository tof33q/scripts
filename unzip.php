<?php

$file = $_GET['file'];

if (!file_exists($file)) {
    die("File not found");
}

$path = __DIR__;

if (!empty($_GET['path'])) {
    $path = realpath($path . "/" . $_GET['path']);
}



$zip = new ZipArchive;
$res = $zip->open($file);
if ($res === true) {
  $zip->extractTo($path . "/");
  $zip->close();
  echo 'extracted';
} else {
  echo 'errors :(';
}

