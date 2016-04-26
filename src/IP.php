<?php

declare (strict_types = 1);

namespace BinSoul\Net;

use BinSoul\Common\Equatable;

/**
 * Represents an IPv4 or IPv6 address.
 */
class IP implements Equatable
{
    /** @var string */
    private $address;
    /** @var bool */
    private $isIPv6;

    /**
     * Constructs an instance of this class.
     *
     * @param string $ip
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $ip)
    {
        if (!self::isValid($ip)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid IP address.', $ip));
        }

        $this->address = strtolower($ip);
        $this->isIPv6 = filter_var($this->address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Returns the given address.
     *
     * The address is not expanded or compcated. It is returned exactly how it was provided.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->address;
    }

    /**
     * Checks if the given IP string is a valid IP address.
     *
     * @param string $ip
     *
     * @return bool
     */
    public static function isValid(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Returns a fully expanded representation of the IP address.
     *
     * This will not compact the address and return exactly 8 x 2byte integers in a hexdecimal
     * representation separated by :.
     *
     * 2001::1 becomes 2001:0000:0000:0000:0000:0000:0000:0001
     *
     * @return IP
     */
    public function expand(): IP
    {
        $result = clone $this;

        if ($this->isIPv6) {
            $bytes = unpack('n*', inet_pton($this->address));

            $result->address = implode(
                ':',
                array_map(
                    function ($b) {
                        return sprintf('%04x', $b);
                    },
                    $bytes
                )
            );
        }

        return $result;
    }

    /**
     * Returns a compact representation of the IP address.
     *
     * For further information about compact IP addresses, please read RFC 3513.
     *
     * 2001:0000:0000:0000:0000:0000:0000:0001 becomes 2001::1
     *
     * @return IP
     */
    public function compact(): IP
    {
        $result = clone $this;

        if ($this->isIPv6) {
            $result->address = inet_ntop(inet_pton($this->address));
        }

        return $result;
    }

    /**
     * Checks if the IP is a private IP address.
     *
     * @return bool
     */
    public function isPrivate(): bool
    {
        if ($this->isLoopback()) {
            return true;
        }

        $filtered = filter_var(
            $this->address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        return $filtered === false;
    }

    /**
     * Checks whether this IP is in the given range or not.
     *
     * Matches:
     * - xxxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx (exact)
     * - xxx.xxx.xxx.xxx (exact)
     * - xxxx:xxxx:xxxx:xxxx/nn (CIDR)
     * - xxx.xxx.xxx/nn (CIDR)
     * - xxxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx-yyyy:yyyy:yyyy:yyyy:yyyy:yyyy:yyyy:yyyy/nn (range)
     * - xxx.xxx.xxx.xxx-yyy.yyy.yyy.yyy (range)
     *
     * @param string $range IP range to match
     *
     * @return bool
     */
    public function isInRange(string $range): bool
    {
        $targetRange = strtolower($range);
        $myIP = bin2hex(inet_pton($this->address));

        if (strpos($targetRange, '-') !== false) {
            $parts = explode('-', $targetRange);
            if (count($parts) > 2) {
                throw new \InvalidArgumentException(sprintf('Invalid range "%s" given.', $range));
            }

            $firstIP = bin2hex(inet_pton(trim($parts[0])));
            $lastIP = bin2hex(inet_pton(trim($parts[1])));

            return $myIP >= $firstIP && $myIP <= $lastIP;
        } elseif (strpos($targetRange, '/') !== false) {
            $parts = explode('/', $targetRange);
            if (count($parts) > 2) {
                throw new \InvalidArgumentException(sprintf('Invalid range "%s" given.', $range));
            }

            if (strpos($parts[0], '.') !== false) {
                $x = explode('.', trim($parts[0]));
                while (count($x) < 4) {
                    $x[] = '0';
                }

                $firstIP = bin2hex(inet_pton(implode('.', $x)));
                $variableBits = 32 - $parts[1];
                $pos = 7;
            } else {
                $firstIP = bin2hex(inet_pton(trim($parts[0])));
                $variableBits = 128 - $parts[1];
                $pos = 31;
            }

            $lastIP = $firstIP;
            while ($variableBits > 0) {
                $oldValue = hexdec(substr($lastIP, $pos, 1));
                $newValue = $oldValue | (pow(2, min(4, $variableBits)) - 1);
                $lastIP = substr_replace($lastIP, dechex($newValue), $pos, 1);
                $variableBits -= 4;
                $pos -= 1;
            }

            return $myIP >= $firstIP && $myIP <= $lastIP;
        } else {
            try {
                $targetIP = new self($targetRange);

                return $this->isEqualTo($targetIP);
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(sprintf('Invalid range "%s" given.', $range));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getHash(): string
    {
        return md5((string) $this->expand());
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(Equatable $other): bool
    {
        if (!($other instanceof self)) {
            return false;
        }

        return (string) $this->expand() == (string) $other->expand();
    }

    /**
     * Checks if the IP is a known loopback address.
     *
     * @return bool
     */
    private function isLoopback(): bool
    {
        if ($this->isIPv6) {
            return $this->compact()->address == '::1';
        }

        return (ip2long($this->address) & 0xff000000) == 0x7f000000;
    }
}
