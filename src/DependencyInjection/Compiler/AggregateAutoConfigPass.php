<?php

declare(strict_types=1);

namespace Zisato\EventSourcingBundle\DependencyInjection\Compiler;

use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zisato\EventSourcingBundle\DependencyInjection\EventSourcingExtension;
use Zisato\EventSourcingBundle\DependencyInjection\Resolver\AggregateRootResolver;
use Zisato\EventSourcingBundle\DependencyInjection\Resolver\EventUpcasterResolver;
use Zisato\EventSourcingBundle\DependencyInjection\Resolver\PrivateDataResolver;

final class AggregateAutoConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $repositoryServiceIds = array_keys(
            $container->findTaggedServiceIds(EventSourcingExtension::TAG_AGGREGATE_ROOT_REPOSITORY, true)
        );

        $aggregates = [];
        $aggregateRootResolver = new AggregateRootResolver($container);
        $eventUpcasterResolver = new EventUpcasterResolver($container);
        $privateDataResolver = new PrivateDataResolver($container);

        foreach ($repositoryServiceIds as $repositoryServiceId) {
            $aggregateRootReflection = $aggregateRootResolver->aggregateRoot($repositoryServiceId);

            if (! $aggregateRootReflection instanceof ReflectionClass) {
                continue;
            }

            $aggregates[] = [
                'class' => $aggregateRootReflection->getName(),
                'repository' => $repositoryServiceId,
                'upcasters' => $eventUpcasterResolver->eventUpcasters($aggregateRootReflection),
                'private_data' => $privateDataResolver->privateData($aggregateRootReflection),
            ];
        }

        $container->setParameter('event_sourcing_bundle.aggregates', $aggregates);
    }
}
