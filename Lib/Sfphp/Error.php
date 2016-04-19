<?php
# 
# -/:::::::::::::::::::/- `/soooooooooooooooooooy: ./::::::::::::::::::://`                                                                             
# +:-------------------:+.-ho+/////////////////+sh.:/:------------------:*                                                                             
# +////:::::::::::::////+--hyyyssooooooooooossyyyh-+/////:::::::::::::////+                                                                             
# +/////////////////////+--hyyyyyyyyyyyyyyyyyyyyyh-+//////////////////////+                                                                             
# +/////////////////////+-.hyyyyyyyyyyyyyyyyyyyyyh-+//////////////////////+                                                                             
# +/////////////////////+--hyyyyyyyyyyyyyyyyyyyyyh-+//////////////////////+                                                                             
# +/////////////////////+.-hyyyyyyyyyyyyyyyyyyyyyh.//////////////////////+:                                                                             
# :///////////////////+/. `ohyyyyyyyyyyyyyyyyyyhh/ `////////////////////+:`                                                                             
#  .-------------------.    --------------------.   ```.------.``````````                            .......`           `......            `......`     
# +o+++++++++++++++++++so `:::::::::::::::::::::/-   -syhhyyyhyss-    sssss  ysssss+`    +sssy.  `:+shhyyyyhyy/-    `-ssyhyyyyhsy+`    `/osyhyyyyhys/.  
# hs++///////////////++sh:.+::-----------------:/+`-syyyhyyyyyyyyy+   yyyyh  dyyyyyys.   oyyyh.`:yyyyyyyyyyyyyyho` /hyyyyyyyyyyyyyy+  +hyyyyhyyyyyyyyyo.
# hyyyssssooooooossssyyyh/`+//////::::::::://////+`/hyyyd/-```ohyyh:  yyyyh  dyyyhyyyh:  oyyyh./hyyyy+`   `-hyyyh/.hyyyhs.    /yyyyho-hyyyho-   .-syyyyy
# hyyyyyyyyyyyyyyyyyyyyyh/`+/////////////////////+`-yyyyyyyyyyhss+:   yyyyh  dyyyhsyyyhh`oyyyh-oyyyh:       `----`yyyyys        ----.oyyyyo       `yyyyh
# hyyyyyyyyyyyyyyyyyyyyyh/`+/////////////////////+```/oshyyyyyyyyyho  yyyyh  dyyyy +hyyyhhyyyh-yyyyy/       .:::::hyyyys       `::::-oyyyys       -yyyyh
# hyyyyyyyyyyyyyyyyyyyyyh/`+/////////////////////+.yhyyds`  .:ohyyyd` yyyyh  dyyyy  `yyyyhyyyh./hyyyho.   ./syyyh/.hyyyh+-   `:yyyyh/.hyyyy+:    /yyyyys
# hyyyyyyyyyyyyyyyyyyyyyy-.+/////////////////////+`-yyyyyhyoyhhyyyh:  yyyyh  dyyyy    +hyyyyyh. +hyyyyyhhhyyyyyh+  -hyyyyyhhhyyyyyy/  :yhyyyyhhhyyyyyy+`
# +hhyyyyyyhhhhhhhhhhhhd-  -+++++++++++++++++++++.   /ohhyyyyyhhoo.   hhhhh  dhhhy     -shhhhd.   .syhhhyyhhyo:`     :+shhhyyhhho/      -/ohhhyyhhdo:`  
# 
# NOTICE OF LICENSE
#
# This source file is subject to the Open Software License (OSL 3.0)
# that is available through the world-wide-web at this URL:
# http://opensource.org/licenses/osl-3.0.php
#
# -----------------------
# @author: Iván Miranda
# @version: 1.0.0
# -----------------------
# Error genérico del framework
# -----------------------

final class Sfphp_Error extends Exception {
    // Redefinir la excepción, por lo que el mensaje no es opcional
    public function __construct($message, $code = 0, Exception $previous = null) {
        // asegúrese de que todo está asignado apropiadamente
        parent::__construct($message, $code, $previous);
    }

    // representación de cadena personalizada del objeto
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    // Procesa el log del sistema
    public function procesa($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            // Este código de error no está incluido en error_reporting
            return;
        }
        switch ($errno) {
            case E_USER_ERROR:
                $data = "[ERROR {$errno}]\n\rDesc::{$errstr}\n\rFile::{$errfile}\n\rLine::{$errline}\n\r";
                Sfphp_Log::set($data, "err");
                $data = str_replace("\n\r", "<br>", $data);
                $data = str_replace("[", "<b>[", $data);
                $data = str_replace("]", "]</b>", $data);
                self::fatal($data);
            break;
            case E_USER_WARNING:
                $data = "[WARNING {$errno}]\n\rDesc::{$errstr}\n\rFile::{$errfile}\n\rLine::{$errline}\n\r";
                Sfphp_Log::set($data, "err");
            break;
            case E_USER_NOTICE:
                $data = "[NOTICE {$errno}]\n\rDesc::{$errstr}\n\rFile::{$errfile}\n\rLine::{$errline}\n\r";
                Sfphp_Log::set($data, "err");
            break;
            default:
                $data = "[minor {$errno}]\n\rDesc::{$errstr}\n\rFile::{$errfile}\n\rLine::{$errline}\n\r";
                Sfphp_Log::set($data, "err");
            break;
        }
        $data = str_replace("\n\r", "<br>", $data);
        $data = str_replace("[", "<b>[", $data);
        $data = str_replace("]", "]</b>", $data);
        if(DEV_SHOWERRORS)
            self::draw($data);
        return true;
    }

    private function draw($data) {
        echo $data;
    }

    private function fatal($data) {
    ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo APP_NAME ?></title>
             <!-- Bootstrap Table -->
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
            <link rel="shortcut icon" href="{BASE_URL}favicon.ico">
        </head>
        <body>
            <div class="panel panel-danger">
                <div class="panel-heading">
                <?php echo $data ?>
                </div>
            </div>
            <nav>
                <ul class="pager">
                    <li class="previous"><a href="javascript:window.history.back()"><span aria-hidden="true">&larr;</span> Regresar</a></li>
                </ul>
            </nav>
        </body>
        </html>
    <?php
        exit(1);
    }
}