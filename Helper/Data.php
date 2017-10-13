<?php

namespace Snowdog\CustomDescription\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Data
 * @package Snowdog\CustomDescription\Helper
 */
class Data extends AbstractHelper
{
    const IMAGES_UPLOAD = 'snowdog/customdescription/images';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * Data constructor.
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        Context $context
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * @param $imageName
     * @return string
     */
    public function getImageUrl($imageName)
    {
        return $this->storeManager->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . self::IMAGES_UPLOAD
            . $imageName;
    }

    /**
     * @param $imagePath
     * @return string
     */
    public function getImageNameFromPath($imagePath)
    {
        $path = explode('/', $imagePath);
        return end($path);
    }

    /**
     * @param $image
     * @return mixed
     */
    public function getImageSize($image)
    {
        return $this->mediaDirectory->stat(self::IMAGES_UPLOAD . $image)['size'];
    }

    /**
     * @param $image
     * @return bool
     */
    public function isExistingImage($image)
    {
        return $this->mediaDirectory->isFile(self::IMAGES_UPLOAD . $image);
    }

    /**
     * @param $image
     * @return string
     */
    public function getImageFullPath($image)
    {
        return $this->mediaDirectory->getAbsolutePath()
            . self::IMAGES_UPLOAD
            . $image;
    }
}
