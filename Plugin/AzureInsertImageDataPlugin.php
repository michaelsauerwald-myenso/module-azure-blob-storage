<?php
/**
 * MyEnso AzureBlobStorage
 *
 * @category   MyEnso
 * @package    MyEnso_AzureBlobStorage
 * @license    MIT
 */

namespace MyEnso\AzureBlobStorage\Plugin;

use Magento\Framework\App\DeploymentConfig;
use Magento\MediaGalleryUi\Model\InsertImageData\GetInsertImageData;
use Magento\MediaGalleryUi\Model\InsertImageDataFactory;

class AzureInsertImageDataPlugin
{
    public function __construct(
        InsertImageDataFactory $insertImageDataFactory,
        DeploymentConfig $deploymentConfig
    ) {
        $this->insertImageDataFactory = $insertImageDataFactory;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Before plugin for execute method
     *
     * @param GetInsertImageData $subject
     */
    public function afterExecute(
        GetInsertImageData $subject,
        $result
    )
    {
        $contentPath = $result->getContent();
        $size = $result->getSize();
        $type = $result->getType();

        // Retrieve bucket name from env.php
        $remoteStorageConfig = $this->deploymentConfig->get('remote_storage', []);
        if (isset($remoteStorageConfig['driver']) && $remoteStorageConfig['driver'] == 'azure-blob-storage') {
            $blobName = $remoteStorageConfig['config']['blob_container'];
            $accountName = $remoteStorageConfig['config']['account_name'];

            $relativePath = str_replace($blobName . "/", "", $contentPath);
            $cleanPath = str_replace($accountName . "/", "", $relativePath);

            $finalContent = $cleanPath;

            return $this->insertImageDataFactory->create([
                'content' => $finalContent,
                'size' => $size,
                'type' => $type
            ]);
        }

        return $result;
    }
}
