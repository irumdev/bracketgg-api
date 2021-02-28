<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\User\UserLogoutController;
use App\Http\Controllers\User\CheckEmailDuplicateController;
use App\Http\Controllers\User\CreateUserController;
use App\Http\Controllers\User\ShowUserController;
use App\Http\Controllers\User\UserVerifyController;
use App\Http\Controllers\User\VerifyEmailController;
use App\Http\Controllers\User\Channel\ShowFollowChannelController;

use App\Http\Controllers\Channel\FollowChannelController;
use App\Http\Controllers\Channel\LikeChannelController;
use App\Http\Controllers\Channel\ShowChannelController;
use App\Http\Controllers\Channel\ShowUserChannelController;
use App\Http\Controllers\Channel\CreateChannelController;
use App\Http\Controllers\Channel\UpdateChannelController;
use App\Http\Controllers\Channel\Board\ShowArticleController as ShowChannelArticleController;

use App\Http\Controllers\Team\CreateTeamController;
use App\Http\Controllers\Team\CheckTeamNameExistsController;
use App\Http\Controllers\Team\UpdateInformationController;
use App\Http\Controllers\Team\ShowTeamInfoController;
use App\Http\Controllers\Team\Member\InviteMemberController;
use App\Http\Controllers\Team\Member\KickController;
use App\Http\Controllers\Team\Board\ShowArticleController as ShowTeamArticleController;

use App\Http\Controllers\Game\FindTypeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!\
|
*/


