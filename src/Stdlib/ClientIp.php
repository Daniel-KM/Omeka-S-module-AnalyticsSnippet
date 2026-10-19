<?php declare(strict_types=1);

namespace AnalyticsSnippet\Stdlib;

/**
 * Get the ip of the visitor sent to the trackers.
 *
 * Behind a reverse proxy, the ip of the client is read from the headers
 * X-Forwarded-For and X-Real-IP only when the direct peer is a trusted proxy,
 * else the headers may be spoofed. Then the level of privacy is applied.
 */
class ClientIp
{
    /**
     * @var string[]
     */
    protected $trustedIps = [];

    /**
     * @var array List of trusted ranges: packed network, prefix, length.
     */
    protected $trustedRanges = [];

    /**
     * @param string $trustedProxies Ips or cidr ranges separated with spaces,
     *   commas or semicolons.
     */
    public function __construct(string $trustedProxies = '')
    {
        $tokens = array_filter(array_map('trim', preg_split('/[\s,;]+/', $trustedProxies) ?: []));
        foreach ($tokens as $token) {
            if (strpos($token, '/') !== false) {
                [$network, $prefix] = explode('/', $token, 2);
                $packed = @inet_pton($network);
                if ($packed === false) {
                    continue;
                }
                $prefix = (int) $prefix;
                if ($prefix < 0 || $prefix > strlen($packed) * 8) {
                    continue;
                }
                $this->trustedRanges[] = [$packed, $prefix, strlen($packed)];
            } elseif (filter_var($token, FILTER_VALIDATE_IP)) {
                $this->trustedIps[] = $token;
            }
        }
    }

    /**
     * Resolve the ip of the client from the server params.
     */
    public function resolve(array $server): string
    {
        $remote = (string) ($server['REMOTE_ADDR'] ?? '');
        if (!filter_var($remote, FILTER_VALIDATE_IP)) {
            return '';
        }
        if (!$this->isTrusted($remote)) {
            return $remote;
        }

        // The leftmost ip is the client, the next ones are proxies.
        $chain = array_filter(array_map('trim', explode(',', (string) ($server['HTTP_X_FORWARDED_FOR'] ?? ''))));
        foreach ($chain as $candidate) {
            if (filter_var($candidate, FILTER_VALIDATE_IP) && !$this->isTrusted($candidate)) {
                return $candidate;
            }
        }
        foreach ($chain as $candidate) {
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }
        $real = trim((string) ($server['HTTP_X_REAL_IP'] ?? ''));
        return filter_var($real, FILTER_VALIDATE_IP) ? $real : $remote;
    }

    /**
     * Apply a level of privacy to an ip.
     *
     * The masked parts are set to zero, like the anonymization of the trackers,
     * so the ip remains valid for them.
     *
     * @param string $mode "clear", "partial_3", "partial_2", "partial_1" (number
     *   of kept octets of an ipv4 or groups of an ipv6) or "anonymous".
     */
    public static function privacy(string $ip, string $mode): string
    {
        $packed = $ip === '' ? false : @inet_pton($ip);
        if ($packed === false) {
            return '';
        }
        switch ($mode) {
            case 'clear':
                return $ip;
            case 'partial_3':
            case 'partial_2':
            case 'partial_1':
                $parts = (int) substr($mode, -1);
                // Octets for ipv4, groups of two bytes for ipv6.
                $kept = strlen($packed) === 4 ? $parts : $parts * 2;
                return (string) inet_ntop(substr($packed, 0, $kept) . str_repeat("\0", strlen($packed) - $kept));
            case 'anonymous':
            default:
                return strlen($packed) === 4 ? '0.0.0.0' : '::';
        }
    }

    protected function isTrusted(string $ip): bool
    {
        if (in_array($ip, $this->trustedIps, true)) {
            return true;
        }
        $packed = @inet_pton($ip);
        if ($packed === false) {
            return false;
        }
        foreach ($this->trustedRanges as [$network, $prefix, $length]) {
            if (strlen($packed) !== $length) {
                continue;
            }
            $bytes = intdiv($prefix, 8);
            if (substr($packed, 0, $bytes) !== substr($network, 0, $bytes)) {
                continue;
            }
            $bits = $prefix % 8;
            if ($bits === 0) {
                return true;
            }
            $mask = (0xFF << (8 - $bits)) & 0xFF;
            if ((ord($packed[$bytes]) & $mask) === (ord($network[$bytes]) & $mask)) {
                return true;
            }
        }
        return false;
    }
}
