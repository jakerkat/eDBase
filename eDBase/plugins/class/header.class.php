<?php
////////////////////////////////////////////////////
//************************************************//
//      Declaramos el objeto Base Datos           //
//************************************************//
////////////////////////////////////////////////////
global $BD_Obj;
$BD_Obj = new BDheader();
/////////////////////////////////////////
//*************************************//
//      Clase de las Base de Datos     //
//*************************************//
/////////////////////////////////////////
class BDheader{
	function __construct(){// (: obtencion de sus valores predefinidos
		$this->DIRroot = str_replace("\\","/",realpath(dirname(__FILE__)."/../../..").'/');
		$this->lang = $this->setLang();
		$this->methodGetVars();
		$this->methodPostVars();
		$this->methodFilesVars();
		$this->getServerVars();
		$this->DIRtheme = $this->setTheme();
		$this->getClassFiles("eDBase/plugins/class/");
		BDarchivo::dellOldFile(strtotime("-1 hour"), $this->DIRroot."eDBase/TBDcontent");
	}
	function setLang(){// (: Obtencion del idioma
		$lang_file = implode(file($this->DIRroot."config/lang.txt"));
		include_once($this->DIRroot."eDBase/plugins/lib/lang/".$lang_file.".lang.inc.php");
		return $editor_lang_data;
	}
	function setTheme(){// (: Obtencion del tema
		$theme_file = implode(file($this->DIRroot."config/theme.txt"));
		$theme_dir = "/eDBase/plugins/lib/theme/".$theme_file."/";
		return $theme_dir;
	}
	//<<  Obtiene todos los archivos de clase >>
	function getClassFiles($class_dir){// (: Escribe el html del body
		$carpeta = opendir($this->DIRroot.$class_dir);
		while($class_file = readdir($carpeta)){
			if(!(is_dir($this->DIRroot.$class_dir.$class_file)) && $class_file!='pagina.class.php'){
				$file[] = $class_file;
			}
		}
		@asort($file);
		if(is_array($file)){
			foreach($file as $value){
				require_once($this->DIRroot.$class_dir.$value);
			}
		}
	}
	//<<  Obtiene todos los archivos de clase js>>
	function methodPostVars(){// (: Obtencion de las variables post
		global $HTTP_POST_VARS, $_POST;
		if(!(empty($HTTP_POST_VARS))){
			foreach ($HTTP_POST_VARS as $clave => $valor){
				$this->SysVars[$clave] = $this->CambioPHPvars($valor);
				if(!is_array($this->SysVars[$clave])){
					$this->SysVars[$clave] = urldecode($this->SysVars[$clave]);
				}
				$this->PostVars[$clave] = $this->SysVars[$clave];
			}
		}
		if(!(empty($_POST))){
			foreach ($_POST as $clave => $valor){
				$this->SysVars[$clave] = $this->CambioPHPvars($valor);
				if(!is_array($this->SysVars[$clave])){
					$this->SysVars[$clave] = urldecode($this->SysVars[$clave]);
				}
				$this->PostVars[$clave] = $this->SysVars[$clave];
			}
		}
	}
	function methodGetVars(){// (: Obtencion de las variables get
		global $HTTP_GET_VARS, $_GET;
		if(!(empty($HTTP_GET_VARS))){
			foreach ($HTTP_GET_VARS as $clave => $valor){
				$this->SysVars[$clave] = $this->CambioPHPvars($valor);
				if(!is_array($this->SysVars[$clave])){
					$this->SysVars[$clave] = urldecode($this->SysVars[$clave]);
				}
			}
		}
		if(!(empty($_GET))){
			foreach ($_GET as $clave => $valor){
				$this->SysVars[$clave] = $this->CambioPHPvars($valor);
				if(!is_array($this->SysVars[$clave])){
					$this->SysVars[$clave] = urldecode($this->SysVars[$clave]);
				}
			}
		}
	}
	function methodFilesVars(){// (: Obtencion de las variables Files
		global $_FILES;
		if(!(empty($_FILES))){
			foreach ($_FILES as $clave => $valor){
				$this->SysVars[$clave] = $this->CambioPHPvars($valor);
				if(is_array($this->SysVars[$clave]['tmp_name'])){
					foreach($this->SysVars[$clave]['tmp_name'] as $value){
						if(file_exists($value)){
							$contents = implode("", file($value));
							$contents = BDheader::auditContentFile($contents);
							@fwrite ( @fopen ( $value, 'w' ),$contents);
						}
					}
				}else{
					if(file_exists($this->SysVars[$clave]['tmp_name'])){
						$contents = implode("", file($this->SysVars[$clave]['tmp_name']));
						$contents = BDheader::auditContentFile($contents);
						@fwrite ( @fopen ( $this->SysVars[$clave]['tmp_name'], 'w' ),$contents);
					}
				}
			}
		}
	}
	function CambioPHPvars($texto){// (: Cambia los caracteres que modifico PHP para normalizarlos
		$codigos = array(
			'\"'	=>	'"',
			"\'"	=>	"'",
			"\\"	=>	"/",
		);
		foreach($codigos as $key=>$value){
			$texto = str_replace($key,$value,$texto);
		}
		return $texto;
	}
	static function arrayVarToString($name_var){//:) Convierte variables que son array a cadena
		global $BD_Obj;
		@set_time_limit(0);
		$var = @$BD_Obj->SysVars[$name_var];
		
		if(is_array(@$var)){
			$value = implode(BDheader::sConcat(),$var);
		}else{
			$value = $var;
		}
		return $value;
		set_time_limit(30);
	}
	function sConcat(){
		return '&*';
	}
	function getServerVars() {
		global $_SERVER;
		if(!(empty($_SERVER))){
			foreach ($_SERVER as $clave => $valor){
				$this->getSysVars[$clave] = $valor;
			}
		}
		$s = (empty($this->getSysVars["HTTPS"]) ? '' : ($this->getSysVars["HTTPS"] == "on")) ? "s" : "";
		$protocol = substr(strtolower($this->getSysVars["SERVER_PROTOCOL"]), 0, strpos(strtolower($this->getSysVars["SERVER_PROTOCOL"]),  "/")).$s;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
		$this->SysVars["URLactual"] = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
		$this->URLroot = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port."/";
		$this->UserIP = $this->getUserIP();	
	}
	function getUserIP(){
		global $_SERVER;
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		      $ip=$_SERVER['HTTP_CLIENT_IP'];
		}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}else{
		      $ip=$_SERVER['REMOTE_ADDR'];
		}
	   	return $ip;
	}
	static function auditContentFile($contenido){//:) Funcion para auditar texto que se sube al servidor
		$DIRroot = str_replace("\\","/",realpath(dirname(__FILE__)."/../../..").'/');
		//:) Evaluamos las funciones que no están permitidas
		include($DIRroot."config/auditFuncFile.php");
		return $contenido;
	}
	//:) Genera cadena con caracteres aleatorios
	static function generaPass($longitudPass = 10, $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890"){
		//Obtenemos la longitud de la cadena de caracteres
		$longitudCadena=strlen($cadena);
		 
		//Se define la variable que va a contener la contraseña
		$pass = "";
		//Se define la longitud de la contraseña, en mi caso 10, pero puedes poner la longitud que quieras
		 
		//Creamos la contraseña
		for($i=1 ; $i<=$longitudPass ; $i++){
			//Definimos numero aleatorio entre 0 y la longitud de la cadena de caracteres-1
			$pos=rand(0,$longitudCadena-1);
			 
			//Vamos formando la contraseña en cada iteraccion del bucle, añadiendo a la cadena $pass la letra correspondiente a la posicion $pos en la cadena de caracteres definida.
			$pass .= substr($cadena,$pos,1);
		}
		return $pass;
	}
	static function encrypt ($input,$Key) {
		$output = "";
		
        	$ivlen = openssl_cipher_iv_length($cipher="aes-256-cbc");
		$iv = openssl_random_pseudo_bytes($ivlen);
        	$ciphertext_raw = openssl_encrypt($input, "aes-256-cbc", $Key, OPENSSL_RAW_DATA,$iv);
        	$hmac = hash_hmac('sha256', $ciphertext_raw, $Key, $as_binary=true);
        	$output = "os:".base64_encode( $iv.$hmac.$ciphertext_raw );
        	
	        return $output;
	}
	

    	static function decrypt ($input,$Key) {
    		$output = "";
    		if(substr($input,0,3) == "os:"){
    			$data = str_replace("os:","",$input);
    			$c = base64_decode($data);
	        	$ivlen = openssl_cipher_iv_length($cipher="aes-256-cbc");
			$iv = substr($c, 0, $ivlen);
			$hmac = substr($c, $ivlen, $sha2len=32);
			$ciphertext_raw = substr($c, $ivlen+$sha2len);
	        	$output = openssl_decrypt($ciphertext_raw, $cipher, $Key, OPENSSL_RAW_DATA, $iv);
	        }else{    			
	        	$output = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($Key), base64_decode($input), MCRYPT_MODE_CBC, md5(md5($Key))), "\0");	        	
	        }
	        return $output;
    	}
	static function evaluar($string){//:) Para ejecutar funcion eval
		if(class_exists('Textos')){
			$string = Textos::auditContentFile($string);
		}
		
		if(class_exists('Texto')){
			$string = Texto::auditContentFile($string);
		}
		
		@eval($string);
		return $resultado;
	}
}
/*   Responsabilidades
/*
-- Cabecera de todos las variables precargadas para el programa eDBase
*/
?>