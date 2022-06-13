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
        $settings = $this->services->get('Omeka\Settings');

        $routeMatch = $this->services->get('Application')->getMvcEvent()->getRouteMatch();
        if (!$routeMatch || $routeMatch->getParam('__SITE__')) {
            $isAdmin = false;
        } else {
            $isAdmin = $routeMatch->getParam('__ADMIN__')
                // Manage bad routing of some modules.
                || (strpos($_SERVER['REQUEST_URI'], $this->services->get('ViewHelperManager')->get('BasePath')() . '/admin') === 0);
        }

        if ($isAdmin) {
            $inlineScript = $settings->get('analyticssnippet_inline_admin', null);
            if (!$inlineScript) {
                return;
            }
            $position = $settings->get('analyticssnippet_position', 'head_end');
        } else {
            // Check if the main public setting is used or overridden.
            // The main public setting is overridable only when there is a site.
            $siteSlug = $routeMatch ? $routeMatch->getParam('site-slug') : null;
            if ($siteSlug) {
                try {
                    $this->services->get('Omeka\ApiManager')->read('sites', ['slug' => $siteSlug]);
                    $hasSite = true;
                } catch (NotFoundException $e) {
                    $hasSite = false;
                }
            } else {
                $hasSite = false;
            }
            if ($hasSite) {
                $siteSettings = $this->services->get('Omeka\Settings\Site');
                $inlineScript = $siteSettings->get('analyticssnippet_inline_public', null);
                $position = $siteSettings->get('analyticssnippet_position', 'head_end');
            }
            if (empty($inlineScript)) {
                $inlineScript = $settings->get('analyticssnippet_inline_public', null);
                if (!$inlineScript) {
                    return;
                }
                $position = $settings->get('analyticssnippet_position', 'head_end');
            }
        }

        if (empty($inlineScript)) {
            return;
        }

        $response = $event->getResponse();
        $content = $response->getContent();
        $endTag = $position === 'body_end'
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
