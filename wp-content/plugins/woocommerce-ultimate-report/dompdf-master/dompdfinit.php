<?php 
define("DOMPDF_DIR2", str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname(__FILE__))));
define("DOMPDF_FONT_DIR", DOMPDF_DIR2 . "/lib/fonts/");
define("DOMPDF_FONT_CACHE", DOMPDF_FONT_DIR);

define("PAGES_TEMP", DOMPDF_DIR2. '/pages_temp'); 
define("ROOT", DOMPDF_DIR2); 

require_once __DIR__.'/autoload.inc.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();