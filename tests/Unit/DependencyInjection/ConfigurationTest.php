<?php

namespace Zisato\EventSourcingBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\CryptoPayloadEncoderAdapter;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\PayloadEncoderAdapterInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\Payload;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\PayloadKeyCollection;
use Zisato\EventSourcing\Aggregate\Event\Version\StaticMethodVersionResolver;
use Zisato\EventSourcing\Aggregate\Event\Version\VersionResolverInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Service\SnapshotServiceInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Service\SynchronousSnapshotService;
use Zisato\EventSourcing\Aggregate\Snapshot\SnapshotInterface;
use Zisato\EventSourcing\Aggregate\Snapshot\Strategy\AggregateRootVersionSnapshotStrategy;
use Zisato\EventSourcing\Aggregate\Snapshot\Strategy\SnapshotStrategyInterface;
use Zisato\EventSourcingBundle\DependencyInjection\Configuration;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /*
    Not compatible with phpunit 10
    
    public function testJsonSchemaPathInvalidValues(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'json_schema_path' => null,
                ]
            ],
            'json_schema_path'
        );
    }
    
    public function testApiProblemExceptionHandlersInvalidValues(): void
    {
        $this->assertConfigurationIsInvalid(
            [
                [
                    'api_problem' => [
                        'exception_handlers' => [
                            'foo'
                        ],
                    ]
                ]
            ],
            'api_problem.exception_handlers'
        );
    }

    */

    public function testProcessConfigurationDefaultValues(): void
    {
        $this->assertProcessedConfigurationEquals(
            [
                []
            ],
            [
                'aggregates' => [],
                'event' => [
                    'version_resolver' => StaticMethodVersionResolver::class,
                ],
                'snapshot' => [
                    'strategy' => AggregateRootVersionSnapshotStrategy::class,
                    'service' => SynchronousSnapshotService::class,
                ],
                'private_data' => [
                    'payload_encoder' => CryptoPayloadEncoderAdapter::class,
                ],
            ]
        );
    }

    public function testProcessEventVersionResolverValue(): void
    {
        $versionResolver = new class implements VersionResolverInterface {
            public function resolve(EventInterface $event): int
            {
                return 1;
            }
        };

        $this->assertProcessedConfigurationEquals(
            [
                [
                    'event' => [
                        'version_resolver' => $versionResolver::class,
                    ]
                ]
            ],
            [
                'aggregates' => [],
                'event' => [
                    'version_resolver' => $versionResolver::class,
                ],
                'snapshot' => [
                    'strategy' => AggregateRootVersionSnapshotStrategy::class,
                    'service' => SynchronousSnapshotService::class,
                ],
                'private_data' => [
                    'payload_encoder' => CryptoPayloadEncoderAdapter::class,
                ],
            ]
        );
    }

    public function testProcessSnapshotStrategyValue(): void
    {
        $snapshotStrategy = new class implements SnapshotStrategyInterface {
            public function shouldCreateSnapshot(AggregateRootInterface $aggregateRoot): bool
            {
                return true;
            }
        };

        $this->assertProcessedConfigurationEquals(
            [
                [
                    'snapshot' => [
                        'strategy' => $snapshotStrategy::class,
                    ]
                ]
            ],
            [
                'aggregates' => [],
                'event' => [
                    'version_resolver' => StaticMethodVersionResolver::class,
                ],
                'snapshot' => [
                    'strategy' => $snapshotStrategy::class,
                    'service' => SynchronousSnapshotService::class,
                ],
                'private_data' => [
                    'payload_encoder' => CryptoPayloadEncoderAdapter::class,
                ],
            ]
        );
    }

    public function testProcessSnapshotServiceValue(): void
    {
        $snapshotService = new class implements SnapshotServiceInterface {
            public function create(SnapshotInterface $snapshot): void {}
        };

        $this->assertProcessedConfigurationEquals(
            [
                [
                    'snapshot' => [
                        'service' => $snapshotService::class,
                    ]
                ]
            ],
            [
                'aggregates' => [],
                'event' => [
                    'version_resolver' => StaticMethodVersionResolver::class,
                ],
                'snapshot' => [
                    'strategy' => AggregateRootVersionSnapshotStrategy::class,
                    'service' => $snapshotService::class,
                ],
                'private_data' => [
                    'payload_encoder' => CryptoPayloadEncoderAdapter::class,
                ],
            ]
        );
    }

    public function testProcessPayloadEncoderrValue(): void
    {
        $payloadEncoderAdapter = new class implements PayloadEncoderAdapterInterface {
            public function show(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
            {
                return [];
            }

            public function hide(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
            {
                return [];
            }

            public function forget(string $aggregateId, PayloadKeyCollection $payloadKeyCollection, array $payload): array
            {
                return [];
            }
        };

        $this->assertProcessedConfigurationEquals(
            [
                [
                    'private_data' => [
                        'payload_encoder' => $payloadEncoderAdapter::class,
                    ]
                ]
            ],
            [
                'aggregates' => [],
                'event' => [
                    'version_resolver' => StaticMethodVersionResolver::class,
                ],
                'snapshot' => [
                    'strategy' => AggregateRootVersionSnapshotStrategy::class,
                    'service' => SynchronousSnapshotService::class,
                ],
                'private_data' => [
                    'payload_encoder' => $payloadEncoderAdapter::class,
                ],
            ]
        );
    }

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }
}
