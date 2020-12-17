<?php

return [
    'GameType' => [
        'SearchTest' => [
            'failSearchItemWhenQueryIsEmpty' => '검색할 게임종목을 입력하지 않아 게임타입 조회에 실패하라',
            'failSearchItemWhenQueryIsNotFound' => '검색할 게임종목을 찾지못해 404를 리턴받아라',
            'successSearchGameTypes' => '게임종목 검색을 성공하라',
        ]
    ],
    'Channel' => [
        'LikeTest' => [
            'likeChannel' => '채널 좋아요 하라',
            'failLikeChannelWhenTryUserEmailIsNotVerified' => '이메일 인증받지 않은 유저가 채널 좋아요 실패하라',
            'failLikeChannelWhenChannelIsNotExists' => '없는 채널에 좋아요 실패하라',
            'failLikeChannelWhenTryUserAlreadyLike' => '이미 좋아요 중복에 실패하라',
        ],
        'FollowTest' => [
            'successFollowChannel' => '채널을 팔로우 하라',
            'ownerFailFollowChannelWhenFollowMyChannel' => '채널장이 내 채널을 팔로우에 실패 하라',
            'failFollowChannelWhenUserEmailIsNotVerified' => '이메일 인증 인받은 유저가 채널을 팔로우를 실패하라',
            'failFollowChannelWhelAlreadyChannelFollowed' => '이미 채널을 팔로우 했는데 또다시 팔로우 시도에 실패하라',
            'failFollowChannelWhenChannelNotExists' => '없는 채널 팔로우에 실패하라'
        ],
        'CreateTest' => [
            'failCreateChannelWithoutChannelName' => '이름을 안넣고 채널생성에 실패하라',
            'failCreateChannelWhenChannelNameIsLong' => '이름 최대길이 넘겨서 채널생성에 실패하라',
            'failCreateChannelWhenChannelNameIsDuplicate' => '이름이 중복되서 채널생성에 실패하라',
            'failCreateChannelWhenChannelCreateCountIsExceed' => '최대 채널 생성개수 제한에 의하여 채널생성에 실패하라',
            'successCreateChannel' => '채널 생성에 성공하라',
        ],

        'CheckFollowTest' => [
            'failFollowChannelWhenChannelIsNotExists' => '없는채널 팔로우 여부조회에 실패하라',
            'failLookupChannelIsFollowingWhenUserIsNotLogin' => '로그인 안한 유저가 팔로우 여부조회에 실패하라',
            'getTrueWhenChannelAlreadyFollow' => '팔로우 한 채널 조회에 true 리턴을 받아라',
            'getFalseWhenChannelUnFollow' => '팔로우 안한 채널 조회에 false 리턴을 받아라',
        ],

        'CheckLikeTest' => [
            'failLookUpChannelIsFanWhenChannelNotExists' => '없는 채널 좋아요여부 조회에 실패하라',
            'failLookUpChannelIsFanWhenUserisNotLogined' => '로그인 안한채로 좋아요 여부 조회에 시도에 실패 하라',
            'getTrueWhenLikeChannelAndLookupChannelIsLike' => '채널 좋아요 후 여부조회에 true 리턴을 받아라',
            'getFalseWhenUnLikeChannelAndLookupChannelIsLike' => '채널 좋아요 안한채로 여부조회에 false 리턴을 받아라',
        ],

        'ShowFollowerListTest' => [
            'failLookUpFollowersWhenUserIsNotLogin' => '로그인 안한채로 팔로워 조회에 실패하라',
            'failLookUpFollowersWhenChannelNotExists' => '존재하지 않는 채널의 팔로워 조화에 실패하라',
            'successLookupChannelFollowersButChannelDontHaveAnyFollower' => '팔로워가 한명도 없을때 조회에 성공하라',
            'successLookupChannelFollowersWithPaginate' => '많은 팔로워들 조회를 페이징 처리하는데 성공하라',
        ],

        'ShowInfoTest' => [
            'successLookupExistsChannelInfoFromSlug' => '슬러그로 존재하는 채널정보 조회를 성공하라',
            'successLookUpExistsChannelInfoFromName' => '채널이름으로 존재하는 채널정보 조회를 성공하라',
            'failLookUpNotExistsChannelInfoFromSlug' => '존재하지않는 슬러그로 채널정보 조회를 실패하라',
            'failLookUpNotExistsChannelInfoFromName' => '존재하지않는 채널이름으로 채널정보 조회를 실패하라',
        ],

        'UpdateInformationTest' => [
            'successUpdateChannelSlug' => '채널 슬러그 업데이트에 성공하라',
            'successUpdateChannelSlugWhenSlugLengthIsBoundaries' => '최소자리로 채널 슬러그 업데이트에 성공하라',
            'failUpdateChannelSlugWhenSlugIsTooShort' => '채널 슬러그 자리수 미달로 업데이트에 실패하라',
            'failUpdateChannelSlugWhenSlugIsTooLong' => '채널 슬러그 자리수 초과로 업데이트에 실패하라',
            'failUpdateChannelSlugWhenSlugPatterlIsWrong' => '채널 슬러그 패턴 불일치로 업데이트에 실패하라',
            'failUpdateChannelSlugWhenSlugIsNotUnique' => '채널 슬러그를 이미 사용하고 있어서 업데이트에 실패하라',
            'successUpdateChannelName' => '채널 이름 업데이트에 성공하라',
            'failUpdateChannelNameWhenChannelNameIsTooLong' => '채널 이름 최대자리 초과로 실패하라',
            'failUpdateChannelNameWhenChannelNameIsDuplicate' => '채널 이름 중복으로 실패하라',
            'successUpdateChannelDescription' => '채널 설명 업데이트에 성공하라',
            'failUpdateLogoImageWhenLogoImageIsNotImageFile' => '로고이미지 이미지 아닌거 올려서 채널정보 업데이트에 실패하라',
            'failUpdateLogoImageWhenLogoImageIsTooLarge' => '로고이미지 이미지 사진큰거 올려서 채널정보 업데이트에 실패하라',
            'successUpdateLogoImage' => '로고이미지 이미지 업데이트에 성공하라',
            'failUpdateBannerImageWhenBannerImageIsNotImageFile' => '배너이미지 이미지 아닌거 올려서 채널정보 업데이트에 실패하라',
            'failUpdateBannerImageWhenBannerImageIsTooLarge' => '배너이미지 이미지 사진큰거 올려서 채널정보 업데이트에 실패하라',
            'failUpdateBannerImageWhenUploadBannerImageButBannerImageIsEmpty' => '배너이미지 이미지 올랄때 배너 아이디 안올려서 채널정보 업데이트에 실패하라',
            'failUpdateBannerImageWhenBannerImageIdIsInvalid' => '배너이미지 이미지 올랄때 배너 아이디 이상한거 올려서 채널정보 업데이트에 실패하라',
            'successUpdateBannerImage' => '배너이미지 이미지 업데이트에 성공하라',
            'successCreateBannerImage' => '배너이미지 생성에 성공하라',
            'failCreateBannerImageWhenBannerAlreadyExists' => '이미 배너이미지가 존재하여 생성에 실패하라'
        ],

        'UnFollowTest' => [
            'successUnFollowChannel' => '팔로우를 취소하라',
            'failUnFolllowChannelWhenUserEmailIsNotVerified' => '이메일 인증안받은 유저가 언팔로우 실패하라',
            'failUnFolllowChannelWhenUserAlreadyUnfollowChannel' => '이미 언팔로우했는데 또 언팔로우시 실패하라',
        ],

        'UnLikeTest' => [
            'successUnLikeChannel' => '채널 좋아요 취소 하라',
            'failUnLikeChannelWhenChannelHasNotLikeUser' => '채널 좋아요 0인데 취소에 실패 하라',
            'failUnLikeChannelWhenUserEmailIsNotVerified' => '이메일 인증받지 않은 유저가 채널 좋아요 실패하라',
        ],
    ],
    'Team' => [
        'ShowInfoTest' => [
            'failLookupTeamInfoWhenTeamIsPrivateButUserIsNotLogin' => '로그인이 안되어 프라이빗 팀 조회에 실패하라',
            'failLookupTeamInfoWhenTeamIsPrivateButUseIsNotTeamMember' => '팀 멤버가 아닌데 프라이빗 팀 조회에 실패하라',
            'failLookupTeamInfoWhenTeamIsNotExists' => '없는 팀 조회에 실패하라',
            'successLookupPrivateTeamInfo' => '프라이빗 팀 조회에 성공하라',
            'successLookupPublicTeamInfo' => '팀 조회에 성공하라',
            'successLookupPublicTeamInfoWithoutLogin' => '로그인 안하고 팀 조회에 성공하라'
        ],
        'DuplicateNameCheckTest' => [
            'getTrueWhenTeamNameIsExists' => '팀 이름이 이미 존재하여 true 리턴 받는다',
            'getFalseWhenTeamNameIsNotExists' => '팀이름이 존재하지 않아 false 리턴 받는다',
        ],
        'CreateTest' => [
            'failCreatTeamWhenTeamNameIsNotUnique' => '팀 이름 중복으로 팀생성에 실패하라',
            'failCreatTeamWhenUserIsNotLogin' => '로그인 안한채로 팀 생성에 실패하라',
            'failCreatTeamWhenTeamNameIsLong' => '팀 이름이 20자를 초과하여 팀 생성에 실패하라',
            'failCreatTeamWhenTeamNameIsEmpty' => '팀 이름이 비어있어 팀 생성에 실패하라',
            'failCreatTeamWhenUserHasManyTeams' => '한 유저가 팀 을 3개 이상 가지고 있어 팀 생성에 실패하라',
            'successCreateTeam' => '팀 생성에 성공하라',
        ],
        'UpdateInformationTest' => [
            'failUpdateTeamSlugWhenSlugIsNotUnique' => '팀 슬러그가 중복되어 팀정보 변경에 실패하라',
            'failUpdateTeamSlugWhenSlugIsLong' => '팀 슬러그가 길어서(최대 16자리) 팀정보 변경에 실패하라',
            'failUpdateTeamSlugWhenSlugIsShort' => '팀 슬러그가 짧아서(최소 4자리) 팀정보 변경에 실패하라',
            'failUpdateTeamSlugWhenSlugPatternIsNotMatch' => '팀 슬러그가 패턴에 맞지 않아(첫글자에 영어 소문자 포함, 이후에는 엉여 대소문자, 숫자, - 포함) 팀정보 변경에 실패하라',
            'failUpdateTeamSlugWhenPublicStatusIsNotBoolean' => '팀 공개여부가 bool이 아니여서 팀정보 변경에 실패하라',
            'failUpdateTeamSlugWhenGameCategoryIsNotArray' => '팀 게임종목을 array로 안보내서 팀정보 변경에 실패하라',
            'failUpdateTeamSlugWhenGameCategoryItemIsLong' => '팀 게임종목이 길어서 팀정보 변경에 실패하라',
            'successUpdateAllItem' => '모든 팀정보 변경에 성공',
            'failUpdateBannerImageWhenTryAnotherTeam' => 'A채널이 B채널의 배너이미지 변경에 실패하라',
            'failCreateBannerWhenBannerIsNotFile' => '업로드한 배너파일이 다 업로드가 안되어 생성에 실패하라',
            'failCreateBannerWhenBannerIsNotImage' => '업로드한 배너파일이 이미지가 아니여서 생성에 실패하라',
            'failCreateBannerWhenBannerIsLarge' => '배너이미지가 용량이 최대(2048kb)를 넘어서 생성에 실패하라',
            'failCreateBannerWhenAlreadyHasBanner' => '이미 배너이미지가 있어서 생성에 실패하라',
            'successCreateBannerImage' => '배너이미지 생성에 성공하라',
            'failUpdateBannerImageWhenBannerIdIsInvalid' => '올바른 배너인덱스가 아니여서 배너 업데이트에 실패하라',
            'failUpdateBannerImageWhenBannerIdIsNotNumeric' => '배너인덱스가 숫자가 아니여서 배너 업데이트에 실패하라',
            'failUpdateBannerImageIsNotAttached' => '배너파일을 첨부하지 않아서 업데이트에 실패하라',
            'successUpdateBannerImage' => '배너 업데이트에 성공하라',
            'failUpdateLogoImageIsNotAttached' => '로고이미지를 첨부하지 않아 로고 업데이트에 실패하라',
            'failUpdateLogoImageIsNotFile' => '첨부한 로고이미지가 파일이 아니여서 로고 업데이트에 실패하라',
            'failUpdateLogoImageIsLarge' => '첨부한 로고이미지가 최대용량(2048kb)를 넘어서 로고 업데이트에 실패하라',
            'failUpdateWhenLogoImageMimeIsWrong' => '첨부한 로고이미지가 MIME가 올바르지 않아서 로고 업데이트에 실패하라',
            'successUpdateLogoImage' => '로고이미지가 업데이트에 성공하라',

        ]
    ],
    'User' => [
        'EmailDuplicateTest' => [
            'getFalseWhenEmailIsNotDuplicate' => '이메일이 중복되지않아 false 를 받아라',
            'getTrueWhenEmailIsDuplicate' => '이메일이 중복되어 true를 받아라'
        ],

        'CreateTest' => [
            'failRegisterUserWithOutAnyInfo' => '아무것도 안넣은채로 회원가입에 실패하라',
            'failRegisterUserWithoutEmail' => '올바른 이메일을 입력안한채 회원가입에 실패하라',
            'failRegisterUserWithDuplicateEmail' => '중복된 이메일로 회원기입 시도후 회원가입에 실패하라',
            'failRegisterUserWithoutNickName' => '이메일은 입력했지만 닉네임을 입력 안한채로 회원가입에 실패하라',
            'failRegisterUserWithEmptyNickName' => '닉네임이 한글자도 없는채로 회원가입에 실패하라',
            'failRegisterUserWithLargeNickName' => '닉네임이 12글자 이상 입력후 회원가입에 실패하라',
            'failRegisterUserWithOutPassword' => '닉네임과 이메일을 입력했지만 비밀번호를 없이 회원가입에 실패하라',
            'failRegisterUserWhenPasswordIsShort' => '닉네임과 이메일 비밀번호를 입력했지만 비밀번호를 8자리 미만입력하여 회원가입에 실패하라',
            'failRegisterUserWhenPasswordIsLong' => '닉네임과 이메일 비밀번호를 입력했지만 비밀번호를 30자리 초과입력하여 회원가입에 실패하라',
            'failRegisterUserWithOutPasswordReEnter' => '비밀번호 재입력을 입력하지않아서 회원가입에 실패하라',
            'failRegisterUserWhenPasswordeEnterIsToShort' => '비밀번호 재입력이 8자리 미만으로 입력하여 회원가입에 실패하라',
            'failRegisterUserWhenPasswordeEnterIsToLong' => '비밀번호 재입력이 30자리 초과로 입력하여 회원가입에 실패하라',
            'failRegisterUserWhenPassworReEnterIsNotEqualsPassword' => '비밀번호 재입력이 입력한 비밀번호와 달라서 회원가입에 실패하라',
            'failRegisterUserUnAgreePolicy' => '약관동의를 안해서 회원가입에 실패하라',
            'failRegisterUserWhebAgreePolicyValueIsInValud' => '약관동의에 이상한 값을 넣어서 회원가입에 실패하라',
            'failRegisterUserWhenPrivacyPolicyNotAgree' => '개인정보 처리방침에 동의하지 않아서 회원가입에 실패하라',
            'failRegisterUserWhenPrivacyPolicyValueInvalid' => '개인정보 처리방침에 이상한값 넣어서 회원가입에 실패하라',
            'failRegisterUserProfileImageIsNotImage' => '프로필사진에 사진 아닌거 올려서 회원가입에 실패하라',
            'failRegisterUserProfileImageIsLarge' => '프로필사진에 사진 2048kb보다 큰거 올려서 회원가입에 실패하라',
            'successRegisterUserWithoutProfileImage' => '프로필이미지 없이 회원가입에 성공하라',
            'successRegisterUserWithoutProfileImageWithSpecificEmail' => '프로필이미지 없이 특수한 이메일로 회원가입에 성공하라',
            'successRegisterUser' => '회원가입에 성공하라',
        ],

        'LoginTest' => [
            'failLoginWithoutEmail' => '이메일 입력안하고 로그인을 시도하라',
            'failLoginWithoutPassword' => '비밀번호 입력안하고 로그인을 시도하라',
            'failLoginWithInvalidEmail' => '올바르지 않은 이메일 입력하고 로그인을 시도하라',
            'failLoginWithWrongPassword' => '비밀번호를 틀리고 로그인 시도하라',
            'failLoginWithNotExistsEmail' => '없는 이메일로 로그인 시도하라',
            'successLogin' => '로그인 성공하라',
            'successLoginWithUndefinedProfileUser' => '프로필 이미지가 없는 유저의 로그인 성공하라',
        ],

        'ShowInfoByTokenTest' => [
            'successLookUpUserInfoWithBearerToken' => '토큰으로 유저의 정보를 조회하라',
            'successLookUpDonthaveProfileImageUserInfoWithBearerToken' => '토큰으로 프로필이미지가 없는 유저의 정보를 조회하라',
            'failLookUpUserInfoWithOutLogin' => '로그인 안한 유저의 정보조회에 실패하라',
        ],

        'ReSendVerifyEmail' => [
            'successSendVerifyEmail' => '이메일 재발송에 성공하라',
            'failSendVerifyEmailWhenAlreadyUserVerifyEmail' => '이미 인증한 유저가 이메일 요청에 실패하라',
        ],

        'ShowChannelInfoByOwnerTest' => [
            'successLookUpChannelWhenChannelHasBannerImageAndFollowers' => '유저가 가진 채널중 배너이미지와 팔로워가 있는 채널들의 정보를 조회하라',
            'successLookUpChannelWhenChannelHasBannerImageAndFollowersAndBroadcastAddress' => '유저가 가진 채널중 배너이미지와 팔로워 그리고 방송국주소를 가진 채널들의 정보를 조회하라',
            'successLookUpChannelWhenChannelHasBannerImage' => '유저가 가진 채널중 배너이미지만 있는 채널들의 정보를 조회하라',
            'failLookUpChannelWhenUserDontHaveChannel' => '유저가 채널이 없을때 조회에 실패하라'
        ],

        'VerifyEmailTest' => [
            'successVerifyEmail' => '이메일 인증에 성공하라',
            'failVerifyEmailWhenTimeOut' => '제한시간 초과로 이메일 인증에 실패하라',
            'failVerifyEmailWhenUserIsNotExists' => '존재하지 않는 유저로 이메일 인증에 실패하라',
            'failVerifyEmailWhenUserAlreadyVerifyEmail' => '이미 이메일 인증한 유저가 이메일 인증에 실패하라',
        ],
    ],
];
