<?
/////////////////////////////////////////
//*************************************//
//      Clase de las Archivo           //
//*************************************//
/////////////////////////////////////////
class BDarchivo{
	static function subirArchivo($directorio_destino, $nvo_nombre='', $extension, $var=''){// (: Subir archivos al servidor	
		//datos del arhivo 
		if(!(empty($var['name']))){
			$nombre_archivo = $var['name']; 
			$tipo_archivo = $var['type']; 
			$nombre_temp = $var['tmp_name'];
			$ext = BDarchivo::getExtFile($nombre_archivo);
			if(empty($nvo_nombre)){
				$nvo_nombre = str_replace(".".$ext, "", $nombre_archivo);
			}
			if(empty($extension)){
				$extension = $ext;
			}
			//********************************************************************//
			//******************   Nombre del nuevo archivo    *******************//
			//********************************************************************//
			if(file_exists($directorio_destino)){
				$carpeta = "true";
			}else{
				if(mkdir($directorio_destino)){
					$carpeta = "true";
				}else{
					$carpeta = "false";
				}
			}
			if($carpeta == "true" && $extension==$ext){
				if(file_exists($nombre_temp)){
					copy($nombre_temp, $directorio_destino."/".$nvo_nombre.".".$extension);
					$archivo_info["nombre"] = $nvo_nombre;
					$archivo_info["tipo"] = $extension;
					$archivo_info["tamano"] = $var['size'];
					unlink($nombre_temp);
					//return $archivo_info;						
				}
			}else{
				unlink($nombre_temp);
			}
		}
	}
	static function fixFileVar($var = array()){//:) Acomoda la variable file multiple para que se pueda usar con la funcion subir archivo
		$fix = array();
		foreach($var as $key=>$value){
			if(is_array($value)){
				foreach($value as $key2=>$value2){
					$fix[$key2][$key] = $value2;
				}
			}else{
				$fix[0][$key] = $value;
			}
		}
		return $fix;
	}
	static function saveArrayInFile($array, $name_var, $carpeta, $name_file, $type="w"){//:) Almacena variables en un archivo
		if(is_array($array)){
				$texto = "";
				foreach($array as $key=>$value){
					if(is_array($value)){
						ini_set("memory_limit","512M");
						$texto .= BDarchivo::saveArrayInFile($value, $name_var."['".$key."']", $carpeta, $name_file, "return");
					}else{
						$value = str_replace('"','"'.".'".'"'."'.".'"',$value);
						$value = str_replace('$','"'.'."$".'.'"',$value);
						$texto .= '$'.$name_var.'["'.$key.'"]="'.$value.'";';
					}
				}
		}else{
			$array = str_replace('"','"'.".'".'"'."'.".'"',$array);
			$array = str_replace('$','"'.'."$".'.'"',$array);
			$texto = '$'.$name_var.'="'.$array.'";';
		}
		if($type!="return"){
			$texto = "<? ".$texto." ?>";
			BDarchivo::crearFile($carpeta, $name_file, $texto, $type);
		}else{
			return $texto;
		}
	}
	static function crearFile($carpeta, $name, $cuerpo, $type_open){// (: Crear fichero
		/*
		'r' _ Abre para sólo lectura; sitúa el apuntador del fichero al comienzo del mismo_ 
		'r+' _ Abre para lectura y escritura; situa el apuntador del fichero al comienzo del fichero_ 
		'w' _ Abre para sólo escritura; sitúa el apuntador del fichero al comienzo del fichero y trunca el fichero con longitud cero_ Si el fichero no existe, trata de crearlo_ 
		'w+' _ Abre el fichero para lectura y escritura; sitúa el apuntador del fichero al comienzo del fichero y trunca el fichero con longitud cero_ Si el fichero no existe, trata de crearlo_ 
		'a' _ Abre sólo para escribir (añadir); sitúa el apuntador del fichero al final del mismo_ Si el fichero no existe, trata de crearlo_ 
		'a+' _ Abre para lectura y escritura (añadiendo); sitúa el apuntador del fichero al final del mismo_ Si el fichero no existe, trata de crearlo_ 
		*/
		if(BDarchivo::dirAccess("write", $carpeta)){
			$carpetal = substr($carpeta, 1, -1);
			$ini = "";
			if(!(file_exists($carpetal))){
				$carpetal=explode("/",$carpetal);
				foreach($carpetal as $value){
					$ini = $ini."/".$value; 
					if(!(file_exists($ini))){
						mkdir($ini);
					}
				}
			}
			$fichero = $carpeta.$name;
			$fichero = fopen($fichero, $type_open);
			$cuerpo = BDheader::auditContentFile($cuerpo);
			fwrite($fichero, $cuerpo);
			fclose($fichero);
		}
	}
	static function leerCarpeta($carpeta, $all = 'all', $ext=''){// (: Leer contenido de la carpeta
		if(BDarchivo::dirAccess("readdir", $carpeta)){
			if(file_exists($carpeta)){			
				$fichero = @opendir($carpeta);
				$elemento=array();
				if($all!='file'){
					if($all=="dir"){
						rewinddir($fichero);
						while($valor = readdir($fichero)){
							$info = $carpeta."/".$valor;
							if(is_dir($info) && $valor!="." && $valor!=".."){
								$elemento[]= $valor;
							}
						}
					}else{
						rewinddir($fichero);
						while($valor = readdir($fichero)){
							$info = $carpeta."/".$valor;
							if(is_dir($info) && $valor!="." && $valor!=".."){
								$elemento[]= $valor;
							}
						}
						rewinddir($fichero);
						while($valor = readdir($fichero)){
							$info = $carpeta."/".$valor;
							if(!(empty($ext))){
								if(!is_dir($info) && BDarchivo::getExtFile($valor)==$ext){
									$elemento[]= $valor;
								}
							}else{
								if(!is_dir($info)){
									$elemento[]= $valor;
								}
							}
						}
					}
				}else{
					while($valor = readdir($fichero)){
						$info = $carpeta."/".$valor;
						if(!(empty($ext))){
							if(!is_dir($info) && BDarchivo::getExtFile($valor)==$ext){
								$elemento[]= $valor;
							}
						}else{
							if(!is_dir($info)){
								$elemento[]= $valor;
							}
						}
					}
				}
				closedir($fichero);
				@asort($elemento);
				return @$elemento;
			}
		}
	}
	static function getExtFile($file){// (: Obtiene la extension de un archivo
		$ext = explode(".",$file);
		$ext = strtolower($ext[count($ext)-1]);
		return $ext;
	}
	static function dellOldFile($fecha_borrado, $carpeta){//:) Borra los archivos antiguos
		$elemento = BDarchivo::leerCarpeta($carpeta, 'file');
		if(is_array($elemento)){
			foreach($elemento as $value){
				$fechaMod = date('YmdHis',filectime($carpeta."/".$value));
				$fechaComp =date("YmdHis",$fecha_borrado);
				if($fechaMod<$fechaComp){
					unlink($carpeta."/".$value);
				}
			}
		}
	}
	static function dirAccess($id, $carpeta){
		//Array con ficheros no permitidos
		$access["file"]=array(
			"/PHPpages",
			"/codigos",
			"/config",
			"/eDBase/bdata",
		);
		$access["readfile"]=array(
			"/PHPpages",
			"/config",
		);
		$access["write"]=array(
			"/PHPpages",
			"/config",
			"/codigos",
			"/ecoder",
			"/spaw2",
		);
		$access["readdir"]=array(
			"/PHPpages",
			"/config",
			"/ecoder",
			"/spaw2",
		);
		
		//Validacion de carpeta
		$permiso = true;
		if(is_array($access[$id])){
			foreach($access[$id] as $value){
				if(stristr($carpeta,$value)){
					$permiso = false;
				}
			}
		}
		return $permiso;
	}
	static function file_get_contents_curl($url) {//:) funcion para leer archivos de otro sitio
		//if (strpos($url,'http://') !== FALSE) {
			$url = str_replace(" ","%20",$url);
			$fc = curl_init();
			curl_setopt($fc, CURLOPT_URL,$url);
			curl_setopt($fc, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($fc, CURLOPT_HEADER,0);
			curl_setopt($fc, CURLOPT_VERBOSE,0);
			curl_setopt($fc, CURLOPT_SSL_VERIFYPEER,FALSE);
			curl_setopt($fc, CURLOPT_TIMEOUT,30);
			$res = curl_exec($fc);
			curl_close($fc);
		//}
		//else $res = file_get_contents($url);
		return $res;
	}
	static function dellCarpetaCont($carpeta){//:) Elimina el contenido de una carpeta
		$elemento = BDarchivo::leerCarpeta($carpeta, "dir");
		if(is_array(@$elemento)){
			foreach($elemento as $value){
				BDarchivo::dellCarpetaCont($carpeta.$value."/");
			}
		}
		$elemento = BDarchivo::leerCarpeta($carpeta, "file");
		if(is_array(@$elemento)){
			foreach($elemento as $value){
				if(file_exists($carpeta.$value)){
					unlink($carpeta.$value);
				}
			}
		}
		if(@opendir($carpeta)){
			@rmdir($carpeta);
		}
	}
}
?>