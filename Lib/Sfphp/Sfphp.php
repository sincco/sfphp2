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
# Metodos basicos para todas las clases del Framework
# -----------------------

final class Sfphp 
{

	public static function obtenNavegador() {
		$u_agent = $_SERVER['HTTP_USER_AGENT']; 
	    $bname = 'Unknown';
	    $platform = 'Unknown';
	    $version= "";
	    $ub = "";

	    if(preg_match('/linux/i', $u_agent)) {
	        $platform = 'linux';
	    } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
	        $platform = 'mac';
	    } elseif (preg_match('/windows|win32/i', $u_agent)) {
	        $platform = 'windows';
	    }
	    
	    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) { 
	        $bname = 'Internet Explorer'; 
	        $ub = "MSIE"; 
	    } elseif(preg_match('/Firefox/i',$u_agent)) { 
	        $bname = 'Mozilla Firefox'; 
	        $ub = "Firefox"; 
	    } elseif(preg_match('/Chrome/i',$u_agent)) { 
	        $bname = 'Google Chrome'; 
	        $ub = "Chrome"; 
	    } elseif(preg_match('/Safari/i',$u_agent)) { 
	        $bname = 'Apple Safari'; 
	        $ub = "Safari"; 
	    } elseif(preg_match('/Opera/i',$u_agent)) { 
	        $bname = 'Opera'; 
	        $ub = "Opera"; 
	    } elseif(preg_match('/Netscape/i',$u_agent)) { 
	        $bname = 'Netscape'; 
	        $ub = "Netscape"; 
	    }    

	    $known = array('Version', $ub, 'other');
	    $pattern = '#(?<browser>' . join('|', $known) .
	    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	    if (!preg_match_all($pattern, $u_agent, $matches)) {
	        $pattern = "";
	    }
	    
	    $i = count($matches['browser']);
	    if ($i != 1) {
	        if (strripos($u_agent,"Version") < strripos($u_agent,$ub))
	            $version= $matches['version'][0];
	        else
	            $version= $matches['version'][1];
	    } else {
	        $version= $matches['version'][0];
	    }
	    
	    if ($version==null || $version=="") {$version="?";}
	    
	    return array(
	        'userAgent' => $u_agent,
	        'name'      => $bname,
	        'version'   => $version,
	        'platform'  => $platform,
	        'pattern'    => $pattern
	    );
	}

	public static function obtenIP() {
		return $_SERVER['REMOTE_ADDR'];
	}

	public static function limpiarEntrada($valor) {
	  $_busquedas = array(
	    '@<script[^>]*?>.*?</script>@si',   #Quitar javascript
	    '@<[\/\!]*?[^<>]*?>@si',            #Quitar html
	    '@<style[^>]*?>.*?</style>@siU',    #Quitar css
	    '@<![\s\S]*?--[ \t\n\r]*>@'         #Quitar comentarios multilinea
	  );
	  if (is_array($valor)) {
	  	foreach ($valor as $_key => $_value) {
	  		$valor[$_key] = Sfphp::limpiarEntrada($_value); #Recursivo para arreglos
	  	}	  	
	  }else {
	  	if(strtolower($valor) == "null")
			$valor = "";
	    $valor = preg_replace($_busquedas, '', $valor);
	    $valor = filter_var($valor,FILTER_SANITIZE_STRING);
	    if (get_magic_quotes_gpc()) {
				$valor = stripslashes($valor);
			}
		}
		return $valor;
	}

	public static function parseaUTF8($contenido) {
		if(is_array($contenido))
			foreach ($contenido as $key => $value)
				$contenido[$key] = utf8_decode($value);
		else
			$contenido = utf8_decode($contenido);
		return $contenido;
	}
	
	public static function convierteUTF8($array)
	{
	    array_walk_recursive($array, function(&$item, $key){
	        if(!mb_detect_encoding($item, 'utf-8', true)){
	                $item = utf8_encode($item);
	        }
	    });
	 
	    return $array;
	}
