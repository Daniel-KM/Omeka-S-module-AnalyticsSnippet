<?php declare(strict_types=1);

namespace AnalyticsSnippetTest\Stdlib;

use AnalyticsSnippet\Stdlib\ClientIp;
use PHPUnit\Framework\TestCase;

class ClientIpTest extends TestCase
{
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
