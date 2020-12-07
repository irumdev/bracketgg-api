<?php

declare(strict_types=1);

namespace App\Helpers\Macros;

/**
 * Arr 헬퍼에 메소드를 추가하기 위한 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ArrayMixin
{
    /**
     * 원하는 키가 들어있는곳에 새로운 키로 치환하는 메소드입니다.
     *
     * @param array $target 바꾸어야 할 배열
     * @param string $oldKey 치환 타겟 키
     * @param string $newKey 차환 할 키
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return callable 실행할 콜백
     */
    public function changeKey(): callable
    {
        return function (array $target, string $oldKey, string $newKey) {
            $tmp = $target[$oldKey];
            $target[$newKey] = $tmp;
            unset($target[$oldKey]);
            return $target;
        };
    }

    /**
     * 배열의 키값을 가지고 새로운 아이템을 넣어주는 메소드 입니다.
     *
     * @param array $items 치환 대상 배열
     * @param mixed $key 치환하기 위해 필요한 키
     * @param mixed $item 치환할 값
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return callable 실행할 콜백
     */
    public function replaceItemByKey(): callable
    {
        return function (array $items, $key, $item) {
            $items[$key] = $item;
            return $items;
        };
    }
}
