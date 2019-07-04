<?php

namespace Sifuen\UpgradableContent\Model\Content;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Cms\Model\PageFactory;

/**
 * Class Page
 * @package Sifuen\UpgradableContent\Model\Content
 */
class Page extends AbstractContent
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;
    /**
     * @var PageFactory
     */
    private $pageFactory;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Page constructor.
     * @param PageRepositoryInterface $pageRepository
     * @param PageFactory $blockFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        PageFactory $blockFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->pageRepository = $pageRepository;
        $this->pageFactory = $blockFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param string $identifier
     * @return \Magento\Cms\Api\Data\PageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getEntity($identifier)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('identifier', $identifier)
            ->create();

        $result = $this->pageRepository->getList($searchCriteria);

        if ($result->getTotalCount() === 0) {
            return $this->createEntity($identifier);
        }

        $items = $result->getItems();
        return $items[array_keys($items)[0]];
    }

    /**
     * @param string $identifier
     * @return PageInterface
     */
    protected function createEntity($identifier)
    {
        return $this->pageFactory->create()
            ->setIdentifier($identifier);
    }

    /**
     * @param string $identifier
     * @param string $content
     * @param array $changes
     * @return mixed|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyChanges($identifier, $content, array $changes)
    {
        $entity = $this->getEntity($identifier);
        $entity->setContent($content);
        $entity->addData($changes);
        $this->pageRepository->save($entity);
    }
}