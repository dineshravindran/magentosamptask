<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeStockImage\Test\Unit\Model;

use Magento\AdobeStockAssetApi\Api\Data\AssetInterface;
use Magento\AdobeStockAssetApi\Api\SaveAssetInterface;
use Magento\AdobeStockImage\Model\Extract\AdobeStockAsset as DocumentToAsset;
use Magento\AdobeStockImage\Model\SaveImage;
use Magento\AdobeStockImage\Model\SaveImageFile;
use Magento\AdobeStockImage\Model\SaveMediaGalleryAsset;
use Magento\Framework\Api\Search\Document;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Save image model.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveImageTest extends TestCase
{
    /**
     * @var MockObject|SaveAssetInterface
     */
    private $saveAdobeStockAsset;

    /**
     * @var MockObject|DocumentToAsset
     */
    private $documentToAsset;

    /**
     * @var SaveImageFile|MockObject
     */
    private $saveImageFile;

    /**
     * @var SaveMediaGalleryAsset|MockObject
     */
    private $saveMediaGalleryAsset;

    /**
     * @var SaveImage
     */
    private $saveImage;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->saveAdobeStockAsset = $this->createMock(SaveAssetInterface::class);
        $this->documentToAsset = $this->createMock(DocumentToAsset::class);
        $this->saveImageFile = $this->createMock(SaveImageFile::class);
        $this->saveMediaGalleryAsset = $this->createMock(SaveMediaGalleryAsset::class);
        $this->saveImage = (new ObjectManager($this))->getObject(
            SaveImage::class,
            [
                'saveAdobeStockAsset' =>  $this->saveAdobeStockAsset,
                'documentToAsset' =>  $this->documentToAsset,
                'saveImageFile' => $this->saveImageFile,
                'saveMediaGalleryAsset' => $this->saveMediaGalleryAsset
            ]
        );
    }

    /**
     * Verify that image from the Adobe Stock can be saved.
     *
     * @dataProvider imageDataProvider
     * @param \Closure $document
     * @param string $url
     * @param string $destinationPath
     * @throws LocalizedException
     */
    public function testExecute(\Closure $document, string $url, string $destinationPath): void
    {
        $document = $document($this);
        $assetId = 42;
        $asset = $this->createMock(AssetInterface::class);

        $this->saveImageFile->expects($this->once())
            ->method('execute')
            ->with($document, $url, $destinationPath);

        $this->saveMediaGalleryAsset->expects($this->once())
            ->method('execute')
            ->with($document, $destinationPath)
            ->willReturn($assetId);

        $this->documentToAsset->expects($this->once())
            ->method('convert')
            ->with($document, ['media_gallery_id' => $assetId])
            ->willReturn($asset);

        $this->saveAdobeStockAsset->expects($this->once())
            ->method('execute')
            ->with($asset);

        $this->saveImage->execute($document, $url, $destinationPath);
    }

    /**
     * Data provider for testExecute
     *
     * @return array[]
     */
    public static function imageDataProvider(): array
    {
        $document = static fn (self $testCase) => $testCase->getMockForDocumentClass();
        return [
            [
                $document,
                'https://as2.ftcdn.net/jpg/500_FemVonDcttCeKiOXFk.jpg',
                'path'
            ]
        ];
    }

    protected function getMockForDocumentClass()
    {
        return $this->createMock(Document::class);
    }

    /**
     * @return array
     */
    public static function getInvalidPathValues(): array
    {
        $document = static fn (self $testCase) => $testCase->getMockForDocumentClass();
        return [
            [
                $document,
                'https://as2.ftcdn.net/jpg/500_FemVonDcttCeKiOXFk.jpg',
                '\\invalid chars\\'
            ],
            [
                $document,
                'https://as2.ftcdn.net/jpg/500_FemVonDcttCeKiOXFk.jpg',
                '{*invalid_path/\'chars}'
            ],
            [
                $document,
                'https://as2.ftcdn.net/jpg/500_FemVonDcttCeKiOXFk.jpg',
                '<img src=\"\" onerror=\"alert(0)\">'
            ]
        ];
    }

    /**
     * Verify that path validation works if invalid characters are passed.
     *
     * @dataProvider getInvalidPathValues
     * @param \Closure $document
     * @param string $url
     * @param string $destinationPath
     * @throws LocalizedException
     */
    public function testExecuteInvalidPath(\Closure $document, string $url, string $destinationPath): void
    {
        $document = $document($this);
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->saveImage->execute($document, $url, $destinationPath);
    }
}
