<?php

namespace Huozi\ImageProcess\Drivers;

class QiniuOss extends TencentCos
{

    /**
     * @inheritDoc
     */
    public function resize($w, $h = null, $mode = null)
    {
        switch ($mode) {
            case 'p':
                $params = ['!', $w, 'p'];
                break;
            case 'l':
                $params = [$w, 'x', $h];
                break;
            case 's':
                $params = ['!', $w, 'x', $h, 'r'];
                break;
                break;
            case 'fixed':
                $params = [$w, 'x', $h, '!'];
                break;
            case 'fill':
            default:
                $params = [$w, 'x', $h];
                break;
        }

        $this->putHandle('imageMogr2', 'thumbnail', $params);
        return $this;
    }

}