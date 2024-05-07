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
