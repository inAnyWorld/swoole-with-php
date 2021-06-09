<?php
/**
 * Created by PhpStorm.
 * User: code301
 * Date: 2020/7/18
 * Time: 19:31
 */
namespace Code301\Core;

class Config
{
    private static $configMap=[];
    private static $instance;
    private function __construct()
    {
    }

    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance=new self();
        }
        return self::$instance;
    }

    public function load()
    {
        $files = glob(CONFIG_PATH."/*.php");
        if(!empty($files)) {
            foreach ($files as $dir=>$fileName) {
                self::$configMap += include $fileName;
            }
        }
    }

    public function get($key)
    {
        if(isset(self::$configMap[$key])) {
            return self::$configMap[$key];
        }
        return false;
    }
}