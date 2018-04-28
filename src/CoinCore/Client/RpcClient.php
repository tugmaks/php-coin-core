<?php
/**
 * @author "Maksim Tyugaev" <tugmaks@yandex.ru>
 */

declare(strict_types = 1);

namespace Tugmaks\CoinCore\Client;

use Http\Client\HttpClient;
use Http\Message\RequestFactory;
use Http\Discovery\MessageFactoryDiscovery;
use Psr\Http\Message\ResponseInterface;
use Tugmaks\CoinCore\CoinCoreException;
use Tugmaks\CoinCore\InvalidCredentialsException;
use Tugmaks\CoinCore\ResourceNotFoundException;
use Tugmaks\CoinCore\ServerException;

class RpcClient
{
    /** @var string */
    private $urlAndPort;

    /** @var HttpClient */
    private $httpClient;

    /** @var RequestFactory */
    private $requestFactory;

    /** @var string */
    private $requestId;

    /**
     * RpcClient constructor.
     *
     * @param string              $urlAndPort
     * @param HttpClient          $httpClient
     * @param RequestFactory|null $requestFactory
     */
    public function __construct(
        string $urlAndPort,
        HttpClient $httpClient,
        RequestFactory $requestFactory = null
    ) {
        $this->urlAndPort     = $urlAndPort;
        $this->httpClient     = $httpClient;
        $this->requestFactory = $requestFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * @param string $id
     *
     * @return RpcClient
     */
    public function setRequestId(string $id): self
    {
        $this->requestId = $id;

        return $this;
    }

    /**
     * Returns the hash of the best (tip) block in the longest blockchain.
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getBestBlockHash(): string
    {
        return $this->callRpc('getbestblockhash');
    }

    /**
     * If verbosity is 0, returns a string that is serialized, hex-encoded data for block 'hash'.
     * If verbosity is 1, returns an Object with information about block <hash>.
     * If verbosity is 2, returns an Object with information about block <hash> and information about each transaction.
     *
     * @param string $blockHash
     * @param int    $verbosity
     *
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     *
     * @return string
     */
    public function getBlock(string $blockHash, int $verbosity = 1): string
    {
        $allowedVerbosity = [0, 1, 2];
        if (!in_array($verbosity, $allowedVerbosity)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Verbosity has invalid value. Expected values are: %s, actual: %s',
                    implode(',', $allowedVerbosity),
                    $verbosity
                )
            );
        }

