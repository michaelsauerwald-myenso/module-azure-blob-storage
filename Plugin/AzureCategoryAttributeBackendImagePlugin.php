<?php
/**
 * MyEnso AzureBlobStorage
 *
 * @category   MyEnso
 * @package    MyEnso_AzureBlobStorage
 * @license    MIT
 */

namespace MyEnso\AzureBlobStorage\Plugin;

use Magento\Catalog\Model\Category\Attribute\Backend\Image as Subject;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AzureCategoryAttributeBackendImagePlugin
{
    private $deploymentConfig;
    private $scopeConfig;

    public function __construct(
        DeploymentConfig $deploymentConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->scopeConfig = $scopeConfig;
    }

    public function beforeBeforeSave(Subject $subject, $object)
    {
                $attributeName = $subject->getAttribute()->getName();
        $value = $object->getData($attributeName);

        // Retrieve bucket name from env.php
        $remoteStorageConfig = $this->deploymentConfig->get('remote_storage', []);
        $blobName = $remoteStorageConfig['config']['blob_container'] ?? 'category-images';
        $accountName = $remoteStorageConfig['config']['account_name'] ?? 'devstoreaccount1';

        if (isset($remoteStorageConfig['driver']) && $remoteStorageConfig['driver'] == 'azure-blob-storage' && is_array($value) && isset($value[0]['url'])) {
            $value[0]['url'] = str_replace($blobName . "/", "", $value[0]['url']);
            $value[0]['url'] = str_replace($accountName. "/", "", $value[0]['url']);

            $object->setData($attributeName, $value);
        }

        return [$object];
    }
}
