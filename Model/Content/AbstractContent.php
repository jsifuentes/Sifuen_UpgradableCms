<?php

namespace Sifuen\UpgradableContent\Model\Content;

/**
 * Class AbstractContent
 * @package Sifuen\UpgradableContent\Model\Content
 */
abstract class AbstractContent
{
    /**
     * @param string $identifier
     * @return mixed
     */
    abstract protected function getEntity($identifier);

    /**
     * @param string $identifier
     * @return mixed
     */
    abstract protected function createEntity($identifier);

    /**
     * @param string $identifier
     * @param string $content
     * @param array $changes
     * @return mixed
     */
    abstract public function applyChanges($identifier, $content, array $changes);
}