        return $this->callRpc('getblock', [$blockHash, $verbosity]);
    }

    /**
     * Returns an object containing various state info regarding blockchain processing.
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getBlockchainInfo(): string
    {
        return $this->callRpc('getblockchaininfo');
    }

    /**
     * Returns the number of blocks in the longest blockchain.
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getBlockCount(): string
    {
        return $this->callRpc('getblockcount');
    }

    /**
     * Returns hash of block in best-block-chain at height provided.
     *
     * @param int $height
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getBlockHash(int $height): string
    {
        return $this->callRpc('getblockhash', [$height]);
    }

    /**
     * If verbose is false, returns a string that is serialized, hex-encoded data for blockheader 'hash'.
     * If verbose is true, returns an Object with information about blockheader <hash>.
     *
     * @param string $hash
     * @param bool   $verbose
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getBlockHeader(string $hash, bool $verbose = true): string
    {
        return $this->callRpc('getblockheader', [$hash, $verbose]);
    }

    /**
     * Compute statistics about the total number and rate of transactions in the chain.
     *
     * @param int|null    $nBlock    Size of the window in number of blocks (default: one month).
     * @param string|null $blockHash The hash of the block that ends the window.
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getChainTxStats(int $nBlock = null, string $blockHash = null): string
    {
        return $this->callRpc('getchaintxstats', [$nBlock, $blockHash]);
    }

    /**
     * Returns the proof-of-work difficulty as a multiple of the minimum difficulty.
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getDifficulty(): string
    {
        return $this->callRpc('getdifficulty');
    }

    /**
     * Return information about all known tips in the block tree, including the main chain as well as orphaned branches.
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getChainTips(): string
    {
        return $this->callRpc('getchaintips');
    }

    /**
     * If txid is in the mempool, returns all in-mempool ancestors.
     *
     * @param string $txId    The transaction id (must be in mempool)
     * @param bool   $verbose True for a json object, false for array of transaction ids
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getMempoolAncestors(string $txId, bool $verbose = false): string
    {
        return $this->callRpc('getmempoolancestors', [$txId, $verbose]);
    }

    /**
     * If txid is in the mempool, returns all in-mempool descendants.
     *
     * @param string $txId
     * @param bool   $verbose
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getMempoolDescendants(string $txId, bool $verbose = false): string
    {
        return $this->callRpc('getmempooldescendants', [$txId, $verbose]);
    }

    /**
     * Returns mempool data for given transaction
     *
     * @param string $txId
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getMempoolEntry(string $txId): string
    {
        return $this->callRpc('getmempoolentry', [$txId]);
    }

    /**
     * Returns details on the active state of the TX memory pool
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getMempoolInfo(): string
    {
        return $this->callRpc('getmempoolinfo');
    }

    /**
     * Returns all transaction ids in memory pool as a json array of string transaction ids.
     *
     * @param bool $verbose True for a json object, false for array of transaction ids
     *
     * @see RpcClient::getMempoolEntry() to fetch a specific transaction from the mempool.
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getRawMempool(bool $verbose = false): string
    {
        return $this->callRpc('getrawmempool', [$verbose]);
    }

    /**
     * Returns details about an unspent transaction output.
     *
     * @param string $txId           The transaction id
     * @param int    $n              vout number
     * @param bool   $includeMempool Whether to include the mempool. Unspent output that is spent in the mempool won't
     *                               appear.
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getTxOut(string $txId, int $n, $includeMempool = true): string
    {
        return $this->callRpc('gettxout', [$txId, $n, $includeMempool]);
    }

    /**
     * Returns statistics about the unspent transaction output set.
     * Note this call may take some time.
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getTxOutSetInfo(): string
    {
        return $this->callRpc('gettxoutsetinfo');
    }

    /**
     * Returns an object containing information about memory usage
     *
     * @param string $mode
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getMemoryInfo(string $mode = 'stats'): string
    {
        $allowedMods = ['stats', 'mallocinfo'];
        if (!in_array($mode, $allowedMods)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Mode has invalid value. Expected values are: %s, actual: %s',
                    implode(',', $allowedMods),
                    $mode
                )
            );
        }

        return $this->callRpc('getmemoryinfo', [$mode]);
    }

    /**
     * Stop Litecoin server.
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function stop(): string
    {
        return $this->callRpc('stop');
    }

    /**
     * Returns thehe number of seconds that the server has been running
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function uptime():string
    {
        return $this->callRpc('uptime');
    }

    /**
     * NOTE: By default this function only works for mempool transactions. If the -txindex option is
     * enabled, it also works for blockchain transactions. If the block which contains the transaction
     * is known, its hash can be provided even for nodes without -txindex. Note that if a blockhash is
     * provided, only that block will be searched and if the transaction is in the mempool or other
     * blocks, or if this node does not have the given block available, the transaction will not be found.
     *
     * @param string      $txId      The transaction id
     * @param bool        $verbose   If false, return a string, otherwise return a json object
     * @param string|null $blockHash The block in which to look for the transaction
     *
     * @return mixed|string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getRawTransaction(string $txId, bool $verbose = false, string $blockHash = null)
    {
        return $this->callRpc('getrawtransaction', [$txId, $verbose, $blockHash]);
    }

    /**
     * Estimates the approximate fee per kilobyte needed for a transaction to begin
     * confirmation within conf_target blocks if possible and return the number of blocks
     * for which the estimate is valid. Uses virtual transaction size as defined
     * in BIP 141 (witness data is discounted).
     *
     * @param int    $confTarget   Confirmation target in blocks (1 - 1008)
     * @param string $estimateMode The fee estimate mode.
     *                             Whether to return a more conservative estimate which also satisfies
     *                             a longer history. A conservative estimate potentially returns a
     *                             higher feerate and is more likely to be sufficient for the desired
     *                             target, but is not as responsive to short term drops in the
     *                             prevailing fee market.  Must be one of:
     *                             "UNSET" (defaults to CONSERVATIVE)
     *                             "ECONOMICAL"
     *                             "CONSERVATIVE"
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function estimateSmartFee(int $confTarget, string $estimateMode = 'CONSERVATIVE'): string
    {
        $allowedMods = ['UNSET', 'ECONOMICAL', 'CONSERVATIVE'];
        if (!in_array($estimateMode, $allowedMods)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Mode has invalid value. Expected values are: %s, actual: %s',
                    implode(',', $allowedMods),
                    $estimateMode
                )
            );
        }

        return $this->callRpc('estimatesmartfee', [$confTarget, $estimateMode]);
    }

    /**
     * Return information about the given address.
     *
     * @param string $address
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function validateAddress(string $address): string
    {
        return $this->callRpc('validateaddress', [$address]);
    }

    /**
     * Add a nrequired-to-sign multisignature address to the wallet. Requires a new wallet backup.
     * Each key is a Litecoin address or hex-encoded public key.
     * This functionality is only intended for use with non-watchonly addresses.
     * See `importaddress` for watchonly p2sh address support.
     * If 'account' is specified (DEPRECATED), assign address to that account.
     *
     * @param int         $nRequired   The number of required signatures out of the n keys or addresses.
     * @param array       $keys        Array of addresses or hex-encoded public keys
     * @param string|null $account     DEPRECATED. An account to assign the addresses to.
     * @param string|null $accountType The address type to use. Options are "legacy", "p2sh-segwit", and "bech32".
     *                                 Default is set by -addresstype.
     *
     * @return mixed|string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function addMultiSigAddress(int $nRequired, array $keys, string $account = null, string $accountType = null)
    {
        if (null !== $account) {
            $params[] = $account;
        }

        if (null !== $accountType) {
            $allowedTypes = ['legacy', 'p2sh-segwit', 'bech32'];
            if (!in_array($accountType, $allowedTypes)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Account type has invalid value. Expected values are: %s, actual: %s',
                        implode(',', $allowedTypes),
                        $accountType
                    )
                );
            }
            $params[] = $accountType;
        }
        $params = [$nRequired, $keys];

        return $this->callRpc('addmultisigaddress', $params);
    }

    /**
     * If account is not specified, returns the server's total available balance.
     * The available balance is what the wallet considers currently spendable, and is
     * thus affected by options which limit spendability such as -spendzeroconfchange.
     * If account is specified (DEPRECATED), returns the balance in the account.
     * Note that the account "" is not the same as leaving the parameter out.
     * The server total may be different to the balance in the default "" account.
     *
     * @param string $account          DEPRECATED. The account string may be given as a
     *                                 specific account name to find the balance associated with wallet keys in
     *                                 a named account, or as the empty string ("") to find the balance
     *                                 associated with wallet keys not in any named account, or as "*" to find
     *                                 the balance associated with all wallet keys regardless of account.
     *                                 When this option is specified, it calculates the balance in a different
     *                                 way than when it is not specified, and which can count spends twice when
     *                                 there are conflicting pending transactions (such as those created by
     *                                 the bumpfee command), temporarily resulting in low or even negative
     *                                 balances. In general, account balance calculation is not considered
     *                                 reliable and has resulted in confusing outcomes, so it is recommended to
     *                                 avoid passing this argument.
     * @param int    $minConf          Only include transactions confirmed at least this many times.
     * @param bool   $includeWatchOnly Also include balance in watch-only addresses (see 'importaddress')
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getBalance(string $account = '*', int $minConf = 1, bool $includeWatchOnly = false): string
    {
        return $this->callRpc('getbalance', [$account, $minConf, $includeWatchOnly]);
    }

    /**
     * Returns a new Litecoin address for receiving payments.
     * If 'account' is specified (DEPRECATED), it is added to the address book
     * so payments received with the address will be credited to 'account'.
     *
     * @param string|null $account     DEPRECATED. The account name for the address to be linked to. If not provided,
     *                                 the default account "" is used. It can also be set to the empty string "" to
     *                                 represent the default account. The account does not need to exist, it will be
     *                                 created if there is no account by the given name.
     * @param string|null $addressType The address type to use. Options are "legacy", "p2sh-segwit", and "bech32".
     *                                 Default is set by -addresstype.
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getNewAddress(string $account = null, string $addressType = null): string
    {
        if (null !== $addressType) {
            $allowedTypes = ['legacy', 'p2sh-segwit', 'bech32'];
            if (!in_array($addressType, $allowedTypes)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Address type has invalid value. Expected values are: %s, actual: %s',
                        implode(',', $allowedTypes),
                        $addressType
                    )
                );
            }
        }

        return $this->callRpc('getnewaddress', [$account, $addressType]);
    }

    /**
     * Returns the total amount received by the given address in transactions with at least minconf confirmations.
     *
     * @param string $address
     * @param int    $minConf
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getReceivedByAddress(string $address, int $minConf = 1): string
    {
        return $this->callRpc('getreceivedbyaddress', [$address, $minConf]);
    }

    /**
     * Get detailed information about in-wallet transaction <txid>
     *
     * @param string $txId             The transaction id
     * @param bool   $includeWatchonly Whether to include watch-only addresses in balance calculation and details[]
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getTransaction(string $txId, bool $includeWatchonly = false): string
    {
        return $this->callRpc('gettransaction', [$txId, $includeWatchonly]);
    }

    /**
     * Returns the server's total unconfirmed balance
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getUnconfirmedBalance(): string
    {
        return $this->callRpc('getunconfirmedbalance');
    }

    /**
     * Returns an object containing various wallet state info.
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function getWalletInfo(): string
    {
        return $this->callRpc('getwalletinfo');
    }

    /**
     * DEPRECATED. Returns Object that has account names as keys, account balances as values.
     *
     * @deprecated
     *
     * @param int  $minConf
     * @param bool $includeWatchOnly
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function listAccounts(int $minConf = 1, bool $includeWatchOnly = false): string
    {
        return $this->callRpc('listaccounts', [$minConf, $includeWatchOnly]);
    }

    /**
     * Lists groups of addresses which have had their common ownership
     * made public by common use as inputs or as the resulting change
     * in past transactions
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function listAddressGroupings(): string
    {
        $this->callRpc('listaddressgroupings');
    }

    /**
     * DEPRECATED. List balances by account.
     *
     * @deprecated
     *
     * @param int  $minConf          The minimum number of confirmations before payments are included.
     * @param bool $includeEmpty     Whether to include accounts that haven't received any payments.
     * @param bool $includeWatchonly Whether to include watch-only addresses (see 'importaddress').
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function listReceivedByAccount(
        int $minConf = 1,
        bool $includeEmpty = false,
        bool $includeWatchonly = false
    ): string {
        return $this->callRpc('listreceivedbyaccount', [$minConf, $includeEmpty, $includeWatchonly]);
    }

    /**
     * List balances by receiving address.
     *
     * @param int  $minConf          The minimum number of confirmations before payments are included.
     * @param bool $includeEmpty     Whether to include accounts that haven't received any payments.
     * @param bool $includeWatchonly Whether to include watch-only addresses (see 'importaddress').
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function listReceivedByAddress(
        int $minConf = 1,
        bool $includeEmpty = false,
        bool $includeWatchonly = false
    ): string {
        return $this->callRpc('listreceivedbyaddress', [$minConf, $includeEmpty, $includeWatchonly]);
    }

    /**
     * Get all transactions in blocks since block [blockhash], or all transactions if omitted.
     * If "blockhash" is no longer a part of the main chain, transactions from the fork point onward are included.
     * Additionally, if include_removed is set, transactions affecting the wallet which were removed are returned in
     * the "removed" array.
     *
     * @param string|null $blockHash           The block hash to list transactions since
     * @param int         $targetConfirmations Return the nth block hash from the main chain. e.g. 1 would mean the
     *                                         best block hash. Note: this is not used as a filter, but only affects
     *                                         [lastblock] in the return value
     * @param bool        $includeWatchonly    Include transactions to watch-only addresses (see 'importaddress')
     * @param bool        $includeRemoved      Show transactions that were removed due to a reorg in the "removed"
     *                                         array
     *                                         (not guaranteed to work on pruned nodes)
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function listSinceBlock(
        string $blockHash = null,
        int $targetConfirmations = 1,
        bool $includeWatchonly = false,
        bool $includeRemoved = true
    ): string {
        return $this->callRpc('listsinceblock', [$blockHash, $targetConfirmations, $includeWatchonly, $includeRemoved]);
    }

    /**
     * Returns up to 'count' most recent transactions skipping the first 'from' transactions for account 'account'.
     * List the most recent 10 transactions in the systems
     * > litecoin-cli listtransactions
     *
     * List transactions 100 to 120
     * > litecoin-cli listtransactions "*" 20 100
     *
     * @param string $account          DEPRECATED. The account name. Should be "*".
     * @param int    $count            The number of transactions to return
     * @param int    $skip             The number of transactions to skip
     * @param bool   $includeWatchOnly Include transactions to watch-only addresses (see 'importaddress')
     *
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function listTransactions(
        string $account = '*',
        int $count = 10,
        int $skip = 0,
        bool $includeWatchOnly = false
    ): string {
        return $this->callRpc('listtransactions', [$account, $count, $skip, $includeWatchOnly]);
    }

    /**
     * Send an amount to a given address.
     *
     * @param string      $address               The address to send to.
     * @param string      $amount                The amount  to send. eg 0.1
     * @param string|null $comment               A comment used to store what the transaction is for.  This is not part
     *                                           of the transaction, just kept in your wallet.
     * @param string|null $commentTo             A comment to store the name of the person or organization
     *                                           to which you're sending the transaction. This is not part of the
     *                                           transaction, just kept in your wallet.
     * @param bool        $subtractFeeFromAmount The fee will be deducted from the amount being sent.
     *                                           The recipient will receive less litecoins than you enter in the amount
     *                                           field.
     * @param bool        $replaceable           Allow this transaction to be replaced by a transaction with higher
     *                                           fees via BIP 125
     * @param int         $confTarget            Confirmation target (in blocks)
     * @param string      $estimateMode          The fee estimate mode, must be one of:
     *                                           "UNSET"
     *                                           "ECONOMICAL"
     *                                           "CONSERVATIVE"
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function sendToAddress(
        string $address,
        string $amount,
        string $comment = null,
        string $commentTo = null,
        bool $subtractFeeFromAmount = false,
        bool $replaceable = false,
        int $confTarget = 6,
        string $estimateMode = 'UNSET'
    ): string {
        $allowedModes = ['UNSET', 'ECONOMICAL', 'CONSERVATIVE'];
        if (!in_array($estimateMode, $allowedModes)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Estimate mode has invalid value. Expected values are: %s, actual: %s',
                    implode(',', $allowedModes),
                    $estimateMode
                )
            );
        }

        return $this->callRpc(
            'sendtoaddress',
            [$address, $amount, $comment, $commentTo, $subtractFeeFromAmount, $replaceable, $confTarget, $estimateMode]
        );
    }

    /**
     * Returns a list of currently loaded wallets
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function listWallets(): string
    {
        return $this->callRpc('listwallets');
    }

    /**
     * Set the transaction fee per kB. Overwrites the paytxfee parameter
     *
     * @param string $amount
     *
     * @return string
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    public function setTxFee(string $amount):string
    {
        return $this->callRpc('settxfee',[$amount]);
    }

    /**
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     * @throws CoinCoreException
     * @throws \Http\Client\Exception
     */
    private function callRpc(string $method, array $params = []): string
    {
        $request  = $this->requestFactory->createRequest(
            'POST',
            $this->urlAndPort,
            [],
            json_encode(['method' => $method, 'params' => $params, 'id' => $this->requestId])
        );
        $response = $this->httpClient->sendRequest($request);

        if (200 !== $response->getStatusCode()) {
            throw $this->convertResponseToException($response);
        }

        $this->requestId = null;

        return (string)$response->getBody();
    }

    private function convertResponseToException(ResponseInterface $response): CoinCoreException
    {
        switch ($response->getStatusCode()) {
            case 500:
                $serverResponse = json_decode((string)$response->getBody());

                return new ServerException($serverResponse->error->message);
            case 401:
                return new InvalidCredentialsException();
            case 404:
                return new ResourceNotFoundException();
        }
    }
}