<?php

namespace Zisato\EventSourcingBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorChain;
use Zisato\EventSourcing\Aggregate\Event\Decorator\EventDecoratorInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\CryptoInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\OpenSSLCrypto;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\SecretKeyStoreInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Repository\PrivateDataRepositoryInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer\JsonPayloadValueSerializer;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer\PayloadValueSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\CryptoPrivateDataPayloadService;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\ExternalPrivateDataPayloadService;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventPayloadService;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventServiceInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy\PayloadKeyCollectionByEventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy\PayloadKeyCollectionStrategyInterface;
use Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializer;
use Zisato\EventSourcing\Aggregate\Event\Serializer\EventSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\Version\StaticMethodVersionResolver;
use Zisato\EventSourcing\Aggregate\Event\Version\VersionResolverInterface;
use Zisato\EventSourcing\Aggregate\Serializer\AggregateRootSerializerInterface;
use Zisato\EventSourcing\Aggregate\Serializer\ReflectionAggregateRootSerializer;
use Zisato\EventSourcing\Aggregate\Snapshot\Service\SynchronousSnapshotService;
use Zisato\EventSourcing\Aggregate\Snapshot\Snapshotter;
use Zisato\EventSourcing\Aggregate\Snapshot\SnapshotterInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Store\SnapshotStoreInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Strategy\AggregateRootVersionSnapshotStrategy;
use Zisato\EventSourcingBundle\DependencyInjection\EventSourcingExtension;

class EventSourcingExtensionTest extends AbstractExtensionTestCase
{
    public function testParameterAggregatesIsSet(): void
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('event_sourcing_bundle.aggregates', []);
    }

    /**
     * @dataProvider getServiceExistsData
     */
    public function testServiceIdIsSetWhenDefaultConfiguration(string $serviceId): void
    {
        $this->load();

        $this->assertContainerBuilderHasService($serviceId);
    }

    /**
     * @dataProvider getServiceAliasExistsData
     */
    public function testServiceAliasIsSet(string $serviceAlias): void
    {
        $this->load();

        $this->assertContainerBuilderHasAlias($serviceAlias);
    }

    public static function getServiceExistsData(): array
    {
        return [
            [
                StaticMethodVersionResolver::class,
            ],
            [
                EventSerializer::class,
            ],
            [
                EventDecoratorChain::class,
            ],
            [
                AggregateRootVersionSnapshotStrategy::class,
            ],
            [
                SynchronousSnapshotService::class,
            ],
            [
                ReflectionAggregateRootSerializer::class,
            ],
            [
                Snapshotter::class,
            ],
            /*
            [
                DBALSnapshotStore::class,
            ],
            */
            [
                PayloadKeyCollectionByEventInterface::class,
            ],
            /*
            [
                DBALSecretKeyStore::class,
            ],
            */
            [
                OpenSSLCrypto::class,
            ],
            [
                PrivateDataEventPayloadService::class,
            ],
            [
                JsonPayloadValueSerializer::class,
            ],
            /*
            [
                DBALPrivateDataRepository::class,
            ],
            */
            [
                ExternalPrivateDataPayloadService::class,
            ],
            [
                CryptoPrivateDataPayloadService::class,
            ],
        ];
    }

    public static function getServiceAliasExistsData(): array
    {
        return [
            [
                VersionResolverInterface::class,
            ],
            [
                EventSerializerInterface::class,
            ],
            [
                EventDecoratorInterface::class,
            ],
            [
                AggregateRootSerializerInterface::class,
            ],
            [
                SnapshotterInterface::class,
            ],
            [
                SnapshotStoreInterface::class,
            ],
            [
                PayloadKeyCollectionStrategyInterface::class,
            ],
            [
                SecretKeyStoreInterface::class,
            ],
            [
                CryptoInterface::class,
            ],
            [
                PrivateDataEventServiceInterface::class,
            ],
            [
                PayloadValueSerializerInterface::class,
            ],
            [
                PrivateDataRepositoryInterface::class,
            ],
        ];
    }

    protected function getContainerExtensions(): array
    {
        return array(
            new EventSourcingExtension()
        );
    }
}
