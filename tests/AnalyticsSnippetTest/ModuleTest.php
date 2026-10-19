<?php declare(strict_types=1);

namespace AnalyticsSnippetTest;

use AnalyticsSnippet\Module;
use AnalyticsSnippetTest\Tracker\FakeApplication;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\RouteMatch;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\ViewEvent;
use PHPUnit\Framework\TestCase;

/**
 * The listener processes one response per request (static flag), so each test
 * runs in a separate process.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ModuleTest extends TestCase
{
    const PAGE = '<!DOCTYPE html><html><head></head><body></body></html>';

    protected function append(?string $routeName, array $excludedRoutes): void
    {
        if (!defined('OMEKA_PATH')) {
            define('OMEKA_PATH', dirname(__DIR__, 4));
        }
        require_once dirname(__DIR__, 2) . '/Module.php';

        $mvcEvent = new MvcEvent();
        if ($routeName !== null) {
            $routeMatch = new RouteMatch([]);
            $routeMatch->setMatchedRouteName($routeName);
            $mvcEvent->setRouteMatch($routeMatch);
        }

        $viewHelpers = new ServiceManager();
        $viewHelpers->setService('ServerUrl', fn (): string => 'http://example.org/s/test');

        $services = new ServiceManager();
        $services->setService('Application', new FakeApplication($mvcEvent));
        $services->setService('ViewHelperManager', $viewHelpers);
        $services->setService('Config', [
            'analyticssnippet' => [
                'trackers' => [SpyTracker::class],
                'excluded_routes' => $excludedRoutes,
            ],
        ]);

        $response = new Response();
        $response->setContent(self::PAGE);
        $viewEvent = new ViewEvent();
        $viewEvent->setResponse($response);

        $module = new Module();
        $module->setServiceLocator($services);
        $module->appendAnalyticsSnippet($viewEvent);
    }

    public function testExcludedRouteIsNotTracked(): void
    {
        $this->append('site/resource', ['site/resource']);

        $this->assertSame([], SpyTracker::$calls);
    }

    public function testNotExcludedRouteIsTracked(): void
    {
        $this->append('site/page', ['site/resource']);

        $this->assertSame([['url' => 'http://example.org/s/test', 'type' => 'html']], SpyTracker::$calls);
    }

    public function testMissingRouteMatchIsTracked(): void
    {
        $this->append(null, ['site/resource']);

        $this->assertCount(1, SpyTracker::$calls);
    }
}
