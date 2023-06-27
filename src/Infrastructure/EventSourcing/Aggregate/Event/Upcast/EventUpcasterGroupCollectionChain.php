<?php

namespace Zisato\EventSourcingBundle\Infrastructure\EventSourcing\Aggregate\Event\Upcast;

use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\Upcast\UpcasterInterface;

final class EventUpcasterGroupCollectionChain implements UpcasterInterface
{
    public function __construct(private readonly EventUpcasterGroupCollection $eventUpcasterGroupCollection) {}

    public function canUpcast(EventInterface $event): bool
    {
        return $this->eventUpcasterGroupCollection->exists(\get_class($event));
    }

    public function upcast(EventInterface $event): EventInterface
    {
        $upcasters = $this->eventUpcasterGroupCollection->get(\get_class($event)) ?? [];

        foreach ($upcasters as $upcaster) {
            if ($upcaster->canUpcast($event)) {
                $event = $upcaster->upcast($event);
            }
        }

        return $event;
    }
}
