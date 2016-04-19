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
# Clase abstracta para las vistas en el sistema
# -----------------------

final class Sfphp_Vista {
	private $_html = "";
	private $_path = "";

	public function __construct() {
		$this->_path = "./App/Vistas/";
	}

#Procesa y dibuja una vista solicitada
	public function dibuja($vista) {
		$this->_html = $this->cargarArchivo($vista.".tpl");
		if($this->_html) {
			$this->asignaVariables();
			$this->procesaMenu();
			$this->procesaIncluir();
			$this->procesaVariables();
			$this->procesaCiclo();
			$this->procesaTabla();
			$this->procesaGrid();
			echo $this->_html;
		} else {
			throw new Sfphp_Error("Vista ".$this->_path.$vista.".tpl no existe", 1);
		}
	}

#Carga el contenido de un archivo
	private function cargarArchivo($vista) {
		$vista = str_replace("_", "/", $vista);
		$_path = $this->_path.$vista;
		$_respuesta = FALSE;
		if(file_exists($_path))
			return file_get_contents($_path);
		else
			return FALSE;
	}

#Procesa la etiqueta de menu
	private function procesaMenu() {
		preg_match_all ('/<.*?menu(.*?)>/', $this->_html, $_etiquetas);
		foreach ($_etiquetas[1] as $_clave => $_etiqueta) {
			$_menus = Sfphp_Disco::XMLArreglo(new SimpleXMLElement(file_get_contents("./Etc/Config/menu.xml")));
			$_html = '<div class="navbar navbar-default navbar-fixed-top"><div class="navbar-header"><button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button><a class="navbar-brand" href="{BASE_URL}dashboard">{APP_NAME}</a></div><div class="navbar-collapse collapse navbar-responsive-collapse"><ul class="nav navbar-nav navbar-right">';
			foreach ($_menus as $_menu) {
				if(isset($_menu["menu"])) {
					$_html .= '<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$_menu["texto"].' <b class="caret"></b></a><ul class="dropdown-menu">';
                	foreach ($_menu["menu"] as $_submenu) {
                		if(isset($_submenu["texto"]))
                			$_html .= '<li><a href="{BASE_URL}'.$_submenu["url"].'">'.$_submenu["texto"].'</a></li>';
                		else
                			$_html .= '<li role="separator" class="divider"></li>';
                	}
                	$_html .= '</ul></li>';
				} else {
					if(isset($_menu["texto"]))
            			$_html .= '<li><a href="{BASE_URL}'.$_menu["url"].'">'.$_menu["texto"].'</a></li>';
            		else
            			$_html .= '<li role="separator" class="divider"></li>';
				}
			}
			$_html .= '</div></div>';
			$this->_html = str_replace('<menu>', $_html, $this->_html);
		}
	}

#Procesa las etiquetas de inclusion de archivos externos a la plantilla
	private function procesaIncluir() {
		preg_match_all ('/<.*?incluir(.*?)>/', $this->_html, $_etiquetas);
		foreach ($_etiquetas[1] as $_clave => $_etiqueta) {
			preg_match_all ('/archivo="([a-zA-Z0-9\-_]*?)"/', $_etiqueta, $_data);
			$_incluir = $this->cargarArchivo($_data[1][0].".tpl");
			$this->_html = str_replace('<incluir archivo="'.$_data[1][0].'">', $_incluir, $this->_html);
		}
	}

#Asignar los valores a cada una de las variables requeridas por la plantilla
	private function procesaVariables() {
		preg_match_all ('#\{([a-z0-9\-_]*?)\}#is', $this->_html, $_etiquetas);
		foreach ($_etiquetas[0] as $_clave => $_valor) {
			$_valor = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "\\1", $_valor);
			if(isset(get_object_vars($this)[$_valor]))
				$this->_html = str_replace("{".$_valor."}", get_object_vars($this)[$_valor], $this->_html);
			if(isset(get_defined_constants()[$_valor]))
				$this->_html = str_replace("{".$_valor."}", get_defined_constants()[$_valor], $this->_html);	
			if(isset($_SESSION[$_valor]))
				$this->_html = str_replace("{".$_valor."}", $_SESSION[$_valor], $this->_html);	
		}
	}

#Las variables asignadas a la vista, se bajan a nivel de variables que pueden ser procesadas
#por el script
	private function asignaVariables() {
		foreach (get_object_vars($this) as $_nombre => $_valor )
			$$_nombre = $_valor;
	}

