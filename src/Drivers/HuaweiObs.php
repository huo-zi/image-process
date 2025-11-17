<?php

namespace Huozi\ImageProcess\Drivers;

class HuaweiObs extends AliOss
{

    protected static function formatGravity($g)
    {
        $g = \str_replace(['North', 'Sorth', 'West', 'East'], ['Top', 'Bottom', 'Left', 'Right'], $g);
        $words = static::ucsplit($g);

        return \count($words) > 1 ? \array_reduce($words, function($g, $word) {
            return $g . \strtolower($word[0]);
        }) : \strtolower($g);
    }

    protected function handle() : string
    {
        $image = $this->image;
        if (!\strpos($image, 'x-image-process')) {
            $image .= '?x-image-process=image';
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