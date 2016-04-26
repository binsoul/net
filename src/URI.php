<?php

declare (strict_types = 1);

namespace BinSoul\Net;

use BinSoul\Common\Equatable;

/**
 * Represents an URI according to RFC 3986.
 */
class URI implements Equatable
{
    /** Unreserved characters */
    const UNRESERVED = '0-9a-zA-Z-\._~';
    /** Sub delim characters */
    const SUB_DELIMS = '!$&\'\(\)\*\+,;=';

    /** @var int[] */
    private static $knownPorts = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'ssh' => 22,
        'telnet' => 23,
    ];

    /** @var string */
    private $scheme;
    /** @var string */
    private $user;
    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var string */
    private $path;
    /** @var string */
    private $query;
    /** @var string */
    private $fragment;

    /**
     * Constructs an instance of the URI class.
     *
     * @param string   $scheme
     * @param string   $host
     * @param string   $path
     * @param string   $query
     * @param string   $fragment
     * @param string   $user
     * @param string   $password
     * @param int|null $port
     */
    public function __construct(
        string $scheme = '',
        string $host = '',
        string $path = '',
        string $query = '',
        string $fragment = '',
        string $user = '',
        string $password = '',
        $port = null
    ) {
        $this->scheme = $scheme != '' ? $this->filterScheme($scheme) : '';
        $this->host = $host != '' ? $this->filterHost($host) : '';
        $this->port = $port !== null ? $this->filterPort($port) : null;
        $this->path = $path != '' ? $this->filterPath($path) : '';
        $this->query = $query != '' ? $this->filterQuery($query) : '';
        $this->fragment = $fragment != '' ? $this->filterFragment($fragment) : '';

        $this->user = $user != '' ? $user : '';
        if ($password != '') {
            $this->user .= ':'.$password;
        }
    }

    /**
     * Parses the given URI string and returns a new URI instance.
     *
     * @param string $uri
     *
     * @return URI
     */
    public static function parse(string $uri): URI
    {
        if ($uri == '') {
            return new static();
        }

        $parts = parse_url($uri);
        if ($parts === false) {
            throw new \InvalidArgumentException(sprintf('Cannot parse malformed uri "%s".', $uri));
        }

        if (!isset($parts['scheme'])) {
            throw new \InvalidArgumentException(sprintf('Missing scheme in uri "%s".', $uri));
        }

        if (count($parts) == 1 && isset($parts['scheme'])) {
            throw new \InvalidArgumentException(sprintf('Missing hierarchical segment in uri "%s".', $uri));
        }

        $defaults = [
            'scheme' => '',
            'host' => '',
            'user' => '',
            'pass' => '',
            'port' => null,
            'path' => '',
            'query' => '',
            'fragment' => '',
        ];

        $parts = array_merge($defaults, $parts);

        return new static(
            $parts['scheme'],
            $parts['host'],
            $parts['path'],
            $parts['query'],
            $parts['fragment'],
            $parts['user'],
            $parts['pass'],
            $parts['port']
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $uri = '';

        if ($this->scheme != '') {
            $uri .= $this->scheme.':';
        }

        $authority = $this->getAuthority();
        if ($authority != '') {
            $uri .= '//'.$authority;
        }

        if ($this->path != '') {
            if ($authority != '') {
                $uri .= '/'.ltrim($this->path, '/');
            } else {
                $uri .= $this->path;
            }
        }

        if ($this->query != '') {
            $uri .= '?'.$this->query;
        }

        if ($this->fragment != '') {
            $uri .= '#'.$this->fragment;
        }

        return $uri;
    }

    /**
     * Returns the scheme scheme component of the URI.
     *
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * Returns the authority component of the URI.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [username[:password]@]host[:port]
     * </pre>
     *
     * @return string
     */
    public function getAuthority(): string
    {
        if ($this->host == '') {
            return '';
        }

        $authority = $this->host;
        if ($this->user != '') {
            $authority = $this->user.'@'.$authority;
        }

        if ($this->port !== null && !$this->isDefaultPort($this->scheme, $this->port)) {
            $authority .= ':'.$this->port;
        }

        return $authority;
    }

    /**
     * Returns the user info component of the URI.
     *
     * The user info syntax of the URI is:
     *
     * <pre>
     * username[:password]
     * </pre>
     *
     * @return string
     */
    public function getUserInfo(): string
    {
        return $this->user;
    }

    /**
     * Returns the host component of the URI.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Returns the port component of the URI.
     *
     * If no port is present or the port is the default port of the scheme null is returned.
     *
     * @return int|null
     */
    public function getPort()
    {
        return $this->port === null || $this->isDefaultPort($this->scheme, $this->port) ? null : $this->port;
    }

    /**
     * Returns the path component of the URI.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns the query component of the URI.
     *
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Returns the fragment component of the URI.
     *
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * Returns a new instance with the specified scheme.
     *
     * @param string $scheme
     *
     * @return URI
     */
    public function withScheme(string $scheme): URI
    {
        $result = clone $this;
        $result->scheme = $this->filterScheme($scheme);

        return $result;
    }

    /**
     * Returns a new instance with the specified username and password.
     *
     * @param string      $username
     * @param string|null $password
     *
     * @return URI
     */
    public function withUserInfo(string $username, $password = null): URI
    {
        $result = clone $this;
        $result->user = $username.((string) $password != '' ? ':'.(string) $password : '');

        return $result;
    }

    /**
     * Returns a new instance with the specified host.
     *
     * @param string $host
     *
     * @return URI
     */
    public function withHost(string $host): URI
    {
        $result = clone $this;
        $result->host = $this->filterHost($host);

        return $result;
    }

    /**
     * Returns a new instance with the specified port.
     *
     * @param int|null $port
     *
     * @return URI
     */
    public function withPort($port): URI
    {
        $result = clone $this;
        $result->port = $this->filterPort($port);

        return $result;
    }

    /**
     * Returns a new instance with the specified path.
     *
     * @param string $path
     *
     * @return URI
     */
    public function withPath(string $path): URI
    {
        $result = clone $this;
        $result->path = $this->filterPath($path);

        return $result;
    }

    /**
     * Returns a new instance with the specified query.
     *
     * @param string $query
     *
     * @return URI
     */
    public function withQuery(string $query): URI
    {
        $result = clone $this;
        $result->query = $this->filterQuery($query);

        return $result;
    }

    /**
     * Returns a new instance with the specified fragment.
     *
     * @param string $fragment
     *
     * @return URI
     */
    public function withFragment(string $fragment): URI
    {
        $result = clone $this;
        $result->fragment = $this->filterFragment($fragment);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getHash(): string
    {
        return md5((string) $this);
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(Equatable $other): bool
    {
        if (!($other instanceof self)) {
            return false;
        }

        return (string) $this == (string) $other;
    }

    /**
     * Checks if the port is the default port for the scheme.
     *
     * @param string $scheme
     * @param int    $port
     *
     * @return bool
     */
    private function isDefaultPort(string $scheme, int $port): bool
    {
        return isset(self::$knownPorts[$scheme]) && self::$knownPorts[$scheme] == $port;
    }

    /**
     * Filters the scheme to ensure it is a valid scheme.
     *
     * @param string $scheme
     *
     * @return string
     */
    private function filterScheme(string $scheme): string
    {
        $result = strtolower($scheme);
        if (isset(self::$knownPorts[$result])) {
            return $result;
        }

        $result = preg_replace('#:(//)?$#', '', $result);
        if (!preg_match('#^[a-z][a-z0-9\+\-\.]*$#i', $result)) {
            throw new \InvalidArgumentException(sprintf('Invalid scheme "%s".', $scheme));
        }

        return $result;
    }

    /**
     * Filters the host to ensure it is properly encoded.
     *
     * @param string $host
     *
     * @return string
     */
    private function filterHost(string $host): string
    {
        return preg_replace_callback(
            '/(?:[^'.self::UNRESERVED.self::SUB_DELIMS.':%\[\]]+|%(?![A-Fa-f0-9]{2}))/',
            function (array $match) {
                return rawurlencode($match[0]);
            },
            $host
        );
    }

    /**
     * Filters the port to ensure it is a valid port.
     *
     * @param int|null $port
     *
     * @return int
     */
    private function filterPort($port)
    {
        if ($port === null) {
            return $port;
        }

        if (!preg_match('#^[0-9]+$#', (string) $port)) {
            throw new \InvalidArgumentException(sprintf('Invalid port "%s".', $port));
        }

        return (int) $port;
    }

    /**
     * Filters the path to ensure it is properly encoded.
     *
     * @param string $path
     *
     * @return string
     */
    private function filterPath(string $path): string
    {
        if ($path == '') {
            return $path;
        }

        $result = preg_replace_callback(
            '/(?:[^'.self::UNRESERVED.self::SUB_DELIMS.':@%~\/]+|%(?![A-Za-f0-9]{2}))/',
            function (array $match) {
                return rawurlencode($match[0]);
            },
            $path
        );

        if ($result[0] != '/') {
            return $result;
        }

        return '/'.ltrim($result, '/');
    }

    /**
     * Filters the query to ensure it is properly encoded.
     *
     * @param string $query
     *
     * @return string
     */
    private function filterQuery(string $query): string
    {
        if (trim($query) == '') {
            return '';
        }

        if ($query[0] == '?') {
            $query = substr($query, 1);
        }

        $parts = explode('&', $query);
        foreach ($parts as $index => $part) {
            $data = explode('=', $part);
            $key = array_shift($data);

            if (count($data) == 0) {
                $parts[$index] = $this->encodeQuery($key);
                continue;
            }

            $value = implode('=', $data);
            $parts[$index] = $this->encodeQuery($key).'='.$this->encodeQuery($value);
        }

        return implode('&', $parts);
    }

    /**
     * Filters the fragment value to ensure it is properly encoded.
     *
     * @param string $fragment
     *
     * @return string
     */
    private function filterFragment(string $fragment): string
    {
        if (trim($fragment) == '') {
            return '';
        }

        if ($fragment[0] == '#') {
            $fragment = substr($fragment, 1);
        }

        return preg_replace_callback(
            '/(?:[^'.self::UNRESERVED.self::SUB_DELIMS.':@%~\/\[\]\?]+|%(?![A-Fa-f0-9]{2}))/',
            function (array $match) {
                return rawurlencode($match[0]);
            },
            $fragment
        );
    }

    /**
     * Percent-encodes all necessary characters of the given query.
     *
     * @param string $value
     *
     * @return string
     */
    private function encodeQuery(string $value): string
    {
        return preg_replace_callback(
            '/(?:[^'.self::UNRESERVED.self::SUB_DELIMS.':@%~\/\[\]\?]+|%(?![A-Fa-f0-9]{2}))/',
            function (array $match) {
                return rawurlencode($match[0]);
            },
            $value
        );
    }
}
