<?php
/**
 * @author "Maksim Tyugaev" <tugmaks@yandex.ru>
 */

declare(strict_types=1);

namespace Tugmaks\CoinCore;

/**
 * Class Configuration
 */
class Configuration
{
    /** @var string */
    private $url;

    /** @var int */
    private $port;

    /** @var string */
    private $login;

    /** @var string */
    private $password;

    /**
     * Configuration constructor.
     *
     * @param string $url
     * @param int    $port
     * @param string $login
     * @param string $password
     */
    public function __construct(string $url, int $port, string $login, string $password)
    {
        $this->url      = $url;
        $this->port     = $port;
        $this->login    = $login;
        $this->password = $password;
    }

    /**
     * Return Url
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Return Port
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Return Login
     *
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * Return Password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getUrlAndPort():string
    {
        return sprintf('%s:%s', $this->getUrl(), $this->getPort());
    }


}