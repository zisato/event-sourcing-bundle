<?php

declare(strict_types=1);

namespace Zisato\EventSourcingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorChain;
use Zisato\EventSourcingBundle\DependencyInjection\EventSourcingExtension;

final class EventDecoratorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (! $container->hasDefinition(EventDecoratorChain::class)) {
            return;
        }

        $eventDecorators = \array_keys($container->findTaggedServiceIds(EventSourcingExtension::TAG_EVENT_DECORATOR, true));

        $eventDecoratorDefinitions = \array_map(static function (string $eventDecorator): Reference {
            return new Reference($eventDecorator);
        }, $eventDecorators);

        $container->findDefinition(EventDecoratorChain::class)
            ->setArguments($eventDecoratorDefinitions);
    }
}
