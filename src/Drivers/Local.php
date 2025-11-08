<?php

namespace Huozi\ImageProcess\Drivers;

use Huozi\ImageProcess\Text;

class Local extends AbstractDriver
{

    /**
     * @inheritDoc
     */
    public function resize($w, $h = null, $mode = null)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function crop($w = 0, $h = 0, $x = 0, $y = 0, $g = 'NorthWest')
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function format(string $type)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function imageWatermark(
        string $path,
        int $x = 10,
        int $y = 10,
        string $g = 'SouthEast',
        int $t = 100,
        $fill = 0
    ) {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function textWatermark(
        Text $text,
        int $x = 10,
        int $y = 10,
        string $g = 'SouthEast',
        int $t = 100,
        $fill = 0
    ){
        return $this;
    }

    protected function handle() : string
    {
        return '';
    }
}