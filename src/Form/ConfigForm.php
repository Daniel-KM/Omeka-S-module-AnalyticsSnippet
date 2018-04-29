<?php
namespace AnalyticsSnippet\Form;

use Zend\Form\Element\Textarea;
use Zend\Form\Form;

class ConfigForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'analyticssnippet_inline_public',
            'type' => Textarea::class,
            'options' => [
                'label' => 'Code to append to public pages', // @translate
                'info' => 'Don’t forget to add the tags <script> and </script> for javascript.', // @translate
            ],
            'attributes' => [
                'id' => 'analyticssnippet-inline-public',
                'rows' => 12,
                'placeholder' => '<script>
console.log("Analytics Snippet ready!");
</script>',
            ],
        ]);

        $this->add([
            'name' => 'analyticssnippet_inline_admin',
            'type' => Textarea::class,
            'options' => [
                'label' => 'Code to append to admin pages', // @translate
                'info' => 'Don’t forget to add the tags <script> and </script> for javascript.', // @translate
            ],
            'attributes' => [
                'id' => 'analyticssnippet-inline-admin',
                'rows' => 12,
                'placeholder' => '<script>
console.log("Analytics Snippet ready for admin!");
</script>',
            ],
        ]);
    }
}
