<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorChain;
use Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorInterface;
use Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializer;
use Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\Version\StaticMethodVersionResolver;
use Zisato\EventSourcing\Aggregate\Event\Version\VersionResolverInterface;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set(StaticMethodVersionResolver::class, StaticMethodVersionResolver::class)
        ->alias(VersionResolverInterface::class, StaticMethodVersionResolver::class)

        ->set(EventSerializer::class, EventSerializer::class)
        ->args([service(VersionResolverInterface::class)])
        ->alias(EventSerializerInterface::class, EventSerializer::class)

        ->set(EventDecoratorChain::class, EventDecoratorChain::class)
        ->alias(EventDecoratorInterface::class, EventDecoratorChain::class)
    ;
};
