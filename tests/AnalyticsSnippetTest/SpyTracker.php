<?php declare(strict_types=1);

namespace AnalyticsSnippetTest;

use AnalyticsSnippet\Tracker\TrackerInterface;
use Laminas\EventManager\Event;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Tracker that records the tracked calls.
 */
class SpyTracker implements TrackerInterface
{
    /**
     * @var array
     */
    public static $calls = [];

    public function setServiceLocator(ServiceLocatorInterface $services): void
    {
    }

    public function track($url, $type, Event $event): void
    {
        self::$calls[] = ['url' => $url, 'type' => $type];
    }
}
