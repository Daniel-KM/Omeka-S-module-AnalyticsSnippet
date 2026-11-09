<?php declare(strict_types=1);

namespace AnalyticsSnippetTest\Tracker;

use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\RouteMatch;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\ViewEvent;
use PHPUnit\Framework\TestCase;

/**
 * Base for tracker unit tests: builds a minimal service manager with fake
 * settings, route match, api and logger, so no database is required.
 */
abstract class TrackerTestCase extends TestCase
{
    /**
     * @var FakeLogger
     */
    protected $logger;

    /**
     * Build the services required by AbstractTracker::trackInlineScript().
     *
     * Options:
     * - settings (array): main settings.
     * - site_settings (array): site settings.
     * - route_match (array|null): route match params, null for no route.
     * - site_exists (bool): whether the site slug is a known site.
     */
    protected function buildServices(array $options = []): ServiceManager
    {
        $options += [
            'settings' => [],
            'site_settings' => [],
            'route_match' => ['__SITE__' => true, 'site-slug' => 'test'],
            'site_exists' => true,
        ];

        $mvcEvent = new MvcEvent();
        if ($options['route_match'] !== null) {
            $mvcEvent->setRouteMatch(new RouteMatch($options['route_match']));
        }

        $this->logger = new FakeLogger();

        $viewHelpers = new ServiceManager();
        $viewHelpers->setService('BasePath', fn (): string => '');
        $viewHelpers->setService('Identity', fn () => null);

        $services = new ServiceManager();
        $services->setService('Omeka\Settings', new FakeSettings($options['settings']));
        $services->setService('Omeka\Settings\Site', new FakeSettings($options['site_settings']));
        $services->setService('Omeka\ApiManager', new FakeApiManager((bool) $options['site_exists']));
        $services->setService('Omeka\Logger', $this->logger);
        $services->setService('Omeka\ClientIp', class_exists(\Omeka\Stdlib\ClientIp::class) ? new \Omeka\Stdlib\ClientIp() : new \Common\Stdlib\ClientIp());
        $services->setService('ViewHelperManager', $viewHelpers);
        $services->setService('Application', new FakeApplication($mvcEvent));

        return $services;
    }

    protected function buildViewEvent(string $content): ViewEvent
    {
        $response = new Response();
        $response->setContent($content);

        $viewEvent = new ViewEvent();
        $viewEvent->setResponse($response);

        return $viewEvent;
    }
}
