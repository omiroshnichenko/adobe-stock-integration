<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeStockImage\Model;

use Magento\AdobeStockImageApi\Api\SaveImagePreviewInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

/**
 * Class SaveImagePreview
 */
class SaveImagePreview implements SaveImagePreviewInterface
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GetImageByAdobeId
     */
    private $getImageByAdobeId;

    /**
     * @var SaveAsset
     */
    private $saveAsset;

    /**
     * SaveImagePreview constructor.
     * @param SaveAsset $saveAsset
     * @param Storage $storage
     * @param GetImageByAdobeId $getImageByAdobeId
     * @param LoggerInterface $logger
     */
    public function __construct(
        SaveAsset $saveAsset,
        Storage $storage,
        GetImageByAdobeId $getImageByAdobeId,
        LoggerInterface $logger
    ) {
        $this->storage = $storage;
        $this->logger = $logger;
        $this->getImageByAdobeId = $getImageByAdobeId;
        $this->saveAsset = $saveAsset;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $adobeId, string $destinationPath): void
    {
        try {
            $asset = $this->getImageByAdobeId->execute($adobeId);
            $path = $this->storage->save($asset->getPreviewUrl(), $destinationPath);
            $asset->setPath($path);
            $this->saveAsset->execute($asset);
        } catch (\Exception $exception) {
            $message = __('Image was not saved: %1', $exception->getMessage());
            $this->logger->critical($message);
            throw new CouldNotSaveException($message);
        }
    }
}
