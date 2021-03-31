<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\User\UserLogoutController;
use App\Http\Controllers\User\CheckEmailDuplicateController;
use App\Http\Controllers\User\CreateUserController;
use App\Http\Controllers\User\UpdateUserController;
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
use App\Http\Controllers\Channel\Board\Category\ChangeStatusController as ChangeChannelCategoryStatusController;
use App\Http\Controllers\Channel\Board\UploadArticleController as ChannelBoardArticleUploadController;

use App\Http\Controllers\Team\CreateTeamController;
use App\Http\Controllers\Team\CheckTeamNameExistsController;
use App\Http\Controllers\Team\UpdateInformationController;
use App\Http\Controllers\Team\ShowTeamInfoController;
use App\Http\Controllers\Team\Member\InviteMemberController;
use App\Http\Controllers\Team\Member\KickController;
use App\Http\Controllers\Team\Board\ShowArticleController as ShowTeamArticleController;
use App\Http\Controllers\Team\Board\Category\ChangeStatusController as ChangeTeamCategoryStatusController;
use App\Http\Controllers\Team\Board\UploadArticleController as TeamBoardArticleUploadController;

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
    Route::post('auth', [UserVerifyController::class, 'verifyUser'])->name('user.auth');


    Route::group(['prefix' => 'email'], function (): void {

        /**
         * [BRACKETGG-55] 이메일 중복검사
         */
        Route::get('duplicate', [CheckEmailDuplicateController::class, 'getUserEmailDuplicate'])->name('user.checkEmailDuplicate');

        /**
         * [BRACKETGG-59] 이메일 인증
         */
        Route::get('verify/{id}/{hash}', [VerifyEmailController::class, 'verifyEmail'])->middleware('signed')->name('user.verifyEmail');
    });

    Route::group(['prefix' => 'user'], function (): void {

        /**
         * [BRACKETGG-54] 회원가입
         */
        Route::post('', [CreateUserController::class, 'createUser'])->name('user.create');
    });

    Route::group(['prefix' => 'channel'], function (): void {

        /**
         * [BRACKETGG-70] 슬러그로 채널정보 조회
         */
        Route::get('slug/{slug}', [ShowChannelController::class, 'getChannelById'])->name('channel.findBySlug');

        /**
         * [BRACKETGG-69] 채널 이름으로 채널 정보 조회
         */
        Route::get('name/{name}', [ShowChannelController::class, 'getChannelById'])->name('channel.findByName');

        /**
         * [BRACKETGG-155] 채널 게시판에 특정 카테고리에 해당하는 게시글들
         */
        Route::get('{slug}/{channelBoardCategory}/articles', [ShowChannelArticleController::class, 'showArticleListByCategory'])->name('channel.getArticlesByCategory');

        /**
         * [BRACKETGG-177] 채널 게시판에 특정 카테고리에 해당하는 특정게시글
         */
        Route::get('{slug}/{channelBoardCategory}/article/{channelArticle}', [ShowChannelArticleController::class, 'showArticleByModel'])->name('channel.getArticleById');
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
        Route::group(['prefix' => 'user'], function (): void {

            /**
             * 현재 로그인 한 유저 정보
             */
            Route::get('', [ShowUserController::class, 'getCurrent'])->name('user.current');

            /**
             * [BRACKETGG-221] 유저 비밀번호 변경
             */
            Route::post('password', [UpdateUserController::class, 'updatePassword'])->name('user.updateUserPassword');

            /**
             * [BRACKETGG-196] 나의계정/프로필 사진 변경 API
             */
            Route::post('image', [UpdateUserController::class, 'updateProfileImage'])->name('user.updateUserProfileImage');
        });

        Route::group(['prefix' => 'email'], function (): void {

            /**
             * [BRACKETGG-58] 이메일 인증 메일 재전송
             */
            Route::post('resend', [VerifyEmailController::class, 'resendEmail'])->name('user.resendVerifyEmail');
        });

        /**
         * 로그아웃
         */
        Route::post('logout', [UserLogoutController::class, 'logout'])->name('user.logout');

        Route::group(['prefix' => 'channel'], function (): void {

            /**
             * 채널 생성
             */
            Route::post('', [CreateChannelController::class, 'createChannel'])->name('channel.create');

            /**
             * [BRACKETGG-65] 채널 정보 업데이트
             */
            Route::post('{slug}', [UpdateChannelController::class, 'updateChannelInfoWithOutImage'])->name('channel.updateInfo');

            /**
             * [BRACKETGG-66] 채널 배너 업데이트
             */
            Route::post('{slug}/update-banner', [UpdateChannelController::class, 'updateBannerImage'])->name('channel.updateBanner');

            /**
             * [BRACKETGG-67] 채널 로고 업데이트
             */
            Route::post('{slug}/update-logo', [UpdateChannelController::class, 'updateLogoImage'])->name('channel.updateLogo');

            /**
             * [BRACKETGG-61] 채널 팔로워 조회
             */
            Route::get('{slug}/followers', [ShowUserChannelController::class, 'getFollower'])->name('channel.getFollowers');

            /**
             * [BRACKETGG-223] 채널 게시판 카테고리 변경
             */
            Route::post('{slug}/category', [ChangeChannelCategoryStatusController::class, 'changeChannelCategory'])->name('channel.changeCategory');

            /**
             * {user}가 채널장인 채널들 조회
             */
            Route::get('owner/{user}', [ShowUserChannelController::class, 'getChannelsByUserId'])->name('channel.getByOwnerId');

            /**
             * [BRACKETGG-72] {slug} 채널 팔로우 여부
             */
            Route::get('{slug}/isfollow', [FollowChannelController::class, 'isFollow'])->name('channel.isFollow');

            /**
             * [BRACKETGG-71] {slug} 채널 좋아요 여부
             */
            Route::get('{slug}/islike', [LikeChannelController::class, 'isLike'])->name('channel.isLike');

            /**
             * [BRACKETGG-72] {slug} 채널 팔로우
             */
            Route::patch('{slug}/follow', [FollowChannelController::class, 'followChannel'])->name('channel.follow');

            /**
             * [BRACKETGG-76] {slug} 채널 언팔로우
             */
            Route::patch('{slug}/unfollow', [FollowChannelController::class, 'unFollowChannel'])->name('channel.unFollow');

            /**
             * [BRACKETGG-73] {slug} 채널 좋아요
             */
            Route::patch('{slug}/like', [LikeChannelController::class, 'likeChannel'])->name('channel.like');

            /**
             * [BRACKETGG-74] {slug} 채널 좋아요 취소
             */
            Route::patch('{slug}/unlike', [LikeChannelController::class, 'unLikeChannel'])->name('channel.unLike');

            Route::group(['prefix' => '{slug}/{channelBoardCategory}/article'], function (): void {
                /**
                 * [BRACKETGG-216] 채널, 팀 게시글 업로드
                 */
                Route::post('', [ChannelBoardArticleUploadController::class, 'uploadChannelArticle'])->name('channel.article.upload.article');

                /**
                 * [BRACKETGG_215] 채널, 팀 게시글 이미지 업로드
                 */
                Route::post('image', [ChannelBoardArticleUploadController::class, 'uploadArticleImage'])->name('channel.article.upload.image');
            });
        });

        Route::group(['prefix' => 'team'], function (): void {

            /**
             * 팀 생성
             */
            Route::post('', [CreateTeamController::class, 'createTeam'])->name('team.create');

            /**
             * [BRACKETGG-77] 배너이미지를 제외한 팀 정보 업데이트
             */
            Route::post('{teamSlug}', [UpdateInformationController::class, 'updateInfo'])->name('team.updateInfo');

            /**
             * [BRACKETGG-79] 팀 배너 업데이트
             */
            Route::post('{teamSlug}/update-banner', [UpdateInformationController::class, 'updateBannerImage'])->name('team.updateBanner');

            /**
             * [BRACKETGG-78] 팀 로고 업데이트
             */
            Route::post('{teamSlug}/update-logo', [UpdateInformationController::class, 'updateLogoImage'])->name('team.updateLogo');
            /**
             * 팀 이름 중복 검사
             */
            Route::get('{teamName}/exists', [CheckTeamNameExistsController::class, 'nameAlreadyExists'])->name('team.name.isDuplicate');

            /**
             * [BRACKETGG-98] 팀원 초대
             */
            Route::post('{teamSlug}/invite/{userIdx}', [InviteMemberController::class, 'sendInviteCard'])->name('team.inviteMember');

            /**
             * [BRACKETGG-82]유저 인덱스로 유저가 가진 팀리스트 조회
             */
            Route::get('owner/{owner}', [ShowTeamInfoController::class, 'getTeamssByUserId'])->name('team.showInfoByOwnerId');

            /**
             * [BRACKETGG-128] 가입 신청 한 유저 리스트
             */
            Route::get('{teamSlug}/request-join-user', [ShowTeamInfoController::class, 'getRequestJoinUserList'])->name('team.getRequestJoinUserList');

            /**
             * [BRACKETGG-97] 팀원 리스트
             */
            Route::get('{teamSlug}/members', [ShowTeamInfoController::class, 'getTeamMemberList'])->name('team.getMembers');

            /**
             * [BRACKETGG-205] 현재 팀원인 유저 추방
             */
            Route::post('{teamSlug}/kick/{userIdx}', [KickController::class, 'kickByUserId'])->name('team.kickMember');

            /**
             * [BRACKETGG-202] 팀 게시판 카테고리 변경
             */
            Route::post('{teamSlug}/category', [ChangeTeamCategoryStatusController::class, 'changeTeamCategory'])->name('team.changeCategory');

            Route::group(['prefix' => '{teamSlug}/{teamBoardCategory}/article'], function (): void {
                /**
                 * [BRACKETGG-216] 채널, 팀 게시글 업로드
                 */
                Route::post('', [TeamBoardArticleUploadController::class, 'uploadTeamArticle'])->name('team.article.upload.article');

                /**
                 * [BRACKETGG_215] 채널, 팀 게시글 이미지 업로드
                 */
                Route::post('image', [TeamBoardArticleUploadController::class, 'uploadArticleImage'])->name('team.article.upload.image');
            });
        });

        Route::group(['prefix' => 'user'], function (): void {
            /**
             * [BRACKETGG-95] 팀원 초대 수락
             */
            Route::post('accept/team/{teamSlug}', [InviteMemberController::class, 'acceptInviteCard'])->name('user.acceptTeamInvite');

            /**
             * [BRACKETGG-96] 팀원 초대 거절
             */
            Route::post('reject/team/{teamSlug}', [InviteMemberController::class, 'rejectInviteCard'])->name('user.rejectTeamInvite');

            /**
             * [BRACKETGG-197] 유저가 팔로우 한 채널 리스트 조회
             */
            Route::get('channels', [ShowFollowChannelController::class, 'getFollowedChannelByUser'])->name('user.getFollowedChannel');
        });

        Route::get('game-types', [FindTypeController::class, 'getTypesByKeyword'])->name('gameTypes.getByKeyword');
    });
});
