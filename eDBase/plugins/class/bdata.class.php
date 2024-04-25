<? 
/////////////////////////////////////////
//*************************************//
//      Clase de las Bases de Datos    //
//*************************************//
/////////////////////////////////////////
class BData{
	var $time_zone = "";
	var $fields = array();
	static function conexList(){// (: Obtiene un array con el nombre de las conexiones
		global $BD_Obj;
		$elemento = BDarchivo::leerCarpeta($BD_Obj->DIRroot."eDBase/bdata/", 'file', 'php');
		if(is_array($elemento)){
			foreach($elemento as $value){
				$conex[str_replace(".php","",$value)] = $value;
			}
			return $conex;
		}
	}
	static function conexLista(){// (: Obtiene un array con el nombre de las conexiones
		global $BD_Obj;
		$elemento = BDarchivo::leerCarpeta($BD_Obj->DIRroot."eDBase/bdata/", 'file', 'php');
		if(is_array($elemento)){
			foreach($elemento as $value){
				$conex[str_replace(".php","",$value)]["value"] = $value;
			}
			return $conex;
		}
	}
	function conexFile($fileconnect){// (: Conexion con el nombre del archivo
		global $BD_Obj;
		$SysVars = $BD_Obj->SysVars;
		include($BD_Obj->DIRroot."eDBase/bdata/".$fileconnect);
		if(file_exists($BD_Obj->DIRroot."config/clave.php")){
			include($BD_Obj->DIRroot."config/clave.php");
		}
		$this->fileconnect = str_replace(".php","",$fileconnect);
		if($BD_Obj->SysVars["encrypt"]){
			$password = BDheader::decrypt($BD_Obj->SysVars["password"],$clave);
			if(substr($BD_Obj->SysVars["password"],0,3)!="os:"){
				$varConex["typeConnect"] = $BD_Obj->SysVars["typeConnect"];
				if($BD_Obj->SysVars["typeConnect"]!="ODBC"){
					$varConex["bdata"] = $BD_Obj->SysVars["bdata"];
				}
				$varConex["server"] = $BD_Obj->SysVars["server"];
				$varConex["user"] = $BD_Obj->SysVars["user"];
				$varConex["password"] = BDheader::encrypt($password,$clave);
				$varConex["log"] = $BD_Obj->SysVars["log"];
				$varConex["NoMonth"] = $BD_Obj->SysVars["NoMonth"];
				$varConex["NoFiles"] = $BD_Obj->SysVars["NoFiles"];
				$varConex["encrypt"] = $BD_Obj->SysVars["encrypt"];			
				
				$saveConex = new Archivo;
				$saveConex ->saveArrayInFile($varConex, "BD_Obj->SysVars", $BD_Obj->DIRroot."eDBase/bdata/", $fileconnect);
			}
			$BD_Obj->SysVars["password"] = $password;
		}
		$this->BD(@$BD_Obj->SysVars["bdata"]);
		$this->conexType($BD_Obj->SysVars["typeConnect"], $BD_Obj->SysVars["server"], $BD_Obj->SysVars["user"], $BD_Obj->SysVars["password"]);
		
		$this->server(@$BD_Obj->SysVars["server"]);
		
		//:) Variables del Log
		$this->logOn = $BD_Obj->SysVars["log"];
		$this->NoMonth = $BD_Obj->SysVars["NoMonth"];
		$this->NoFiles = $BD_Obj->SysVars["NoFiles"];
		
		$BD_Obj->SysVars = $SysVars;
	}
	function conexType($typeConex, $servidor, $usuario, $password){// (: Tipo de Conexion
		global $BD_Obj;
		$this->typeConex = $typeConex;
		$this->BD(@$BD_Obj->SysVars["bdata"]);
		if(!empty($this->typeConex) && !empty($servidor) && !empty($usuario)){
			@eval("$"."this->conex".$this->typeConex."('".$servidor."','".$usuario."','".$password."');");
		}
	}
	//<< Conexiones >>
	function conexODBC($base, $usuario, $password){// (: Conexion de la Base de datos 
		global $BD_Obj;
		$this->BD($base);
		$this->conexion = odbc_connect($base, $usuario, $password);
		$this->select = "*";
		$this->set = "";
		$this->where = "";
		$this->having = "";
		$this->group = "";
		$this->order = "";
		$this->values = "";
		$this->noRows = "";
		$this->typeConex = "ODBC";
		if (!$this->conexion) {
		   $this->conerror = $BD_Obj->lanarrayg['basedata']['conerror']['false'].odbc_error();
		}else{
		   $this->conerror = $BD_Obj->lang['basedata']['conerror']['true'];
		}
		//$this->typeConex = "ODBC";
	}
	function conexSyBase($servidor, $usuario, $password){// (: Conexion de la Base de datos 
		// Se establece la conexion con la fuente de datos
		global $BD_Obj;
		sybase_min_client_severity(100);
		sybase_min_server_severity(100);
		$this->conexion = @sybase_connect($servidor, $usuario, $password);
		$this->select = "*";
		$this->set = "";
		$this->where = "";
		$this->having = "";
		$this->group = "";
		$this->order = "";
		$this->values = "";
		$this->noRows = "";
		$this->typeConex = "SyBase";
		if (!$this->conexion) {
		   $this->conerror = $BD_Obj->lang['basedata']['conerror']['false'] . sybase_error();
		}else{
		   $this->conerror = $BD_Obj->lang['basedata']['conerror']['true'];
		}
		//$this->typeConex = "SyBase";
	}
	function conexMsSQL($servidor, $usuario, $password){// (: Conexion de la Base de datos 
		// Se establece la conexion con la fuente de datos
		global $BD_Obj;
		//$this->conexion = new PDO("sqlsrv:server=".$servidor.";dbname=".$this->BD, $usuario, $password);
		//ini_set('display_errors', 'On');
		//error_reporting (E_ALL); // For all
		if(function_exists("mssql_connect")){
			$this->conexion = @mssql_connect($servidor, $usuario, $password);
			$this->type_conn_php = "mssql";
		}
		if(function_exists("sqlsrv_connect") && !$this->conexion){
			$connectionInfo = array( "Database"=>$this->BD, "UID"=>$usuario, "PWD"=>$password);
			$conn = sqlsrv_connect( $servidor, $connectionInfo);
			$this->type_conn_php = "sqlsrv";
		}
		if(!$this->conexion){
			$this->conexion = new PDO("sqlsrv:server=".$servidor.";Database=".$this->BD, $usuario, $password);
			$this->type_conn_php = "PDO";
		}
		$this->select = "*";
		$this->set = "";
		$this->where = "";
		$this->having = "";
		$this->group = "";
		$this->order = "";
		$this->values = "";
		$this->noRows = "";
		$this->typeConex = "MsSQL";
		if (!$this->conexion) {
		   if($this->type_conn_php=="mssql"){
		   	$this->conerror = $BD_Obj->lang['basedata']['conerror']['false'].mssql_get_last_message();
		   }else if($this->type_conn_php=="sqlsrv"){
		   	$this->conerror = $BD_Obj->lang['basedata']['conerror']['false'].print_r( sqlsrv_errors(), true);
		   }else if($this->type_conn_php=="PDO"){
		   	$this->conerror = $BD_Obj->lang['basedata']['conerror']['false'];
		   }
		}else{
		   $this->conerror = $BD_Obj->lang['basedata']['conerror']['true'];
		}
	}
	function conexMySQL($servidor, $usuario, $password){// (: Conexion de la Base de datos 
		// Se establece la conexion con la fuente de datos
		global $BD_Obj;
		$this->conexion = new mysqli($servidor, $usuario, $password, $this->BD);
		$this->conexion->set_charset("utf8");
		$this->select = "*";
		$this->set = "";
		$this->where = "";
		$this->having = "";
		$this->group = "";
		$this->order = "";
		$this->values = "";
		$this->noRows = "";
		$this->typeConex = "MySQL";
		if ($this->conexion->connect_errno) {
		   $this->conerror = $BD_Obj->lang['basedata']['conerror']['false'] . $this->conexion->connect_error;
		}else{
		   $this->conerror = $BD_Obj->lang['basedata']['conerror']['true'];
		}
	}
	function conexORA($tns, $usuario, $password){// (: Conexion de la Base de datos 
		// Se establece la conexion con la fuente de datos
		global $BD_Obj;
		$this->conexion = oci_connect($usuario, $password, $tns);
		$this->select = "*";
		$this->set = "";
		$this->where = "";
		$this->having = "";
		$this->group = "";
		$this->order = "";
		$this->values = "";
		$this->noRows = "";
		$this->typeConex = "ORA";
		if (!$this->conexion) {
		   $e = oci_error();
		   $this->conerror = $BD_Obj->lang['basedata']['conerror']['false'].$e['message'];
		}else{
		   $this->conerror = $BD_Obj->lang['basedata']['conerror']['true'];
		}
	}
	function conexPG($servidor, $usuario, $password){// (: Conexion de la Base de datos 
		// Se establece la conexion con la fuente de datos
		global $BD_Obj;
				
		$host = explode(":",$servidor);
		
		if(empty($host[1])){
			$host[1] = "5432";
		}
		
		if(empty($this->options)){
			$this->options = "--client_encoding=UTF8";
		}
		
		$conn_string = "host=".$host[0]." port=".$host[1]." dbname=".$this->BD." user=".$usuario." password=".$password." options='".$this->options."'";
		
		$this->conexion = @pg_connect($conn_string);
		$this->select = "*";
		$this->set = "";
		$this->where = "";
		$this->having = "";
		$this->group = "";
		$this->order = "";
		$this->values = "";
		$this->noRows = "";
		$this->typeConex = "PG";
		if (!$this->conexion) {
		   $this->conerror = $BD_Obj->lang['basedata']['conerror']['false'];
		}else{
		   $this->conerror = $BD_Obj->lang['basedata']['conerror']['true'];
		}
	}
	//:) funcion para cargar campo y valor
	function fieldValue($array){
		$this->fieldValue = $array; 
	}
	//:) funcion para cargar campo y valor
	function options($options){
		$this->options = $options; 
	}
	//<< Partes de la Consulta >>
	function server($server){// (: Base a escoger 
		$this->server = $server;
	}
	function BD($base){// (: Base a escoger 
		$this->BD = $base;
	}
	function limit($rows){// (: Para limitar el numero de registros 
		$this->noRows = $rows;
	}
	function autoincrement($value){// (: Para limitar el numero de registros 
		$this->autoincrement = $value;
	}
	function tableBD($table){// (: Tabla a escoger 
		$this->table = $table;
	}
	function joinBD($table=array()){// (: Tabla a escoger con JOIN 
		//************************
		// [table] 	=> array(	"Table to make the join"
		//	as		=> "Name altern of the table"
		// 	type 		=> "Tipo de Join. Left, Right, Inner, Full " 
		// 	on		=> array(	"related tables"
		//		column	=> otherTable.column
		//	),
		// ); 
		//************************
		//if($this->typeConex == "MySQL" || $this->typeConex == "PG"){
			$this->tableJoin = $table;
			
			foreach($this->tableJoin as $key=>$value){
				if(empty($value["type"])){
					$type = "INNER JOIN";
				}else{
					$type = $value["type"]." JOIN";
				}
				if(empty($value["as"])){
					$Table = $key;
					$AS = "";
				}else{
					$Table = $value["as"];
					$AS = ' AS '.$Table.' ';
				}
				$on = array();
				foreach($value["on"] as $key1=>$value1){
					$on[] = $Table.".".$key1." = ".$value1;
				}
				$on = implode(" AND ",$on);

				if(empty($value["table"])){
					$tableJoin[] = $type." ".$key.$AS." ON ".$on;
				}else{
					$tableJoin[] = $type." ".$value["table"].$AS." ON ".$on;
				}
			}
			$this->tableJoin = implode(" ",$tableJoin);
		//}
	}
	function selectBD($select=array()){// (: Selecciones de los campos de la Tabla 
		$this->select = $select;
		
		//:) Cuando existe el array fieldValue
		if(is_array(@$this->fieldValue)){
			foreach($this->fieldValue as $key=>$value){
				//if(!(empty($value)) && $value!="''" && $value!='""'){
					$select[] = $key;
				//}
			}
			$select = implode(",",$select);
			$this->select = $select;
		}
	}
	function setBD($set=""){// (: Set de los campos de la Tabla para actualizar 
		if(!(empty($set))){
			$this->set = "SET ".$set;
		}else{
			$this->set = "";
		}
		
		//:) Cuando existe el array fieldValue
		if(is_array(@$this->fieldValue)){
			$set = array();
			foreach($this->fieldValue as $key=>$value){
				if(!(empty($value)) && $value!="''" && $value!='""'){
					$set[] = $key."=".$value;
				}else{
					$set[] = $key."=null";
				}
			}
			$set = implode(",",$set);
			$this->set = "SET ".$set;
		}
	}
	function whereBD($where){// (: Where de la consulta de la Tabla 
		if($where){
			$this->where = "WHERE ".$where;
		}else{
			$this->where = "";
		}	
	}
	function havingBD($having){// (: Having de la consulta de la Tabla 
		if(!(empty($having))){
			$this->having = "HAVING ".$having;
		}else{
			$this->having = "";
		}	
	}
	function groupBD($group){// (: Group de la consulta de la Tabla 
		if(!(empty($group))){
			$this->group = "GROUP BY ".$group;
		}else{
			$this->group = "";
		}
	}
	function orderBD($order){// (: Order de la consulta de la Tabla 
		if(!(empty($order))){
			$this->order = "ORDER BY ".$order;
		}else{
			$this->order  = "";
		}
	}
	function valuesBD($values=""){// (: valores del Insert 
		if(!(empty($values))){
			$this->values = "VALUES (".$values.")";
		}else{
			$this->values  = "";
		}
		
		//:) Cuando existe el array fieldValue
		if(is_array($this->fieldValue)){
			$values=array();
			foreach($this->fieldValue as $key=>$value){
				if(!(empty($value)) && $value!="''" && $value!='""'){
					$values[] = $value;
				}else{
					$values[] = "null";
				}
			}
			$values = implode(",",$values);
			$this->values = "VALUES (".$values.")";
		}
	}
	function fieldsBD($field){// (: Valores para la creacion y modificacion de campos
		global $BD_Obj;
		if($this->typeConex=="MySQL"){
			if(@$field["field"]!=""){
				if(!(empty($field["type"])) || $this->select == "DROP COLUMN" || $this->select == "DROP INDEX"){
					//:) fieldAnt
					if(!(empty($field["fieldAnt"]))){
						@$fields .= " `".$field["fieldAnt"]."` ";
					}
					//:) field
					@$fields .= " `".$field["field"]."` ";
					//:) type
					if(empty($field["opt_type"])){
						$type= @$field["type"];
					}else{
						$type= str_replace("opt", $field["opt_type"], $field["type"]);
					}
					if(empty($field["extra_type"])){
						$type= @$type;
					}else{
						$type= $type." ".$field["extra_type"];
					}				
					$fields.= str_replace("(opt)","",$type)." ";
					//:) extra
					if(!(empty($field["extra"]))){
						$fields.= $field["extra"]." ";
					}
					//:) null
					if(empty($field["null"])){
						$fields.= "NOT NULL ";
					}
					//:) default
					if(!(empty($field["default"]))){
						if($field["default"]!="USER_DEFINED"){
							$fields.= "DEFAULT ".$field["default"]." ";
						}else{
							$fields.= "DEFAULT '".$field["opt_default"]."' ";
						}
					}
					//:) position
					if(!(empty($field["pst_field"]))){
						$fields.= $field["pst_field"]." ";
					}
					//:) carga las opciones
					$this->fields[]  = $fields;
				}
				//:) key
				if(!(empty($field["key"]))){
					$this->index[$field["key"]][] = " `".$field["field"]."` ";
				}
			}
		}
	}
	function setTime_Zone($time_zone=""){
		global $BD_Obj;
		if($this->typeConex=="MySQL"){
			if($time_zone){
				$this->time_zone = "SET time_zone = '".$time_zone."';";
			}else{
				$now = new DateTime();
				$mins = $now->getOffset() / 60;
				$sgn = ($mins < 0 ? -1 : 1);
				$mins = abs($mins);
				$hrs = floor($mins / 60);
				$mins -= $hrs * 60;
				$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
			
				$this->time_zone = "SET time_zone = '".$offset."';";
			}
		}
	}
	//<< Consulta >>
	function consultaBD($tipo, $save=true){// (: crea la consulta Base Datos
		$this->tipo = $tipo;
		
		if(!(empty($this->tableJoin))){
			$this->table= $this->table." ".$this->tableJoin;
			$this->tableJoin = "";
		}
		
		if($tipo=="DELETE" || $tipo=="SELECT"){
			if($tipo=="DELETE" && $this->select=="*"){
				$this->select = "";
			}
			if($tipo=="SELECT" && $this->select==""){
				$this->select = "*";
			}
			if(!(empty($this->table)) && !(strstr($this->table,"FROM"))){
				$this->table= " FROM ".$this->table;
			}			
			if(!(empty($this->noRows)) && $this->typeConex=="MsSQL" && $tipo=="SELECT"){//:) Generamos el limite en MsSQL
				$this->select = " TOP ".$this->noRows." ".$this->select;
			}
			
			$this->conSQL = $this->tipo." ".$this->select." ".$this->set.@$this->table." ".$this->where." ".$this->group." ".$this->having." ".$this->order;
			
			if(!(empty($this->noRows)) && ($this->typeConex=="MySQL" || $this->typeConex=="PG") && $tipo=="SELECT"){//:) Generamos el limite en MySQL
				$this->conSQL = $this->conSQL." LIMIT ".$this->noRows;
			}
		}else{
			if($tipo=="UPDATE"){
				$this->conSQL = $this->tipo." ".$this->table." ".$this->set." ".$this->where;
			}
			if($tipo=="INSERT INTO"){
				if(!(empty($this->select)) && $this->select!="*"){
					$this->select = "(".$this->select.") ";
				}else{
					$this->select = "";
				}
				$this->conSQL = $this->tipo." ".$this->table." ".$this->select." ".$this->values;
			}
			if($tipo=="DROP VIEW" || $tipo=="DROP TABLE" || $tipo=="EXEC"){
				$this->setDrop();
			}
			if($tipo=="ALTER VIEW" || $tipo=="CREATE VIEW"){
				$this->setView();
			}
			//:) Para Crear Tablas.
			if(is_array( @$this->fields) && $tipo=="CREATE TABLE"){
				$this->createTable();
			}
			//:) Para Alterar Tablas.
			if($tipo=="ALTER TABLE"){
				$this->alterTable();				
			}
		}
		$this->registResult($save);
	}
	function setView(){//:) funciones para obtener el sql de la vista
		if($this->typeConex=="MySQL"){
			$this->conSQL = $this->tipo." `". $this->table."` AS ".$this->select;
		}
	}
	function setDrop(){//:) funciones para obtener el sql de la vista
		if($this->typeConex=="MySQL"){
			$this->conSQL = $this->tipo." `".$this->table."` ";
		}
	}
	//--------------------------------------------------------
	//:) Funciones para las Tablas
	function createTable(){//:) Genera SQL para crear tablas
		if($this->typeConex=="MySQL"){
			//:) campo id
			$content = array();
			foreach($this->fields as $key=>$field){
				$content[] = $field;
			}
			if(is_array(@$this->index)){
				foreach($this->index as $type=>$field){
					foreach($field as $key=>$value){
						$attrib[] = $value;
					}
					$attrib = @implode(",",$attrib);
					$content[]= $type." (".$attrib.") ";					
				}
			}
			@$this->conSQL .= $this->tipo." `".$this->table."` (".@implode(",",$content).") ";
		}
	}
	function alterTable(){//:) Genera SQL para alterar tablas
		if($this->typeConex=="MySQL"){
			if($this->select=="*"){
				$this->select="";
			}
			@$header .= $this->tipo." ".$this->table." ";
			$key=0;
			if(is_array(@$this->fields)){
				foreach($this->fields as $field){
					$content[] = $this->select." ".$field;
				}
			}
			
			if($this->select == "CHANGE" || $this->select == "MODIFY"){
				$select = "ADD";
				$PRIpass = false;
			}else{
				$select = $this->select;
				$PRIpass = true;
			}
			if(is_array(@$this->index)){
				foreach($this->index as $type=>$field){
					if($type!="PRIMARY KEY"){
						foreach($field as $value){
							$content[] = $select." ".$type."(".$value.")";
						}
					}else{
						if($PRIpass){
							foreach($field as $key2=>$value){
								@$attrib[] .= $value;
							}
							$attrib = @implode(",",$attrib);
							$content[] = $select." ".$type." (".$attrib.") ";
						}
					}						
				}
			}else{
				if(@$this->index=="PRIMARY KEY" && $PRIpass){
					$content[] = $select." ".$this->index;
				}
			}
			
			if(!(empty($this->autoincrement))){
				$autoincrement = str_replace('"',"",str_replace("'","",$this->autoincrement));
				$content[] = "AUTO_INCREMENT=".$autoincrement;
			}
			$this->conSQL = $header.@implode(",",$content);
			//echo $this->conSQL;
		}
	}
	function registResult($save){// (: valores del result
		global $BD_Obj;
		//---------------
		$this->time_query = microtime();
		$saved_file = false;
		if(file_exists($BD_Obj->DIRroot.@$BD_Obj->carpeta."conSQL/conSQL_data.php")){
			include($BD_Obj->DIRroot.@$BD_Obj->carpeta."conSQL/conSQL_data.php");
		}
		if(!(empty($conSQL_data[$this->BD.$this->conSQL]))){
			$file = $BD_Obj->DIRroot.$BD_Obj->carpeta."conSQL/".$conSQL_data[$this->BD.$this->conSQL].".php";
			if(is_readable($file)){
				$saved_file = true;
			}
			$save = false;
		}
		if($saved_file){
			$this->result = "";
			include($file);
		}else{//:) ejecutamos consullta para ver la base de datos
			if (!($this->conexion)) {//:) guardamos la consulta para ejecutarla cuando haya conexion
				if(!(empty($this->server)) && $this->server!="127.0.0.1" && strtolower($this->server)!="localhost"){
					$this->saveConSQLnoConn();
				}
			}else{
				$this->ejectConSQL();
				eval("$"."this->result".$this->typeConex."();");
				if(is_array(@$this->result) && $save){
					if(count($this->result)>1){
						/*if(file_exists($BD_Obj->DIRroot.$BD_Obj->carpeta."conSQL/conSQL_data.php")){
							include($BD_Obj->DIRroot.$BD_Obj->carpeta."conSQL/conSQL_data.php");
						}
						Archivo::crearFile($BD_Obj->DIRroot.$BD_Obj->carpeta."conSQL/", "conSQL_data.php", "<? $"."conSQL_data['".Texto::CambioNombArch($this->BD.$this->conSQL)."'] ='".@count($conSQL_data)."_file';?>", "a");
						Archivo::saveArrayInFile($this->result, "this->result", $BD_Obj->DIRroot.$BD_Obj->carpeta."conSQL/", @count($conSQL_data)."_file.php");
						*/
					}
				}
			}
		}
		@$this->time_query = microtime()-@$this->time_query;
	}
	//<< Los Results >>
	function resultODBC(){// (: ejecucion de consulta ODBC
		set_time_limit(0);
		$resultado = @odbc_do($this->conexion, $this->conSQL.";");
		$this->conSQL = "";
		if(!$resultado){
			$this->result = "false";
		}else{
			$num_filas = 1;
			$vacio = "true";
			while(@odbc_fetch_row($resultado)){
				for ($i=0; $i<odbc_num_fields($resultado); $i++) {
					if($num_filas==1){
						$valor[0][$i+1] = odbc_field_name($resultado, ($i+1));
					}
					$valor[$num_filas][$i+1] = $this->fixResult(odbc_result($resultado, ($i+1)));
				}
				$num_filas++;
			}
			if(!(empty($valor))){
				$this->result = $valor;
			}else{
				$this->result = $vacio;
			}
		}
		@odbc_free_result($resultado);
		@odbc_close($resultado);
		set_time_limit(30);
	}
	function resultSyBase(){// (: ejecucion de consulta SyBASE
		set_time_limit(0);
		$resultado = @sybase_query($this->conSQL, $this->conexion);
		$this->conSQL = "";
		if(!$resultado){
			$this->result = "false";
		}else{
			$vacio = "true";
			for ($i=0; $i<@sybase_num_rows($resultado); $i++) {
				for ($j=0; $j<sybase_num_fields($resultado); $j++) {
					$valor[$i+1][$j+1] = $this->fixResult(sybase_result($resultado, $i, $j));
				}
			}
			if(!(empty($valor))){
				$this->result = $valor;
			}else{
				$this->result = $vacio;
			}
		}
		@sybase_free_result($resultado);
		@sybase_close($resultado);
		set_time_limit(30);
	}
	function resultMsSQL(){// (: ejecucion de consulta MsSQL
		set_time_limit(0);
		//ini_set('display_errors', 'On');
		//error_reporting (E_ALL); // For all
		@mssql_select_db($this->BD, $this->conexion);
		
		$resultado = @mssql_query($this->conSQL);
		//:) Con SQLlite
		//$resultado = $this->conexion->query($this->conSQL);
		//$this->conSQL = "";
		if(!$resultado){
			$this->result = false;
			$this->resultError = mssql_get_last_message() ;
			$this->logErrorSQL();
		}else{
			$vacio = "true";
			for ($j=0; $j<@mssql_num_fields($resultado); $j++) {
				$valor[0][$j+1] = mssql_field_name($resultado, $j);
				$this->resultFields[mssql_field_name($resultado, $j)] = $j+1;
				for ($i=0; $i<mssql_num_rows($resultado); $i++) {
					$valor[$i+1][$j+1] = $this->fixResult(mssql_result($resultado, $i, $j));
				}
			}
			if(!(empty($valor))){
				$this->result = $valor;
			}else{
				$this->result = $vacio;
			}
		}
		@mssql_free_result($resultado);
		@mssql_close($resultado);
		set_time_limit(30);
	}
	function resultMySQL(){// (: ejecucion de consulta MySQL
		if($this->conexion){
			set_time_limit(0);
			//mysqli_select_db($this->BD, $this->conexion);
			
			if($this->time_zone){
				$resultado = $this->conexion->query($this->time_zone);
			}
			$resultado = $this->conexion->query($this->conSQL.";");
			
			//echo $this->conSQL;
			//$this->conSQL = "";
			if(@$this->conexion->error){
				$this->result = false;
				$this->resultError = $this->conexion->error;
				$this->logErrorSQL();
			}else{
				$vacio = "true";
				$k=0;
				if(@$resultado->field_count){
					foreach ($resultado->fetch_fields() as $value) {
						$valor[0][$k+1] = $value->name;
						$this->resultFields[$value->name] = $k+1;
						$k++;
					}
					for ($j=0; $j<$resultado->num_rows; $j++) {
						/*$valor[0][$j+1] = $resultado->fetch_fields()[$j]->name;
						$this->resultFields[$resultado->fetch_fields()[$j]->name] = $j+1;*/
						$row = $resultado->fetch_array();
						for ($i=0; $i<$resultado->field_count; $i++) {
							$valor[$j+1][$i+1] = $this->fixResult($row[$i]);
						}
					}
					$resultado->free();
				}
				if(!(empty($valor))){
					$this->result = $valor;
				}else{
					$this->result = $vacio;
				}
			}
			@$this->conexion->close();
			set_time_limit(30);
		}
	}
	function resultORA(){// (: ejecucion de consulta ORACLE 
		if($this->conexion){
			set_time_limit(0);
			
			$resultado = @oci_parse($this->conexion, $this->conSQL);
			$error = @oci_execute($resultado);
			
			if($error == false){
				$this->result = false;
				$error = @oci_error($resultado);
				$this->resultError = $error['message'];
				$this->logErrorSQL();
			}else{
				$vacio = "true";
				for ($j=1; $j<=@oci_num_fields($resultado); $j++) {
					$valor[0][$j] = oci_field_name($resultado, $j);
					$this->resultFields[oci_field_name($resultado, $j)] = $j;
				}
				$j=1;
				while ($row = oci_fetch_array($resultado, OCI_ASSOC+OCI_RETURN_NULLS)) {
				    $i=1;
				    foreach ($row as $item) {
				        $valor[$j][$i] = $this->fixResult(($item !== null ? $item : ""));
				    	$i++;
				    }
				    $j++;
				}				
				
				if(!(empty($valor))){
					$this->result = $valor;
				}else{
					$this->result = $vacio;
				}
			}
			@oci_free_statement($resultado);
			@oci_close($resultado);
			set_time_limit(30);
		}
	}
	function resultPG(){// (: ejecucion de consulta PostGres
		set_time_limit(0);
		//ini_set('display_errors', 'On');
		//error_reporting (E_ALL); // For all
		//@mssql_select_db($this->BD, $this->conexion);
		$resultado = pg_query($this->conSQL);
		//:) Con SQLlite
		//$resultado = $this->conexion->query($this->conSQL);
		//$this->conSQL = "";
		if(!$resultado){
			$this->result = false;
			$this->resultError = pg_last_error() ;
			$this->logErrorSQL();
		}else{
			$vacio = "true";
			for ($j=0; $j<@pg_num_fields($resultado); $j++) {
				$valor[0][$j+1] = pg_field_name($resultado, $j);
				$this->resultFields[pg_field_name($resultado, $j)] = $j+1;
				for ($i=0; $i<pg_num_rows($resultado); $i++) {
					$valor[$i+1][$j+1] = $this->fixResult(pg_result($resultado, $i, $j));
				}
			}
			if(!(empty($valor))){
				$this->result = $valor;
			}else{
				$this->result = $vacio;
			}
		}
		@pg_free_result($resultado);
		@pg_close($resultado);
		set_time_limit(30);
	}
	//:) Ajusta los resultados de la consulta para que no mande errores.
	function fixResult($value){
		$value = str_replace('"',"&quot;",$value);
		$value = str_replace("'","&apos;",$value);
		return $value;
	}
	//:) funciones para obtener los schemas de una base de datos
	function getSchema(){
		eval("$"."this->schema".$this->typeConex."();");
	}
	function schemaMySQL(){
		$this->conSQL = "select * FROM information_schema.schemata WHERE SCHEMA_NAME = '".$this->BD."'";
		$this->resultMySQL();
	}
	function schemaPG(){
		$this->conSQL = "select * from information_schema.schemata WHERE catalog_name = '".$this->BD."';";
		$this->resultPG();
	}
	function schemaMsSQL(){
	}
	function schemaORA(){
	}
	//:) funciones para obtener las tablas de una base de datos
	function getTable($like="",$exec=true){
		eval("$"."this->tables".$this->typeConex."($"."like,$"."exec);");
	}
	function tablesMySQL($like="",$exec=true){
		$this->conSQL = "SHOW TABLE STATUS LIKE '".$like."'";
		if($exec){
			$this->resultMySQL();
		}
	}
	function tablesPG(){
		$this->conSQL = "SELECT * FROM pg_catalog.pg_tables";
		$this->resultPG();
	}
	function tablesMsSQL(){
	}
	function tablesORA(){
	}
	//:) funciones para obtener los campos de las tablas
	function getField($field){
		$this->getFields();
		$i=0;
		if(is_array($this->result)){
			foreach($this->result as $key=>$value){
				if($key==0 || $value[1]==$field){
					$result[$i]=$value;
					$i++;
				}			
			}
			$this->result = $result;
		}
	}
	function getFields(){
		eval("$"."this->fields".$this->typeConex."();");
	}
	function fieldsMySQL(){
		if($this->table != "CREATE TABLE"){
			$this->conSQL = "SHOW COLUMNS FROM ".$this->table;
			$this->resultMySQL();
			$this->resultAllFields = $this->result;
		}
			$this->fieldOptRef = $this->fieldOptRef();
	}	
	function fieldsPG(){
   		$table = explode(".",$this->table);
		$this->conSQL = "SELECT * FROM information_schema.columns WHERE table_schema = '".$table[0]."' AND table_name   = '".$table[1]."'";
		$this->resultPG();
		$this->resultAllFields = $this->result;
		$this->fieldOptRef = $this->fieldOptRef();
	}
	function fieldOptRef(){//:) Referencias de compatibilidad de las opciones de los campos
		$fieldOptRef = array(
			"MySQL" => array(
				"Field"		=>"field",
				"Type"		=>"type",
				"Null"		=>"null",
				"Key"		=>"key",
				"Default"	=>"default",
				"Extra"		=>"extra",
			),
		);
		return $fieldOptRef[$this->typeConex];
	}
	function FIoption($type){//:) Ajusta el array para el nuevo tipo input select
		$array = $this->fieldOption($type);
		
		foreach($array as $key=>$value){
			if(!(stristr($key,"optgroup"))){
				$arrayn[$key]["value"] = $value;
				if(!(empty($optkey))){
					$optList[$optkey]["list"][] = $key;
				}
			}else{
				if(stristr($key,"/")){
					$optkey = "";
				}else{
					$optkey = $key;
					$optList[$key]["label"] = $value;
					$optList[$key]["type"] = "optgroup";
				}
			}
		}
		if(is_array($optList)){
			foreach($optList as $key=>$value){
				$arrayn[$key] = $value;
			}
		}
		
		return $arrayn;
	}
	function fieldOption($type){//:) Obtiene las opciones para crear el campo
		eval("$"."array = $"."this->fieldOption".$this->typeConex."($"."type);");
		return $array;
	}
	function fieldOptionMySQL($type){//:) Obtiene los input de las opciones para crear campo
		global $BD_Obj;
	
		/* Array position */
		$option["pst"] = array(
			" "				=> array("value" => ""),
			"FIRST"				=> array("value" => "FIRST"),
		);
		if(is_array(@$this->resultAllFields)){
			foreach($this->resultAllFields as $key=>$value){
				if($key!=0){
					$option["pst"]["AFTER ".$value[1]]["value"]="AFTER `".$value[1]."` ";
				}
			}
		}
		
		
		//:) Array del tipo de campo
		$option["type"] = array(
			"optgroup1"	=> array(
				"label"		=> "TEXT",
				"type"		=> "optgroup",
				"list"		=> array("CHAR()","VARCHAR()","TINYTEXT","TEXT","BLOB","MEDIUMTEXT","MEDIUMBLOB","LONGTEXT","LONGBLOB"),
			),
			"CHAR()"	=> array("value" => "CHAR(opt)"),
			"VARCHAR()"	=> array("value" => "VARCHAR(opt)"),
			"TINYTEXT"	=> array("value" => "TINYTEXT"),
			"TEXT"		=> array("value" => "TEXT"),	
			"BLOB"		=> array("value" => "BLOB"),
			"MEDIUMTEXT"	=> array("value" => "MEDIUMTEXT"),
			"MEDIUMBLOB"	=> array("value" => "MEDIUMBLOB"),
			"LONGTEXT"	=> array("value" => "LONGTEXT"),
			"LONGBLOB"	=> array("value" => "LONGBLOB"),
			"optgroup2"	=> array(
				"label"		=> "NUMERIC",
				"type"		=> "optgroup",
				"list"		=> array("TINYINT()","SMALLINT()","MEDIUMINT()","INT()","BIGINT()","FLOAT(,)","DOUBLE(,)","DECIMAL(,)"),
			),
			"TINYINT()"	=> array("value" => "TINYINT(opt)"),
			"SMALLINT()"	=> array("value" => "SMALLINT(opt)"),
			"MEDIUMINT()"	=> array("value" => "MEDIUMINT(opt)"),
			"INT()"		=> array("value" => "INT(opt)"),
			"BIGINT()"	=> array("value" => "BIGINT(opt)"),
			"FLOAT(,)"	=> array("value" => "FLOAT(opt)"),
			"DOUBLE(,)"	=> array("value" => "DOUBLE(opt)"),
			"DECIMAL(,)"	=> array("value" => "DECIMAL(opt)"),
			"optgroup3"	=> array(
				"label"		=> "DATE",
				"type"		=> "optgroup",
				"list"		=> array("DATE","DATETIME","TIMESTAMP","TIME"),
			),
			"DATE"		=> array("value" => "DATE"),
			"DATETIME"	=> array("value" => "DATETIME"),
			"TIMESTAMP"	=> array("value" => "TIMESTAMP"),
			"TIME"		=> array("value" => "TIME"),
			"optgroup4"	=> array(
				"label" 	=> "MISC",
				"type"		=> "optgroup",
				"list"		=> array("ENUM()","SET()"),
			),
			"ENUM()"	=> array("value" => "ENUM(opt)"),
			"SET()"		=> array("value" => "SET(opt)"),
		);
		
		//:) Array del extra para tipo de campo
		$option["extra_type"] = array(
			" "				=> array("value" => ""),
			"BINARY"			=> array("value" => "BINARY"),
			"UNSIGNED"			=> array("value" => "UNSIGNED"),
			"UNSIGNED ZEROFILL"		=> array("value" => "UNSIGNED ZEROFILL"),
		);
		
		
		//:) Array para el tipo de llave
		$option["key"] = array(
			" "		=> array("value" =>""),
			"PRIMARY"	=> array("value" =>"PRIMARY KEY"),
			"UNIQUE"	=> array("value" =>"UNIQUE"),
			"INDEX"		=> array("value" =>"INDEX"),
			"FULLTEXT"	=> array("value" =>"FULLTEXT"),
		);
		
		//:) Array para el default
		$option["default"] = array(
			"NONE"			=> array("value" => ""),
			$BD_Obj->lang["basedata"]["USER_DEFINED"]=> array("value" => "USER_DEFINED"),
			"NULL"			=> array("value" => "NULL"),
			"CURRENT_TIMESTAMP"	=> array("value" => "CURRENT_TIMESTAMP"),
		);
		
		//:) Array para el extra
		$option["extra"] = array(
			" "				=> array("value" => ""),
			"ON UPDATE CURRENT_TIMESTAMP"	=> array("value" => "ON UPDATE CURRENT_TIMESTAMP"),
			"AUTO_INCREMENT"		=> array("value" => "AUTO_INCREMENT"),
		);
		
		//:) Obtenemos el tipo del campo
		$array = $option[$type];
		return $array;
	}
	function fieldsOptions($field, $name, $cssform="formsTable"){//:) Obtiene los inputs para las opciones
		global $BD_Obj;
		
		$fields = "";
		
		//:) Opciones para MySQL
		if($field=="field"){
			$fieldsR = "";
			$fields = new BDforma;
			$i_array = "";
			$i_array = array(
				"type"		=> "text",
				"name"		=> $name,
				"value"		=> @$BD_Obj->SysVars[$name],
			);
			$fieldsR .= "<div>";
			$fieldsR .= $fields->setInput($i_array);
			$fieldsR .= "</div>";
			if($BD_Obj->SysVars["tbname"]!="CREATE TABLE"){
				$i_array = "";
				$i_array = array(
					"type"		=> "select",
					"name"		=> "pst_".$name,
					"value"		=> @$BD_Obj->SysVars["pst_".$name],
					"list"		=> $this->fieldOptionMySQL("pst"),
				);
				$fieldsR .= "<div>";
				$fieldsR .= $fields->setInput($i_array);
				$fieldsR .= "</div>";
			}
			/*$fields = new BDobject;
			$fields ->contenido("<input class='".$cssform."Input' name='".$name."' value='".@$BD_Obj->SysVars[$name]."'/>");
			if($BD_Obj->SysVars["tbname"]!="CREATE TABLE"){
				$fields ->selectInput($this->fieldOptionMySQL("pst"), "pst_".$name, '', false, $cssform."Select");
			}
			$fields ->menuVertical(" border='0' cellspacing='0' cellpadding='0' ");
			$fields = $fields ->writeTEXT();*/
			$fields = $fieldsR;
		}
		if($field=="type"){
			//:) Ajustamos la variable para que asigne las opciones
			@$BD_Obj->SysVars[$name] = explode(" ",@$BD_Obj->SysVars[$name],2);
			if(!(empty($BD_Obj->SysVars[$name][1])) && empty($BD_Obj->SysVars["extra_".$name])){
				$BD_Obj->SysVars["extra_".$name]= strtoupper($BD_Obj->SysVars[$name][1]);
			}
			if(strstr($BD_Obj->SysVars[$name][0],"(")){
				if(strstr($BD_Obj->SysVars[$name][0],"opt")){
					$BD_Obj->SysVars[$name] = $BD_Obj->SysVars[$name][0];
				}else{
					$BD_Obj->SysVars[$name][0] = explode("(",$BD_Obj->SysVars[$name][0],2);
					if(empty($BD_Obj->SysVars["opt_".$name])){
						$BD_Obj->SysVars["opt_".$name]=str_replace(")","",$BD_Obj->SysVars[$name][0][1]);
					}
					$BD_Obj->SysVars[$name] = strtoupper($BD_Obj->SysVars[$name][0][0])."(opt)";
				}				
			}else{
				$BD_Obj->SysVars[$name] = strtoupper($BD_Obj->SysVars[$name][0]);
			}
			
			//:) Creamos los inputs
			/*$fields = new BDobject;
			$fields ->selectInput($this->fieldOptionMySQL("type"), $name, '', false, $cssform."Select");
			$fields ->contenido('<input class="'.$cssform.'Input" name="opt_'.$name.'" value="'.@$BD_Obj->SysVars["opt_".$name].'"/>');
			
			$fields ->selectInput($this->fieldOptionMySQL("extra_type"), "extra_".$name, '', false, $cssform."Select");
			$fields ->menuVertical(" border='0' cellspacing='0' cellpadding='0' ");
			$fields = $fields ->writeTEXT();*/
			$fieldsR = "";
			$fields = new BDforma;
			$i_array = "";
			$i_array = array(
				"type"		=> "select",
				"name"		=> $name,
				"value"		=> @$BD_Obj->SysVars[$name],
				"list"		=> $this->fieldOptionMySQL("type"),
			);
			$fieldsR .= "<div>";
			$fieldsR .= $fields->setInput($i_array);
			$fieldsR .= "</div>";
			
			$i_array = "";
			$i_array = array(
				"type"		=> "text",
				"name"		=> "opt_".$name,
				"value"		=> @$BD_Obj->SysVars["opt_".$name],
			);
			$fieldsR .= "<div>";
			$fieldsR .= $fields->setInput($i_array);
			$fieldsR .= "</div>";
			
			$i_array = "";
			$i_array = array(
				"type"		=> "select",
				"name"		=> "extra_".$name,
				"value"		=> @$BD_Obj->SysVars["extra_".$name],
				"list"		=> $this->fieldOptionMySQL("extra_type"),
			);
			$fieldsR .= "<div>";
			$fieldsR .= $fields->setInput($i_array);
			$fieldsR .= "</div>";
			
			$fields = $fieldsR;
			
		}
		if($field=="null"){
		
			$fieldsR = "";
			$fields = new BDforma;
			$i_array = "";
			$i_array = array(
				"type"		=> "checkbox",
				"name"		=> $name,
				"value"		=> "YES",
			);
		
			if(!(empty($BD_Obj->SysVars[$name]))){
				if($BD_Obj->SysVars[$name]!="NO"){
					$i_array["checked"]='true';
				}
			}
			//$fields ="<input type='checkbox' name='".$name."' value='YES' ".@$checked."/>";*/
			
			$fieldsR .= "<div>";
			$fieldsR .= $fields->setInput($i_array);
			$fieldsR .= "</div>";
			
			$fields = $fieldsR;
		}
		if($field=="key"){
			//:) Normalizamos la variable recibida del registro
			$var_regist = array(
				"PRI"	=> "PRIMARY KEY",
				"UNI"	=> "UNIQUE",
				"MUL"	=> "INDEX",
			);
			if(array_key_exists(@$BD_Obj->SysVars[$name], $var_regist)){
				$BD_Obj->SysVars[$name]=$var_regist[$BD_Obj->SysVars[$name]];
			}
			
			$fieldsR = "";
			$fields = new BDforma;
			$i_array = "";
			$i_array = array(
				"type"		=> "select",
				"name"		=> $name,
				"value"		=> @$BD_Obj->SysVars[$name],
				"list"		=> $this->fieldOptionMySQL("key"),
			);
			$fieldsR .= "<div>";
			$fieldsR .= $fields->setInput($i_array);
			$fieldsR .= "</div>";
			
			$fields = $fieldsR;
			
			/*$fields = new BDobject;
			$fields ->selectInput($this->fieldOptionMySQL("key"), $name, '', false, $cssform."Select");
			$fields = $fields ->writeTEXT();*/
		}
		if($field=="default"){
			
			$option = array(
				"NONE"			=> "",
				$BD_Obj->lang["basedata"]["USER_DEFINED"]=> "USER_DEFINED",
				"NULL"			=> "NULL",
				"CURRENT_TIMESTAMP"	=> "CURRENT_TIMESTAMP",
			);			
			
			//:) Normalizamos la variable recibida del registro
			@$BD_Obj->SysVars[$name]=strtoupper(@$BD_Obj->SysVars[$name]);
			if(!(in_array($BD_Obj->SysVars[$name],$option))){
				$BD_Obj->SysVars["opt_".$name]=$BD_Obj->SysVars[$name];
				$BD_Obj->SysVars[$name]="USER_DEFINED";
			}
			
			//:) Generamos los inputs
			/*$fields = new BDobject;
			$fields ->selectInput($this->fieldOptionMySQL("default"), $name, '', false, $cssform."Select");
			$fields ->contenido("<input class='".$cssform."Input' name='opt_".$name."' value='".@$BD_Obj->SysVars["opt_".$name]."'/>");
			$fields ->menuVertical(" border='0' cellspacing='0' cellpadding='0' ");
			$fields = $fields ->writeTEXT();*/
			
			$fieldsR = "";
			$fields = new BDforma;
			$i_array = "";
			$i_array = array(
				"type"		=> "select",
				"name"		=> $name,
				"value"		=> @$BD_Obj->SysVars[$name],
				"list"		=> $this->fieldOptionMySQL("default"),
			);
			$fieldsR .= "<div>";
			$fieldsR .= $fields->setInput($i_array);
			$fieldsR .= "</div>";
			
			$i_array = "";
			$i_array = array(
				"type"		=> "text",
				"name"		=> "opt_".$name,
				"value"		=> @$BD_Obj->SysVars["opt_".$name],
			);
			$fieldsR .= "<div>";
			$fieldsR .= $fields->setInput($i_array);
			$fieldsR .= "</div>";
			
			$fields = $fieldsR;
		}
		if($field=="extra"){
			//:) Normalizamos la variable recibida del registro
			@$BD_Obj->SysVars[$name]=strtoupper(@$BD_Obj->SysVars[$name]);
			
			//:) creamos las opciones
			/*$fields = new BDobject;
			$fields ->selectInput($this->fieldOptionMySQL("extra"), $name, '', false, $cssform."Select");
			$fields = $fields ->writeTEXT();*/
			
			$fieldsR = "";
			$fields = new BDforma;
			$i_array = "";
			$i_array = array(
				"type"		=> "select",
				"name"		=> $name,
				"value"		=> @$BD_Obj->SysVars[$name],
				"list"		=> $this->fieldOptionMySQL("extra"),
			);
			$fieldsR .= "<div>";
			$fieldsR .= $fields->setInput($i_array);
			$fieldsR .= "</div>";
			
			$fields = $fieldsR;
		}
		
		return $fields;
	}
	//:) funciones para obtener el Primary KEY
	function getPriKey(){
		eval("$"."this->priKey".$this->typeConex."();");
	}
	function priKeyMySQL(){
		$this->conSQL = "SHOW INDEX FROM ".$this->table." WHERE Key_name = 'PRIMARY' ";
		$this->resultMySQL();
	}
	//--------------------------------------------------------
	//:) Funciones para las Vistas
	function getView(){//:) funciones para obtener el sql de la vista
		eval("$"."this->view".$this->typeConex."();");
	}
	function viewMySQL(){
		$this->conSQL = "SHOW CREATE VIEW `". $this->table."`";
		$this->resultMySQL();
		$inicioSQL = "VIEW `". $this->table."` AS ";
		$inicioSQLln = strlen($inicioSQL);
		$sobraSQLln = strpos($this->result[1][2], $inicioSQL);
		$inicioSQLln = $inicioSQLln+$sobraSQLln;
		$totalSQLln = strlen($this->result[1][2]);
		$extraeSQLln =$inicioSQLln-$totalSQLln;
		$this->result[1][4]=substr($this->result[1][2],$extraeSQLln);
	}
	function crteTBfmVW($exec = true){//:) funciones para crear una tabla de una vista
		if($exec){
			$exec='true';
		}else{
			$exec='false';
		}
		eval("$"."conSQL"."=$"."this->crteTBfmVW".$this->typeConex."(".$exec.");");
		return $conSQL;
	}
	function crteTBfmVWMySQL($exec = true){
		$conSQL = "CREATE TABLE `tb_new` SELECT * FROM `".$this->table."`";
		$this->conSQL = $conSQL;
		if($exec){
			$this->resultMySQL();
		}
		return $conSQL;
	}
	function crteTBfmDF($exec = true){//:) funciones para crear una tabla de una vista
		if($exec){
			$exec='true';
		}else{
			$exec='false';
		}
		eval("$"."conSQL"."=$"."this->crteTBfmDF".$this->typeConex."(".$exec.");");
		return $conSQL;
	}
	function crteTBfmDFMySQL($exec = true){
		$conSQL = "SHOW CREATE TABLE `".$this->table."`";
		$this->conSQL = $conSQL;
		if($exec){
			$this->resultMySQL();
		}
		return $conSQL;
	}
	//--------------------------------------------
	//:) Funciones para descargar csv
	function crteCSV($delimiter = ',', $enclosure = '"', $escape_char = '\/',$ext = '.csv'){//:) Funcion para crear archivo
		global $BD_Obj;
		$CSVfile = str_replace(".","",$BD_Obj->UserIP)."_".date("Ymd_His")."_".rand(10,100).$ext;
		$CSVcarpeta = $BD_Obj->DIRroot."eDBase/TBDcontent/";
		$fp = fopen($CSVcarpeta.$CSVfile, 'w');
		foreach ($this->result as $campos) {
		    fputcsv($fp, $campos, $delimiter, $enclosure, str_replace("/","",$escape_char));
		}
		fclose($fp);
		
		//:) Descargamos el archivo
		BDjava::execJavaScript("window.open('/PHPpages/administrador/descarga.php?name_file=".$CSVfile."&carpeta=".$CSVcarpeta."','_blank');");
	}
	//------------------------------------------------
	//:) Funciones para descargar las consultas de otro servidor
	function saveConSQL(){//:) guarda la consulta para descargar posteriormente
		global $BD_Obj;
		if(empty($this->savedConSQL) && !(empty($this->fileconnect)) && empty($this->resultError)){
			$dataFileName = microtime();
			$dataFileName = explode(" ",$dataFileName);
			$dataFileName = $dataFileName[1].$dataFileName[0].".sql";
			BDarchivo::saveArrayInFile($this->conSQL, "conSQL", $BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/", $dataFileName);
			
			$this->savedConSQL = "true";
		}
	}
	function saveConSQLnoConn(){//:) guarda la consulta para descargar posteriormente cuando no hay conexion con el server
		global $BD_Obj;
		if(empty($this->savedConSQLnoConn) && !(empty($this->fileconnect))){
			$dataFileName = microtime();
			$dataFileName = explode(" ",$dataFileName);
			$dataFileName = $dataFileName[1].$dataFileName[0].".sql";
			BDarchivo::saveArrayInFile($this->conSQL, "conSQL", $BD_Obj->DIRroot."eDBase/conSQLnoConn/".$this->fileconnect."/", $dataFileName);
			$this->savedConSQLnoConn = "true";
		}
	}
	function ejectConSQL(){//:) ejecuta SQL
		global $BD_Obj;
		
		$elemeno = BDarchivo::leerCarpeta($BD_Obj->DIRroot."eDBase/conSQLnoConn/".$this->fileconnect."/", 'file', 'sql');
		
		$antConSQL = $this->conSQL;
		
		if(is_array($elemeno)){
			foreach($elemeno as $value){
				$file = $BD_Obj->DIRroot."eDBase/conSQLnoConn/".$this->fileconnect."/".$value;
				
				include($file);
				$this->conSQL = $conSQL;
				eval("$"."this->result".$this->typeConex."();");
				unlink($file);
			}
		}
		
		$this->conSQL = $antConSQL;
	}
	function jsonConSQL($pass="", $time="0"){
		global $BD_Obj;
		
		$elemeno = BDarchivo::leerCarpeta($BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/", 'file', 'sql');
		
		if(!(file_exists($BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/opt/pass.php"))){
			$Pass = BDheader::generaPass();
			BDarchivo::saveArrayInFile($Pass, "Pass", $BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/opt/", "pass.php");
		}else{
			include($BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/opt/pass.php");
		}

		if(file_exists($BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/opt/pass2.php")){
			include($BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/opt/pass2.php");
		}else{
			$Pass2 = "AbCd";
		}
		
		
		//:) Verifico los passwords
		$access = false;
		$changePass2 = true;
		if($pass==$Pass){
			$access = true;
		}
		if($pass==$Pass2){
			$access = true;
			$changePass2 = false;
		}
		
		if($access){
			if(is_array($elemeno)){
				$i=0;
				foreach($elemeno as $value){
					if($i<=20){
						$file = $BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/".$value;
						$Time = str_replace(".sql","",$value);
						if($time>=$Time){
							unlink($file);
						}else{
							include($file);
							$arrayConSQL[] = array(
								"conSQL" 	=> str_replace('"',"'",$conSQL),
								"time"		=> $Time,
							);
							$i++;
						}
					}
				}
			}
			

			if($changePass2){
				BDarchivo::saveArrayInFile($Pass, "Pass2", $BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/opt/", "pass2.php");
			}			
			
			$newPass = BDheader::generaPass();
			BDarchivo::saveArrayInFile($newPass, "Pass", $BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/opt/", "pass.php");

			$arrayConSQL["pass"] = $newPass;
			
			echo json_encode($arrayConSQL);
		}
	}
	function jsonDLconSQL($url, $pass, $server="Server1", $delaySeg = 30){
		global $BD_Obj;
		
		if(file_exists($BD_Obj->DIRroot."eDBase/DLconSQL/".$this->fileconnect."/".$server."/opt/delay.php")){
			include($BD_Obj->DIRroot."eDBase/DLconSQL/".$this->fileconnect."/".$server."/opt/delay.php");
		}else{
			$delay = 0;
		}
			
		$timeOpen = time();
		
		if($delay<=$timeOpen){
		
			$delay = $timeOpen+$delaySeg;
			BDarchivo::saveArrayInFile($delay, "delay", $BD_Obj->DIRroot."eDBase/DLconSQL/".$this->fileconnect."/".$server."/opt/", "delay.php");
		
			if(file_exists($BD_Obj->DIRroot."eDBase/DLconSQL/".$this->fileconnect."/".$server."/opt/pass.php")){
				include($BD_Obj->DIRroot."eDBase/DLconSQL/".$this->fileconnect."/".$server."/opt/pass.php");
			}
			
			if(file_exists($BD_Obj->DIRroot."eDBase/DLconSQL/".$this->fileconnect."/".$server."/opt/time.php")){
				include($BD_Obj->DIRroot."eDBase/DLconSQL/".$this->fileconnect."/".$server."/opt/time.php");
			}else{
				$time = 0;
			}
		
			$arrayConSQL = BDarchivo::file_get_contents_curl($url."?pass=".$pass."&time=".$time);
			$data = json_decode($arrayConSQL,true);	

			if(is_array($data)){
				$newPass = $data["pass"];
				BDarchivo::saveArrayInFile($newPass, "pass", $BD_Obj->DIRroot."eDBase/DLconSQL/".$this->fileconnect."/".$server."/opt/", "pass.php");
				
				foreach($data as $key=>$value){
					if($key!='pass' || empty($key)){
						$this->conSQL = $value["conSQL"];
						eval("$"."this->result".$this->typeConex."();");
						$Time = $value["time"];
					}
				}
				if(!(empty($Time))){
					BDarchivo::saveArrayInFile($Time, "time", $BD_Obj->DIRroot."eDBase/DLconSQL/".$this->fileconnect."/".$server."/opt/", "time.php");
				}
			}
		}
			
	}
	//-----------------------------------------
	//:) Log de errores BD
	function logErrorSQL(){
		global $BD_Obj, $pag_obj, $_SERVER, $SysVars;
		if(!(empty($this->tipo)) && $this->tipo != "SELECT" && !(empty($this->resultError)) && !(empty($this->logOn))){
			$error["conSQL"] = $this->conSQL;
			$error["resultError"] = $this->resultError;
			if(@$pag_obj->SysVars["app_name"]){
				$error["app_name"] = $pag_obj->SysVars["app_name"];
			}
			if(@$SysVars->SysVars["POST"]["carpeta"]){
				$error["carpeta"] = $SysVars->SysVars["POST"]["carpeta"];
			}
			$error["webpage"] = str_replace($BD_Obj->URLroot,"",$BD_Obj->SysVars["URLactual"]);
			
			$dir = date('Y_m');
			
			$dataFileName = microtime();
			$dataFileName = explode(" ",$dataFileName);
			$dataFileName = $dataFileName[1].$dataFileName[0].".php";
			
			$carpeta = $BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/log/".$dir."/";
			BDarchivo::saveArrayInFile($error, "error", $carpeta, $dataFileName);
			
			if(file_exists($carpeta."num.dat")){
				include($carpeta."num.dat");
				if($number>=$this->NoFiles){
					$elemento = BDarchivo::leerCarpeta($carpeta, 'file','php');
					$number=count($elemento);
					$i = 0;
					while($number>$this->NoFiles){
						@unlink($carpeta.$elemento[$i]);
						$i++;
						$number--;
					}
					BDarchivo::saveArrayInFile($number, "number", $carpeta, "num.dat");
				}else{
					$number++;
					BDarchivo::saveArrayInFile($number, "number", $carpeta, "num.dat");
				}
			}else{
				$elemento = BDarchivo::leerCarpeta($carpeta, 'file','php');
				$number=count($elemento);
				BDarchivo::saveArrayInFile($number, "number", $carpeta, "num.dat");
			}
			
		}
		//:) Borramos las carpetas viejas 
		if(file_exists($BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/log/")){
			$carpeta = $BD_Obj->DIRroot."eDBase/conSQL/".$this->fileconnect."/log/";
			$elemento = BDarchivo::leerCarpeta($carpeta, 'dir');
			
			if($this->NoMonth == 1){
				$dirAct = date('Ym', strtotime('-'.$this->NoMonth.' month'));
			}else{
				$dirAct = date('Ym', strtotime('-'.$this->NoMonth.' months'));
			}
			if(is_array($elemento)){
				foreach($elemento as $value){
					$dir = str_replace("_","",$value);
					if($dir < $dirAct){
						BDarchivo::dellCarpetaCont($carpeta.$value."/");
					}
				}
			}
		}
	}
	function traceSQL(){//:) Genera el trace de la session
		global $BD_Obj,$pag_obj,$SysVars;
		
		if($SysVars->SysVars["SESSION"][$SysVars->SysVars["SESS"]["name_sess"]]["trace"] && $this->tipo && $this->tipo != "SELECT" && empty($this->resultError)){
			
			$trace = $SysVars->SysVars["SESSION"][$SysVars->SysVars["SESS"]["name_sess"]]["trace"];
			$SysVars->SysVars["SESSION"][$SysVars->SysVars["SESS"]["name_sess"]]["trace"] = "";
			
			
			$fechaActual = date("Y-m-d H:i:s");
								
			$conex = new BData();
			$conex->conexFile($this->fileconnect.".php");
			$conex->tableBD("Trace");
			$arrayFV = array(
				"Time"			=> "'".$SysVars->SysVars["SESSION"]["time"]."'",
				"UserId"		=> "'".$SysVars->SysVars["SESSION"][$SysVars->SysVars["SESS"]["name_sess"]]["id"]."'",
				"SesionId"		=> "'".$SysVars->SysVars["SESS"]["name_sess"]."'",
				"IP"			=> "'".$SysVars->UserIP."'",
				"OS"			=> "'".$SysVars->UserDetect["os"]."'",
				"Browser"		=> "'".$SysVars->UserDetect["browser"]."'",
				"Tabla"			=> "'".$this->table."'",
				"TypeSQL"		=> "'".$this->tipo."'",
				"SQLtext"		=> "'".(str_replace("'","",$this->conSQL))."'",
				"Carpeta"		=> "'".$pag_obj->SysVars["name_file"]."'",
				"WebPage"		=> "'".(str_replace($BD_Obj->URLroot,"",$BD_Obj->SysVars["URLactual"]))."'",
				"APPname"		=> "'".$pag_obj->SysVars["app_name"]."'",
				"Creado"		=> "'".$fechaActual."'",
			);
			$conex->fieldValue($arrayFV);	
			$conex->selectBD();
			$conex->valuesBD();
			$conex->consultaBD("INSERT INTO");
			
			$fechaBorrado = date("Y-m-d");
			$fechaBorrado = date("Y-m-d",strtotime($fechaBorrado."- ".$trace." days"));
			
			$conex = new BData();
			$conex->conexFile($this->fileconnect.".php");
			$conex->tableBD("Trace");
			$conex->whereBD("Creado < '".$fechaBorrado." 00:00:00' AND SesionId='".$SysVars->SysVars["SESS"]["name_sess"]."'");
			$conex->consultaBD("DELETE");
			
			$SysVars->SysVars["SESSION"][$SysVars->SysVars["SESS"]["name_sess"]]["trace"] = $trace;
		}
	}
	function appendConSQL($conex){
		//: Unimos los datos abajo
		if(empty($conex->resultError)){
			$j = count($this->result);
			if(is_array($conex->result)){
				foreach($conex->result as $key=>$value){
					if($key){
						$this->result[$j] = $value;
						$j++;
					}
				}
			}
		}
	}
	function addColumn($name, $defaultValue = ""){
		$count_cols = count($this->result[0])+1;
		$this->resultFields[$name] = $count_cols;
		$this->result[0][$count_cols] = $name;
		
		if(is_array($this->result)){
			foreach($this->result as $key=>$value){
				if($key){
					$this->result[$key][$count_cols] = $defaultValue;
				}
			}
		}
	}
	function whereFROMfields(){//:) obtenemos el where por los ampos de la tabla
		global $BD_Obj,$pag_obj,$SysVars;
		
		$conex = new BData;
		$conex ->conexFile($this->fileconnect.".php");
		$conex ->tableBD($this->table);
		$conex ->getFields();
		
		$where = array();
		foreach($conex->resultAllFields as $key=>$value){
			if($key){
				if(@$BD_Obj->SysVars[$value[1]]){
					$where[] = $value[1]." LIKE '".$BD_Obj->SysVars[$value[1]]."'";
				}
			}
		}
		
		$this->whereBD(implode(" AND ",$where));
	}
	function BKPfile($name = ""){
		global $BD_Obj,$pag_obj,$SysVars;
		
		ini_set('memory_limit', '512M');
		set_time_limit(0);
				
		if($this->typeConex == "MySQL"){
			
			$newConSQL = new BData;
			$newConSQL->conexFile($this->fileconnect.".php");
			$newConSQL->conSQL = "SHOW TABLES";
			$newConSQL->resultMySQL();
						
			//:) Recorrer todas las tablas y generar el respaldo
			$content = '';
			$tables = $newConSQL->result;
			foreach($tables as $key=>$table) {
				if($key){
				    //:) Obtener el esquema de la tabla
				    $newConSQL = new BData;
				    $newConSQL->conexFile($this->fileconnect.".php");
				    $newConSQL->conSQL = 'SHOW CREATE TABLE '.$table[1];
				    $newConSQL->resultMySQL();
				    
				    $content .= "\n\n".$newConSQL->result[1][2].";\n\n";
				
				    //:) Obtener los datos de la tabla
				    $data = new BData;
				    $data->conexFile($this->fileconnect.".php");
				    $data->conSQL = 'SELECT * FROM '.$table[1];
				    $data->resultMySQL();
				    
				    /*echo $data->resultError;
				    echo $data->conSQL;*/
				    			    
				    foreach($data->result as $key1=>$rows) {
					if($key1){
						if(is_array($rows)){
							$content .= 'INSERT INTO '.$table[1].' VALUES(';
							foreach($rows as $i=>$cols){
								$value = addslashes($cols);
							        $value = str_replace("\n","\\n",$value);
							        if (isset($value)) {
							            $content .= '"'.$value.'"' ;
							        } else {
							            $content .= '""';
							        }
							        if ($i<$num_fields-1) {
							            $content .= ',';
							        }
						        }
						        $content .= ");\n";
					        }
					}
				    }
				}
			}
			BDarchivo::crearFile($BD_Obj->DIRroot."eDBase/BKPtmp/", $name.".txt", $content, "w");
			
		}
	}
	/*   Responsabilidades
	*/	
	//	-- Gestionar las actividades con la base de datos.
	//	
}
?>
