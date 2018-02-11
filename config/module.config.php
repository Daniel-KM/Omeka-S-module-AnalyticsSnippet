<?php
namespace AnalyticsSnippet;

return [
    'form_elements' => [
        'invokables' => [
            Form\ConfigForm::class => Form\ConfigForm::class,
        ],
    ],
    'analyticssnippet' => [
        'config' => [
            'analyticssnippet_inline_public' => '',
            'analyticssnippet_inline_admin' => '',
        ],
        'trackers' => [
            'default' => Tracker\InlineScript::class,
        ],
    ],
];
