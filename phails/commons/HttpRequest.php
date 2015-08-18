<?php
require_once 'ParameterWrapper.php';

/**
 * HttpRequest Http请求类
 *
 * @author Xu Shiwen <xusw21@gmail.com>
 * @since  2015/7/7
 *
 */
class HttpRequest extends ParameterWrapper
{
    function __construct($params)
    {
        parent::__construct($params);
        $this->addServerParams();
    }

    protected function addServerParams()
    {
        $serverParams['method'] = $_SERVER['REQUEST_METHOD'];
        $serverParams['protocol'] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
        $serverParams['host'] = $_SERVER['HTTP_HOST'];
        $serverParams['uri'] = $_SERVER['REQUEST_URI'];
        $this->add($serverParams);
    }
}