Route::group(['prefix' => 'v1'], function (): void {

    /**
     * [BRACKETGG-56] 로그인
     */
    Route::post('auth', [UserVerifyController::class, 'verifyUser'])->name('verify');


    Route::group(['prefix' => 'email'], function (): void {

        /**
         * [BRACKETGG-55] 이메일 중복검사
         */
        Route::get('duplicate', [CheckEmailDuplicateController::class, 'getUserEmailDuplicate'])->name('checkEmailDuplicate');

        /**
         * [BRACKETGG-59] 이메일 인증
         */
        Route::get('verify/{id}/{hash}', [VerifyEmailController::class, 'verifyEmail'])->middleware('signed')->name('verifyEmail');
    });

    Route::group(['prefix' => 'user'], function (): void {

        /**
         * [BRACKETGG-54] 회원가입
         */
        Route::post('', [CreateUserController::class, 'createUser'])->name('createUser');

        /**
         * 현재 로그인 한 유저 정보
         */
        Route::get('', [ShowUserController::class, 'getCurrent'])->name('currentUser')
                                                                ->middleware('auth:sanctum');
    });

    Route::group(['prefix' => 'channel'], function (): void {

        /**
         * [BRACKETGG-70] 슬러그로 채널정보 조회
         */
        Route::get('slug/{slug}', [ShowChannelController::class, 'getChannelById'])->name('findChannelBySlug');

        /**
         * [BRACKETGG-69] 채널 이름으로 채널 정보 조회
         */
        Route::get('name/{name}', [ShowChannelController::class, 'getChannelById'])->name('findChannelByName');

        /**
         * [BRACKETGG-155] 채널 게시판에 특정 카테고리에 해당하는 게시글들
         */
        Route::get('{slug}/{channelBoardCategory}/articles', [ShowChannelArticleController::class, 'showArticleListByCategory'])->name('getChannelArticlesByCategory');

        /**
         * [BRACKETGG-177] 채널 게시판에 특정 카테고리에 해당하는 특정게시글
         */
        Route::get('{slug}/{channelBoardCategory}/article/{channelArticle}', [ShowChannelArticleController::class, 'showArticleByModel'])->name('getChannelArticle');
    });

    Route::group(['prefix' => 'team'], function (): void {

        /**
         * 팀 슬러그로 팀 정보 조회
         */
        Route::get('{teamSlug}', [ShowTeamInfoController::class, 'getInfo'])->name('getTeamInfoBySlug');

        /**
         * 팀 게시판에 특정 카테고리에 해당하는 게시글들
         */
        Route::get('{teamSlug}/{teamBoardCategory}/articles', [ShowTeamArticleController::class, 'showArticleListByCategory'])->name('getTeamArticlesByCategory');

        /**
         * [BRACKETGG-177] 팀 게시판에 특정 카테고리에 해당하는 특정게시글
         */
        Route::get('{teamSlug}/{teamBoardCategory}/article/{teamArticle}', [ShowTeamArticleController::class, 'showArticleByModel'])->name('getTeamArticle');
    });

    Route::group(['middleware' => ['auth:sanctum']], function (): void {
        Route::group(['prefix' => 'email'], function (): void {

            /**
             * [BRACKETGG-58] 이메일 인증 메일 재전송
             */
            Route::post('resend', [VerifyEmailController::class, 'resendEmail'])->name('resendVerifyEmail');
        });

        /**
         * 로그아웃
         */
        Route::post('logout', [UserLogoutController::class, 'logout'])->name('logoutUser');

        Route::group(['prefix' => 'channel'], function (): void {

            /**
             * 채널 생성
             */
            Route::post('', [CreateChannelController::class, 'createChannel'])->name('createChannel');

            /**
             * [BRACKETGG-65] 채널 정보 업데이트
             */
            Route::post('{slug}', [UpdateChannelController::class, 'updateChannelInfoWithOutImage'])->name('updateChannelInfo');

            /**
             * [BRACKETGG-66] 채널 배너 업데이트
             */
            Route::post('{slug}/update-banner', [UpdateChannelController::class, 'updateBannerImage'])->name('updateChannelBanner');

            /**
             * [BRACKETGG-67] 채널 로고 업데이트
             */
            Route::post('{slug}/update-logo', [UpdateChannelController::class, 'updateLogoImage'])->name('updateChannelLogo');

            /**
             * [BRACKETGG-61] 채널 팔로워 조회
             */
            Route::get('{slug}/followers', [ShowUserChannelController::class, 'getFollower'])->name('getFollower');

            /**
             * {user}가 채널장인 채널들 조회
             */
            Route::get('owner/{user}', [ShowUserChannelController::class, 'getChannelsByUserId'])->name('showChannelByOwnerId');

            /**
             * [BRACKETGG-72] {slug} 채널 팔로우 여부
             */
            Route::get('{slug}/isfollow', [FollowChannelController::class, 'isFollow'])->name('channelIsFollow');

            /**
             * [BRACKETGG-71] {slug} 채널 좋아요 여부
             */
            Route::get('{slug}/islike', [LikeChannelController::class, 'isLike'])->name('isLikeChannel');

            /**
             * [BRACKETGG-72] {slug} 채널 팔로우
             */
            Route::patch('{slug}/follow', [FollowChannelController::class, 'followChannel'])->name('followChannel');

            /**
             * [BRACKETGG-76] {slug} 채널 언팔로우
             */
            Route::patch('{slug}/unfollow', [FollowChannelController::class, 'unFollowChannel'])->name('unFollowChannel');

            /**
             * [BRACKETGG-73] {slug} 채널 좋아요
             */
            Route::patch('{slug}/like', [LikeChannelController::class, 'likeChannel'])->name('likeChannel');

            /**
             * [BRACKETGG-74] {slug} 채널 좋아요 취소
             */
            Route::patch('{slug}/unlike', [LikeChannelController::class, 'unLikeChannel'])->name('unLikeChannel');
        });

        Route::group(['prefix' => 'team'], function (): void {

            /**
             * 팀 생성
             */
            Route::post('', [CreateTeamController::class, 'createTeam'])->name('createTeam');

            /**
             * [BRACKETGG-77] 배너이미지를 제외한 팀 정보 업데이트
             */
            Route::post('{teamSlug}', [UpdateInformationController::class, 'updateInfo'])->name('updateTeamInfoWithoutImage');

            /**
             * 팀 이름 중복 검사
             */
            Route::get('{teamName}/exists', [CheckTeamNameExistsController::class, 'nameAlreadyExists'])->name('checkTeamNameDuplicate');

            /**
             * [BRACKETGG-98] 팀원 초대
             */
            Route::post('{teamSlug}/invite/{userIdx}', [InviteMemberController::class, 'sendInviteCard'])->name('inviteTeamMember');

            /**
             * [BRACKETGG-79] 팀 배너 업데이트
             */
            Route::post('{teamSlug}/update-banner', [UpdateInformationController::class, 'updateBannerImage'])->name('updateTeamBanner');

            /**
             * [BRACKETGG-78] 팀 로고 업데이트
             */
            Route::post('{teamSlug}/update-logo', [UpdateInformationController::class, 'updateLogoImage'])->name('updateTeamLogo');

            /**
             * [BRACKETGG-82]유저 인덱스로 유저가 가진 팀리스트 조회
             */
            Route::get('owner/{owner}', [ShowTeamInfoController::class, 'getTeamssByUserId'])->name('showTeamByOwnerId');

            /**
             * [BRACKETGG-128] 가입 신청 한 유저 리스트
             */
            Route::get('{teamSlug}/request-join-user', [ShowTeamInfoController::class, 'getRequestJoinUserList'])->name('getRequestJoinUserList');

            /**
             * [BRACKETGG-97] 팀원 리스트
             */
            Route::get('{teamSlug}/members', [ShowTeamInfoController::class, 'getTeamMemberList'])->name('getTeamMemberList');

            /**
             * [BRACKETGG-205] 현재 팀원인 유저 추방
             */
            Route::post('{teamSlug}/kick/{userIdx}', [KickController::class, 'kickByUserId'])->name('kickTeamMember');
        });

        Route::group(['prefix' => 'user'], function (): void {
            /**
             * [BRACKETGG-95] 팀원 초대 수락
             */
            Route::post('accept/team/{teamSlug}', [InviteMemberController::class, 'acceptInviteCard'])->name('acceptInvite');

            /**
             * [BRACKETGG-96] 팀원 초대 거절
             */
            Route::post('reject/team/{teamSlug}', [InviteMemberController::class, 'rejectInviteCard'])->name('rejectInvite');

            /**
             * [BRACKETGG-197] 유저가 팔로우 한 채널 리스트 조회
             */
            Route::get('channels', [ShowFollowChannelController::class, 'getFollowedChannelByUser'])->name('getFollowedChannel');
        });

        Route::get('game-types', [FindTypeController::class, 'getTypesByKeyword'])->name('getGameTypeByKeyword');
    });
});
