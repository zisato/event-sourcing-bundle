<?php

declare(strict_types=1);

namespace Zisato\EventSourcingBundle\DependencyInjection\Resolver;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\PrivateDataPayloadInterface;

final class PrivateDataResolver
{
    private readonly ContainerBuilder $container;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    public function privateData(ReflectionClass $aggregateRootReflection): bool
    {
        foreach ($aggregateRootReflection->getMethods() as $method) {
            if (! $this->isApplyEventMethod($method)) {
                continue;
            }

            $reflectionType = $method->getParameters()[0]
                ->getType();

            if (! $reflectionType instanceof ReflectionNamedType) {
                continue;
            }

            if ($this->eventHasPrivateData($reflectionType)) {
                return true;
            }
        }

        return false;
    }

    private function isApplyEventMethod(ReflectionMethod $method): bool
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

        $argumentTypeReflection = $method->getParameters()[0]
            ->getType();
        if (! $argumentTypeReflection instanceof ReflectionNamedType) {
            return false;
        }

        $argumentReflectionClass = $this->container->getReflectionClass($argumentTypeReflection->getName());
        if (! $argumentReflectionClass instanceof ReflectionClass) {
            return false;
        }

        return $this->isValidEventType($argumentReflectionClass);
    }

    private function isValidEventType(ReflectionClass $eventReflection): bool
    {
        if ($eventReflection->getName() === EventInterface::class) {
            return false;
        }

        if ($eventReflection->isAbstract()) {
            return false;
        }

        return $eventReflection->implementsInterface(EventInterface::class);
    }

    private function eventHasPrivateData(ReflectionNamedType $reflectionNamedType): bool
    {
        $eventReflection = $this->container->getReflectionClass($reflectionNamedType->getName());

        if (! $eventReflection instanceof ReflectionClass) {
            return false;
        }

        return $eventReflection->implementsInterface(PrivateDataPayloadInterface::class);
    }
}
