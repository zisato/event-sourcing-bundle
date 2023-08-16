# event-sourcing-bundle
Bundle for autoloading event sourcing definitions in a Symfony application.

## Default configuration
```
event_sourcing:
    event:
        version_resolver: Zisato\EventSourcing\Aggregate\Event\Version\StaticMethodVersionResolver
    snapshot:
        strategy: Zisato\EventSourcing\Aggregate\Snapshot\Strategy\AggregateRootVersionSnapshotStrategy
        service: Zisato\EventSourcing\Aggregate\Snapshot\Service\SynchronousSnapshotService
    private_data:
        payload_encoder: Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\CryptoPrivateDataPayloadService
```