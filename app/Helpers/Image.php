<?php

namespace App\Helpers;

use Illuminate\Support\Arr;

/**
 * faker image url 다운으로 인한
 * 대체 클래스
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class Image
{

    /**
     * faker의 fakeImageUrl의 서버 다운으로 인하여
     * placeimg로 대체
     *
     * @param   int $width 이미지 width 값
     * @param   int $height 이미지 허이트 값
     * @param   bool $isGray 흑백여부
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return string fake 이미지 url
     */
    public static function create(int $width = 640, int $height = 480, bool $isGray = false): string
    {
        $baseUrl = "https://placeimg.com/";
        $category = Arr::random([
            'animals', 'arch','nature','people', 'tech', 'any'
        ]);
        $url = "{$width}/{$height}/{$category}";
        if ($isGray) {
            $url .= "/grayscale";
        }

        return $baseUrl . $url;
    }
}
