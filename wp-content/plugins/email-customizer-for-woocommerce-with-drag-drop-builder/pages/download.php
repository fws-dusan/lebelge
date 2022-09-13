<?php

$main_dir=dirname(__FILE__);
$path= str_replace('pages', '', $main_dir);
$file_id=$_GET['fileid'];
$json_filename = $path."/exports/".$file_id.'.json';

header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header('Cache-Control: pre-check=0, post-check=0, max-age=0', false);
header("Pragma: no-cache");
header("Content-type: application/json");
header("Content-Disposition:attachment; filename=".$file_id.".json");
header("Content-Type: application/force-download");

readfile("{$json_filename}");
exit();