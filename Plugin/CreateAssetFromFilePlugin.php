<?php
/**
 * MyEnso AzureBlobStorage
 *
 * @category   MyEnso
 * @package    MyEnso_AzureBlobStorage
 * @license    MIT
 */

namespace MyEnso\AzureBlobStorage\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGallerySynchronization\Model\CreateAssetFromFile;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGallerySynchronization\Model\Filesystem\GetFileInfo;
use Magento\MediaGallerySynchronization\Model\GetContentHash;

class CreateAssetFromFilePlugin
{

    public function __construct(
        Filesystem $filesystem,
        File $driver,
        AssetInterfaceFactory $assetFactory,
        GetContentHash $getContentHash,
        GetFileInfo $getFileInfo
    ) {
        $this->filesystem = $filesystem;
        $this->driver = $driver;
        $this->assetFactory = $assetFactory;
        $this->getContentHash = $getContentHash;
        $this->getFileInfo = $getFileInfo;
    }
    /**
     * After execute plugin
     *
     * @param CreateAssetFromFile $subject
     * @param AssetInterface $result
     * @param string $path
     * @return AssetInterface
     */
    public function afterExecute(CreateAssetFromFile $subject, AssetInterface $result, string $path): AssetInterface
    {
        $absolutePath = $this->getMediaDirectory()->getAbsolutePath($path);
        $driver = $this->getMediaDirectory()->getDriver();

        $file = $this->getFileInfo->execute($absolutePath);
        [$width, $height] = getimagesizefromstring($driver->fileGetContents($absolutePath));

        $result = $this->assetFactory->create(
            [
                'id' => null,
                'path' => $path,
                'title' => $result->getTitle() ?? '',
                'width' => $width ?? 0,
                'height' => $height ?? 0,
                'hash' => $result->getHash($path),
                'size' => $result ->getSize() ?? 0,
                'contentType' => $result->getContentType(),
                'source' => 'Local'
            ]
        );

        return $result;
    }

    /**
     * Retrieve media directory instance with write access
     *
     * @return Filesystem\Directory\WriteInterface
     */
    private function getMediaDirectory(): Filesystem\Directory\WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }


}
