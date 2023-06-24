<?php

declare(strict_types=1);

namespace Zisato\EventSourcingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\CryptoPrivateDataPayloadService;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataPayloadServiceInterface;
use Zisato\EventSourcing\Aggregate\Event\Version\StaticMethodVersionResolver;
use Zisato\EventSourcing\Aggregate\Event\Version\VersionResolverInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Service\SnapshotServiceInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Service\SynchronousSnapshotService;
use Zisato\EventSourcing\Aggregate\Snapshot\Strategy\AggregateRootVersionSnapshotStrategy;
use Zisato\EventSourcing\Aggregate\Snapshot\Strategy\SnapshotStrategyInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('event_sourcing_bundle');

        /** @var ParentNodeDefinitionInterface $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('aggregates')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('class')->end()
                            ->scalarNode('repository')->end()
                            ->arrayNode('upcasters')
                                ->scalarPrototype()->end()
                            ->end()
                            ->booleanNode('private_data')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('event')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('version_resolver')
                            ->info('The event version resolver')
                            ->defaultValue(StaticMethodVersionResolver::class)
                            ->validate()
                                ->ifTrue(static function ($value): bool {
                                    return ! is_a($value, VersionResolverInterface::class, true);
                                })
                                ->thenInvalid(sprintf('event.version_resolver must implement %s', VersionResolverInterface::class))
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('snapshot')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('strategy')
                            ->info('The snapshot strategy service')
                            ->defaultValue(AggregateRootVersionSnapshotStrategy::class)
                            ->validate()
                                ->ifTrue(static function ($value): bool {
                                    return ! is_a($value, SnapshotStrategyInterface::class, true);
                                })
                                ->thenInvalid(sprintf('snapshot.strategy must implement %s', SnapshotStrategyInterface::class))
                            ->end()
                        ->end()
                        ->scalarNode('service')
                            ->info('The snapshot create service')
                            ->defaultValue(SynchronousSnapshotService::class)
                            ->validate()
                                ->ifTrue(static function ($value): bool {
                                    return ! is_a($value, SnapshotServiceInterface::class, true);
                                })
                                ->thenInvalid(sprintf('snapshot.service must implement %s', SnapshotServiceInterface::class))
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('private_data')
                    ->addDefaultsIfNotSet()
                        ->children()
                        ->scalarNode('payload_service')
                            ->info('The private data payload service')
                            ->defaultValue(CryptoPrivateDataPayloadService::class)
                            ->validate()
                                ->ifTrue(static function ($value): bool {
                                    return ! is_a($value, PrivateDataPayloadServiceInterface::class, true);
                                })
                                ->thenInvalid(
                                    sprintf('private_data.payload_service must implement %s', PrivateDataPayloadServiceInterface::class)
                                )
                            ->end()
                        ->end()
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
