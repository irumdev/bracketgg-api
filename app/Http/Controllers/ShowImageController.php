<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Helpers\Image;
use Illuminate\Support\Facades\Storage;

/**
 * 이미지들을 보여주는 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ShowImageController extends Controller
{
    /**
     * 유저 프로필을 클라이언트에게 리턴하는 메소드 입니다.
     *
     * @param   string $profileImage 이미지 이름
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 유저 프로필 이미지 바이너리
     */
    public function getUserProfile(string $profileImage): BinaryFileResponse
    {
        return Image::userProfile($profileImage);
    }

    /**
     * 채널 배너를 클라이언트에게 리턴하는 메소드 입니다.
     *
     * @param  string $bannerImage 이미지 이름
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 채널 배너 이미지 바이너리
     */
    public function getChannelBoardArticle(string $artcleImage): BinaryFileResponse
    {
        return Image::getChannelBoardArticle($artcleImage);
    }

    public function getTeamBoardArticle(string $artcleImage): BinaryFileResponse
    {
        return Image::getTeamBoardArticle($artcleImage);
    }

    /**
     * 채널 배너를 클라이언트에게 리턴하는 메소드 입니다.
     *
     * @param  string $bannerImage 이미지 이름
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 채널 배너 이미지 바이너리
     */
    public function getChannelBanner(string $bannerImage): BinaryFileResponse
    {
        return Image::channelBanner($bannerImage);
    }

    /**
     * 채널 로고를 클라이언트에게 리턴하는 메소드 입니다.
     *
     * @param  string $logoImage 이미지 이름
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 채널 로고 이미지 바이너리
     */
    public function getChannelLogo(string $logoImage): BinaryFileResponse
    {
        return Image::channelLogo($logoImage);
    }

    /**
     * 팀 로고를 클라이언트에게 리턴하는 메소드 입니다.
     *
     * @todo 비공개 팀의 이미지 일 경우 401 리턴
     * @param  string $logoImage 이미지 이름
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 채널 로고 이미지 바이너리
     */
    public function getTeamLogo(string $logoImage): BinaryFileResponse
    {
        return Image::teamLogo($logoImage);
    }

    /**
     * 팀 로고를 클라이언트에게 리턴하는 메소드 입니다.
     *
     * @todo 비공개 팀의 이미지 일 경우 401 리턴
     * @param  string $logoImage 이미지 이름
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return BinaryFileResponse 채널 로고 이미지 바이너리
     */
    public function getTeamBanner(string $bannerImage): BinaryFileResponse
    {
        return Image::teamBanner($bannerImage);
    }
}
