<?php declare(strict_types=1);

namespace AnalyticsSnippet\Form;

use AnalyticsSnippet\Form\Element as AnalyticsSnippetElement;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;

class SiteSettingsFieldset extends Fieldset
{
    protected $label = 'Analytics Snippet'; // @translate

    public function init(): void
    {
        $this
            ->add([
                'name' => 'analyticssnippet_inline_public',
                'type' => Element\Textarea::class,
                'options' => [
                    'label' => 'Code to append to public pages', // @translate
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
                'name' => 'analyticssnippet_position',
                'type' => AnalyticsSnippetElement\OptionalRadio::class,
                'options' => [
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
