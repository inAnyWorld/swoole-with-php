<?php
/**
 * Created by PhpStorm.
 * User: code301
 * Date: 2020/6/25
 * Time: 20:21
 */

namespace   App\ Admin \ Controller;

/**
 * Class TestController
 * @Controller(prefix="/admin")
 */
class IndexController
{
    /**
     * @RequestMapping(route="index")
     */
    public function index()
    {
        echo "xxxx";
        return "Admin index";
    }

    /**
     * @RequestMapping(route="test")
     */
    public function test()
    {
        return "Admin test";
    }
}