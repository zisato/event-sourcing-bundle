<?php

declare(strict_types=1);

namespace Zisato\EventSourcingBundle\DependencyInjection\Compiler;

use Doctrine\DBAL\Connection;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Zisato\EventSourcing\Aggregate\Event\AbstractEvent;
use Zisato\EventSourcing\Aggregate\Event\Bus\EventBusInterface;
use Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorInterface;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventServiceInterface;
use Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\Serializer\PrivateDataEventSerializer;
use Zisato\EventSourcing\Aggregate\Event\Serializer\UpcasterEventSerializer;
use Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepository;
use Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepositoryWithSnapshot;
use Zisato\EventSourcing\Aggregate\Snapshot\SnapshotterInterface;
use Zisato\EventSourcing\Infrastructure\Aggregate\Event\Store\DBALEventStore;
use Zisato\EventSourcingBundle\Infrastructure\EventSourcing\Aggregate\Event\Upcast\EventUpcasterGroup;
use Zisato\EventSourcingBundle\Infrastructure\EventSourcing\Aggregate\Event\Upcast\EventUpcasterGroupCollection;
use Zisato\EventSourcingBundle\Infrastructure\EventSourcing\Aggregate\Event\Upcast\EventUpcasterGroupCollectionChain;

final class AggregateConfigDefinitionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $aggregates = $container->getParameter('event_sourcing_bundle.aggregates');

        foreach ($aggregates as $aggregateConfig) {
            if (! $container->hasDefinition($aggregateConfig['repository'])) {
                continue;
            }

            $this->createEventSerializer($aggregateConfig, $container);
            $this->createEventStore($aggregateConfig, $container);
            $this->createRepository($aggregateConfig, $container);
        }
    }

    private function createEventSerializer(array $config, ContainerBuilder $container): void
    {
        $eventSerializerDefinition = new ChildDefinition(EventSerializerInterface::class);

        if (count($config['upcasters']) > 0) {
            $eventSerializerDefinition = $this->createUpcasterEventSerializer(
                $eventSerializerDefinition,
                $config['upcasters']
            );
        }

        if ($config['private_data']) {
            $eventSerializerDefinition = $this->createPrivateDataPayloadEventSerializer(
                $eventSerializerDefinition
            );
        }

        $container->setDefinition(
            \sprintf('event_sourcing_bundle.event_serializer.%s', $config['class']),
            $eventSerializerDefinition
        );
    }

    private function createEventStore(array $config, ContainerBuilder $container): void
    {
        $eventStoreDefinition = new Definition(DBALEventStore::class);
        $eventStoreDefinition
            ->setArguments(
                [
                    new Reference(Connection::class),
                    new Reference(\sprintf('event_sourcing_bundle.event_serializer.%s', $config['class'])),
                ]
            );

        $container->setDefinition(\sprintf('event_sourcing_bundle.event_store.%s', $config['class']), $eventStoreDefinition);
    }

    private function createRepository(array $config, ContainerBuilder $container): void
    {
        $repositoryReflection = $container->getReflectionClass($config['repository']);

        if ($repositoryReflection->isSubclassOf(AggregateRootRepository::class)) {
            $repositoryDefinition = $container->findDefinition($config['repository']);
            $eventBus = $container->hasDefinition(EventBusInterface::class) ? new Reference(EventBusInterface::class) : null;
            $eventDecorator = $container->hasDefinition(EventDecoratorInterface::class) ? new Reference(EventDecoratorInterface::class) : null;

            $repositoryDefinition->setArguments([
                $config['class'],
                new Reference(\sprintf('event_sourcing_bundle.event_store.%s', $config['class'])),
                $eventDecorator,
                $eventBus,
            ]);
        }

        if ($repositoryReflection->isSubclassOf(AggregateRootRepositoryWithSnapshot::class)) {
            $repositoryDefinition = new Definition(AggregateRootRepository::class);
            $eventBus = $container->hasDefinition(EventBusInterface::class) ? new Reference(EventBusInterface::class) : null;
            $eventDecorator = $container->hasDefinition(EventDecoratorInterface::class) ? new Reference(EventDecoratorInterface::class) : null;

            $repositoryDefinition->setArguments([
                $config['class'],
                new Reference(\sprintf('event_sourcing_bundle.event_store.%s', $config['class'])),
                $eventDecorator,
                $eventBus,
            ]);

            $repositoryWithSnapshotDefinition = $container->findDefinition($config['repository']);
            $repositoryWithSnapshotDefinition->setArguments([
                $repositoryDefinition,
                new Reference(\sprintf('event_sourcing_bundle.event_store.%s', $config['class'])),
                new Reference(SnapshotterInterface::class),
            ]);
        }
    }

    private function createUpcasterEventSerializer(Definition $eventSerializerDefinition, array $upcasters): Definition
    {
        $upcastersGroupedByEventName = [];

        foreach ($upcasters as $id) {
            $reflection = new \ReflectionClass($id);

            $method = $reflection->getMethod('upcast');
            if (!$method->getReturnType() instanceof ReflectionNamedType) {
                continue;
            }

            $returnTypeName = $method->getReturnType()->getName();

            if ($returnTypeName === AbstractEvent::class) {
                continue;
            }

            if (($reflection = new \ReflectionClass($returnTypeName))->isAbstract()) {
                continue;
            }

            if (!$reflection->implementsInterface(EventInterface::class)) {
                continue;
            }

            $upcastersGroupedByEventName[$returnTypeName][] = new Reference($id);
        }

        $upcasterGroups = [];
        foreach ($upcastersGroupedByEventName as $eventName => $upcasters) {
            $upcasterGroups[] = new Definition(EventUpcasterGroup::class, [$eventName, ...$upcasters]);
        }

        $upcasterChain = new Definition(EventUpcasterGroupCollectionChain::class, [
            new Definition(EventUpcasterGroupCollection::class, $upcasterGroups),
        ]);

        return new Definition(UpcasterEventSerializer::class, [$eventSerializerDefinition, $upcasterChain]);
    }

    private function createPrivateDataPayloadEventSerializer(Definition $eventSerializerDefinition): Definition
    {
        return new Definition(PrivateDataEventSerializer::class, [
            $eventSerializerDefinition,
            new Reference(PrivateDataEventServiceInterface::class)
        ]);
    }
}
