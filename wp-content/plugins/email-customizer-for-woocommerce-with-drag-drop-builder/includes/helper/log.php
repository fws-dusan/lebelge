<?php

abstract class LogType
{
    const AJAX='';// = EC_WOO_BUILDER_PATH.'logs/ajax.txt';
}

/**
 * Logs
 */
class Log
{
    private $logFileName;
    public function __construct($logType)
    {
        //$this->logFileName=$logType;
        $this->logFileName=EC_WOO_BUILDER_PATH.'logs/ajax.txt';
    }

    public function write($method_name, $exception)
    {
        $str='; date : '.date("d.m.Y H:i:s").PHP_EOL;
        $str.='; $method_name: '.$method_name.PHP_EOL;
        $str.='; $exception: '.$exception.PHP_EOL;

        $this->write_file($str);
    }

    private function write_file($log_str)
    {
        $old_str=file_get_contents($this->logFileName);
        $split=PHP_EOL.PHP_EOL.'--------------------'.PHP_EOL.PHP_EOL;
        $fp = fopen($this->logFileName, 'w');
        fwrite($fp, $log_str.$split.$old_str);
        fclose($fp);
    }
}
