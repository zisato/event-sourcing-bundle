<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Zisato\EventSourcing\Aggregate\Serializer\AggregateRootSerializerInterface;
use Zisato\EventSourcing\Aggregate\Serializer\ReflectionAggregateRootSerializer;
use Zisato\EventSourcing\Aggregate\Snapshot\Service\SnapshotServiceInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Service\SynchronousSnapshotService;
use Zisato\EventSourcing\Aggregate\Snapshot\Snapshotter;
use Zisato\EventSourcing\Aggregate\Snapshot\SnapshotterInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Store\SnapshotStoreInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Strategy\AggregateRootVersionSnapshotStrategy;
use Zisato\EventSourcing\Aggregate\Snapshot\Strategy\SnapshotStrategyInterface;
use Zisato\EventSourcing\Infrastructure\Aggregate\Snapshot\Store\DBALSnapshotStore;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set(AggregateRootVersionSnapshotStrategy::class, AggregateRootVersionSnapshotStrategy::class)

        ->set(SynchronousSnapshotService::class, SynchronousSnapshotService::class)
        ->args([service(SnapshotStoreInterface::class)])

        ->set(ReflectionAggregateRootSerializer::class, ReflectionAggregateRootSerializer::class)
        ->alias(AggregateRootSerializerInterface::class, ReflectionAggregateRootSerializer::class)

        ->set(Snapshotter::class, Snapshotter::class)
        ->args([
            service(SnapshotStoreInterface::class),
            service(SnapshotStrategyInterface::class),
            service(SnapshotServiceInterface::class),
        ])
        ->alias(SnapshotterInterface::class, Snapshotter::class)

        ->set(DBALSnapshotStore::class, DBALSnapshotStore::class)
        ->args([service(Connection::class), service(AggregateRootSerializerInterface::class)])
        ->alias(SnapshotStoreInterface::class, DBALSnapshotStore::class)
    ;
};
