<?php

declare(strict_types=1);

namespace Zisato\EventSourcingBundle\Infrastructure\EventSourcing\Aggregate\Event\Upcast;

use Zisato\EventSourcing\Aggregate\Event\Upcast\UpcasterInterface;

final class EventUpcasterGroup
{
    private readonly string $name;

    /**
     * @var array<UpcasterInterface>
     */
    private array $upcasters = [];

    public function __construct(string $name, UpcasterInterface ...$upcasters)
    {
        $this->name = $name;
        $this->upcasters = $upcasters;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array<UpcasterInterface> $upcasters
     */
    public function upcasters(): array
    {
        return $this->upcasters;
    }
}
