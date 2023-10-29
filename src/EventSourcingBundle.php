<?php

declare(strict_types=1);

namespace Zisato\EventSourcingBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zisato\EventSourcingBundle\DependencyInjection\Compiler\AggregateAutoConfigPass;
use Zisato\EventSourcingBundle\DependencyInjection\Compiler\AggregateConfigDefinitionPass;
use Zisato\EventSourcingBundle\DependencyInjection\Compiler\EventDecoratorPass;

final class EventSourcingBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AggregateAutoConfigPass());
        $container->addCompilerPass(new AggregateConfigDefinitionPass());
        $container->addCompilerPass(new EventDecoratorPass());
    }
}
