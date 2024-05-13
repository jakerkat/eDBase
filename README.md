# eDBase
Libreria para conectar bases de datos a proyecto PHP, puedes establecer conexiones PHP de manera sencilla y ejecutar consultas de forma más eficiente y organizada, simplificando así el proceso de manejo de datos en tus proyectos.

Bienvenido al uso de esta libreria con la que podras conectarte rápidamente a una base de datos y organizar mejor las consultas para tus proyectos.

# Instalación de libreria
Para instalar la librería eDBase y usarla en tu proyecto PHP, hay que colocar la carpeta eDBase en la raiz del directorio web.

Hay que incluir el archivo header en la pagina php donde se va a usar.

<pre>
// incluimos la clase de las Bases de Datos.
$DIRroot = str_replace("\\","/",realpath(dirname(__FILE__)."/../../..").'/');
require_once($DIRroot."/eDBase/plugins/class/header.class.php");
</pre>

# Crear conexión a una base de datos
 
David Urrutia edited this page 2 weeks ago · 3 revisions
Para crear una conexión a una base de datos se requiere crear un archivo php en la carpeta bdata dentro de la carpeta eDBase. El archivo se puede guardar con cualquier nombre, sólo que haga referencia a la base de datos que se va a conectar.

Dentro del archivo se deben crear las siguientes variables:

<pre>
<?
$BD_Obj->SysVars=array();
$BD_Obj->SysVars["typeConnect"]="MySQL"; // Tipo de Base de Datos. ODBC,SyBase,MsSQL,MySQL,ORA(Oracle),PG(PostGres).
$BD_Obj->SysVars["bdata"]="NombreBD"; // Nombre de la Base de Datos.
$BD_Obj->SysVars["server"]="IPoRED"; // IP o nombre de red del Servidor.
$BD_Obj->SysVars["user"]="usuario_bd"; // Usuario para autentificar
$BD_Obj->SysVars["password"]="********"; // Password
$BD_Obj->SysVars["log"]="1"; // Para activar o desactivar logs, se guardaran en carpeta _conSQL_ dentro de _eDBase_. _0(desactivado), 1(activado)_
$BD_Obj->SysVars["NoMonth"]="4"; // Numero de meses a guardar.
$BD_Obj->SysVars["NoFiles"]="100"; // Número máximo de archivos a guardar.
$BD_Obj->SysVars["encrypt"]="0"; // Si se encrypta la contraseña o no, más adelante se explica como activarla
?>
</pre>

# Crear primera consulta
 
David Urrutia edited this page 2 weeks ago · 2 revisions
Para crear una consulta simple, sería con el siguiente código.
<pre>
$conex = new BData;
$conex ->conexFile("NombreConexion.php"); // Nombre del archivo de conexión
$conex ->tableBD("NombreTabla"); // Nombre de la tabla que vas a consultar
$conex ->consultaBD("SELECT"); // Tipo de consulta

echo $conex ->conSQL; // Te muestra el SQL de la consulta que estás haciendo.
echo $conex ->resultError; // Te muestra los errores de la consulta ejecutada, sí no hay aparece vacío.

$conex ->result; // array que contiene el resultado de la consulta.

Donde:

echo $conex ->result[0][0]; // sería el título de la primer columna.
</pre>
