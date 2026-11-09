<?php declare(strict_types=1);

namespace AnalyticsSnippet;

use Common\Stdlib\PsrMessage;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Omeka\Api\Manager $api
 * @var \Omeka\Settings\Settings $settings
 * @var \Laminas\I18n\View\Helper\Translate $translate
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Mvc\Controller\Plugin\Messenger $messenger
 */
$plugins = $services->get('ControllerPluginManager');
$api = $plugins->get('api');
// $config = $services->get('Config');
$settings = $services->get('Omeka\Settings');
$translate = $plugins->get('translate');
// $translator = $services->get('MvcTranslator');
$connection = $services->get('Omeka\Connection');
$messenger = $plugins->get('messenger');
$entityManager = $services->get('Omeka\EntityManager');

if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.96')) {
    $message = new \Omeka\Stdlib\Message(
        $translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
        'Common', '3.4.96'
    );
    $messenger->addError($message);
    throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $translate('Missing requirement. Unable to upgrade.')); // @translate
}

if (version_compare($oldVersion, '3.3.3.2', '<')) {
    $settings->set('analyticssnippet_position', 'body_end');
    $message = new PsrMessage(
        'A new option allows to append the snippet to head or to body.' // @translate
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

if (version_compare($oldVersion, '3.4.7', '<')) {
    // Reuse the trusted proxies of the module Analytics, if any.
    $trusted = (string) $settings->get('analytics_trusted_proxies', '');
    $settings->set('analyticssnippet_trusted_proxies', $trusted);
    // Keep the value set during a previous partial upgrade.
    if ($settings->get('analyticssnippet_ip_privacy') === null) {
        $settings->set('analyticssnippet_ip_privacy', 'clear');
    }
    $message = new PsrMessage(
        'The ip sent by the server side trackers (Matomo with a token, Google) is now resolved behind the trusted reverse proxies, else the trackers receive the ip of the proxy for all the visitors, and a level of privacy can be applied. Check the new settings.' // @translate
    );
    $messenger->addWarning($message);
}

// The trusted proxies are managed in the main settings (module Common): merge
// the list of the module into it.
$moduleTrustedProxies = $settings->get('analyticssnippet_trusted_proxies');
if ($moduleTrustedProxies !== null) {
    $trustedProxies = array_values(array_unique(array_filter(array_merge(
        (array) ($settings->get('trusted_proxies') ?: []),
        is_array($moduleTrustedProxies) ? $moduleTrustedProxies : preg_split('/[\s,;]+/', (string) $moduleTrustedProxies)
    ))));
    $settings->set('trusted_proxies', $trustedProxies);
    $settings->delete('analyticssnippet_trusted_proxies');
    if ($trustedProxies) {
        $messenger->addWarning(new \Omeka\Stdlib\Message(
            'The trusted reverse proxies are now managed in the security section of the main settings and apply to all modules, including the ones that grant rights or lock out by ip. Check that each one is a reverse proxy of the server: %s.', // @translate
            implode(', ', $trustedProxies)
        ));
    }
}
