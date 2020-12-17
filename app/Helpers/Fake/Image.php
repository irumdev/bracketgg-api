<?php

declare(strict_types=1);

namespace App\Helpers\Fake;

use InvalidArgumentException;
use RuntimeException;

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
     * 페이크 이미지 카테고리들
     * @var array[string]
     */
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
    public static function url(int $width = 640, int $height = 480, string $category = null, bool $randomize = true, bool $gray = false): string
    {
        $baseUrl = "https://placeimg.com/";
        $url = "{$width}/{$height}/";

        if ($category) {
            if (!in_array($category, static::$categories)) {
                throw new InvalidArgumentException(sprintf('Unknown image category "%s"', $category));
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

    /**
     * fake이미지를 생성하는 메소드 입니다.
     *
     * @param string $dir 저장할 경로
     * @param int $width 페이크 이미지 넓이
     * @param int $height 페이크 이미지 높이
     * @param string $category 페이크 이미지 카테고리
     * @param bool $fullPath 리턴값을 절대경로 받을지 여부
     * @param string $randomize 페이크 이미지 다운받을 시 랜덤 쿼리스트링 여부
     * @param string $word (사용 안함)
     * @param string $gray 페이크 이미지 그레이스케일 여부
     * @throws \InvalidArgumentException 경로에 작성을 못할 경우 throw됩니다.
     * @throws \RuntimeException 이미지서버가 동작 안할경우 throw됩니다.
     * @throws \RuntimeException fopen시 url을 허용 안할경우 (php.ini 확인 바랍니다)
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return string 페이크 이미지 저장 경로
     */
    public static function create($dir = null, $width = 640, $height = 480, $category = null, $fullPath = true, $randomize = true, $word = null, $gray = false)
    {
        $dir = is_null($dir) ? sys_get_temp_dir() : $dir;
        // Validate directory path
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new InvalidArgumentException(sprintf('Cannot write to directory "%s"', $dir));
        }

        $name = md5(uniqid(empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'], true));
        $filename = $name . '.jpg';
        $filepath = $dir . DIRECTORY_SEPARATOR . $filename;

        $url = static::url($width, $height, $category, $randomize, $gray);

        if (function_exists('curl_exec')) {
            $fp = fopen($filepath, 'w');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $success = curl_exec($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
            fclose($fp);
            curl_close($ch);

            if (!$success) {
                unlink($filepath);
                return new RuntimeException('Image server is not working');
            }
        } elseif (ini_get('allow_url_fopen')) {
            // use remote fopen() via copy()
            $success = copy($url, $filepath);
        } else {
            return new RuntimeException('The image formatter downloads an image from a remote HTTP server. Therefore, it requires that PHP can request remote hosts, either via cURL or fopen()');
        }

        return $fullPath ? $filepath : $filename;
    }
}
