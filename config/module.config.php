<?php declare(strict_types=1);
namespace AnalyticsSnippet;

return [
    'form_elements' => [
        'invokables' => [
            Form\SettingsFieldset::class => Form\SettingsFieldset::class,
            Form\SiteSettingsFieldset::class => Form\SiteSettingsFieldset::class,
        ],
    ],
    'analyticssnippet' => [
        'settings' => [
            'analyticssnippet_inline_admin' => '',
        ],
        'site_settings' => [
            'analyticssnippet_inline_public' => '',
        ],
        'trackers' => [
            'default' => Tracker\InlineScript::class,
        ],
    ],
];
