<?php
$ch = curl_init();
/**
* Set the URL of the page or file to download.
*/
curl_setopt($ch, CURLOPT_URL,'https://astorchocolate.com/ast.zip');

$fp = fopen('ast.zip', 'w+');
/**
* Ask cURL to write the contents to a file
*/
curl_setopt($ch, CURLOPT_FILE, $fp);

curl_exec ($ch);

curl_close ($ch);
fclose($fp);