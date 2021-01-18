<?php

declare(strict_types=1);

namespace App\Helpers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Storage;

/**
 * 이미지를 찾아주어 바이너리 리턴을 도와주는 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class Image
{
    public static function toStaticUrl(string $routeName, array $param): string
    {
        $parsedStaticUrl = parse_url(route($routeName, $param));
        $base = config('app.staticUrl') . $parsedStaticUrl['path'];

        $staticUrl = isset($parsedStaticUrl['query']) === false ? $base : (
            $base . '?' . $parsedStaticUrl['query']
        );
        return $staticUrl;
    }

    /**
     * 유저 프로필 이미지 찾아주거나 404를 띄워주는 메소드 입니다.
     *
     * @param string $profileImageName 프로필 이미지 이름
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 프로필 이미지 리스폰스
     */
    public static function userProfile(string $profileImageName): BinaryFileResponse
    {
        return self::returnFileOrFail('profileImages', $profileImageName);
    }

    /**
     * 채널 로고 이미지 찾아주거나 404를 띄워주는 메소드 입니다.
     *
     * @param string $channelLogo 채널 로고 이미지 이름
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 채널 배너 이미지 리스폰스
     */
    public static function channelLogo(string $channelLogo): BinaryFileResponse
    {
        return self::returnFileOrFail('channelLogos', $channelLogo);
    }

    /**
     * 채널 배너 이미지 찾아주거나 404를 띄워주는 메소드 입니다.
     *
     * @param string $channelBanner 채널 배너 이미지 이름
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 채널 배너 이미지 리스폰스
     */
    public static function channelBanner(string $channelBanner): BinaryFileResponse
    {
        return self::returnFileOrFail('channelBanners', $channelBanner);
    }

    /**
     * 팀 로고 이미지 찾아주거나 404를 띄워주는 메소드 입니다.
     *
     * @param string $teamLogo 팀 로고 이미지 이름
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 팀 배너 이미지 리스폰스
     */
    public static function teamLogo(string $teamLogo): BinaryFileResponse
    {
        return self::returnFileOrFail('teamLogos', $teamLogo);
    }

    /**
     * 팀 배너 이미지 찾아주거나 404를 띄워주는 메소드 입니다.
     *
     * @param string $teamBanner 팀 배너 이미지 이름
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 팀 배너 이미지 리스폰스
     */
    public static function teamBanner(string $teamBanner): BinaryFileResponse
    {
        return self::returnFileOrFail('teamBanners', $teamBanner);
    }

    /**
     * 파일이 있을 시 바이너리 리스폰스를 하거나 404익셉션을 합니다.
     *
     * @param string $type 이미지 타입 ex) 유저 프로필, 팀 로고 등등..
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 이미지 파일 바이너리 리스폰스
     */
    private static function returnFileOrFail(string $type, string $fileName): BinaryFileResponse
    {
        self::findOrFail($type, $fileName);
        return self::fileResponse(
            self::filePath($type, $fileName)
        );
    }

    /**
     * 파일이 없을 시 404 notfound 익셉션을 throw 해줍니다.
     *
     * @param string $fileName 파일 이름
     * @param string $type 이미지 타입 ex) 유저 프로필, 팀 로고 등등..
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return void
     */
    private static function findOrFail(string $type, string $fileName): void
    {
        abort_if(Storage::missing(sprintf("%s/%s", $type, $fileName)), 404);
    }

    /**
     * 파일 경로에 맞추어 클라이언트에게 바이너리 리스폰스
     *
     * @param string $path 파일 경로
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 파일 바이너리 리스폰스
     */
    private static function fileResponse(string $path): BinaryFileResponse
    {
        return response()->file($path);
    }

    /**
     * 스토리지 파일 경로를 리턴하는 메소드 입니다.
     *
     * @param string $fileName 파일 이름
     * @param string $type 이미지 타입 ex) 유저 프로필, 팀 로고 등등..
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return string 스토리지 파일 경로
     */
    private static function filePath(string $type, string $fileName): string
    {
        return storage_path(sprintf('app/%s/%s', $type, $fileName));
    }
}
