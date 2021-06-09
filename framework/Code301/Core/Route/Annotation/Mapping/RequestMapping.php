<?php

namespace Code301\Core\Route\Annotation\Mapping;


class RequestMapping
{
    /**
     * Action routing path
     *
     * @var string
     * @Required()
     */
    private $routePath = '';

    /**
     * @var string
     */
    private $prefix = '';

    private  $method;

    private  $handle;

    /**
     * RequestMapping constructor.
     *
     * @param array $values
     */
    public function __construct($classDocComment, $methodDocComment, $reflect, $method)
    {
        //注解信息的收集
        preg_match('/@Controller\((.*)\)/i', $classDocComment, $prefix);
        $prefix=str_replace("\"","",explode("=",$prefix[1])[1]); //清除掉引号
        preg_match('/@RequestMapping\((.*)\)/i', $methodDocComment, $suffix);
        $suffix=str_replace("\"","",explode("=",$suffix[1])[1]); //清除掉引号

        //路由地址（前缀+后缀）
        $this->routePath = $prefix.'/'.$suffix;

        //解析出来方法类型
        $this->method ='GET';

        //处理类
        $this->handle = $reflect->getName()."@".$method->getName();
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->routePath;
    }

    /**
     * @return array
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        //return $this->params;
    }
}
