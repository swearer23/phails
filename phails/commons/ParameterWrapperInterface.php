<?php

interface ParameterWrapperInterface
{
    public function get($name);
    public function set($name, $value);
    public function add($params);
    public function clear();
    public function all();
}