#Procesa las etiquetas de ciclo en base a arreglos
	private function procesaCiclo() {
		preg_match_all ('/<ciclo ([a-zA-Z0-9\-_]*?)>/', $this->_html, $_etiquetas);
		foreach ($_etiquetas[0] as $_clave => $_valor) {
			$_ciclo = preg_replace('/<ciclo ([a-zA-Z0-9\-_]*?)>/', "\\1", $_valor);
			if(isset(get_object_vars($this)[$_ciclo])) {
				$_arreglo = get_object_vars($this)[$_ciclo];
				$_posInicial = strpos($this->_html, '<ciclo '.$_ciclo.'>') + strlen('<ciclo '.$_ciclo.'>');
				$_posInicialAux = (strpos($this->_html, '<ciclo '.$_ciclo.'>') - strlen('<ciclo '.$_ciclo.'>')) - 1;
				$_posFinal = strpos($this->_html, '</ciclo '.$_ciclo.'>');
				$_codigo = substr($this->_html, $_posInicial, $_posFinal-$_posInicial);
				$_codigoReemplazo = "";
				if(is_array($_arreglo)) {
					foreach ($_arreglo as $_elemento) {
						$_codigoReemplazo .= $_codigo;
						preg_match_all ('#\{([a-z0-9\-_]*?)\}#is', $_codigo, $_variables);
						foreach ($_variables[1] as $_variable) {
							if(isset($_elemento[$_variable])) {
								if(Sfphp::esEntero($_elemento[$_variable])) {
									$_codigoReemplazo = str_replace("{".$_variable."}", $_elemento[$_variable], $_codigoReemplazo);
								}
								if(Sfphp::esFlotante($_elemento[$_variable])) {
									$_codigoReemplazo = str_replace("{".$_variable."}", number_format($_elemento[$_variable],2,".",","), $_codigoReemplazo);
								}
								if($_fecha = Sfphp::esFecha($_elemento[$_variable])) {
									$_codigoReemplazo = str_replace("{".$_variable."}", date_format($_fecha, 'Y-m-d'), $_codigoReemplazo);
								}
								if(Sfphp::esCadena($_elemento[$_variable])) {
									$_codigoReemplazo = str_replace("{".$_variable."}", trim($_elemento[$_variable]), $_codigoReemplazo);
								}
							}
						}
					}
					$this->_html=substr_replace($this->_html, $_codigoReemplazo, strpos($this->_html, '<ciclo '.$_ciclo.'>'), $_posFinal - $_posInicialAux);
				} else {
					$this->_html = str_replace("<ciclo ".$_ciclo.">", "El ciclo {$_ciclo} no existe", $this->_html);
					$this->_html = str_replace("</ciclo ".$_ciclo.">", "", $this->_html);
				}
			} else {
				$this->_html = str_replace("<ciclo ".$_ciclo.">", "El ciclo {$_ciclo} no existe", $this->_html);
				$this->_html = str_replace("</ciclo ".$_ciclo.">", "", $this->_html);
			}
		}
	}

