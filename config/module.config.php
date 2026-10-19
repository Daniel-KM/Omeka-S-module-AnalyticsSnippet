<?php declare(strict_types=1);

namespace AnalyticsSnippet;

return [
    'form_elements' => [
        'invokables' => [
            Form\SettingsFieldset::class => Form\SettingsFieldset::class,
            Form\SiteSettingsFieldset::class => Form\SiteSettingsFieldset::class,
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => \Laminas\I18n\Translator\Loader\Gettext::class,
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'analyticssnippet' => [
        'settings' => [
            'analyticssnippet_inline_public' => '',
            'analyticssnippet_inline_admin' => '',
            // Position is "body_end" or "head_end" (recommended).
            'analyticssnippet_position' => 'head_end',
            // Ips or cidr ranges of the reverse proxies in front of the server.
            'analyticssnippet_trusted_proxies' => '',
            // Privacy of the ip sent to the trackers: "clear", "partial_3",
            // "partial_2", "partial_1" or "anonymous".
            'analyticssnippet_ip_privacy' => 'clear',
        ],
        'site_settings' => [
            'analyticssnippet_inline_public' => '',
            'analyticssnippet_position' => 'head_end',
        ],
        'trackers' => [
            'default' => Tracker\InlineScript::class,
        ],
    ],
];
