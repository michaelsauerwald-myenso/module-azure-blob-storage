<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MyEnso\AzureBlobStorage\Driver;

use Exception;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterfaceFactory;
use Magento\RemoteStorage\Driver\Adapter\CachedAdapterInterfaceFactory;
use Magento\RemoteStorage\Driver\Adapter\MetadataProviderInterfaceFactory;
use Magento\RemoteStorage\Driver\DriverException;
use Magento\RemoteStorage\Driver\DriverFactoryInterface;
use Magento\RemoteStorage\Driver\RemoteDriverInterface;
use Magento\RemoteStorage\Model\Config;

/**
 * Creates a pre-configured instance of Azure Blob Storage driver.
 */
class AzureBlobStorageFactory implements DriverFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var MetadataProviderInterfaceFactory
     */
    private $metadataProviderFactory;

    /**
     * @var CacheInterfaceFactory
     */
    private $cacheInterfaceFactory;

    /**
     * @var CachedAdapterInterfaceFactory
     */
    private $cachedAdapterInterfaceFactory;

    /**
     * @var string|null
     */
    private $cachePrefix;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     * @param MetadataProviderInterfaceFactory $metadataProviderFactory
     * @param CacheInterfaceFactory $cacheInterfaceFactory
     * @param CachedAdapterInterfaceFactory $cachedAdapterInterfaceFactory
     * @param string|null $cachePrefix
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config,
        MetadataProviderInterfaceFactory $metadataProviderFactory,
        CacheInterfaceFactory $cacheInterfaceFactory,
        CachedAdapterInterfaceFactory $cachedAdapterInterfaceFactory,
        string $cachePrefix = null
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->metadataProviderFactory = $metadataProviderFactory;
        $this->cacheInterfaceFactory = $cacheInterfaceFactory;
        $this->cachedAdapterInterfaceFactory = $cachedAdapterInterfaceFactory;
        $this->cachePrefix = $cachePrefix;
    }

    /**
     * @inheritDoc
     */
    public function create(): RemoteDriverInterface
    {
        try {
            return $this->createConfigured(
                $this->config->getConfig(),
                $this->config->getPrefix()
            );
        } catch (LocalizedException $exception) {
            throw new DriverException(__($exception->getMessage()), $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function createConfigured(
        array $config,
        string $prefix,
        string $cacheAdapter = '',
        array $cacheConfig = []
    ): RemoteDriverInterface {

        $config['version'] = 'latest';
        $accountName = $config['account_name'] ?? '';
        $accountKey = $config['account_key'] ?? '';
        $endpoint = $config['endpoint'] ?? '';
        $connectionString = 'DefaultEndpointsProtocol=https;AccountName='.$accountName.';AccountKey='.$accountKey.';BlobEndpoint='.$endpoint.';';

        $blobClient = BlobRestProxy::createBlobService($connectionString);
        $containerName = $config['container'] ?? 'category-images';
        $adapter = new AzureBlobStorageAdapter($blobClient, $containerName, $prefix);

        // rest of the code stays the same...

        $cache = $this->cacheInterfaceFactory->create(
        // Custom cache prefix required to distinguish cache records for different sources.
        // phpcs:ignore Magento2.Security.InsecureFunction
            $this->cachePrefix ? ['prefix' => $this->cachePrefix] : ['prefix' => md5($connectionString . $prefix)]
        );
        $metadataProvider = $this->metadataProviderFactory->create(
            [
                'adapter' => $adapter,
                'cache' => $cache
            ]
        );

        // As BlobRestProxy doesn't have a method to get object URL, we have to construct it manually
        // Assuming usage of blob.core.windows.net - adjust as needed
        $objectUrl = $config['endpoint'].$config['blob_container'] . trim($prefix, '\\/') . '/';

        // Here, AzureBlob should be your equivalent class for AwsS3.
        return $this->objectManager->create(
            AzureBlobStorage::class,
            [
                'adapter' => $this->cachedAdapterInterfaceFactory->create(
                    [
                        'adapter' => $adapter,
                        'cache' => $cache,
                        'metadataProvider' => $metadataProvider
                    ]
                ),
                'objectUrl' => $objectUrl,
                'metadataProvider' => $metadataProvider,
            ]
        );
    }
}
