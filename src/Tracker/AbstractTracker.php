<?php declare(strict_types=1);

namespace AnalyticsSnippet\Tracker;

use Laminas\EventManager\Event;
use Laminas\Http\PhpEnvironment\RemoteAddress;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Omeka\Api\Exception\NotFoundException;
use Omeka\Stdlib\Message;

abstract class AbstractTracker implements TrackerInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $services;

    public function setServiceLocator(ServiceLocatorInterface $services): void
    {
        $this->services = $services;
    }

    /**
     * Get service locator.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->services;
    }

    public function track($url, $type, Event $event): void
    {
        if ($type === 'html') {
            $this->trackInlineScript($url, $type, $event);
        } else {
            $this->trackNotInlineScript($url, $type, $event);
        }
    }

    protected function trackInlineScript($url, $type, Event $event): void
    {
        $routeMatch = $this->services->get('Application')->getMvcEvent()->getRouteMatch();
        // Manage public error.
        if (empty($routeMatch)) {
            return;
        } elseif ($routeMatch->getParam('__SITE__')) {
            $inlineScript = 'analyticssnippet_inline_public';
        } elseif ($routeMatch->getParam('__ADMIN__')) {
            $inlineScript = 'analyticssnippet_inline_admin';
        }
        // Manage bad routing of some modules.
        else {
            $basePath = $this->services->get('ViewHelperManager')->get('BasePath');
            $inlineScript = strpos($_SERVER['REQUEST_URI'], $basePath() . '/admin') === 0
                ? 'analyticssnippet_inline_admin'
                : 'analyticssnippet_inline_public';
        }

        if ($inlineScript == 'analyticssnippet_inline_public') {
            // Disable on login page.
            $siteSlug = $routeMatch->getParam('site-slug');
            if (empty($siteSlug)) {
                return;
            }
            
            // Disable on site not found error.
            try {
                $this->services->get('Omeka\ApiManager')->read('sites', ['slug' => $siteSlug]);
            } catch (NotFoundException $e) {
                return;
            }
        }
        
        $inlineScript = ($inlineScript == 'analyticssnippet_inline_admin')
            ? $this->services->get('Omeka\Settings')->get($inlineScript, null)
            : $this->services->get('Omeka\Settings\Site')->get($inlineScript, null);
        
        if (empty($inlineScript)) {
            return;
        }

        $response = $event->getResponse();
        $content = $response->getContent();
        $settings = $this->services->get('Omeka\Settings');
        $endTag = $settings->get('analyticssnippet_position') === 'body_end'
            ? strripos((string) $content, '</body>', -7)
            : stripos((string) $content, '</head>');
        if (empty($endTag)) {
            $this->trackError($url, $type, $event);
            return;
        }

        $content = substr_replace($content, $inlineScript, $endTag, 0);
        $response->setContent($content);
    }

    protected function trackNotInlineScript($url, $type, Event $event): void
    {
    }

    protected function trackError($url, $type, Event $event): void
    {
        $logger = $this->services->get('Omeka\Logger');
        $logger->err(new Message('Error in content "%s" from url %s (referrer: %s; user agent: %s; user #%d; ip %s).', // @translate
            $type, $url, $this->getUrlReferrer(), $this->getUserAgent(), $this->getUserId(), $this->getClientIp()));
    }

    /**
     * Get the url referrer.
     *
     * @return string
     */
    protected function getUrlReferrer()
    {
        return @$_SERVER['HTTP_REFERER'];
    }

    /**
     * Get the ip of the client.
     *
     * @return string
     */
    protected function getClientIp()
    {
        $ip = (new RemoteAddress())->getIpAddress();
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
        ) {
            return $ip;
        }
        return '::';
    }

    /**
     * Get the user agent.
     *
     * @return string
     */
    protected function getUserAgent()
    {
        return @$_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Get the user id.
     *
     * @return int
     */
    protected function getUserId()
    {
        $services = $this->getServiceLocator();
        $identity = $services->get('ViewHelperManager')->get('Identity');
        $user = $identity();
        return $user ? $user->getId() : 0;
    }
}
