<?php

namespace Sifuen\UpgradableContent\Model;

use Magento\Framework\Module\Dir;
use Sifuen\UpgradableContent\Model\Content\Page;
use Sifuen\UpgradableContent\Model\Content\Block;

/**
 * Class ContentUpgrader
 * @package Sifuen\UpgradableContent\Model
 */
class ContentUpgrader
{
    /**
     * @var string
     */
    protected $contentDirectory;
    /**
     * @var string
     */
    protected $contentModule;
    /**
     * @var string
     */
    protected $moduleContentFolder;
    /**
     * @var string
     */
    protected $contentFileExtension;
    /**
     * @var Dir
     */
    protected $dir;
    /**
     * @var Page
     */
    protected $pageContent;
    /**
     * @var Block
     */
    protected $blockContent;

    /**
     * ContentUpgrader constructor.
     * @param Dir $dir
     * @param Page $pageContent
     * @param Block $blockContent
     * @param string $contentDirectory
     * @param string $contentModule
     * @param string $moduleContentFolder
     * @param string $contentFileExtension
     */
    public function __construct(
        Dir $dir,
        Page $pageContent,
        Block $blockContent,
        $contentDirectory = '',
        $contentModule = '',
        $moduleContentFolder = '',
        $contentFileExtension = ''
    )
    {
        $this->dir = $dir;
        $this->pageContent = $pageContent;
        $this->blockContent = $blockContent;
        $this->contentDirectory = $contentDirectory;
        $this->contentModule = $contentModule;
        $this->moduleContentFolder = $moduleContentFolder;
        $this->contentFileExtension = $contentFileExtension;
    }

    /**
     * @param string $contentDirectory
     * @return $this
     */
    public function setContentDirectory($contentDirectory)
    {
        $this->contentDirectory = $contentDirectory;
        return $this;
    }

    /**
     * @param string $moduleName
     * @return $this
     */
    public function setContentModule($moduleName)
    {
        $this->contentModule = $moduleName;
        return $this;
    }

    /**
     * @param string $moduleContentFolder
     * @return $this
     */
    public function setModuleContentFolder($moduleContentFolder)
    {
        $this->moduleContentFolder = $moduleContentFolder;
        return $this;
    }

    /**
     * @param string $contentFileExtension
     * @return $this
     */
    public function setContentFileExtension($contentFileExtension)
    {
        $this->contentFileExtension = $contentFileExtension;
        return $this;
    }

    /**
     * @return string
     */
    protected function getContentDirectory()
    {
        if (!$this->contentDirectory) {
            if (!$this->contentModule) {
                throw new \RuntimeException(
                    'Please set the content module by using $this->contentUpgrader->setModuleName([module name]). ' .
                    'See the README.md for other ways to initialize the ContentUpgrader.'
                );
            }

            $this->contentDirectory = $this->getModuleDirectory($this->contentModule) . '/' .
                $this->moduleContentFolder;
        }

        return $this->contentDirectory;
    }

    /**
     * @param string $version
     * @param array $identifiers
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upgradePages($version, array $identifiers)
    {
        $identifiers = $this->normalizeIdentifiers($identifiers);

        foreach ($identifiers as $identifier => $data) {
            $this->pageContent->applyChanges(
                $identifier,
                $this->getContentFile($version, 'pages', $identifier),
                $data
            );
        }
    }

    /**
     * @param string $version
     * @param array $identifiers
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upgradeBlocks($version, array $identifiers)
    {
        $identifiers = $this->normalizeIdentifiers($identifiers);

        foreach ($identifiers as $identifier => $data) {
            $this->blockContent->applyChanges(
                $identifier,
                $this->getContentFile($version, 'blocks', $identifier),
                $data
            );
        }
    }

    /**
     * @param array $identifiers
     * @return array
     */
    private function normalizeIdentifiers(array $identifiers)
    {
        $result = [];

        foreach ($identifiers as $i => $data) {
            if (is_array($data)) {
                $result[$i] = $data;
            } else {
                $result[$data] = [];
            }
        }

        return $result;
    }

    /**
     * @param string $moduleName
     * @return string
     */
    private function getModuleDirectory($moduleName)
    {
        try {
            $path = $this->dir->getDir($moduleName);
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException(sprintf(
                'Unable to find module directory path for %s. Exception thrown: %s %s',
                $moduleName,
                $e->getMessage(),
                $e->getTraceAsString()
            ));
        }

        return rtrim($path, '/');
    }

    /**
     * @param string $version
     * @param string $type
     * @param string $identifier
     * @return string
     */
    protected function getContentFile($version, $type, $identifier)
    {
        $filePath = sprintf(
            '%s/%s/%s/%s%s',
            rtrim($this->getContentDirectory(), '/'),
            $version,
            $type,
            $identifier,
            $this->contentFileExtension
        );

        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('Content file does not exist: ' . $filePath);
        }

        if (($content = file_get_contents($filePath)) === false) {
            throw new \InvalidArgumentException('Unable to read content file: ' . $filePath);
        }

        return $content;
    }
}