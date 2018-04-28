<?php
/**
 * @author "Maksim Tyugaev" <tugmaks@yandex.ru>
 */

declare(strict_types = 1);

namespace Tugmaks\CoinCore\Client;

use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Message\Authentication\BasicAuth;
use Tugmaks\CoinCore\Configuration;

class ClientFactory
{
    /**
     * Build the HTTP client to talk with the API.
     *
     * @param Configuration   $configuration
     * @param HttpClient|null $client
     * @param array           $plugins
     *
     * @return HttpClient
     */
    public static function create(
        Configuration $configuration,
        HttpClient $client = null,
        array $plugins = []
    ): HttpClient {
        if (!$client) {
            $client = HttpClientDiscovery::find();
        }

        $authentication    = new BasicAuth($configuration->getLogin(), $configuration->getPassword());
        $plugins[]         = new AuthenticationPlugin($authentication);

        return new PluginClient($client, $plugins);
    }
}