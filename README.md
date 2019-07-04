# Sifuen_UpgradableContent

This Magento 2 module allows you to upgrade your CMS blocks and pages using UpgradeData scripts.

## Installation

You can install this module using composer

```
composer require sifuen/module-upgradable-content
```

Or you can clone this repository and place it into `app/code/Sifuen/UpgradableContent/`.

```
php bin/magento module:enable Sifuen_UpgradableContent
```

## How To Use

Your `UpgradeData` script should inject `Sifuen\UpgradableContent\Model\ContentUpgrader`. In your constructor, you
should initialize the ContentUpgrader using `$this->contentUpgrader->setContentModule([your module name])`. This is used
to find your CMS content files.

### Initializing

Assuming the module you create your UpgradeData file in is called `Sifuen_CmsTest`, you would invoke
`setContentModule('Sifuen_CmsTest')` to tell the ContentUpgrader that this module is where you can find your CMS
content files.

By default, CMS content files are read from `[your module directory]/Setup/content/[version]/[pages/blocks]/[identifier].html`.
To change this, see [Advanced Initialization](#advanced-initialization)

See [Advanced Initialization](#advanced-initialization) for other ways to initialize the ContentUpgrader.

```php
use Sifuen\UpgradableContent\Model\ContentUpgrader;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ContentUpgrader
     */
    private $contentUpgrader;

    /**
     * UpgradeData constructor.
     * @param ContentUpgrader $contentUpgrader
     */
    public function __construct(
        ContentUpgrader $contentUpgrader
    )
    {
        $this->contentUpgrader = $contentUpgrader;
        // Set the current module we are in so the content upgrader
        // can find our content files
        $this->contentUpgrader->setContentModule('Sifuen_CmsTest');
    }
``` 

### Upgrading Content

You are given access to two main methods on the ContentUpgrader instance.

`$this->upgradeCmsPages($version, $identifiers)`

`$this->upgradeCmsBlocks($version, $identifiers)`

`$version` is the module version you are upgrading to.

`$identifiers` can be two things:
* An array of page/block identifiers
* An array of arrays, where the index of each element is the identifier of the page/block being updated/created and the
value is the page/block data, minus the content.


You usually use the second version of `$identifiers` if you are **creating** CMS content, while you will use the first
if you are **updating** CMS content.


#### Example

```php
/**
 * @param ModuleDataSetupInterface $setup
 * @param ModuleContextInterface $context
 * @throws \Magento\Framework\Exception\LocalizedException
 */
public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
{
    if (version_compare($context->getVersion(), '1.0.1', '<')) {
        /**
         * In this instance, we are creating a new CMS page with the identifier
         * 'example-page-test'. The array of page data is added to the Page model
         * using addData()
         */

        $this->contentUpgrader->upgradePages('1.0.1', [
            'examplepage-test' => [
                'title' => 'Example Page',
                'page_layout' => '1column',
                'content_heading' => 'Example Page'
            ]
        ]);
    }

    if (version_compare($context->getVersion(), '1.0.2', '<')) {
        /**
         * Now we're upgrading an existing page named 'examplepage-test'. Only
         * the content will be updated to what the HTML file contains.
         */

        $this->contentUpgrader->upgradePages('1.0.2', ['examplepage-test']);
    }

    if (version_compare($context->getVersion(), '1.0.3', '<')) {
        /**
         * This will create a new CMS block named 'exampleblock-test' with the title
         * 'Example Block Title'.
         */

        $this->contentUpgrader->upgradeBlocks('1.0.3', [
            'exampleblock-test' => [
                'title' => 'Example Block Title'
            ]
        ]);
    }

    if (version_compare($context->getVersion(), '1.0.4', '<')) {
        /**
         * This will update the CMS block 'exampleblock-test' with the newest content from the HTML file
         */
        $this->contentUpgrader->upgradeBlocks('1.0.4', ['exampleblock-test']);
    }
}
```

The files that the ContentUpgrader will read are:

* app/code/Sifuen/CmsTest/Setup/content/1.0.1/pages/examplepage-test.html
* app/code/Sifuen/CmsTest/Setup/content/1.0.2/pages/examplepage-test.html
* app/code/Sifuen/CmsTest/Setup/content/1.0.3/blocks/exampleblock-test.html
* app/code/Sifuen/CmsTest/Setup/content/1.0.4/blocks/exampleblock-test.html

### Advanced Initialization

There are a few constructor arguments that can be overridden on `Sifuen\UpgradableContent\Model\ContentUpgrader`. See
the following `di.xml` example to see what is available to override. 

If you set `contentDirectory` on `ContentUpgrader`, then `contentModuleName` and `moduleContentFolder` are ignored.
`contentDirectory` is meant to completely override how the module finds the content files. 

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Sifuen\UpgradableContent\Model\ContentUpgrader">
        <arguments>
            <!-- The full path where you can find your CMS content files -->
            <argument name="contentDirectory" xsi:type="string">/var/somewhere/else/completely</argument>
            <!-- The module name where to find your CMS content files -->
            <argument name="contentModuleName" xsi:type="string">Sifuen_ModuleName</argument>
            <!-- Where in the module it can find your CMS content files -->
            <argument name="moduleContentFolder" xsi:type="string">Setup/content</argument>
            <!-- The file extension your CMS content files will have -->
            <argument name="contentFileExtension" xsi:type="string">.html</argument>
        </arguments>
    </type>
</config>
```

You can also set these manually in the constructor of your UpgradeData script.

```php
/**
 * UpgradeData constructor.
 * @param ContentUpgrader $contentUpgrader
 */
public function __construct(
    ContentUpgrader $contentUpgrader
)
{
    $this->contentUpgrader = $contentUpgrader;
    // The full path where you can find your CMS content files
    $this->contentUpgrader->setContentDirectory('/var/somewhere/else/completely');
    // The module name where to find your CMS content files
    $this->contentUpgrader->setContentModule('Sifuen_CmsTest');
    // Where in the module it can find your CMS content files
    $this->contentUpgrader->setModuleContentFolder('Setup/content');
    // The file extension your CMS content files will have
    $this->contentUpgrader->setContentFileExtension('.html');
}
```