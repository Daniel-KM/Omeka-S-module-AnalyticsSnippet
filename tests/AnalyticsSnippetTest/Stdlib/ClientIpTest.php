<?php declare(strict_types=1);

namespace AnalyticsSnippetTest\Stdlib;

use AnalyticsSnippet\Stdlib\ClientIp;
use PHPUnit\Framework\TestCase;

class ClientIpTest extends TestCase
{
    public function testWithoutProxyTheRemoteAddressIsUsed(): void
    {
        $clientIp = new ClientIp();
        $server = ['REMOTE_ADDR' => '172.18.0.9', 'HTTP_X_FORWARDED_FOR' => '203.0.113.5'];
        // The headers are not trusted without proxy: they may be spoofed.
        $this->assertSame('172.18.0.9', $clientIp->resolve($server));
    }

    public function testBehindTrustedProxyTheClientIsUsed(): void
    {
        $clientIp = new ClientIp('172.18.0.0/16');
        $this->assertSame('203.0.113.5', $clientIp->resolve([
            'REMOTE_ADDR' => '172.18.0.9',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.5, 172.18.0.2',
        ]));
        $this->assertSame('198.51.100.7', $clientIp->resolve([
            'REMOTE_ADDR' => '172.18.0.9',
            'HTTP_X_REAL_IP' => '198.51.100.7',
        ]));
    }

    public function testUntrustedPeerIgnoresHeaders(): void
    {
        $clientIp = new ClientIp('172.18.0.0/16 10.0.0.1');
        $this->assertSame('203.0.113.9', $clientIp->resolve([
            'REMOTE_ADDR' => '203.0.113.9',
            'HTTP_X_FORWARDED_FOR' => '1.2.3.4',
        ]));
        $this->assertSame('5.6.7.8', $clientIp->resolve([
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '5.6.7.8',
        ]));
    }

    public function testPrivacyIpv4(): void
    {
        $this->assertSame('192.168.56.12', ClientIp::privacy('192.168.56.12', 'clear'));
        $this->assertSame('192.168.56.0', ClientIp::privacy('192.168.56.12', 'partial_3'));
        $this->assertSame('192.168.0.0', ClientIp::privacy('192.168.56.12', 'partial_2'));
        $this->assertSame('192.0.0.0', ClientIp::privacy('192.168.56.12', 'partial_1'));
        $this->assertSame('0.0.0.0', ClientIp::privacy('192.168.56.12', 'anonymous'));
    }

    public function testPrivacyIpv6(): void
    {
        $this->assertSame('2a01:e34:ec2a::', ClientIp::privacy('2a01:e34:ec2a:94a0::1', 'partial_3'));
        $this->assertSame('2a01::', ClientIp::privacy('2a01:e34:ec2a:94a0::1', 'partial_1'));
        $this->assertSame('::', ClientIp::privacy('2a01:e34:ec2a:94a0::1', 'anonymous'));
        $this->assertSame('', ClientIp::privacy('not-an-ip', 'clear'));
    }
}