#Procesa el armado de una tabla avanzada según el arreglo solicitado desde la vista
	private function procesaTabla() {
		$_codigoReemplazo = "";
		$_columns = array();
		preg_match_all ('/<.*?tabla(.*?)>/', $this->_html, $_etiquetas);
		foreach ($_etiquetas[1] as $_clave => $_etiqueta) {
			preg_match_all ('/datos="([a-zA-Z0-9\-_]*?)"/', $_etiqueta, $_data);
			preg_match_all ('/pagina="([0-9]*?)"/', $_etiqueta, $_pagina);
			preg_match_all ('/exportar="([a-zA-Z]*?)"/', $_etiqueta, $_exportar);
			preg_match_all ('/buscar="([a-zA-Z]*?)"/', $_etiqueta, $_buscar);
			preg_match_all ('/clic="([a-zA-Z]*?)"/', $_etiqueta, $_clic);
			$_buscar = (isset($_buscar[1][0])) ? "data-search=\"true\"" : false;
			$_exportar = (isset($_exportar[1][0])) ? "data-show-export=\"true\"" : false;
			$_pagina = (isset($_pagina[1][0])) ? "data-page-size=\"".$_pagina[1][0]."\" data-pagination=\"true\" data-show-pagination-switch=\"true\"" : false;
			if(isset($_data[1][0]))
				$_data = $_data[1][0];
			if(isset($_clic[1][0])) {
				$_clic = "$('#".$_data."').on('click-row.bs.table', function (e, row, \$element) {".$_clic[1][0]."(row)})";
			} else 
				$_clic = false;
			if(isset(get_object_vars($this)[$_data])) {
				$_datos = get_object_vars($this)[$_data];
				if(is_null($_datos))
					$_datos = array();
				if(count($_datos)) {
					$_json = json_encode($_datos);
					foreach (array_keys($_datos[0]) as $_llave => $_valor) {
					 	array_push($_columns,"{field:'".$_valor."',title:'".$_valor."',sortable:true, visible:true}");
					};
					$_columns = "[".implode(",",$_columns)."]";
					$_codigoReemplazo .= "<table id=\"".$_data."\" 
					data-show-columns=\"true\" data-mobile-responsive=\"true\"
					".$_buscar." ".$_exportar." ".$_pagina."></table>\n";
					$_codigoReemplazo .= "<script>\n
						$('#".$_data."').bootstrapTable({
							columns:".$_columns.",\n
							data:".$_json."\n
						});\n".$_clic."\n
						</script>\n";
					$_etiqueta = "<tabla".$_etiqueta.">";
					$this->_html = str_replace($_etiqueta, $_codigoReemplazo, $this->_html);
				}
			} else {
				$this->_html = str_replace($_etiqueta, " Variable ".$_data." no asignada", $this->_html);
			}
		}
	}

	private function procesaGrid() {
		$_codigoReemplazo = "";
		$_columnas = array();
		$_titulos = array();
		preg_match_all ('/<.*?excel(.*?)>/', $this->_html, $_etiquetas);
		foreach ($_etiquetas[1] as $_clave => $_etiqueta) {
			preg_match_all ('/datos="([a-zA-Z0-9\-_]*?)"/', $_etiqueta, $_data);
			preg_match_all ('/cambios="([a-zA-Z]*?)"/', $_etiqueta, $_cambios);
			preg_match_all ('/borrado="([a-zA-Z]*?)"/', $_etiqueta, $_borrado);
			preg_match_all ('/filas="([a-zA-Z]*?)"/', $_etiqueta, $_filas);
			$_filas = (isset($_filas[1][0])) ? "rowHeaders:true," : false;
			if(isset($_data[1][0]))	$_data = $_data[1][0];
			if(isset($_cambios[1][0])) {
				$_cambios = "
					afterChange: function (changes, source) {\n
          	if ((source == 'edit')) {\n
            	".$_cambios[1][0]."(changes[0][1],hot".$_data.".getDataAtRow(changes[0][0]),changes)\n
          	}\n
          },\n";
			} else 
				$_cambios = false;
			if(isset($_borrado[1][0])) {
				$_borrado = "
					beforeRemoveRow: function (index, amount) {\n
						return ".$_borrado[1][0]."(hot".$_data.".getDataAtRow(index))\n
          },\n";
			} else 
				$_cambios = false;
			$objeto = get_object_vars($this);
			if(isset($objeto[$_data])) {
				$_datos = $objeto[$_data];
				if(is_array($_datos)) {
					if(count($_datos) > 0) {
						$_json = json_encode($_datos);
						foreach (array_keys($_datos[0]) as $_llave => $_valor) {
						 	array_push($_columnas,"{data:'".$_valor."'}");
						 	array_push($_titulos,"'".$_valor."'");
						};
						$_columnas = "[".implode(",",$_columnas)."]";
						$_titulos = "[".implode(",",$_titulos)."]";
						$_codigoReemplazo .= "<div id=\"grid\" class=\"handsontable\"></div>\n
							<script>\n
							hot".$_data." = new Handsontable(document.getElementById('grid'), {\n
								data:".$_json.",\n
								minSpareRows: 1,\n
	            	contextMenu: true,
	            	fixedRowsTop: 0,\n
								colHeaders: ".$_titulos.",\n
								columns: ".$_columnas."\n,
								".$_filas."\n
								".$_cambios."\n
								".$_borrado."\n
							})\n
							</script>\n";
						$_etiqueta = "<grid".$_etiqueta.">";
						$this->_html = str_replace($_etiqueta, $_codigoReemplazo, $this->_html);
					} else {
						$_etiqueta = "<grid".$_etiqueta.">";
						$this->_html = str_replace($_etiqueta, " Variable ".$_data." sin datos", $this->_html);
					}
				} else {
					$_etiqueta = "<grid".$_etiqueta.">";
					$this->_html = str_replace($_etiqueta, " Variable ".$_data." no asignada", $this->_html);
				}
			} else {
				$_etiqueta = "<grid".$_etiqueta.">";
				$this->_html = str_replace($_etiqueta, " Variable ".$_data." no asignada", $this->_html);
			}
		}
	}

}
