<?php

namespace Snowdog\CustomDescription\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Snowdog\CustomDescription\Api\CustomDescriptionRepositoryInterface;
use Snowdog\CustomDescription\Api\Data;
use Snowdog\CustomDescription\Model\Resource\CustomDescription\CollectionFactory;
use Snowdog\CustomDescription\Model\CustomDescriptionFactory;
use Snowdog\CustomDescription\Model\Resource\CustomDescription as CustomDescriptionResource;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class CustomDescriptionRepository
 * 
 * @package Snowdog\CustomDescription\Model
 */
class CustomDescriptionRepository implements CustomDescriptionRepositoryInterface
{

    /**
     * @var \Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface[]
     */
    protected $entities = [];

    /**
     * @var \Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface[]
     */
    protected $entitiesByProductId = [];

    /**
     * @var bool
     */
    protected $allLoaded = false;

    /**
     * @var \Snowdog\CustomDescription\Model\CustomDescriptionFactory
     */
    protected $customDescriptionFactory;

    /**
     * @var CollectionFactory
     */
    protected $customDescriptionCollectionFactory;

    /**
     * @var CustomDescriptionResource
     */
    protected $resource;

    /**
     * CustomDescriptionRepository constructor.
     * @param \Snowdog\CustomDescription\Model\CustomDescriptionFactory $customDescriptionFactory
     * @param CollectionFactory $customDescriptionCollectionFactory
     * @param CustomDescriptionResource $customDescriptionResource
     */
    public function __construct(
        CustomDescriptionFactory $customDescriptionFactory,
        CollectionFactory $customDescriptionCollectionFactory,
        CustomDescriptionResource $customDescriptionResource
    ) {
        $this->customDescriptionFactory = $customDescriptionFactory;
        $this->customDescriptionCollectionFactory = $customDescriptionCollectionFactory;
        $this->resource = $customDescriptionResource;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (isset($this->entities[$id])) {
            return $this->entities[$id];
        }

        $customDescription = $this
            ->customDescriptionFactory
            ->create();

        $customDescription->load($id);

        if ($customDescription->getId() === null) {
            throw new NoSuchEntityException(__('Requested custom description is not found'));
        }

        $this->entities[$id] = $customDescription;

        return $customDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        if (!$this->allLoaded) {
            /** @var $customDescriptionCollection \Snowdog\CustomDescription\Model\Resource\CustomDescription\Collection */
            $customDescriptionCollection = $this
                ->customDescriptionCollectionFactory
                ->create();

            foreach ($customDescriptionCollection as $item) {
                $this->entities[$item->getId()] = $item;
                $this->entitiesByProductId[$item->getProductId()][] = $item;
            }

            $this->allLoaded = true;
        }

        return $this->entities;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomDescriptionByProductId($productId)
    {
        if (isset($this->entitiesByProductId[$productId])) {
            return $this->entitiesByProductId[$productId];
        }

        $customDescriptionFactory = $this
            ->customDescriptionFactory
            ->create();

        $customDescriptionCollection = $customDescriptionFactory
            ->getCustomDescriptionByProductId($productId);

        $this->entitiesByProductId[$productId] = $customDescriptionCollection;

        return $customDescriptionCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface $customDescription)
    {
        try {
            $this->resource->save($customDescription);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $customDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Snowdog\CustomDescription\Api\Data\CustomDescriptionInterface $customDescription)
    {
        try {
            $customDescription->delete();
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
    }
}