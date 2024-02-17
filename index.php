<?php
require_once "vendor/autoload.php";

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . "/php_log.log");
error_reporting(E_ALL);

spl_autoload_register(function ($name) {
    $path = explode("\\", $name);
    $class=array_pop($path);
    $path = implode("/", $path);
    $file = $path . "/" . $class . ".php";
    if (file_exists($file)) {
        require_once $file;
    }
});

use classes\core\Application;

if (!isset($_REQUEST)) {
    exit;
}

$data = json_decode(file_get_contents("php://input"));
Application::run($data);

//$stage = \models\DialogueStages::getByUserId(715883939);

//$user = \models\User::add(1234);
//$test = \models\DialogueStages::getByUserId(715883939);
//var_dump($test);
