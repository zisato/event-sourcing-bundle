<?php

declare(strict_types=1);

namespace Zisato\EventSourcingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Zisato\EventSourcing\Aggregate\AbstractAggregateRoot;
use Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\PayloadEncoderAdapterInterface;
use Zisato\EventSourcing\Aggregate\Event\Upcast\UpcasterInterface;
use Zisato\EventSourcing\Aggregate\Event\Version\VersionResolverInterface;
use Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepository;
use Zisato\EventSourcing\Aggregate\Repository\AggregateRootRepositoryWithSnapshot;
use Zisato\EventSourcing\Aggregate\Snapshot\Service\SnapshotServiceInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Strategy\SnapshotStrategyInterface;

final class EventSourcingExtension extends ConfigurableExtension
{
    /**
     * @var string
     */
    public const TAG_AGGREGATE_ROOT = 'event_sourcing_bundle.aggregate_root';

    /**
     * @var string
     */
    public const TAG_AGGREGATE_ROOT_REPOSITORY = 'event_sourcing_bundle.aggregate_root.repository';

    /**
     * @var string
     */
    public const TAG_AGGREGATE_ROOT_REPOSITORY_WITH_SNAPSHOT = 'event_sourcing_bundle.aggregate_root.repository_with_snapshot';

    /**
     * @var string
     */
    public const TAG_EVENT_DECORATOR = 'event_sourcing_bundle.aggregate_root.event.decorator';

    /**
     * @var string
     */
    public const TAG_EVENT_UPCASTER = 'event_sourcing_bundle.aggregate_root.event.upcaster';

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('event_sourcing.php');
        $loader->load('event_sourcing_private_data.php');
        $loader->load('event_sourcing_snapshot.php');

        $container->setParameter('event_sourcing_bundle.aggregates', $mergedConfig['aggregates']);

        $container->setAlias(VersionResolverInterface::class, $mergedConfig['event']['version_resolver']);
        $container->setAlias(SnapshotStrategyInterface::class, $mergedConfig['snapshot']['strategy']);
        $container->setAlias(SnapshotServiceInterface::class, $mergedConfig['snapshot']['service']);
        $container->setAlias(PayloadEncoderAdapterInterface::class, $mergedConfig['private_data']['payload_encoder']);

        $container->registerForAutoconfiguration(EventDecoratorInterface::class)
            ->addTag(self::TAG_EVENT_DECORATOR);

        $container->registerForAutoconfiguration(AggregateRootRepository::class)
            ->addTag(self::TAG_AGGREGATE_ROOT_REPOSITORY);

        $container->registerForAutoconfiguration(AggregateRootRepositoryWithSnapshot::class)
            ->addTag(self::TAG_AGGREGATE_ROOT_REPOSITORY);

        $container->registerForAutoconfiguration(UpcasterInterface::class)
            ->addTag(self::TAG_EVENT_UPCASTER);

        $container->registerForAutoconfiguration(AbstractAggregateRoot::class)
            ->addTag(self::TAG_AGGREGATE_ROOT);
    }
}
