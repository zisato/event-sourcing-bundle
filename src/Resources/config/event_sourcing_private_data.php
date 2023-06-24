<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Connection;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer\JsonPayloadValueSerializer;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Serializer\PayloadValueSerializerInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\CryptoPrivateDataPayloadService;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\ExternalPrivateDataPayloadService;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataPayloadServiceInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy\PayloadKeyCollectionByEventInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Strategy\PayloadKeyCollectionStrategyInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventServiceInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\CryptoInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\SecretKeyStoreInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Crypto\OpenSSLCrypto;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Repository\PrivateDataRepositoryInterface;
use Zisato\EventSourcing\Aggregate\Event\PrivateData\Service\PrivateDataEventPayloadService;
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

        ->set(PrivateDataEventPayloadService::class, PrivateDataEventPayloadService::class)
        ->args([
            service(PayloadKeyCollectionStrategyInterface::class),
            service(PrivateDataPayloadServiceInterface::class),
        ])
        ->alias(PrivateDataEventServiceInterface::class, PrivateDataEventPayloadService::class)

        ->set(JsonPayloadValueSerializer::class, JsonPayloadValueSerializer::class)
        ->alias(PayloadValueSerializerInterface::class, JsonPayloadValueSerializer::class)

        ->set(DBALPrivateDataRepository::class, DBALPrivateDataRepository::class)
        ->args([
            service(Connection::class),
            service(PayloadValueSerializerInterface::class),
        ])
        ->alias(PrivateDataRepositoryInterface::class, DBALPrivateDataRepository::class)

        ->set(ExternalPrivateDataPayloadService::class, ExternalPrivateDataPayloadService::class)
        ->args([
            service(PrivateDataRepositoryInterface::class),
        ])

        ->set(CryptoPrivateDataPayloadService::class, CryptoPrivateDataPayloadService::class)
        ->args([
            service(PayloadValueSerializerInterface::class),
            service(SecretKeyStoreInterface::class),
            service(CryptoInterface::class),
        ])
    ;
};
