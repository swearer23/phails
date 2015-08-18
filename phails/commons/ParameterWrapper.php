<?php
/**
 * ParameterWrapper 参数包装器
 * @author Xu Shiwen
 */
class ParameterWrapper implements ParameterWrapperInterface, IteratorAggregate, Countable, ArrayAccess
{
    protected $params = array();

    function __construct($params)
    {
        if (is_array($params)) {
            $this->params = $params;
        } else if (in_array("ParameterWrapperInterface", class_implements($params))) {
            $this->params = $params->all();
        } else {
            throw new InvalidArgumentException(
                "参数不正确，必须为array或ParameterWrapperInterface实现类"
            );
        }
    }

    //--------------------------------------------------------------------------
    // ArrayAccess 接口实现
    //--------------------------------------------------------------------------
    public function &__get ($key) {
        return $this->params[$key];
    }

    public function __set($key,$value) {
        $this->params[$key] = $value;
    }

    public function __isset ($key) {
        return isset($this->params[$key]);
    }

    public function __unset($key) {
        unset($this->params[$key]);
    }

    public function offsetExists ($offset)
    {
        return isset($this->params[$offset]);
    }

    public function offsetGet ($offset)
    {
        return isset($this->params[$offset]) ? $this->params[$offset] : null;
    }

    public function offsetSet ($offset, $value)
    {
        if (is_null($offset)) {
            $this->params[] = $value;
        } else {
            $this->params[$offset] = $value;
        }
    }

    public function offsetUnset ($offset)
    {
        unset($this->params[$offset]);
    }

    //--------------------------------------------------------------------------
    // Countable 接口实现
    //--------------------------------------------------------------------------
    public function count()
    {
        return count($this->params);
    }

    //--------------------------------------------------------------------------
    // IteratorAggregate 接口实现
    //--------------------------------------------------------------------------
    public function getIterator()
    {
        return new ArrayIterator($this->params);
    }

    //--------------------------------------------------------------------------
    // ParameterWrapperInterface 接口实现
    //--------------------------------------------------------------------------

    /**
     * 获取指定参数的值
     * @param  mixed $name 参数名称
     * @return mixed       参数值。如果参数不存在，则返回null
     */
    public function get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * 设置指定参数的值
     * @param mixed $name  参数名称
     * @param mixed $value 参数值
     */
    public function set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    /**
     * 向参数包装器对象增加参数
     * @param mixed $params 只要是可遍历的对象即可，包括ParameterWrapper本身
     */
    public function add($params)
    {
        foreach ($params as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    /**
     * 清除参数包装器的所有参数
     */
    public function clear()
    {
        $this->params = array();
    }

    /**
     * 获取参数包装器里的所有参数
     * @return array 所有参数返回的为数组
     */
    public function all()
    {
        return $this->params;
    }

}
