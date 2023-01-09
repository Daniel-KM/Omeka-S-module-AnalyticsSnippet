<?php declare(strict_types=1);

namespace AnalyticsSnippet;

use Omeka\Stdlib\Message;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
 * @var string $oldVersion
 * @var string $newVersion
 *
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Omeka\Api\Manager $api
 * @var array $config
 * @var \Omeka\Settings\Settings $settings
 * @var \Omeka\Mvc\Controller\Plugin\Messenger $messenger
 */
$plugins = $services->get('ControllerPluginManager');
// $entityManager = $services->get('Omeka\EntityManager');
// $connection = $services->get('Omeka\Connection');
// $api = $services->get('Omeka\ApiManager');
// $config = require dirname(__DIR__, 2) . '/config/module.config.php';
$settings = $services->get('Omeka\Settings');
$messenger = $plugins->get('messenger');

if (version_compare($oldVersion, '3.3.3.2', '<')) {
    $settings->set('analyticssnippet_position', 'body_end');
    $message = new Message(
        'A new option allows to append the snippet to head or to body.' // @translate
    );
    $messenger->addNotice($message);
    $message = new Message(
        'To get statistics about keywords used by visitors in search engines, see %1$sMatomo/Piwik help%2$s.', // @translate
        '<a href="https://matomo.org/faq/reports/analyse-search-keywords-reports/" target="_blank">',
        '</a>'
    );
    $message->setEscapeHtml(false);
    $messenger->addNotice($message);
}
