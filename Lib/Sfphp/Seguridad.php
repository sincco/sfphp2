<?php
#Esqueleto para el manejo de seguridad, se valida que las clases derivadas tengan el metodo
#que se invoca desde el control de arranque del framework
abstract class Sfphp_Seguridad {

#Se ejecuta para verificar si se tiene permitido ejecutar el controlador/modelo
	abstract public function validarAcceso($controlador = "", $modelo = "");
} 