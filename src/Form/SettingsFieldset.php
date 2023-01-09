<?php declare(strict_types=1);

namespace AnalyticsSnippet\Form;

use AnalyticsSnippet\Form\Element as AnalyticsSnippetElement;
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
                'type' => AnalyticsSnippetElement\OptionalRadio::class,
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
        ;
    }
}
