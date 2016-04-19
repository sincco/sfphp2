<?php
final class ElasticEmail {
	public function send($para, $asunto, $contenidoTxt, $contenidoHtml, $de, $deNombre)
	{
		$respuesta = "";
		$_data = "username=".urlencode();
		$_data .= "&api_key=".urlencode();
		$_data .= "&from=".urlencode($de);
		$_data .= "&from_name=".urlencode($deNombre);
		$_data .= "&to=".urlencode($para);
		$_data .= "&subject=".urlencode($asunto);
		if($contenidoHtml)
		$_data .= "&body_html=".urlencode($contenidoHtml);
		if($contenidoTxt)
		$_data .= "&body_text=".urlencode($contenidoTxt);
		$_header = "POST /mailer/send HTTP/1.0\r\n";
		$_header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$_header .= "Content-Length: " . strlen($_data) . "\r\n\r\n";
		$fp = fsockopen('ssl://api.elasticemail.com', 443, $errno, $errstr, 30);
		if(!$fp)
		return "ERROR. Could not open connection";
		else {
		fputs ($fp, $_header.$_data);
		while (!feof($fp)) {
		$respuesta .= fread ($fp, 1024);
		}
		fclose($fp);
		}
		return $respuesta;
	}
}