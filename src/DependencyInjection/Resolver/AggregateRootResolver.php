<?php

declare(strict_types=1);

namespace Zisato\EventSourcingBundle\DependencyInjection\Resolver;

use ReflectionClass;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zisato\EventSourcing\Aggregate\AggregateRootInterface;

final class AggregateRootResolver
{
    private readonly ContainerBuilder $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    public function aggregateRoot(string $repositoryId): ReflectionClass|null
    {
        $repositoryReflection = $this->container->getReflectionClass($repositoryId);
        if (!$repositoryReflection instanceof ReflectionClass) {
            return null;
        }

        $reflectionType = $repositoryReflection->getMethod('get')->getReturnType();
        if (!$reflectionType instanceof ReflectionNamedType) {
            return null;
        }
    
        $aggregateReflection = $this->container->getReflectionClass($reflectionType->getName());
        if (!$aggregateReflection instanceof ReflectionClass) {
            return null;
        }

        if (!$this->isValidAggregateRoot($aggregateReflection)) {
            return null;
        }

        return $aggregateReflection;
    }

    private function isValidAggregateRoot(ReflectionClass $reflection): bool
    {
        if (!$reflection->implementsInterface(AggregateRootInterface::class)) {
            return false;
        }

        return ! $reflection->isAbstract();
    }
}
