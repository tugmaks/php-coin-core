<?php
/**
 * @author "Maksim Tyugaev" <tugmaks@yandex.ru>
 */

declare(strict_types = 1);

namespace Tugmaks\CoinCore\Tests\Client;

use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Tugmaks\CoinCore\Client\RpcClient;
use GuzzleHttp\Psr7;

class RpcClientTest extends TestCase
{
    /**
     * @test
     */
    public function itAddsRequestIdAndReturnIt()
    {
        $id       = bin2hex(random_bytes(8));
        $stream   = Psr7\stream_for('{"data" : "test", "id": "' . $id . '"}');
        $response = new Psr7\Response(200, ['Content-Type' => 'application/json'], $stream);
        $client   = new Client();
        $client->addResponse($response);
        $rpc = new RpcClient('localhost', $client);

        $info = json_decode($rpc->getBlockchainInfo(), true);

        $this->assertTrue(array_key_exists('id', $info));
        $this->assertSame($id, $info['id']);
    }

    /**
     * @test
     */
    public function itValidatesVerbosityForGetBlock()
    {
        $client = new Client();
        $rpc    = new RpcClient('localhost', $client);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Verbosity has invalid value. Expected values are: 0,1,2, actual: 4');
        $rpc->getBlock('hash', 4);
    }

    /**
     * @test
     */
    public function itValidatesModeForGetMemoryInfo()
    {
        $client = new Client();
        $rpc    = new RpcClient('localhost', $client);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Mode has invalid value. Expected values are: stats,mallocinfo, actual: abracadabra'
        );
        $rpc->getMemoryInfo('abracadabra');
    }

    /**
     * @test
     */
    public function itValidatesModeForEstimateSmartFee()
    {
        $client = new Client();
        $rpc    = new RpcClient('localhost', $client);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Mode has invalid value. Expected values are: UNSET,ECONOMICAL,CONSERVATIVE, actual: abracadabra'
        );
        $rpc->estimateSmartFee(1, 'abracadabra');
    }

    /**
     * @test
     */
    public function itValidatesAccountTypeForAddMultisiAddress()
    {
        $client = new Client();
        $rpc    = new RpcClient('localhost', $client);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Account type has invalid value. Expected values are: legacy,p2sh-segwit,bech32, actual: abracadabra'
        );
        $rpc->addMultiSigAddress(2, ['f', 'f'], '', 'abracadabra');
    }

    /**
     * @test
     */
    public function itValidatesAddressTyoeForGetNewAddress()
    {
        $client = new Client();
        $rpc    = new RpcClient('localhost', $client);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Address type has invalid value. Expected values are: legacy,p2sh-segwit,bech32, actual: abracadabra'
        );
        $rpc->getNewAddress(null, 'abracadabra');
    }

    /**
     * @test
     */
    public function itValidatesEstimateModeForSendToAddress()
    {
        $client = new Client();
        $rpc    = new RpcClient('localhost', $client);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Estimate mode has invalid value. Expected values are: UNSET,ECONOMICAL,CONSERVATIVE, actual: abracadabra'
        );
        $rpc->sendToAddress('fff', '1', '', '', false, false, 10, 'abracadabra');
    }
}