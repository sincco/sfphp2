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
# Para manejo de archivos y directorios
# -----------------------

final class Sfphp_Disco {

#------------------------------------
# Funciones de XML
#------------------------------------
	# Graba un XML desde un arreglo
	public static function grabaXML ($arreglo, $root, $archivo) {
		$_xml = new SimpleXMLElement("<?xml version=\"1.0\"?><".$root."></".$root.">");
		self::array_to_xml($arreglo,$_xml);
		return $_xml->asXML($archivo);
	}

	# Parsea el XML a un arreglo multidimensional
	public static function XMLArreglo ($xml) {
		$resp = array();
		foreach ( (array) $xml as $indice => $nodo )
			$resp[$indice] = ( is_object ( $nodo ) ) ? self::XMLArreglo($nodo) : $nodo;
		return $resp;
	}

	public function arregloXML($array, $root) {
		$_xml = new SimpleXMLElement("<?xml version=\"1.0\"?><".$root."></".$root.">");
		foreach($array as $key => $value) {
			if(is_array($value)) {
				if(!is_numeric($key)){
					$subnode = $_xml->addChild("$key");
					self::array_to_xml($value, $subnode);
				} else{
					$subnode = $_xml->addChild("item$key");
					self::array_to_xml($value, $subnode);
				}
			} else {
				$_xml->addChild("$key",htmlspecialchars("$value"));
			}
		}
		return $_xml;
	}

	private function array_to_xml($array, &$_xml) {
		foreach($array as $key => $value) {
			if(is_array($value)) {
				if(!is_numeric($key)){
					$subnode = $_xml->addChild("$key");
					self::array_to_xml($value, $subnode);
				} else{
					$subnode = $_xml->addChild("item$key");
					self::array_to_xml($value, $subnode);
				}
			} else {
				$_xml->addChild("$key",htmlspecialchars("$value"));
			}
		}
	}

#------------------------------------
# Calcula el hash de una archivo o directorio
#------------------------------------
	public static function MD5($directory) {
		if (! is_dir($directory)) {
			return md5_file($directory);
		}
		$files = array();
		$dir = dir($directory);
		while (false !== ($file = $dir->read())) {
			if ($file != '.' and $file != '..') {
				if (is_dir($directory . '/' . $file)) {
					$files[] = self::MD5($directory . '/' . $file);
				}
				else {
					$files[] = md5_file($directory . '/' . $file);
				}
			}
		}
		$dir->close();
		return md5(implode('', $files));
	}
}