<?php declare(strict_types=1);

namespace strikingPHP\core\route\Annotation\Parser;

class RequestMappingParser
{
    public function parse($annotation)
    {
        $routeInfo=[
            'path'   => $annotation->getRoute(),
            'handle' => $annotation->getHandle()
        ];
        // 注册路由
        \strikingPHP\core\route\Route::register($annotation->getMethod(),$routeInfo);
    }
}
