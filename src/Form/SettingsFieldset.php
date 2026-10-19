<?php declare(strict_types=1);

namespace AnalyticsSnippet\Form;

use Common\Form\Element as CommonElement;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;

class SettingsFieldset extends Fieldset
{
    protected $label = 'Analytics Snippet'; // @translate

    protected $elementGroups = [
        'analytics' => 'Analytics', // @translate
    ];

    public function init(): void
    {
        $this
            ->setAttribute('id', 'analytics-snippet')
            ->setOption('element_groups', $this->elementGroups)
            ->add([
                'name' => 'analyticssnippet_inline_public',
                'type' => Element\Textarea::class,
                'options' => [
                    'element_group' => 'analytics',
                    'label' => 'Code to append to all public pages (overridable in site settings)', // @translate
                    'info' => 'Don’t forget to add the tags <script> and </script> for javascript.', // @translate
                ],
                'attributes' => [
                    'id' => 'analyticssnippet-inline-public',
                    'rows' => 5,
                    'placeholder' => '<script>
console.log("Analytics Snippet ready!");
</script>',
                ],
            ])
            ->add([
                'name' => 'analyticssnippet_inline_admin',
                'type' => Element\Textarea::class,
                'options' => [
                    'element_group' => 'analytics',
                    'label' => 'Code to append to admin pages', // @translate
                    'info' => 'Don’t forget to add the tags <script> and </script> for javascript.', // @translate
                ],
                'attributes' => [
                    'id' => 'analyticssnippet-inline-admin',
                    'rows' => 5,
                    'placeholder' => '<script>
console.log("Analytics Snippet ready for admin!");
</script>',
                ],
            ])
            ->add([
                'name' => 'analyticssnippet_position',
                'type' => CommonElement\OptionalRadio::class,
                'options' => [
                    'element_group' => 'analytics',
                    'label' => 'Position', // @translate
                    'value_options' => [
                        'head_end' => 'Before "</head>" (recommended)', // @translate
                        'body_end' => 'Before "</body>"', // @translate
                    ],
                ],
                'attributes' => [
                    'id' => 'analyticssnippet_position',
                    'value' => 'head_end',
                ],
            ])
            ->add([
                'name' => 'analyticssnippet_trusted_proxies',
                'type' => Element\Text::class,
                'options' => [
                    'element_group' => 'analytics',
                    'label' => 'Trusted reverse proxies for the trackers', // @translate
                    'info' => 'Ips or cidr ranges (space or comma separated) of the reverse proxies in front of the server (Traefik, Nginx, Varnish…). The headers X-Forwarded-For and X-Real-IP are read only when the direct peer matches this list, so the trackers receive the ip of the visitor and not the one of the proxy. Empty means no proxy.', // @translate
                ],
                'attributes' => [
                    'id' => 'analyticssnippet_trusted_proxies',
                    'placeholder' => '172.18.0.0/16',
                ],
            ])
            ->add([
                'name' => 'analyticssnippet_ip_privacy',
                'type' => CommonElement\OptionalRadio::class,
                'options' => [
                    'element_group' => 'analytics',
                    'label' => 'Privacy of the ip sent to the trackers (server side tracking)', // @translate
                    'info' => 'Apply to the trackers that send the visits from the server, like Matomo with a token or Google. The masked parts are set to zero. The tracker may anonymize the ips too.', // @translate
                    'value_options' => [
                        'clear' => 'Clear ip', // @translate
                        'partial_3' => 'Partial ip (first 3 parts)', // @translate
                        'partial_2' => 'Partial ip (first 2 parts)', // @translate
                        'partial_1' => 'Partial ip (first part)', // @translate
                        'anonymous' => 'Anonymous (no ip)', // @translate
                    ],
                ],
                'attributes' => [
                    'id' => 'analyticssnippet_ip_privacy',
                    'value' => 'clear',
                ],
            ])
        ;
    }
}
