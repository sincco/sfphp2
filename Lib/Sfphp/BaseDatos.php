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
# @version: 1.0.2
# -----------------------
# Manejador principal de la base de datos
# -----------------------
final class Sfphp_BaseDatos {  
    static private $origen;
    static private $conexion;
    static private $instancia;
    # Conexion a la BD desde la creación de la clase
    private function __construct($configuracion = "default") {
        $base = Sfphp_Config::get('bases');
        $base = $base[$configuracion];
        self::$origen = $configuracion;
        try {
            if(!isset($base["charset"]))
                $base["charset"] = "utf8";
            $parametros = array();
            switch ($base["type"]) {
                case 'sqlsrv':
                    self::$conexion = new PDO($base["type"].":Server=".$base["host"].";",
                        $base["user"], Sfphp::decrypt($base["password"]), $parametros);
                    break;
                case 'mysql':
                    array_push($parametros, array(
                        PDO::MYSQL_ATTR_COMPRESS => TRUE,
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '. $base["charset"]
                    ));
                    self::$conexion = new PDO($base["type"].":host=".$base["host"].";dbname=".$base["dbname"],
                        $base["user"], Sfphp::decrypt($base["password"]), $parametros);
                    break;
                case 'firebird':
                    array_push($parametros, array(
                        PDO::FB_ATTR_TIMESTAMP_FORMAT => "%d-%m-%Y",
                        PDO::FB_ATTR_DATE_FORMAT => "%d-%m-%Y"
                    ));
                    self::$conexion = new PDO($base["type"].":dbname=".$base["host"].$base["dbname"], $base["user"], Sfphp::decrypt($base["password"]), $parametros);
                    break;
                default:
                    self::$conexion = new PDO($base["type"].":host=".$base["host"].";dbname=".$base["dbname"],
                        $base["user"], Sfphp::decrypt($base["password"]));
                    break;
            }
        } catch (PDOException $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }

    public static function get($base = "default") {
    # Conectar a la base, sólo si es distinta a la actualmente establecida
        if($base != self::$origen)
            self::$instancia = new self($base);
        return self::$instancia;
    }

    public function beginTransaction() {
        return self::$conexion->beginTransaction();
    }

    public function commit() {
        return self::$conexion->commit();
    }

    public function errorCode() {
        return self::$conexion->errorCode();
    }
    
    public function errorInfo() {
        return self::$conexion->errorInfo();
    }

    # Ejecucion de querys, con soporte para pase de parametros en un arreglo
    public function query($consulta, $valores = array(), $cache = TRUE) {
        $resultado = FALSE;
        $query = $consulta;
        $cache = APP_CACHE;
        if(APP_CACHE)
            if(strstr(strtoupper(trim($query)), "__SESIONES"))
                $cache = FALSE;
        if($statement = self::$conexion->prepare($consulta)) {
            if(preg_match_all("/(:\w+)/", $consulta, $campo, PREG_PATTERN_ORDER)) {
                $campo = array_pop($campo);
                foreach($campo as $parametro){
                    $statement->bindValue($parametro, $valores[substr($parametro,1)]);
                    $query = str_replace($parametro, $valores[substr($parametro,1)], $query);
                }
            }
            if($cache) {
                $cached = Sfphp_Cache::get("data_".md5($query));
                if($cached)
                    return $cached;
            }
            try {
                if (!$statement->execute())
                    throw new PDOException("[SQLSTATE] ".$statement->errorInfo()[2],$statement->errorInfo()[1]);
                $resultado = $statement->fetchAll(PDO::FETCH_ASSOC);
                $statement->closeCursor();
            }
            catch(PDOException $e) {
                trigger_error($e->getMessage(), E_USER_ERROR);
            }
            if($cache)
                Sfphp_Cache::set("data_".md5($query), $resultado);
            if(DEV_QUERYLOG)
                Sfphp_Log::query($query);
            return $resultado;
        }
    }

    # Ejecucion de querys, para uso en el propio framework (construccion de modelos automaticos)
    public function raw_query($consulta, $valores = array()) {
        $resultado = false;
        if($statement = self::$conexion->prepare($consulta)) {
            if(preg_match_all("/(:\w+)/", $consulta, $campo, PREG_PATTERN_ORDER)) {
                $campo = array_pop($campo);
                foreach($campo as $parametro) {
                    $statement->bindValue($parametro, $valores[substr($parametro,1)]);
                }
            }
            try {
                if (!$statement->execute())
                    return array();
                $resultado = $statement->fetchAll(PDO::FETCH_ASSOC);
                if(count($resultado) == 1)
                    $resultado = $resultado[0];
                $statement->closeCursor();
            }
            catch(PDOException $e) {
                trigger_error($e->getMessage(), E_USER_ERROR);
            }
            return $resultado;
        }
    }

    # Ejecucion de INSERT
    public function insert($consulta, $valores = array()) {
        $resultado = false;
        if($statement = self::$conexion->prepare($consulta)) {
            if(preg_match_all("/(:\w+)/", $consulta, $campo, PREG_PATTERN_ORDER)) {
                $campo = array_pop($campo);
                foreach($campo as $parametro){
                    $statement->bindValue($parametro, $valores[substr($parametro,1)]);
                }
            }
            try {
                if (!$statement->execute())
                    throw new PDOException("[SQLSTATE] ".$statement->errorInfo()[2],$statement->errorInfo()[1]);
                $resultado = self::$conexion->lastInsertId();
            }
            catch(PDOException $e) {
                trigger_error($e->getMessage(), E_USER_ERROR);
            }
            return $resultado;
        }
    }
}