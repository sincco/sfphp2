<?php
/**
 * Control de seguridad.
 * Esta clase y método se invocan automaticamente antes de lanzar
 * cualquier control/accion en el sistema, por lo que es el lugar donde
 * se deben realizar todas las validaciones para el acceso al sistema
 */
class Seguridad extends Sfphp_Seguridad
{
	public function validarAcceso($controlador = "", $modelo = "") {
		return TRUE;
	}
}