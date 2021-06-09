<?php
/**
 * Created by PhpStorm.
 * User: code301
 * Date: 2020/7/3
 * Time: 22:40
 */
return [
    'Route' => function(){
        return  \Code301\Core\Route\Route::get_instance(); //单例
    },
    'Config' => function(){
        return  \Code301\Core\Config::get_instance(); //单例
    }
];