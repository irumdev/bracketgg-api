#!/bin/bash
############################ util ############################
function init() {
    cd /var/www && \
    php artisan config:clear > /dev/null && \
    php artisan optimize:clear > /dev/null && \
    clear
}
function useFakeUrl() {
    sed -i "s/TEST_USE_REAL_IMAGE=true/TEST_USE_REAL_IMAGE=false/g" /var/www/.env && \
    init
}
function useRealImage() {
    sed -i "s/TEST_USE_REAL_IMAGE=false/TEST_USE_REAL_IMAGE=true/g" /var/www/.env && \
    init
}
############################ util ############################

function stepOne() {
    useFakeUrl && \
    echo "STEP 1/4 : TEST_USE_REAL_IMAGE=false / migrate:fresh" && \
    echo "now migrate..." && \
    php artisan migrate:fresh > /dev/null && \
    php artisan test
}

function stepTwo() {
    useFakeUrl && \
    echo " ✓ [PASS] STEP 1/4 : TEST_USE_REAL_IMAGE=false / migrate:fresh" && \
    echo "STEP - 2/4 : TEST_USE_REAL_IMAGE=false / migrate:fresh --seed" && \
    echo "now migrate..." && \
    php artisan migrate:fresh --seed > /dev/null && clear && \
    php artisan test
}

function stepThree() {
    useRealImage && \
    echo " ✓ [PASS] STEP 1/4 : TEST_USE_REAL_IMAGE=false / migrate:fresh" && \
    echo " ✓ [PASS] STEP 2/4 : TEST_USE_REAL_IMAGE=false / migrate:fresh --seed" && \
    echo "STEP 3/4 : TEST_USE_REAL_IMAGE=true / migrate:fresh" && \
    echo "now migrate..." && \
    php artisan migrate:fresh > /dev/null && \
    php artisan test
}

function stepFour() {
    useRealImage && \
    echo " ✓ [PASS] STEP 1/4 : TEST_USE_REAL_IMAGE=false / migrate:fresh" && \
    echo " ✓ [PASS] STEP 2/4 : TEST_USE_REAL_IMAGE=false / migrate:fresh --seed" && \
    echo " ✓ [PASS] STEP 3/4 : TEST_USE_REAL_IMAGE=true / migrate:fresh" && \
    echo "STEP 4/4 : TEST_USE_REAL_IMAGE=true / migrate:fresh --seed" && \
    echo "now migrate..." && \
    php artisan migrate:fresh --seed > /dev/null && \
    php artisan test
}

function completeInfo() {
    clear && \
    echo " ✓ [PASS] STEP 1/4 : TEST_USE_REAL_IMAGE=false / migrate:fresh" && \
    echo " ✓ [PASS] STEP 2/4 : TEST_USE_REAL_IMAGE=false / migrate:fresh --seed" && \
    echo " ✓ [PASS] STEP 3/4 : TEST_USE_REAL_IMAGE=true / migrate:fresh" && \
    echo " ✓ [PASS] STEP 4/4 : TEST_USE_REAL_IMAGE=true / migrate:fresh --seed"
}


function cleanUp() {
    cd /var/www &&
    rm -rf ./storage/app/channelBanners/*.jpg && \
    rm -rf ./storage/app/channelLogos/*.jpg && \
    rm -rf ./storage/app/profileImages/*.jpg && \
    rm -rf ./storage/app/teamBanners/*.jpg && \
    rm -rf ./storage/app/teamLogos/*.jpg && \
    rm -rf ./storage/app/channelBanners/*.png && \
    rm -rf ./storage/app/channelLogos/*.png && \
    rm -rf ./storage/app/profileImages/*.png && \
    rm -rf ./storage/app/teamBanners/*.png && \
    rm -rf ./storage/app/teamLogos/*.png && \
    php artisan optimize:clear > /dev/null && php artisan optimize > /dev/null && \
    sed -i "s/TEST_USE_REAL_IMAGE=true/TEST_USE_REAL_IMAGE=false/g" /var/www/.env
}

cleanUp && \
init && \
stepOne && \
stepTwo && \
stepThree && \
stepFour && \
cleanUp && \
completeInfo
