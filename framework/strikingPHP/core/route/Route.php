<?php declare(strict_types=1);
/**
 * Created By Phpstorm.
 * Author: yuanWuDeng
 * Date: 2020/4/10
 * Time: 23:43
 */

namespace strikingPHP\core\route;


class Route
{
    /**
     * 路由变量
     *
     * @var
     */
    private  static $route;

    /**
     * 添加一个路由
     *
     * @param $method
     * @param $routeInfo
     */
    public static function register($method,$routeInfo){
        self::$route[$method][] = $routeInfo;
    }

    /**
     * 路由分发
     *
     * @param $method
     * @param $pathInfo
     * @return string
     */
    public static function dispatch($method,$pathInfo){
        switch ($method){
            case 'GET':
                foreach (self::$route[$method] as $routeInfo){
                    //判断路径是否在注册的路由上
                    if($pathInfo == $routeInfo['path']){
                        $handle = explode("@", $routeInfo['handle']);
                        $class  = $handle[0];
                        $method = $handle[1];
                        return (new $class)->$method();
                    }
                }
                break;
            case 'POST':
                break;
        }
        return '';
    }
}