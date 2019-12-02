<?php

namespace AsknicelyTest\ValidationHandler;

include_once 'AsknicelyException.php';

use AsknicelyTest\Validations\AsknicelyException;

class ValidationHandler
{
    public CONST AJAX = 1;
    public CONST FLASH = 2;
    public CONST EXEPTION = 4;
    public CONST TEXT = 8;

    private $app = null;

    public function __construct($app){
        $this->app = $app;
    }


    public function fail($msg, $code, $mode){
        switch($mode){
            case self::TEXT:
                $this->text($msg, $code);
                break;
            case self::AJAX:
                $this->ajax($msg, $code);
                break;
            case self::EXEPTION:
                $this->exeption($msg, $code);
                break;
            case self::FLASH:
                $this->flash($msg, $code);
                break;
        }
    }

    private function text($msg, $code){
        echo "MESSAGE: $msg, CODE: $code"; exit;
    }

    private function ajax($msg, $code){
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(["MESSAGE" => $msg, "CODE" => $code]);exit;
    }

    private function exeption($msg, $code){
        throw new AsknicelyException($msg, $code);
    }
    
    private function flash($msg, $code){
        $this->app['session']->getFlashBag()->add('Flash', $msg);
        $this->app->redirect('/todo');
    }
}