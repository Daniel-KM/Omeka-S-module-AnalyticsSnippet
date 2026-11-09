<?php declare(strict_types=1);

namespace AnalyticsSnippet\Stdlib;

/**
 * Apply a level of privacy to the ip of the visitor sent to the trackers.
 *
 * The ip itself is resolved by the service Omeka\ClientIp, that manages the
 * reverse proxies.
 */
class ClientIp
{
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
}
