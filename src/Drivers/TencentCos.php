<?php

namespace Huozi\ImageProcess\Drivers;

use Huozi\ImageProcess\Text;

class TencentCos extends AbstractDriver
{

    protected $handlers = [];

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

    /**
     * @inheritDoc
     */
    public function crop($w = 0, $h = 0, $x = 0, $y = 0, $g = null)
    {
        if (!\is_null($g)) {
            $this->putHandle('imageMogr2', 'gravity', $g);
        }

        $params = \array_filter([$w, 'x', $h]);
        if ($x || $y) {
            $params = ['!', $w, 'x', $h, $x > 0 ? 'a' : '', $x, $y > 0 ? 'a' : '', $y];
        }

        $this->putHandle('imageMogr2', 'crop', $params);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function blur($r, $s)
    {
        $this->putHandle('imageMogr2', 'blur', [$r, 'x', $s]);
        return $this;
    }

    /**
     * @inheritDoc
     * @param int $value 1 - 100 cos/qiniu时会自动*3取范围 10-300
     */
    public function sharpen(int $value)
    {
        $value = $value * 3;
        $value = $value < 10 ? 10 : $value;
        $value = $value > 300 ? 300 : $value;
        $this->putHandle('imageMogr2', 'sharpen', $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rotate(int $value)
    {
        $this->putHandle('imageMogr2', 'rotate', $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flip(int $mode)
    {
        switch ($mode) {
            case 0:
                $this->putHandle('imageMogr2', 'flip', 'vertical');
                break;
            case 1:
                $this->putHandle('imageMogr2', 'flip', 'horizontal');
                break;
            case 2:
                $this->putHandle('imageMogr2', 'flip', 'vertical');
                $this->putHandle('imageMogr2', 'flip', 'horizontal');
                break;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function format(string $type)
    {
        $this->putHandle('imageMogr2', 'format', $type);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function quality(int $value)
    {
        $this->putHandle('imageMogr2', 'quality', $value);
        return $this;
    }

    public function interlace(int $value)
    {
        $this->putHandle('imageMogr2', 'interlace', $value);
        return $this;
    }

    public function radius($r)
    {
        $this->putHandle('imageMogr2', 'roundPic.radius', $r);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function imageWatermark(string $path, int $x = 10, int $y = 10, string $g = 'SouthEast', int $t = 100, $fill = 0)
    {
        $this->putHandle('watermark', '1', [
            'image' => static::safeBase64Encode($path),
            'dx' => $x,
            'dy' => $y,
            'gravity' => $g,
            'dissolve' => $t,
            'tile' => $fill,
        ]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function textWatermark(Text $text, int $x = 10, int $y = 10, string $g = 'SouthEast', int $t = 100, $fill = 0)
    {
        $this->putHandle('watermark', '2', \array_filter([
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
        ]);
        return $this;
    }

    protected function putHandle($type, $method, $value)
    {
        $data = $this->handlers[$type] ?? [];
        $i = 0;
        do {
            if (\array_key_exists($method, $data[$i] ?? [])) {
                $i++;
                continue;
            }
            $data[$i][$method] = $value;
            break;
        } while (true);
        $this->handlers[$type] = $data;
    }

    protected function handle() : string
    {
        $image = $this->image;

        return $image . '?' . \implode(
            '|',
            \array_map(function ($value, $key) {
                if (static::isAssoc($value)) {
                    $value = [$value];
                }
                return array_reduce($value, function ($carry, $item) use ($key) {
                    return $carry . ($carry ? '|' : '') . \sprintf(
                        '%s/%s',
                        $key,
                        \implode('/', \array_map(function ($val, $key) {
                            if (\is_array($val)) {
                                $val = static::isAssoc($val) ? \array_reduce(\array_keys($val), function ($init, $k) use ($val) {
                                    return $init . ($init ? '/' : '') . $k . '/' . $val[$k];
                                }) : \implode('', $val);
                            }
                            return $key . '/' . $val;
                        }, \array_values($item), \array_keys($item)))
                    );
                });
            }, \array_values($this->handlers), \array_keys($this->handlers))
        );
    }
}