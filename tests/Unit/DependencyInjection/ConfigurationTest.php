<?php

namespace Zisato\EventSourcingBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Zisato\EventSourcing\Aggregate\AggregateRootInterface;
use Zisato\EventSourcing\Aggregate\Event\EventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\CryptoPrivateDataPayloadService;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataPayloadServiceInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\ValueObject\Payload;
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
                    'payload_service' => CryptoPrivateDataPayloadService::class,
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
                    'payload_service' => CryptoPrivateDataPayloadService::class,
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
                    'payload_service' => CryptoPrivateDataPayloadService::class,
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
                    'payload_service' => CryptoPrivateDataPayloadService::class,
                ],
            ]
        );
    }

    public function testProcessPrivateDataPayloadServiceValue(): void
    {
        $privateDataPayloadService = new class implements PrivateDataPayloadServiceInterface {
            public function hide(Payload $payload): array
            {
                return [];
            }

            public function show(Payload $payload): array
            {
                return [];
            }
        };

        $this->assertProcessedConfigurationEquals(
            [
                [
                    'private_data' => [
                        'payload_service' => $privateDataPayloadService::class,
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
                    'payload_service' => $privateDataPayloadService::class,
                ],
            ]
        );
    }

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }
}
