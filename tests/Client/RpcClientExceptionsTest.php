<?php
/**
 * @author "Maksim Tyugaev" <tugmaks@yandex.ru>
 */

declare(strict_types = 1);

namespace Tugmaks\CoinCore\Tests\Client;

use GuzzleHttp\Psr7;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Tugmaks\CoinCore\Client\RpcClient;
use Tugmaks\CoinCore\InvalidCredentialsException;
use Tugmaks\CoinCore\ResourceNotFoundException;
use Tugmaks\CoinCore\ServerException;


class RpcClientExceptionsTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider clientExceptions
     */
    public function itThrowsClientExceptions(int $code, string $exceptionClass)
    {
        $response = new Psr7\Response($code);
        $client   = new Client();
        $client->addResponse($response);
        $rpc = new RpcClient('localhost', $client);

        $this->expectException($exceptionClass);
        $rpc->getBlockchainInfo();
    }

    public function clientExceptions()
    {
        return [
            [
                401,
                InvalidCredentialsException::class,
            ],
            [
                404,
                ResourceNotFoundException::class,
            ],
        ];
    }

    /**
     * @test
     */
    public function itThrowsServerException()
    {
        $stream   = Psr7\stream_for('{"data" : "", "error":{"code":-5,"message":"Server message"}}');
        $response = new Psr7\Response(500, ['Content-Type' => 'application/json'], $stream);
        $client   = new Client();
        $client->addResponse($response);
        $rpc = new RpcClient('localhost', $client);

        $this->expectException(ServerException::class);
        $this->expectExceptionMessage('Server message');
        $rpc->getBlockchainInfo();
    }
}