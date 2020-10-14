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
    private array $categories = [
        'animals', 'arch','nature','people', 'tech', 'any'
    ];
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
    public static function fakeUrl(int $width = 640, int $height = 480, string $category = null, bool $randomize = true, bool $gray = false): string
    {
        $baseUrl = "https://placeimg.com/";
        $url = "{$width}/{$height}/";

        if ($category) {
            if (!in_array($category, static::$categories)) {
                throw new \InvalidArgumentException(sprintf('Unknown image category "%s"', $category));
            }
            $url .= "{$category}/";
        }

        if ($gray) {
            $url = 'grayscale/' . $url;
        }

        if ($randomize) {

            $url = substr($url, 0, -1);
            $url .= '?' . random_int(100, 1000);
        }

        return $baseUrl . $url;
    }

    public static function create($dir = null, $width = 640, $height = 480, $category = null, $fullPath = true, $randomize = true, $word = null, $gray = false)
    {
        $dir = is_null($dir) ? sys_get_temp_dir() : $dir;
        // Validate directory path
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('Cannot write to directory "%s"', $dir));
        }

        $name = md5(uniqid(empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'], true));
        $filename = $name .'.jpg';
        $filepath = $dir . DIRECTORY_SEPARATOR . $filename;

        $url = static::fakeUrl($width, $height, $category, $randomize, $gray);

        if (function_exists('curl_exec')) {
            $fp = fopen($filepath, 'w');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $success = curl_exec($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
            fclose($fp);
            curl_close($ch);

            if (!$success) {
                unlink($filepath);
                return new \RuntimeException('Image server is not working');


                // return false;
            }
        } elseif (ini_get('allow_url_fopen')) {
            // use remote fopen() via copy()
            $success = copy($url, $filepath);
        } else {
            return new \RuntimeException('The image formatter downloads an image from a remote HTTP server. Therefore, it requires that PHP can request remote hosts, either via cURL or fopen()');
        }

        return $fullPath ? $filepath : $filename;
    }
}
