<?php

declare(strict_types=1);

namespace Zisato\EventSourcingBundle\Infrastructure\EventSourcing\Aggregate\Event\Upcast;

use Zisato\EventSourcing\Aggregate\Event\Upcast\UpcasterInterface;

final class EventUpcasterGroupCollection
{
    /**
     * @var array<string, array<UpcasterInterface>>
     */
    private array $eventUpcasterGroups = [];

    public function __construct(EventUpcasterGroup ...$eventUpcasterGroups)
    {
        foreach ($eventUpcasterGroups as $eventUpcasterGroup) {
            $this->eventUpcasterGroups[$eventUpcasterGroup->name()] = $eventUpcasterGroup->upcasters();
        }
    }

    public function exists(string $name): bool
    {
        return isset($this->eventUpcasterGroups[$name]);
    }

    /**
     * @return null|array<UpcasterInterface> $upcasters
     */
    public function get(string $name): ?array
    {
        return $this->eventUpcasterGroups[$name] ?? null;
    }
}
