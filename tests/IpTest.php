<?php

namespace BinSoul\Test\Net;

use BinSoul\Common\Equatable;
use BinSoul\Net\IP;

class IpTest extends \PHPUnit_Framework_TestCase
{
    public function invalidIP()
    {
        return [
            [''],
            ['1.2.3'],
            ['1.2.3.355'],
            ['2001:'],
            ['h001:db8::'],
            ['h001:db8::'],
            ['abc'],
        ];
    }

    /**
     * @dataProvider invalidIP
     * @expectedException \InvalidArgumentException
     */
    public function test_constructor_validates_data($ip)
    {
        new IP($ip);
    }

    public function test_expand_returns_instance_with_expanded_address()
    {
        $old = new IP('2001::1');
        $new = $old->expand();
        $this->assertNotSame($old, $new);
        $this->assertEquals('2001:0000:0000:0000:0000:0000:0000:0001', (string) $new);

        $old = new IP('1.2.3.4');
        $new = $old->expand();
        $this->assertNotSame($old, $new);
        $this->assertEquals('1.2.3.4', (string) $new);
    }

    public function test_compact_returns_instance_with_compacted_address()
    {
        $old = new IP('2001:0000:0000:0000:0000:0000:0000:0001');
        $new = $old->compact();
        $this->assertNotSame($old, $new);
        $this->assertEquals('2001::1', (string) $new);

        $old = new IP('1.2.3.4');
        $new = $old->compact();
        $this->assertNotSame($old, $new);
        $this->assertEquals('1.2.3.4', (string) $new);
    }

    public function ipV4Range()
    {
        return [
            ['1.2.3.4-255.255.255.254'],
            ['128.128.129.0-128.128.129.255'],
            ['128.128.129.129/32'],
            ['128.128.129.0/24'],
            ['128.128.129.0/16'],
            ['128.128.129.0/8'],
            ['128.128.129/24'],
            ['128.128/16'],
            ['128.128.129.129'],
            ['1.2.3.4 - 255.255.255.254'],
        ];
    }

    /**
     * @dataProvider ipV4Range
     */
    public function test_ip_is_in_range_ipv4($ip)
    {
        $this->assertTrue((new IP('128.128.129.129'))->isInRange($ip), $ip);
        $this->assertFalse((new IP('1.1.1.1'))->isInRange($ip), $ip);
    }

    public function ipV6Range()
    {
        return [
            ['1:2::-ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'],
            ['2001:db8:1234:0000:0000:0000:0000:0001-2001:db8:1234:4567:0000:0000:0000:0001'],
            ['2001:db8:1234:0000:0000:0000:0000:0001-2001:db8:1234:0000:0000:0000:0000:0004'],
            ['2001:db8:1234::0001-2001:db8:1234:0000:0000:0000:0000:0004'],
            ['2001:db8:1234:0000:0000:0000:0000:0001-2001:db8:1234::0004'],
            ['2001:db8:1234::0001-2001:db8:1234::0004'],
            ['2001:db8:1234::/126'],
            ['2001:db8:1234::/64'],
            ['2001:db8:1234:000:0000:0000:0000:0002'],
            ['1:2:: - ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'],
        ];
    }

    /**
     * @dataProvider ipV6Range
     */
    public function test_ip_is_in_range_ipv6($ip)
    {
        $this->assertTrue((new IP('2001:db8:1234:000:0000:0000:0000:0002'))->isInRange($ip), $ip);
        $this->assertFalse((new IP('1:1::'))->isInRange($ip), $ip);
    }

    public function invalidRange()
    {
        return [
            ['1.2.3.4-5.6.7.8-9.10.11.12'],
            ['1.2.3.4/24/8'],
            ['a.b.c.d'],
        ];
    }

    /**
     * @dataProvider invalidRange
     * @expectedException \InvalidArgumentException
     */
    public function test_isInRange_validates_range($range)
    {
        (new IP('1.2.3.4'))->isInRange($range);
    }

    public function privateIP()
    {
        return [
            ['127.0.0.1'], ['127.0.0.2'], ['127.0.0.8'],
            ['10.0.0.1'], ['10.255.255.255'],
            ['172.16.0.1'], ['172.16.255.255'],
            ['192.168.0.1'], ['192.168.255.255'],
            ['::1'],
        ];
    }

    /**
     * @dataProvider privateIP
     */
    public function test_ip_is_private($ip)
    {
        $this->assertTrue((new IP($ip))->isPrivate(), $ip);
    }

    public function publicIP()
    {
        return [
            ['1::1'], ['1.2.3.4'],
        ];
    }

    /**
     * @dataProvider publicIP
     */
    public function test_ip_is_public($ip)
    {
        $this->assertFalse((new IP($ip))->isPrivate(), $ip);
    }

    /**
     * @dataProvider privateIP
     */
    public function test_is_hashable($ip)
    {
        $this->assertEquals((new IP($ip))->getHash(), (new IP($ip))->getHash(), $ip);
        $this->assertNotEquals((new IP('1.2.3.4'))->getHash(), (new IP($ip))->getHash(), $ip);
    }

    public function test_hashes_expanded_ips()
    {
        $compact = new IP('2001::1');

        $this->assertEquals($compact->getHash(), $compact->expand()->getHash());
    }

    /**
     * @dataProvider privateIP
     */
    public function test_is_equatable($ip)
    {
        $this->assertTrue((new IP($ip))->isEqualTo(new IP($ip)), $ip);
        $this->assertFalse((new IP('1.2.3.4'))->isEqualTo(new IP($ip)), $ip);
        $this->assertFalse((new IP($ip))->isEqualTo($this->getMock(Equatable::class)), $ip);
    }

    public function test_equates_expanded_ips()
    {
        $compact = new IP('2001::1');

        $this->assertTrue($compact->isEqualTo($compact->expand()));
    }
}
