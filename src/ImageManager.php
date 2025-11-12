<?php

namespace Huozi\ImageProcess;

use Huozi\ImageProcess\Drivers\AbstractDriver;

/**
 * @method static Drivers\Local local($image = null)
 * @method static Drivers\AliOss oss($image = null)
 * @method static Drivers\TencentCos cos($image = null)
 * @method static Drivers\HuaweiObs obs($image = null)
 * @method static Drivers\QiniuOss qiniu($image = null)
 * @method AbstractDriver driver($driver = null)
 */
class ImageManager
{

    use ManagerTrait;

    /**
     * @var mixed
     */
    protected $container;

    public function __construct($container = null)
    {
        $this->container = $container;
    }

    public function getDefaultDriver()
    {
        return 'local';
    }

    protected function createLocalDriver()
    {
        return new Drivers\Local($this->container);
    }

    protected function createOssDriver()
    {
        return new Drivers\AliOss($this->container);
    }

    protected function createQiniuDriver()
    {
        return new Drivers\QiniuOss($this->container);
    }

    protected function createCosDriver()
    {
        return new Drivers\TencentCos($this->container);
    }

    protected function createObsDriver()
    {
        return new Drivers\HuaweiObs($this->container);
    }

    public static function __callStatic($name, $arguments)
    {
        $driver = (new static())->driver($name);
        if (isset($arguments[0])) {
            $driver->image($arguments[0]);
        }
        return $driver;
    }

}