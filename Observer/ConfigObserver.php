<?php

namespace Wezz\Postcode\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class ConfigObserver
 * @package Wezz\Postcode\Observer
 */
class ConfigObserver implements ObserverInterface
{
    /**
     * @var \Wezz\Postcode\Model\Api\ClientApi
     */
    protected $clientApi;

    /**
     * @var \Magento\Framework\Notification\NotifierInterface|NotifierPool
     */
    protected $notifierPool;

    protected $messageManager;

    /**
     * ConfigObserver constructor.
     * @param \Wezz\Postcode\Model\Api\ClientApi $clientApi
     * @param \Magento\Framework\Notification\NotifierInterface $notifierPool
     */
    public function __construct(
        \Wezz\Postcode\Model\Api\ClientApi $clientApi,
        \Magento\Framework\Notification\NotifierInterface $notifierPool,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->clientApi = $clientApi;
        $this->notifierPool = $notifierPool;
        $this->messageManager = $messageManager;
    }

    /**
     * Method execute observer
     *
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $result = $this->clientApi->testConnection();

        if (isset($result['status']) && isset($result['message'])) {

            if ($result['status'] == 'error') {
                $this->messageManager->addError($result['message']);
            } else if ($result['status'] == 'success') {
                $this->messageManager->addSuccessMessage($result['message']);
            }

            if (isset($result['info'])) {
                $this->messageManager->addNoticeMessage(__('Postcode.nl API Test Troubleshooting: ') . ' '. implode(' // ', $result['info']));
            }
        }
    }
}