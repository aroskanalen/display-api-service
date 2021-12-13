<?php

namespace App\Feed;

use App\Event\GetFeedTypesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractFeedType implements FeedTypeInterface, EventSubscriberInterface
{
    public function getFeedType(): ?string
    {
        return $this::class;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GetFeedTypesEvent::NAME => 'onGetFeedTypes',
        ];
    }

    public function onGetFeedTypes(GetFeedTypesEvent $event): GetFeedTypesEvent
    {
        $event->addFeedType($this->getFeedType());

        return $event;
    }
}
