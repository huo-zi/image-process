<?php

namespace Huozi\ImageProcess;

use Huozi\ImageProcess\Drivers\AbstractDriver;

/**
 * @method AbstractDriver driver()
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

    protected function createaQiniuDriver()
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

}