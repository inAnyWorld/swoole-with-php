<?php

namespace Code301\Core\Route\Annotation\Parser;

use Code301\Core\Bean\BeanFactory;

class RequestMappingParser
{
    public function parse($annotation)
    {
        $routeInfo=[
            'routePath'=>$annotation->getRoute(),
            'handle' =>$annotation->getHandle()
        ];
        //\Six\Core\Route\Route::addRoute($annotation->getMethod(),$routeInfo); //添加路由
         BeanFactory::get('Route')::addRoute($annotation->getMethod(),$routeInfo);
    }
}
