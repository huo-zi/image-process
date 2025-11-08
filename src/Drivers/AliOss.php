<?php

namespace Huozi\ImageProcess\Drivers;

use Huozi\ImageProcess\Text;

class AliOss extends AbstractDriver
{

    protected $handlers = [];

    public function __construct($app = null, $config = [])
    {
        parent::__construct($app, $config);
    }

    /**
     * @inheritDoc
     */
    public function resize($w, $h = null, $mode = null)
    {
        switch ($mode) {
            case 'p':
                $params = ['p' => $w];
                break;
            case 'l':
                $params = ['l' => $w];
                break;
            case 's':
                $params = ['s' => $w];
                break;
            case 'pad':
                $params = \compact('w', 'h') + ['m' => 'pad'];
                break;
            case 'fixed':
                $params = \compact('w', 'h') + ['m' => 'fixed'];
                break;
            case 'fill':
            default:
                $params = \compact('w', 'h') + ['m' => 'fill'];
                break;
        }

        $this->handlers['resize'] = $params;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function crop($w = 0, $h = 0, $x = 0, $y = 0, $g = 'NorthWest')
    {
        $this->handlers['crop'] = \compact('w', 'h', 'x', 'y') + [
            'g' => static::formatGravity($g)
        ];
        return $this;
    }

    public function circle($r)
    {
        $this->handlers['circle'] = \compact('r');
        return $this;
    }

    public function radius($r)
    {
        $this->handlers['rounded-corners'] = \compact('r');
        return $this;
    }

    public function blur($r, $s)
    {
        $this->handlers['blur'] = \compact('r', 's');
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function imageWatermark(string $path, int $x = 10, int $y = 10, string $g = 'SouthEast', int $t = 100, $fill = 0)
    {
        $this->handlers['watermark'] = \compact('x', 'y', 't', 'fill') + [
            'g' => static::formatGravity($g),
            'image' => static::safeBase64Encode(ltrim($path, '/')),
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function textWatermark(Text $text, int $x = 10, int $y = 10, string $g = 'SouthEast', int $t = 100, $fill = 0)
    {
        $this->handlers['watermark'] = \compact('x', 'y', 't', 'fill') + [
            'g' => static::formatGravity($g),
            'text' => static::safeBase64Encode($text->text),
        ] + \array_filter([
            'type' => static::safeBase64Encode($text->font),
            'color' => ltrim($text->color, '#'),
            'size' => $text->size,
        ]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function format(string $type)
    {
        $this->handlers['format'] = $type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function quality(int $value)
    {
        $this->handlers['quality'] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flip(int $mode)
    {
        $this->handlers['flip'] = $mode;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rotate(int $value)
    {
        $this->handlers['rotate'] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function contrast(int $value)
    {
        $this->handlers['contrast'] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sharpen(int $value)
    {
        $this->handlers['sharpen'] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function interlace(int $value = 0)
    {
        $this->handlers['interlace'] = $value;
        return $this;
    }

    protected static function formatGravity($g)
    {
        $words = static::ucsplit($g);
        return \count($words) > 1 ? \array_reduce($words, function($g, $word) {
            return $g . \strtolower($word[0]);
        }) : $g;
    }

    protected function handle() : string
    {
        $image = $this->image;
        if (!\strpos($image, 'x-oss-process')) {
            $image .= '?x-oss-process=image';
        }

        return \array_reduce(\array_keys($this->handlers), function($image, $key) {
            $value = $this->handlers[$key];
            return \sprintf(
                '%s/%s,%s',
                $image,
                $key,
                \is_array($value) ? \array_reduce(\array_keys($value), function($carry, $k) use ($value) {
                    return $carry . ($carry ? ',' : '') . ($k . '_' . $value[$k]);
                }) : $value
            );
        }, $image);
    }

}