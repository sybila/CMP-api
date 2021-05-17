<?php

/**
 * Class MathMLContentToLatex
 * @author Radoslav Doktor
 */
class MathMLContentToLatex
{
    public static function convert(string $input){
        $start_time = microtime(true);
        $xml=simplexml_load_string($input);
        $xsl=simplexml_load_file('../app/Helpers/MathML/mmltex.xsl');
        // Configure the transformer
        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xsl); // attach the xsl rules
//        $end_time = microtime(true);
////
////// Calculate the script execution time
//        $execution_time = ($end_time - $start_time);
//        dump($execution_time,$proc->transformToXml($xml),libxml_get_last_error());exit;
//        if (libxml_get_last_error()){
//            dump(libxml_get_last_error());exit;
//        }
        return $proc->transformToXml($xml);
    }
}