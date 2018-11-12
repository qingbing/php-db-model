<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2018-05-19
 * Version      :   1.0
 */
require("vendor/autoload.php");

define('ENV', "dev");
// 定义配置存放目录
defined("CONFIG_PATH") or define("CONFIG_PATH", realpath(".") . "/conf");
// 定义配置缓存的存放目录
defined("RUNTIME_PATH") or define("RUNTIME_PATH", realpath(".") . "/runtime");
// 定义配置缓存的存放目录
defined("CACHE_PATH") or define("CACHE_PATH", realpath(".") . "/runtime");

try {
    if ($_GET['c']) {
        $className = $_GET['c'];
        $class = "\Test\\{$className}";
    } else {
        $class = "\TestCore\\Helper";
    }
    /* @var $class \TestCore\Tester */
    $class::getInstance()->run();
} catch (Exception $e) {
    var_dump($e);
}