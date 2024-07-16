<?php
/**
 * MyEnso AzureBlobStorage
 *
 * @category   MyEnso
 * @package    MyEnso_AzureBlobStorage
 * @license    MIT
 */

declare(strict_types=1);

namespace MyEnso\AzureBlobStorage\Model;

use Magento\Framework\App\DeploymentConfig;

class Config
{
    public const PATH_CONNECTION_STRING = 'azure_storage/connection_string';
    public const PATH_BLOB_CONTAINER = 'azure_storage/blob_container';
    public const PATH_ACCESS_KEY = 'azure_storage/access_key';
    public const PATH_ACCOUNT_NAME = 'azure_storage/account_name';

    /**
     * @var DeploymentConfig
     */
    private $config;

    /**
     * @param DeploymentConfig $config
     */
    public function __construct(DeploymentConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieves connection string.
     *
     * @return string
     */
    public function getConnectionString(): string
    {
        return (string)$this->config->get(self::PATH_CONNECTION_STRING);
    }

    /**
     * Retrieves blob container.
     *
     * @return string
     */
    public function getBlobContainer(): string
    {
        return (string)$this->config->get(self::PATH_BLOB_CONTAINER);
    }

    /**
     * Retrieves access key.
     *
     * @return string
     */
    public function getAccessKey(): string
    {
        return (string)$this->config->get(self::PATH_ACCESS_KEY);
    }

    /**
     * Retrieves account name.
     *
     * @return string
     */
    public function getAccountName(): string
    {
        return (string)$this->config->get(self::PATH_ACCOUNT_NAME);
    }
}
