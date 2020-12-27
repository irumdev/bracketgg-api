<?php

declare(strict_types=1);

namespace App\Factories\Update;

use App\Factories\Update\Image\ChannelUpdateFacotry;
use App\Factories\Update\Image\TeamUpdateFactory;
use App\Wrappers\UpdateImageTypeWrapper;

class ImageUpdateFactory
{
    private UpdateImageTypeWrapper $updateType;
    private ChannelUpdateFacotry $channelUpdateFactory;
    private TeamUpdateFactory $teamUpdateFactory;

    public function __construct(UpdateImageTypeWrapper $updateType, array $attribute)
    {
        $this->updateType = $updateType;
        $this->channelUpdateFactory = new ChannelUpdateFacotry(
            $this->updateType->type,
            $attribute
        );

        $this->teamUpdateFactory = new TeamUpdateFactory(
            $this->updateType->type,
            $attribute
        );
    }

    public function update(): bool
    {
        switch ($this->updateType->target) {
            case 'channel':
                return $this->channelUpdateFactory->updateImage();

            case 'team':
                return $this->teamUpdateFactory->updateImage();
        }
    }

    public function create(): bool
    {
        switch ($this->updateType->target) {
            case 'channel':
                return $this->channelUpdateFactory->createImage();

            case 'team':
                return $this->teamUpdateFactory->createImage();
        }
    }
}
