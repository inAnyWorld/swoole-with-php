<?php declare(strict_types=1);

namespace strikingPHP\core\route\Annotation\Mapping;


class RequestMapping
{
    /**
     * @var string
     */
    private $path = '';

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var string
     */
    private  $method;

    /**
     * @var string
     */
    private  $handle;

    /**
     * RequestMapping constructor.
     *
     * @param array $values
     */
    public function __construct($classDocComment,$methodDocComment,$reflection,$method)
    {
        //注解信息的收集
        preg_match('/@Controller\((.*)\)/i', $classDocComment, $prefix);
        $prefix=str_replace("\"","",explode("=",$prefix[1])[1]); //清除掉引号
        preg_match('/@RequestMapping\((.*)\)/i', $methodDocComment, $suffix);
        $suffix=str_replace("\"","",explode("=", $suffix[1])[1]); //清除掉引号

        //路由地址（前缀+后缀）
        $this->prefix = $prefix;
        $this->path   = $this->prefix . '/' . $suffix;
        //解析出来方法类型
        $this->method = 'GET';
        //处理类
        /**@var \ReflectionClass $reflection**/
        $this->handle = $reflection->getName() . "@" . $method->getName();
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->path;
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
