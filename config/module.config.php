<?php declare(strict_types=1);

namespace AnalyticsSnippet;

return [
    'form_elements' => [
        'invokables' => [
            Form\Element\OptionalRadio::class => Form\Element\OptionalRadio::class,
            Form\SettingsFieldset::class => Form\SettingsFieldset::class,
            Form\SiteSettingsFieldset::class => Form\SiteSettingsFieldset::class,
        ],
    ],
    'analyticssnippet' => [
        'settings' => [
            'analyticssnippet_inline_public' => '',
            'analyticssnippet_inline_admin' => '',
            // Position is "body_end" or "head_end" (recommended).
            'analyticssnippet_position' => 'head_end',
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
