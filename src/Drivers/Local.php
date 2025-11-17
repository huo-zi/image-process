<?php

namespace Huozi\ImageProcess\Drivers;

use Composer\InstalledVersions;
use Huozi\ImageProcess\Text;
use Intervention\Image\ImageManager;

/**
 * @property ImageManager $name
 */
class Local extends AbstractDriver
{

    private $version;

    public function image($path)
    {
        $this->version = \intval(InstalledVersions::getVersion('intervention/image'));
        $manager = new ImageManager();
        /** @var ImageManager  */
        $this->image = $this->version == 3 ? $manager->read($path) : $manager->make($path);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function resize($w, $h = null, $mode = null)
    {
        $this->image->resize($w, $h, function ($constraint) use ($mode) {
            
        });
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function crop($w = 0, $h = 0, $x = 0, $y = 0, $g = 'NorthWest')
    {
        $g = $this->formatGravity($g);
        if ($this->version == 3) {
            $this->image->crop($w, $h, $x, $y, 'FFFFFF', $g);
        } else {
            $width = $this->image->width();
            $height = $this->image->height();
            foreach (\explode('-', $g) as $g) {
                switch ($g) {
                    case 'right':
                        $x = $width - $w;
                        break;
                    case 'bottom':
                        $y = $height - $h;
                        break;
                    case 'center':
                        $x = intval(($width - $w) / 2);
                        $y = intval(($height - $h) / 2);
                        break;
                }
            }

            $this->image->crop($w, $h, $x, $y);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function blur($r, $s)
    {
        $this->image->blur($r + $s);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sharpen(int $value)
    {
        $this->image->sharpen($value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rotate(int $value)
    {
        $this->image->rotate(- $value);
        return $this;
    }

    public function flip(int $mode)
    {
        return $this;
    }

    /**
     * 
     * @inheritDoc
     */
    public function format(string $type)
    {
        if ($this->version == 3) {
            $this->image->encodeByExtension($type);
        } else {
            $this->image->encode($type);
        }
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
        return $this->image->stream('png');
    }

    protected static function formatGravity($g)
    {
        $g = \str_replace(['North', 'Sorth', 'West', 'East'], ['Top', 'Bottom', 'Left', 'Right'], $g);
        $words = static::ucsplit($g);

        return \count($words) > 1 ? \implode('-', \array_map(function ($word) {
            return \strtolower($word);
        }, $words)) : \strtolower($g);
    }
}