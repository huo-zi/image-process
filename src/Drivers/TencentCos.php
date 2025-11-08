<?php

namespace Huozi\ImageProcess\Drivers;

use Huozi\ImageProcess\Text;

class TencentCos extends AbstractDriver
{

    private $handlers = [];

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

        $this->handlers['imageMogr2']['thumbnail'] = $params;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function crop($w = 0, $h = 0, $x = 0, $y = 0, $g = null)
    {
        if (!\is_null($g)) {
            $this->handlers['imageMogr2']['gravity'] = $g;
        }

        $params = \array_filter([$w, 'x', $h]);
        if ($x || $y) {
            $params = ['!', $w, 'x', $h, $x > 0 ? 'a' : '', $x, $y > 0 ? 'a' : '', $y];
        }

        $this->handlers['imageMogr2']['crop'] = $params;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function format(string $type)
    {
        $this->handlers['imageMogr2']['format'] = $type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function quality(int $value)
    {
        $this->handlers['imageMogr2']['quality'] = $value;
        return $this;
    }

    public function interlace(int $value)
    {
        $this->handlers['imageMogr2']['interlace'] = $value;
        return $this;
    }
    
    public function rotate(int $value)
    {
        $this->handlers['imageMogr2']['imageMogr2.rotate'] = $value;
        return $this;
    }

    public function blur($r, $s)
    {
        $this->handlers['imageMogr2']['imageMogr2.blur'] = [$r, 'x', $s];
        return $this;
    }

    public function sharpen(int $value)
    {
        $this->handlers['imageMogr2']['imageMogr2.sharpen'] = $value;
        return $this;
    }

    public function radius($r)
    {
        $this->handlers['imageMogr2']['roundPic.radius'] = $r;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function imageWatermark(string $path, int $x = 10, int $y = 10, string $g = 'SouthEast', int $t = 100, $fill = 0)
    {
        $this->handlers['watermark']['3'][] = [
            'image' => static::safeBase64Encode($path),
            'dx' => $x,
            'dy' => $y,
            'gravity' => $g,
            'dissolve' => $t,
            'tile' => $fill,
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function textWatermark(Text $text, int $x = 10, int $y = 10, string $g = 'SouthEast', int $t = 100, $fill = 0)
    {
        $this->handlers['watermark']['3'][] = \array_filter([
            'text' => static::safeBase64Encode($text->text),
            'font' => static::safeBase64Encode($text->font),
            'fontsize' => $text->size,
            'fill' => static::safeBase64Encode($text->color),
        ]) + [
            'dx' => $x,
            'dy' => $y,
            'gravity' => $g,
            'dissolve' => $t,
            'tile' => $fill,
        ];
        return $this;
    }

    protected function handle() : string
    {
        $image = $this->image;

        return $image . '?' . \implode(
            '|',
            \array_map(function ($value, $key) {
                return \sprintf(
                    '%s/%s',
                    $key,
                    \implode('/', \array_map(function($val, $key) {
                        if (\is_array($val)) {
                            $val = \is_array($val[0]) ? \array_reduce($val, function($init, $item) {
                                return $init . ($init ? '/' : '') . \implode('/', \array_map(function($v, $k) {
                                    return $k . '/' . $v;
                                }, \array_values($item), \array_keys($item)));
                            }) : \implode('', $val); 
                        }
                        return $key . '/' . $val; 
                    }, \array_values($value), \array_keys($value)))
                );
            }, \array_values($this->handlers), \array_keys($this->handlers))
        );
    }
}