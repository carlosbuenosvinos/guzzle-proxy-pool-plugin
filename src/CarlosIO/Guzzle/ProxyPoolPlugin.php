<?php
namespace CarlosIO\Guzzle;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProxyPoolPlugin
 * @package CarlosIO\Guzzle
 */
class ProxyPoolPlugin implements EventSubscriberInterface
{
    /**
     * @var int|null
     */
    private $indexCurrentProxy = null;

    /**
     * @var int
     */
    private $numProxies = 0;

    /**
     * @var array|null
     */
    private $proxies = null;

    /**
     * @{inheritdoc}
     */
    public function __construct(array $proxies)
    {
        if (empty($proxies)) {
            throw new \Guzzle\Common\Exception\RuntimeException('At least one proxy has to be defined');
        }

        $this->proxies = $proxies;
        $this->numProxies = count($proxies);
        $this->indexCurrentProxy = 0;
    }

    /**
     * @{inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'request.before_send' => 'onBeforeSend'
        );
    }

    /**
     * Return the next proxy (round robbin model)
     *
     * @return mixed
     */
    private function getNextProxy()
    {
        return $this->proxies[($this->indexCurrentProxy++ % $this->numProxies)];
    }

    /**
     * Calculate and set the proxy to use
     *
     * @param \Guzzle\Common\Event $event
     */
    public function onBeforeSend(\Guzzle\Common\Event $event)
    {
        $request = $event['request'];
        $request->getCurlOptions()->set(CURLOPT_PROXY, $this->getNextProxy());
        $request->getCurlOptions()->set(CURLOPT_HTTPPROXYTUNNEL, true);
    }
}
