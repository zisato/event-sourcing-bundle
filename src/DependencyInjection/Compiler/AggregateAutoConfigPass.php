<?php

declare(strict_types=1);

namespace Zisato\EventSourcingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zisato\EventSourcingBundle\DependencyInjection\EventSourcingExtension;
use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\PrivateDataPayloadInterface;

final class AggregateAutoConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $repositoryServiceIds = array_keys(
            $container->findTaggedServiceIds(EventSourcingExtension::TAG_AGGREGATE_ROOT_REPOSITORY, true)
        );
        $upcastersGroupedByEventName = $this->upcastersGroupedByEventName($container);

        $aggregates = [];

        foreach ($repositoryServiceIds as $repositoryServiceId) {
            $repositoryReflection = $container->getReflectionClass($repositoryServiceId);
            if ($repositoryReflection === null) {
                continue;
            }

            $returnType = $repositoryReflection->getMethod('get')
                ->getReturnType();
            if ($returnType === null) {
                continue;
            }

            $aggregateClass = $returnType->getName();
            $aggregateReflection = $container->getReflectionClass($aggregateClass);
            if ($aggregateReflection === null) {
                continue;
            }

            if ($this->isValidAggregateRoot($aggregateReflection)) {
                $upcasters = $this->aggregateRootEventUpcasters($aggregateReflection, $upcastersGroupedByEventName);
                $privateData = $this->aggregateRootPrivateData($aggregateReflection);

                $aggregates[] = [
                    'class' => $aggregateClass,
                    'repository' => $repositoryServiceId,
                    'upcasters' => $upcasters,
                    'private_data' => $privateData,
                ];
            }
        }

        $container->setParameter('event_sourcing_bundle.aggregates', $aggregates);
    }

    private function upcastersGroupedByEventName(ContainerBuilder $container): array
    {
        $upcasterServiceIds = array_keys($container->findTaggedServiceIds(EventSourcingExtension::TAG_EVENT_UPCASTER, true));
        $upcasterGroups = [];

        foreach ($upcasterServiceIds as $id) {
            $reflection = $container->getReflectionClass($id);

            $method = $reflection->getMethod('upcast');
            $returnTypeName = $method->getReturnType()
                ->getName();

            if ($returnTypeName === EventInterface::class) {
                continue;
            }

            if (($reflection = $container->getReflectionClass($returnTypeName))
                ->isAbstract()) {
                continue;
            }

            if (!$reflection->implementsInterface(EventInterface::class)) {
                continue;
            }

            $upcasterGroups[$returnTypeName][] = $id;
        }

        return $upcasterGroups;
    }

    private function isValidAggregateRoot(\ReflectionClass $aggregateReflection): bool
    {
        if (!$aggregateReflection->implementsInterface(AggregateRootInterface::class)) {
            return false;
        }

        return ! $aggregateReflection->isAbstract();
    }

    private function aggregateRootEventUpcasters(
        \ReflectionClass $aggregateReflection,
        array $upcastersGroupedByEventName
    ): array {
        $upcasters = [];

        foreach ($aggregateReflection->getMethods() as $method) {
            if ($this->aggregateRootHasApplyEventMethod($method)) {
                $eventName = $method->getParameters()[0]
                    ->getType()
                    ->getName();

                $upcasters = array_merge($upcasters, $upcastersGroupedByEventName[$eventName] ?? []);
            }
        }

        return $upcasters;
    }

    private function aggregateRootPrivateData(\ReflectionClass $aggregateReflection): bool
    {
        $privateData = false;

        foreach ($aggregateReflection->getMethods() as $method) {
            if ($this->aggregateRootHasApplyEventMethod($method)) {
                $eventReflection = new \ReflectionClass($method->getParameters()[0]->getType()->getName());

                $privateData = $privateData || $eventReflection->implementsInterface(PrivateDataPayloadInterface::class);
            }
        }

        return $privateData;
    }

    private function aggregateRootHasApplyEventMethod(\ReflectionMethod $method): bool
    {
        $prefix = 'apply';
        $methodName = $method->getName();

        if (substr($methodName, 0, strlen($prefix)) !== $prefix) {
            return false;
        }

        if (strlen($methodName) <= strlen($prefix)) {
            return false;
        }

        if ($method->getNumberOfParameters() !== 1) {
            return false;
        }

        if (($r = new \ReflectionClass($method->getParameters()[0]->getType()->getName()))->isAbstract()) {
            return false;
        }

        return (bool) $r->implementsInterface(EventInterface::class);
    }
}
