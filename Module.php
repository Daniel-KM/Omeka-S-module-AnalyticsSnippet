<?php
namespace AnalyticsSnippet;

use AnalyticsSnippet\Form\ConfigForm;
use Omeka\Module\AbstractModule;
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
 * admin pages.
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
        $routeMatch = $services->get('Application')->getMvcEvent()->getRouteMatch();
        if ($routeMatch->getParam('__SITE__')) {
            $inlineScript = 'analyticssnippet_inline_public';
        } elseif ($routeMatch->getParam('__ADMIN__')) {
            $inlineScript = 'analyticssnippet_inline_admin';
        } elseif ('Omeka\Controller\Api' === $services->get('ControllerPluginManager')->get('Params')->fromRoute('controller')) {
            return;
        }
        // Manage bad routing of some modules.
        else {
            $basePath = $services->get('ViewHelperManager')->get('BasePath');
            $inlineScript = strpos($_SERVER['REQUEST_URI'], $basePath() . '/admin') === 0
                ? 'analyticssnippet_inline_admin'
                : 'analyticssnippet_inline_public';
        }

        $settings = $services->get('Omeka\Settings');
        $inlineScript = $settings->get($inlineScript);
        if (empty($inlineScript)) {
            return;
        }

        $response = $viewEvent->getResponse();
        $content = (string) $response->getContent();

        // Quick hack to avoid a lot of checks for an event that always occurs.
        // Headers are not yet available, so the content type cannot be checked.
        // Note: The layout of the theme should start with this doctype, without
        // space or line break. This is not the case in the admin layout of
        // Omeka S 1.0.0, so a check is done.
        // The ltrim is required in case of a bad theme layout, and the substr
        // allows a quicker check because it avoids a trim on all the content.
        // if (substr($content, 0, 15) != '<!DOCTYPE html>') {
        if (strpos(ltrim(substr($content, 0, 30)), '<!DOCTYPE html>') !== 0) {
            return;
        }

        $endTagBody = strripos($content, '</body>', -7);
        if (empty($endTagBody)) {
            return;
        }

        $content = substr_replace($content, $inlineScript, $endTagBody, 0);
        $response->setContent($content);
    }
}
