<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2025 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare(strict_types = 1);

namespace think;

use DateInterval;
use DateTimeInterface;
use Psr\SimpleCache\CacheInterface;
use think\cache\Driver;
use think\cache\TagSet;
use think\exception\InvalidArgumentException;
use think\helper\Arr;

/**
 * 缓存管理类
 * @mixin Driver
 * @mixin \think\cache\driver\File
 */
class CacheManager implements CacheInterface
{

    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    /**
     * 缓存实例
     * @var array
     */
    protected $instance = [];

    /**
     * 连接或者切换缓存
     * @access public
     * @param  string $name  连接配置名
     * @param  bool   $force 强制重新连接
     * @return Driver
     */
    public function store(string $name = '', bool $force = false): Driver
    {
        if ('' == $name) {
            $name = $this->config['default'] ?? 'file';
        }

        if ($force || !isset($this->instance[$name])) {
            if (!isset($this->config['stores'][$name])) {
                throw new InvalidArgumentException('Undefined cache config:' . $name);
            }

            $options = $this->config['stores'][$name];

            $this->instance[$name] = $this->connect($options);
        }

        return $this->instance[$name];
    }

    /**
     * 连接缓存
     * @access public
     * @param  array  $options 连接参数
     * @param  string $name  连接配置名
     * @return Driver
     */
    public function connect(array $options, string $name = ''): Driver
    {
        if ($name && isset($this->instance[$name])) {
            return $this->instance[$name];
        }

        $type = !empty($options['type']) ? $options['type'] : 'File';

        $handler = Container::factory($type, '\\think\\cache\\driver\\', $options);

        if ($name) {
            $this->instance[$name] = $handler;
        }

        return $handler;
    }

    /**
     * 缓存配置
     * @access public
     * @param array $config    配置
     * @return void
     */
    public function config(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 清空缓冲池
     * @access public
     * @return bool
     */
    public function clear(): bool
    {
        return $this->store()->clear();
    }

    /**
     * 读取缓存
     * @access public
     * @param string $key     缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($key, mixed $default = null): mixed
    {
        return $this->store()->get($key, $default);
    }

    /**
     * 写入缓存
     * @access public
     * @param string                             $key   缓存变量名
     * @param mixed                              $value 存储数据
     * @param int|DateTimeInterface|DateInterval $ttl   有效时间 0为永久
     * @return bool
     */
    public function set($key, $value, $ttl = null): bool
    {
        return $this->store()->set($key, $value, $ttl);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $key 缓存变量名
     * @return bool
     */
    public function delete($key): bool
    {
        return $this->store()->delete($key);
    }

    /**
     * 读取缓存
     * @access public
     * @param iterable $keys    缓存变量名
     * @param mixed    $default 默认值
     * @return iterable
     * @throws InvalidArgumentException
     */
    public function getMultiple($keys, $default = null): iterable
    {
        return $this->store()->getMultiple($keys, $default);
    }

    /**
     * 写入缓存
     * @access public
     * @param iterable               $values 缓存数据
     * @param null|int|\DateInterval $ttl    有效时间 0为永久
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool
    {
        return $this->store()->setMultiple($values, $ttl);
    }

    /**
     * 删除缓存
     * @access public
     * @param iterable $keys 缓存变量名
     * @return bool
     * @throws InvalidArgumentException
     */
    public function deleteMultiple($keys): bool
    {
        return $this->store()->deleteMultiple($keys);
    }

    /**
     * 判断缓存是否存在
     * @access public
     * @param string $key 缓存变量名
     * @return bool
     */
    public function has($key): bool
    {
        return $this->store()->has($key);
    }

    /**
     * 缓存标签
     * @access public
     * @param string|array $name 标签名
     * @return TagSet
     */
    public function tag($name)
    {
        return $this->store()->tag($name);
    }

    /**
     * 动态调用
     * @param string $method
     * @param array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->store()->$method(...$parameters);
    }
}