#------------------
#Encripcion AES256
#------------------
	public static function encrypt($data, $key = APP_KEY) {
		$salt = 'cH!swe!retReGu7W6bEDRup7usuDUh9THeD2CHeGE*ewr4n39=E@rAsp7c-Ph@pH';
		$key = substr(hash('sha256', $salt.$key.$salt), 0, 32);
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $iv));
		return $encrypted;
	}
	public static function decrypt($data, $key = APP_KEY) {
		$salt = 'cH!swe!retReGu7W6bEDRup7usuDUh9THeD2CHeGE*ewr4n39=E@rAsp7c-Ph@pH';
		$key = substr(hash('sha256', $salt.$key.$salt), 0, 32);
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($data), MCRYPT_MODE_ECB, $iv);
		return $decrypted;
	}
#------------------------------------
#Ejecuta un metodo de cualquier clase
#------------------------------------
	public static function ejecuta($clase, $metodo, $argumentos) {
		$objeto = Sfphp::CargaModelo($clase);
		if(is_object($objeto)) {
			return Sfphp::EjecutarMetodo($objeto, $metodo, $argumentos);
		} else {
			$objeto = new $clase;
			return Sfphp::EjecutarMetodo($objeto, $metodo, $argumentos);
		}
	}
#------------------------------------
#Obtener la identificación unica del cliente
#------------------------------------
	public static function tokenCliente() {
		$_cadena = $_SERVER["HTTP_USER_AGENT"].
			$_SERVER["HTTP_ACCEPT"].
			$_SERVER["HTTP_ACCEPT_LANGUAGE"].
			$_SERVER["HTTP_ACCEPT_ENCODING"].
			$_SERVER['REMOTE_ADDR'];
		return $_cadena;
	}
#------------------------------------
#Traducir fecha al español
#------------------------------------
	public static function fechaEsp($fecha) {
		$anio = date("y", $fecha);
		$dia = date("j", $fecha);
		$mes=date("F", $fecha);
		if ($mes=="January") $mes="Enero";
		if ($mes=="February") $mes="Febrero";
		if ($mes=="March") $mes="Marzo";
		if ($mes=="April") $mes="Abril";
		if ($mes=="May") $mes="Mayo";
		if ($mes=="June") $mes="Junio";
		if ($mes=="July") $mes="Julio";
		if ($mes=="August") $mes="Agosto";
		if ($mes=="September") $mes="Setiembre";
		if ($mes=="October") $mes="Octubre";
		if ($mes=="November") $mes="Noviembre";
		if ($mes=="December") $mes="Diciembre";
		return "{$dia} {$mes} {$anio}";
	}

#------------------------------------
#Obtener tipo de dato
#------------------------------------
	public static function esCadena($f) {
		if(!is_numeric($f)) {
			if(!self::esFecha($f))
				return true;
			else
				return false;
		}
		else
			return false;
	}

	public static function esEntero($f) {
		if(!self::esFlotante($f)) {
			return is_numeric($f);
		}
	}

	public static function esFlotante($f) {
		if(!preg_match('/^\d+$/', $f))
			return preg_match('/^[+-]?(([0-9]+)|([0-9]*\.[0-9]+|[0-9]+\.[0-9]*)|(([0-9]+|([0-9]*\.[0-9]+|[0-9]+\.[0-9]*))[eE][+-]?[0-9]+))$/', $f);
		else
			return false;
	}

	public static function esFecha($f) {
		if($_fecha = date_create_from_format("Y-m-d H:i:s",$f) OR 
			$_fecha = date_create_from_format("Y-m-d",$f) OR
			$_fecha = date_create_from_format("d-m-Y H:i:s",$f) OR
			$_fecha = date_create_from_format("d-m-Y",$f) OR
			$_fecha = date_create_from_format("d/m/Y H:i:s",$f) OR
			$_fecha = date_create_from_format("d/m/Y",$f)) {
			return $_fecha;
		} else
			return false;
	}
}