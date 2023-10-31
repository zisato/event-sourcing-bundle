<?php

declare(strict_types=1);

namespace Zisato\EventSourcingBundle\DependencyInjection\Resolver;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcingBundle\DependencyInjection\EventSourcingExtension;

final class EventUpcasterResolver
{
    private readonly ContainerBuilder $container;
    /** @var string[] $upcasterServiceIds */
    private readonly array $upcasterServiceIds;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
        $this->upcasterServiceIds = array_keys($this->container->findTaggedServiceIds(EventSourcingExtension::TAG_EVENT_UPCASTER, true));
    }

    /**
     * @return string[]
     */
    public function eventUpcasters(ReflectionClass $aggregateRootReflection): array
    {
        $upcasters = [];

        foreach ($aggregateRootReflection->getMethods() as $method) {
            if (!$this->isApplyEventMethod($method)) {
                continue;
            }

            $reflectionType = $method->getParameters()[0]->getType();

            if (!$reflectionType instanceof ReflectionNamedType) {
                continue;
            }

            $upcasters = array_merge($upcasters, $this->getEventUpcasters($reflectionType));
        }

        return $upcasters;
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

        $argumentTypeReflection = $method->getParameters()[0]->getType();
        if (!$argumentTypeReflection instanceof ReflectionNamedType) {
            return false;
        }

        $argumentReflectionClass = $this->container->getReflectionClass($argumentTypeReflection->getName());
        if (!$argumentReflectionClass instanceof ReflectionClass) {
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

        if (!$eventReflection->implementsInterface(EventInterface::class)) {
            return false;
        }

        return true;
    }

    private function getEventUpcasters(ReflectionNamedType $reflectionNamedType): array
    {
        $eventReflectionName = $reflectionNamedType->getName();

        return array_filter($this->upcasterServiceIds, function (string $upcasterServiceId) use ($eventReflectionName) {
            $upcasterReflection = $this->container->getReflectionClass($upcasterServiceId);

            $returnType = $upcasterReflection->getMethod('upcast')->getReturnType();
            if (!$returnType instanceof ReflectionNamedType) {
                return false;
            }

            return $returnType->getName() === $eventReflectionName;
        });
    }
}
