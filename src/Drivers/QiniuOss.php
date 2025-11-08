<?php

namespace Huozi\ImageProcess\Drivers;

use Huozi\ImageProcess\Text;

class QiniuOss extends TencentCos
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
}