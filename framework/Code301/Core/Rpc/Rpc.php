<?php
/**
 * Created by PhpStorm.
 * User: code301
 * Date: 2020/8/14
 * Time: 22:13
 */

namespace Code301\Core\Rpc;

use Code301\Core\Bean\BeanFactory;
use Code301\Core\Http;
use Code301\Core\Reload;

class Rpc extends Http
{
    protected  $server;
    public function run()
    {
        BeanFactory::get('Config')->load();
        $config = BeanFactory::get('Config')->get("rpc");
        $this->server = new \Swoole\Server($config['host'], $config['port']);
        $this->server->set($config['setting']);
        //是在swoole的worker进程内触发
        $this->server->on('start', [$this, 'start']);
        $this->server->on('workerStart', [$this, 'workerStart']);
        $this->server->on('receive', [$this, 'receive']);
        $this->server->start(); //启动服务器
    }

    public  function listen($server)
    {
        $config = BeanFactory::get('Config')->get("rpc");
        $server->addlistener($config['host'], $config['port'],SWOOLE_SOCK_TCP);
        $server->set($config['setting']);
        $server->on('receive', [$this, 'receive']);
    }

    public function receive($server, int $fd, int $reactor_id, string $data)
    {
         var_dump($server,$data);
    }

    public  function start()
    {
        $reload=Reload::get_instance();
        $reload->watch=[CONFIG_PATH,APP_PATH,ROOT_PATH];
        $reload->md5Flag=$reload->getMd5();
        //定时监控相关的文件
        \Swoole\Timer::Tick(2000,function ()use($reload) {
            if($reload->reload()) { //判断是否需要重启
                $this->server->reload();
            }
        });
        $config = BeanFactory::get('Config')->get("rpc");
        echo " *********************************************************************".PHP_EOL;
        echo sprintf("*RPC     | Listen: %s:%d, type: TCP, worker: %d  ", $config['host'], $config['port'], $config['setting']['worker_num']) . PHP_EOL;
    }
}