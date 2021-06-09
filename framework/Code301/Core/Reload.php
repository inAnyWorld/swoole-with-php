<?php

namespace Code301\Core;

class Reload{
    public $watch; //监控的文件夹
    public $md5Flag; //上次计算的md5的值
    private static $instance;
    private function __construct ()
    {
    }

    public static function  get_instance()
    {
        if(is_null (self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function reload()
    {
         //当前文件的MD5的值跟上一次有没有区别
         $md5=$this->getMd5();
         if($md5 !=$this->md5Flag)  {
                $this->md5Flag=$md5; //重新赋值
                return true;
         }
        return false;
    }

    public function getMd5()
    {
        $md5='';
        foreach ($this->watch as $dir) {
            $md5.=self::md5file($dir);
        }
        return md5($md5);
    }

    public static function md5File($dir)
    {
        //遍历文件夹当中的所有文件,得到所有的文件的md5散列值
        if (!is_dir($dir)) {
            return '';
        }
        $md5File = array();
        $d = dir($dir);
        while (false !== ($entry = $d->read())) {
            if ($entry !== '.' && $entry !== '..') {
                if (is_dir($dir . '/' . $entry)) {
                    //递归调用
                    $md5File[] = self::md5File($dir . '/' . $entry);
                } elseif (substr($entry, -4) === '.php') {
                    $md5File[] = md5_file($dir . '/' . $entry);
                }
                $md5File[] = $entry;
            }
        }
        $d->close();
        return md5(implode('', $md5File));
    }
}