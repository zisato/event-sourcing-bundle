<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\CryptoPayloadEncoderAdapter;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\ExternalPayloadEncoderAdapter;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Adapter\PayloadEncoderAdapterInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\CryptoInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\OpenSSLCrypto;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\SecretKeyStoreInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Repository\PrivateDataRepositoryInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer\JsonPayloadValueSerializer;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer\PayloadValueSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventService;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventServiceInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy\PayloadKeyCollectionByEventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy\PayloadKeyCollectionStrategyInterface;
use Zisato\EventSourcing\Infrastructure\Aggregate\Event\PrivateData\Crypto\DBALSecretKeyStore;
use Zisato\EventSourcing\Infrastructure\Aggregate\Event\PrivateData\Repository\DBALPrivateDataRepository;

return static function (ContainerConfigurator $container): void {

    $container->services()
        
        ->set(PayloadKeyCollectionByEventInterface::class, PayloadKeyCollectionByEventInterface::class)
        ->alias(PayloadKeyCollectionStrategyInterface::class, PayloadKeyCollectionByEventInterface::class)

        ->set(DBALSecretKeyStore::class, DBALSecretKeyStore::class)
        ->args([
            service(Connection::class)
        ])
        ->alias(SecretKeyStoreInterface::class, DBALSecretKeyStore::class)
        
        ->set(OpenSSLCrypto::class, OpenSSLCrypto::class)
        ->alias(CryptoInterface::class, OpenSSLCrypto::class)

        ->set(PrivateDataEventService::class, PrivateDataEventService::class)
        ->args([
            service(PayloadKeyCollectionStrategyInterface::class),
            service(PayloadEncoderAdapterInterface::class),
        ])
        ->alias(PrivateDataEventServiceInterface::class, PrivateDataEventService::class)

        ->set(JsonPayloadValueSerializer::class, JsonPayloadValueSerializer::class)
        ->alias(PayloadValueSerializerInterface::class, JsonPayloadValueSerializer::class)

        ->set(DBALPrivateDataRepository::class, DBALPrivateDataRepository::class)
        ->args([
            service(Connection::class),
            service(PayloadValueSerializerInterface::class),
        ])
        ->alias(PrivateDataRepositoryInterface::class, DBALPrivateDataRepository::class)

        ->set(ExternalPayloadEncoderAdapter::class, ExternalPayloadEncoderAdapter::class)
        ->args([
            service(PrivateDataRepositoryInterface::class),
        ])

        ->set(CryptoPayloadEncoderAdapter::class, CryptoPayloadEncoderAdapter::class)
        ->args([
            service(PayloadValueSerializerInterface::class),
            service(SecretKeyStoreInterface::class),
            service(CryptoInterface::class),
        ])
    ;
};
