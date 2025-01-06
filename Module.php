<?php declare(strict_types=1);

namespace AnalyticsSnippet;

if (!class_exists(\Common\TraitModule::class)) {
    require_once dirname(__DIR__) . '/Common/TraitModule.php';
}

use Common\Stdlib\PsrMessage;
use Common\TraitModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\View\Model\JsonModel;
use Laminas\View\View;
use Laminas\View\ViewEvent;
use Omeka\Module\AbstractModule;

/**
 * AnalyticsSnippet
 *
 * Add a snippet, generally a javascript tracker, in public or admin pages, and
 * allows to track json and xml requests.
 *
 * @copyright Daniel Berthereau, 2017-2025
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    use TraitModule;

    const NAMESPACE = __NAMESPACE__;

    protected function preInstall(): void
    {
        $services = $this->getServiceLocator();
        $plugins = $services->get('ControllerPluginManager');
        $translate = $plugins->get('translate');

        if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.65')) {
            $message = new \Omeka\Stdlib\Message(
                $translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
                'Common', '3.4.65'
            );
            throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message);
        }
    }

    public function postInstall(): void
    {
        $messenger = $this->getServiceLocator()->get('ControllerPluginManager')->get('messenger');
        $message = new PsrMessage(
            'Fill the snippet in the main settings.' // @translate
        );
        $messenger->addNotice($message);
        $message = new PsrMessage(
            'To get statistics about keywords used by visitors in search engines, see {link}Matomo/Piwik help{link_end}.', // @translate
            [
                'link' => '<a href="https://matomo.org/faq/reports/analyse-search-keywords-reports/" target="_blank" rel="noopener">',
                'link_end' => '</a>',
            ]
        );
        $message->setEscapeHtml(false);
        $messenger->addNotice($message);
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        $sharedEventManager->attach(
            View::class,
            ViewEvent::EVENT_RESPONSE,
            [$this, 'appendAnalyticsSnippet']
        );
        $sharedEventManager->attach(
            \Omeka\Form\SettingForm::class,
            'form.add_elements',
            [$this, 'handleMainSettings']
        );
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'handleSiteSettings']
        );
    }

    public function appendAnalyticsSnippet(ViewEvent $viewEvent): void
    {
        // In case of error or a internal redirection, there may be two calls.
        static $processed;
        if ($processed) {
            return;
        }
        $processed = true;

        $model = $viewEvent->getParam('model');
        if (is_object($model) && $model instanceof JsonModel) {
            $this->trackCall('json', $viewEvent);
            return;
        }

        $content = $viewEvent->getResponse()->getContent();

        // Quick hack to avoid a lot of checks for an event that always occurs.
        // Headers are not yet available, so the content type cannot be checked.
        // Note: The layout of the theme should start with this doctype, without
        // space or line break. This is not the case in the admin layout of
        // Omeka S 1.0.0, so a check is done.
        // The ltrim is required in case of a bad theme layout, and the substr
        // allows a quicker check because it avoids a trim on all the content.
        // if (substr($content, 0, 15) != '<!DOCTYPE html>') {
        $startContent = ltrim(substr((string) $content, 0, 30));
        if (strpos($startContent, '<!DOCTYPE html>') === 0) {
            $this->trackCall('html', $viewEvent);
        } elseif (strpos($startContent, '<?xml ') !== 0) {
            $this->trackCall('xml', $viewEvent);
        } elseif (json_decode($content) !== null) {
            $this->trackCall('json', $viewEvent);
        } else {
            $this->trackCall('undefined', $viewEvent);
        }
    }

    /**
     * Track an html, an api, a json, an xml or an undefined response.
     *
     * @param string $type "html", "json", "xml", "undefined", or "error".
     * @param Event $event
     */
    protected function trackCall($type, Event $event): void
    {
        $services = $this->getServiceLocator();
        $serverUrl = $services->get('ViewHelperManager')->get('ServerUrl');
        $url = $serverUrl(true);

        $trackers = $services->get('Config')['analyticssnippet']['trackers'];
        foreach ($trackers as $tracker) {
            $tracker = new $tracker();
            $tracker->setServiceLocator($services);
            $tracker->track($url, $type, $event);
        }
    }
}
