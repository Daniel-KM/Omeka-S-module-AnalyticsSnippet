<?php declare(strict_types=1);

namespace AnalyticsSnippet;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\View\Model\JsonModel;
use Laminas\View\View;
use Laminas\View\ViewEvent;

/**
 * AnalyticsSnippet
 *
 * Add a snippet, generally a javascript tracker, in public or admin pages, and
 * allows to track json and xml requests.
 *
 * @copyright Daniel Berthereau, 2017-2023
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    public function postInstall(): void
    {
        $messenger = $this->getServiceLocator()->get('ControllerPluginManager')->get('messenger');
        $message = new \Omeka\Stdlib\Message(
            'Fill the snippet in the main settings.' // @translate
        );
        $messenger->addNotice($message);
        $message = new \Omeka\Stdlib\Message(
            'To get statistics about keywords used by visitors in search engines, see %1$sMatomo/Piwik help%2$s.', // @translate
            '<a href="https://matomo.org/faq/reports/analyse-search-keywords-reports/" target="_blank">',
            '</a>'
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
