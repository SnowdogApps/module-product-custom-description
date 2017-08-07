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

/**
 * Class Upload
 * @package Snowdog\CustomDescription\Controller\File
 */
class Upload extends Action
{
    const IMAGES_UPLOAD = 'snowdog/customdescription/images';

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
     * Upload constructor.
     * @param Context $context
     * @param FileUploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param IoFile $ioFile
     * @param AdapterFactory $adapterFactory
     */
    public function __construct(
        Context $context,
        FileUploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        IoFile $ioFile,
        AdapterFactory $adapterFactory
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
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
                $mediaDirectory->getAbsolutePath(self::IMAGES_UPLOAD)
            );

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
