<?php declare(strict_types=1);

namespace AnalyticsSnippetTest\Tracker;

use AnalyticsSnippet\Tracker\AbstractTracker;
use Laminas\ServiceManager\ServiceManager;
use Omeka\Settings\Settings;
use PHPUnit\Framework\TestCase;

/**
 * The ip is resolved with the trusted proxies of the main settings.
 */
class ClientIpTest extends TestCase
{
    protected $serverBackup;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $_SERVER['REMOTE_ADDR'] = '192.0.2.10';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1, 203.0.113.50';
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
    }

    protected function clientIp(array $trustedProxies = []): string
    {
        $settings = $this->createMock(Settings::class);
        $settings->method('get')->willReturnCallback(fn ($name, $default = null) => $default);
        $clientIpClass = class_exists(\Omeka\Stdlib\ClientIp::class) ? \Omeka\Stdlib\ClientIp::class : \Common\Stdlib\ClientIp::class;
        $tracker = new class extends AbstractTracker {
            public function publicGetClientIp()
            {
                return $this->getClientIp();
            }
        };
        $tracker->setServiceLocator(new ServiceManager([
            'services' => [
                'Omeka\Settings' => $settings,
                'Omeka\ClientIp' => new $clientIpClass($trustedProxies),
            ],
        ]));
        return $tracker->publicGetClientIp();
    }

    public function testTrustedProxies(): void
    {
        $this->assertSame('203.0.113.50', $this->clientIp(['192.0.2.10']));
        $this->assertSame('192.0.2.10', $this->clientIp(['10.0.0.1']));
    }
}
