PHP Litecoin Core and Bitcoin Core rpc client
===========


This package provides an easy to use rpc client for litecoin and bitcoin wallets

Installation
------------

The recommended way to install client is using
[Composer](http://getcomposer.org/):

[Download and install](http://getcomposer.org/doc/00-intro.md) Composer.

Add ``tugmaks/php-coin-core`` as a dependency of your project:

    
    $ composer require tugmaks/php-coin-core php-http/guzzle6-adapter
    

   
Note: This client relies on [HTTPlug](http://httplug.io/) to perform HTTP requests.
So you will need to install a [client implementation](https://packagist.org/providers/php-http/client-implementation)
to use the this client. The command above uses the Guzzle 6 adapter, but you can use
any implementation.

Usage
------------

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Tugmaks\CoinCore\Client\ClientFactory;
use Tugmaks\CoinCore\Client\RpcClient;
use Tugmaks\CoinCore\Configuration;

$conf = new Configuration('http://127.0.0.1', 9332, 'guest', 'guest');
$client = ClientFactory::create($conf);

$rpcClient = new RpcClient($conf->getUrlAndPort(), $client);

echo $rpcClient->getBalance();
```

Response from rpc
-----------------

This library does not handle deserialization of response.
Typical response (for getBalance) will look like:

```
 {"result":0.15707054,"error":null,"id":null}
``` 

You are free to use json_decode or any other advanced serializers to work with response.
