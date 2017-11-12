<?php
namespace AnalyticsSnippet;

use AnalyticsSnippet\Form\ConfigForm;
use Omeka\Module\AbstractModule;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Mvc\Controller\AbstractController;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\View;
use Zend\View\ViewEvent;

/**
 * AnalyticsSnippet
 *
 * Add a snippet, generally a javascript tracker, at the end of the public or
 * admin pages, and allows to track json and xml requests.
 *
 * @copyright Daniel Berthereau, 2017
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $settings = $serviceLocator->get('Omeka\Settings');
        $config = require __DIR__ . '/config/module.config.php';
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['settings'];
        foreach ($defaultSettings as $name => $value) {
            $settings->set($name, $value);
        }
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $settings = $serviceLocator->get('Omeka\Settings');
        $config = require __DIR__ . '/config/module.config.php';
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['settings'];
        foreach ($defaultSettings as $name => $value) {
            $settings->delete($name);
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $services = $this->getServiceLocator();

        $sharedEventManager->attach(
            View::class,
            ViewEvent::EVENT_RESPONSE,
            [$this, 'appendAnalyticsSnippet']
        );
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');
        $formElementManager = $services->get('FormElementManager');

        $data = [];
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['settings'];
        foreach ($defaultSettings as $name => $value) {
            $data[$name] = $settings->get($name);
        }

        $form = $formElementManager->get(ConfigForm::class);
        $form->init();
        $form->setData($data);
        $html = $renderer->formCollection($form);
        return $html;
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');

        $params = $controller->getRequest()->getPost();

        $form = $this->getServiceLocator()->get('FormElementManager')
            ->get(ConfigForm::class);
        $form->init();
        $form->setData($params);
        if (!$form->isValid()) {
            $controller->messenger()->addErrors($form->getMessages());
            return false;
        }

        $defaultSettings = $config[strtolower(__NAMESPACE__)]['settings'];
        foreach ($params as $name => $value) {
            if (isset($defaultSettings[$name])) {
                $settings->set($name, $value);
            }
        }
    }

    public function appendAnalyticsSnippet(ViewEvent $viewEvent)
    {
        $services = $this->getServiceLocator();
        if ('Omeka\Controller\Api' === $services->get('ControllerPluginManager')->get('Params')->fromRoute('controller')) {
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
        } elseif (json_decode($params['content']) !== null) {
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
    protected function trackCall($type, Event $event)
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
