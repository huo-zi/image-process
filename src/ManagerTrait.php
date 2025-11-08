<?php

namespace Huozi\ImageProcess;

trait ManagerTrait
{

    protected $drivers = [];
    protected $customCreators = [];

    /**
     * 获取指定驱动
     * @param string $driver 驱动名称
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function driver($driver = null)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (\is_null($driver)) {
            throw new \InvalidArgumentException(\sprintf(
                'Unable to resolve NULL driver for [%s].', static::class
            ));
        }

        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }
        return $this->drivers[$driver];
    }

    /**
     * 获取一个驱动实例
     * @param  string  $driver 驱动名称
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function createDriver($driver)
    {
        // 判断是否有定制驱动，否则调用创建驱动自身的生成驱动方法create{DriverName}Driver，最终调用makeDriver方法
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver);
        } else {
            $method = 'create' . static::studly($driver) . 'Driver';

            if (\method_exists($this, $method)) {
                return $this->$method();
            }
        }
        throw new \InvalidArgumentException("Driver [$driver] not supported.");
    }

    /**
     * 注册一个定制驱动生成闭包
     * @param string $driver 驱动名
     * @param \Closure  $callback 闭包
     * @return $this
     */
    public function extend($driver, \Closure $callback)
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }

    /**
     * 获取所有已创建的驱动
     * @return array
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * 获取默认驱动
     * @return string
     */
    public function getDefaultDriver()
    {
        return null;
    }

    /**
     * Call a custom driver creator.
     *
     * @param string  $driver
     * @return mixed
     */
    protected function callCustomCreator($driver)
    {
        return $this->customCreators[$driver]($this->container);
    }

    /**
     * 调用默认驱动的方法
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }

    protected static function studly($value)
    {
        $words = \explode(' ', \str_replace(['-', '_'], ' ', $value));

        $studlyWords = \array_map(function ($word) {
            return \ucfirst($word);
        }, $words);

        return \implode('', $studlyWords);
    }

}
