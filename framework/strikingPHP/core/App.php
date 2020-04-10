<?php declare(strict_types=1);
/**
 * Created By Phpstorm.
 * Author: yuanWuDeng
 * Date: 2020/4/10
 * Time: 22:01
 */

namespace strikingPHP;

use Swoole\Http\Server;
use strikingPHP\core\route\Route;
use strikingPHP\core\route\Annotation\Mapping\RequestMapping;
use strikingPHP\core\route\Annotation\Parser\RequestMappingParser;

/**
 * 运行框架
 *
 * Class App
 * @package strikingPHP
 */
class App
{
    /**
     * 运行框架
     */
    public function run()
    {
        // 初始化常量
        $this->_initConstant();
        // 加载注解
        $this->_autoLoadAnnotations();
        $http = new Server("0.0.0.0", 9501);
        $http->on('request', function ($request, $response) {
            $pathInfo = $request->server['path_info'];
            $method = $request->server['request_method'];
            $res= Route::dispatch($method, $pathInfo);
            $response->end($res);
        });
        // 启动服务器
        $http->start();
    }

    /**
     * 定义常量
     */
    public function _initConstant()
    {
        define('ROOT_PATH', dirname(dirname(__DIR__)));
        define('APP_PATH', ROOT_PATH . '/application');
    }

    /**
     * 加载注解
     *
     * @throws \ReflectionException
     */
    public function _autoLoadAnnotations()
    {
        $dirs = $this->_traversalFile(APP_PATH, "Controller");
        if (!empty($dirs)) {
            foreach ($dirs as $file) {
                $fileName = explode('/', $file);
                $className= reset(explode('.', end($fileName)));
                $file = file_get_contents($file, false, null, 0, 500);
                preg_match('/namespace\s(.*)/i', $file, $nameSpace);
                if(isset($nameSpace[1])){
                    $nameSpace = str_replace([' ',';','"'], '', $nameSpace[1]);
                    $className = trim($nameSpace) . "\\" . $className;
                    $object    = new $className;
                    $reflection= new \ReflectionClass($object);
                    // 类注解
                    $classDocComment = $reflection->getDocComment();
                    // 匹配前缀
                    foreach ($reflection->getMethods() as $method) {
                        // 方法注解
                        $methodDocComment = $method->getDocComment();
                        // 收集路由信息
                        $annotation = new RequestMapping($classDocComment, $methodDocComment, $reflection, $method);
                        // 收集信息（权限验证，比如继承了父类，父类查询到所有的子类，执行解析）
                        // 执行注解逻辑
                        (new RequestMappingParser())->parse($annotation);
                    }
                }
            }
        }
    }

    /**
     * 遍历文件
     *
     * @param $dir
     * @param $filter
     * @return array
     */
    public function _traversalFile($dir, $filter): array
    {
        $dirs = glob($dir . '/*');
        $dirFiles = [];
        foreach ($dirs as $dir){
            if(is_dir($dir)) {
                $result = $this->_traversalFile($dir, $filter);
                if(is_array($dirFiles)){
                    foreach ($result as $v){
                        $dirFiles[] = $v;
                    }
                }
            }

            if (!is_dir($dir)) {
                // 判断是否是控制器
                if(stristr($dir, $filter)){
                    $dirFiles[] = $dir;
                }
            }
        }

        return $dirFiles;
    }
}