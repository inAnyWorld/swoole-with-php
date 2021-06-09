<?php
/**
 * Created by PhpStorm.
 * User: code301
 * Date: 2020/7/25
 * Time: 14:05
 */

namespace Code301\Core;


use Code301\Core\Bean\BeanFactory;
use Code301\Core\Route\Annotation\Mapping\RequestMapping;
use Code301\Core\Route\Annotation\Parser\RequestMappingParser;
use Code301\Core\Rpc\Rpc;
use Code301\Route\Route;
use Swoole\Http\Server;

class Http
{
    protected $server;

    public function run()
    {
        BeanFactory::get('Config')->load();
        $config = BeanFactory::get('Config')->get("http");
        $this->server = new Server($config['host'], $config['port']);
        $this->server->set($config['setting']);

        if (isset($config['rpcEnable']) && (int)$config['rpcEnable'] === 1 ) {
               (new Rpc())->listen($this->server);
        }
        $this->server->on('start', [$this, 'start']);
        $this->server->on('workerStart', [$this, 'workerStart']);
        $this->server->on('request', [$this, 'request']);
        $this->server->start();

    }

    public function start()
    {
        $reload = Reload::get_instance();
        $reload->watch = [CONFIG_PATH, APP_PATH, ROOT_PATH];
        $reload->md5Flag = $reload->getMd5();

        //定时监控相关的文件
        \Swoole\Timer::Tick(2000, function () use ($reload) {
            if ($reload->reload()) { //判断是否需要重启
                $this->server->reload();
            }
        });
        $config = BeanFactory::get('Config')->get("http");
            echo "***********************************************************************" . PHP_EOL;
            echo sprintf("*HTTP     | Listen: %s:%d, type: TCP, worker: %d  ", $config['host'], $config['port'], $config['setting']['worker_num']) . PHP_EOL;
        if (isset($config['rpcEnable']) && (int)$config['rpcEnable']===1){
            $config = BeanFactory::get('Config')->get("rpc");
            echo sprintf("*RPC      | Listen: %s:%d, type: TCP, worker: %d  ", $config['host'], $config['port'], $config['setting']['worker_num']) . PHP_EOL;
            echo "***********************************************************************" . PHP_EOL;
        }
    }

    public function workerStart($server, $worker_id)
    {
        $this->loadAnnotations(); //载入路由的注解
        BeanFactory::get('Config')->load(); //载入配置文件
    }

    public function request($request, $response)
    {
        $path_info = $request->server['path_info'];
        $method = $request->server['request_method'];
        //$res=\Six\Core\Route\Route::dispatch($method,$path_info);
        $res = BeanFactory::get('Route')::dispatch($method, $path_info);
        $response->end($res);
    }

    public function loadAnnotations()
    {
        $dirs = $this->tree(APP_PATH, "Controller");
        if (!empty($dirs)) {
            foreach ($dirs as $file) {
                $fileName = explode('/', $file);
                $className = explode('.', end($fileName))[0];
                $file = file_get_contents($file, false, null, 0, 500);
                preg_match('/namespace\s(.*)/i', $file, $nameSpace);
                if (isset($nameSpace[1])) {
                    $nameSpace = str_replace([' ', ';', '"'], '', $nameSpace[1]);
                    $className = trim($nameSpace) . "\\" . $className;
                    $obj = new  $className;
                    $reflect = new \ReflectionClass($obj);
                    $classDocComment = $reflect->getDocComment(); //类注解
                    //匹配前缀
                    foreach ($reflect->getMethods() as $method) {
                        $methodDocComment = $method->getDocComment(); //方法注解
                        //收集信息（路由）
                        $annotation = new RequestMapping($classDocComment, $methodDocComment, $reflect, $method);
                        //收集信息（权限验证，比如继承了父类，父类查询到所有的子类，执行解析）
                        //执行注解逻辑
                        (new RequestMappingParser())->parse($annotation);
                    }
                }
            }
        }
    }

    /**
     * 遍历目录
     * @param $dir
     */
    public function tree($dir, $filter)
    {
        $dirs = glob($dir . '/*');
        $dirFiles = [];
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $res = $this->tree($dir, $filter);
                if (is_array($dirFiles)) {
                    foreach ($res as $v) {
                        $dirFiles[] = $v;
                    }
                }
            } else {
                //判断是否是控制器
                if (stristr($dir, $filter)) {
                    $dirFiles[] = $dir;
                }
            }
        }
        return $dirFiles;
    }
}