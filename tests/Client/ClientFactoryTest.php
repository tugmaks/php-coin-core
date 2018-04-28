<?php
/**
 * @author "Maksim Tyugaev" <tugmaks@yandex.ru>
 */

declare(strict_types = 1);

namespace Tugmaks\CoinCore\Tests\Client;

use Http\Client\HttpClient;
use PHPUnit\Framework\TestCase;
use Tugmaks\CoinCore\Client\ClientFactory;
use Tugmaks\CoinCore\Configuration;

class ClientFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function itCreatesHttpClient()
    {
        $dummyConfiguration = new Configuration('localhost', 9332, 'guest', 'guest');

        $client = ClientFactory::create($dummyConfiguration);

        $this->assertInstanceOf(HttpClient::class, $client);
    }
}