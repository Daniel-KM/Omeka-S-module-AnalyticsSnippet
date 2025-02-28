<?php declare(strict_types=1);

namespace AnalyticsSnippet\Tracker;

use Laminas\EventManager\Event;
use Laminas\ServiceManager\ServiceLocatorInterface;

interface TrackerInterface
{
    /**
     * Set service locator.
     *
     * @param ServiceLocatorInterface $services
     */
    public function setServiceLocator(ServiceLocatorInterface $services);

    /**
     * Track an html, a json, an xml or an undefined response.
     *
     * @param string $url The request full url.
     * @param string $type "html", "json", "xml", "undefined", or "error".
     * @param Event $event
     */
    public function track($url, $type, Event $event);
}
