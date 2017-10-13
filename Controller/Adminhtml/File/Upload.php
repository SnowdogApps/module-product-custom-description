<?php

namespace Snowdog\CustomDescription\Controller\Adminhtml\File;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory as FileUploaderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Controller\ResultFactory;
use Snowdog\CustomDescription\Helper\Data;

/**
 * Class Upload
 * @package Snowdog\CustomDescription\Controller\File
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Upload extends Action
{
    /**
     * @var FileUploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IoFile
     */
    private $ioFile;

    /**
     * @var AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Upload constructor.
     * @param Context $context
     * @param FileUploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param IoFile $ioFile
     * @param AdapterFactory $adapterFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        FileUploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        IoFile $ioFile,
        AdapterFactory $adapterFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
        $this->adapterFactory = $adapterFactory;
        $this->helper = $helper;
    }
    
    public function execute()
    {
        try {
            /** @var Uploader $uploader */
            $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $imageAdapter = $this->adapterFactory->create();
            $uploader->addValidateCallback('product', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

            $result = $uploader->save(
                $mediaDirectory->getAbsolutePath(Data::IMAGES_UPLOAD)
            );

            if (!empty($result['file'])) {
                $result['url'] = $this->helper->getImageUrl($result['file']);
            }

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_JSON)
            ->setData($result);
    }
}
