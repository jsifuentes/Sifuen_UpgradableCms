<?php

namespace Sifuen\UpgradableContent\Model\Content;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Cms\Model\BlockFactory;

/**
 * Class Block
 * @package Sifuen\UpgradableContent\Model\Content
 */
class Block extends AbstractContent
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;
    /**
     * @var BlockFactory
     */
    private $blockFactory;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Block constructor.
     * @param BlockRepositoryInterface $blockRepository
     * @param BlockFactory $blockFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        BlockFactory $blockFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param string $identifier
     * @return BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getEntity($identifier)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('identifier', $identifier)
            ->create();

        $result = $this->blockRepository->getList($searchCriteria);

        if ($result->getTotalCount() === 0) {
            return $this->createEntity($identifier);
        }

        $items = $result->getItems();
        return $items[array_keys($items)[0]];
    }

    /**
     * @param string $identifier
     * @return BlockInterface
     */
    protected function createEntity($identifier)
    {
        return $this->blockFactory->create()
            ->setIdentifier($identifier);
    }

    /**
     * @param string $identifier
     * @param string $content
     * @param array $changes
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyChanges($identifier, $content, array $changes)
    {
        $entity = $this->getEntity($identifier);
        $entity->setContent($content);
        $entity->addData($changes);
        $this->blockRepository->save($entity);
    }
}