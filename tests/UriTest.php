<?php

namespace BinSoul\Test\Net;

use BinSoul\Common\Equatable;
use BinSoul\Net\URI;

class UriTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_URI = 'http://username:password@www.example.com:8080/foo/bar/?empty&simple=simple&array[]=0&&array[]=1#fragment';

    /** @var URI */
    private $defaultUri;

    public function setUp()
    {
        $this->defaultUri = new URI(
            'http',
            'www.example.com',
            '/foo/bar/',
            'empty&simple=simple&array[]=0&&array[]=1',
            '#fragment',
            'username',
            'password',
            '8080'
        );
    }

    public function validUrls()
    {
        return [
            ['ftp://ftp.is.co.za/rfc/rfc1808.txt'],
            ['http://www.ietf.org/rfc/rfc2396.txt#foo'],
            ['ldap://[2001:db8::7]/c=GB?objectClass?one'],
            ['mailto:John.Doe@example.com'],
            ['news:comp.infosystems.www.servers.unix'],
            ['tel:+1-816-555-1212'],
            ['telnet://192.0.2.16:80/'],
            ['urn:oasis:names:specification:docbook:dtd:xml:4.1.2'],
            [self::DEFAULT_URI],
        ];
    }

    /**
     * @dataProvider validUrls
     */
    public function test_parses_valid_uris($testUri)
    {
        $uri = URI::parse($testUri);
        $this->assertEquals($testUri, (string) $uri);
    }

    public function invalidUrls()
    {
        return [
            'no-scheme' => ['/foo/bar?baz=qux'],
            'invalid-scheme' => [' http:///foo/bar?baz=qux', '#foo#:bar', '123:abc'],
            'malformed' => ['javascript://'],
            'ambiguous-path' => ['::http'],
            'missing-segment' => ['http:'],
            'invalid-port' => ['http://username:passwort@example.com:bogus/'],
        ];
    }

    /**
     * @dataProvider invalidUrls
     * @expectedException \InvalidArgumentException
     */
    public function test_parse_raises_exception_for_invalid_uris($testUri)
    {
        URI::parse($testUri);
    }

    public function test_parse_empty_uri()
    {
        $uri = URI::parse('');

        $this->assertEquals('', $uri->getScheme());
        $this->assertEquals('', $uri->getAuthority());
        $this->assertEquals('', $uri->getPath());
        $this->assertEquals('', $uri->getQuery());
        $this->assertEquals('', $uri->getFragment());
        $this->assertEquals('', $uri->getHost());
        $this->assertEquals('', $uri->getUserInfo());
        $this->assertEquals('', $uri->getPort());

        $this->assertEquals('', (string) $uri);
    }

    public function test_parse_sets_all_properties()
    {
        $uri = URI::parse(self::DEFAULT_URI);
        $this->assertEquals($this->defaultUri->getScheme(), $uri->getScheme());
        $this->assertEquals($this->defaultUri->getUserInfo(), $uri->getUserInfo());
        $this->assertEquals($this->defaultUri->getHost(), $uri->getHost());
        $this->assertEquals($this->defaultUri->getPort(), $uri->getPort());
        $this->assertEquals($this->defaultUri->getAuthority(), $uri->getAuthority());
        $this->assertEquals($this->defaultUri->getPath(), $uri->getPath());
        $this->assertEquals($this->defaultUri->getQuery(), $uri->getQuery());
        $this->assertEquals($this->defaultUri->getFragment(), $uri->getFragment());
    }

    public function authorityInfo()
    {
        return [
            'host' => ['http://example.com/bar', 'example.com'],
            'host-port' => ['http://example.com:8080/bar', 'example.com:8080'],
            'user-host' => ['http://username@example.com/bar', 'username@example.com'],
            'user-pass-host' => ['http://username:password@example.com/bar', 'username:password@example.com'],
            'user-host-port' => ['http://username@example.com:8080/bar', 'username@example.com:8080'],
            'user-pass-host-port' => ['http://username:password@example.com:8080/bar', 'username:password@example.com:8080'],
        ];
    }

    /**
     * @dataProvider authorityInfo
     */
    public function test_parse_returns_expected_authority($url, $expected)
    {
        $uri = URI::parse($url);
        $this->assertEquals($expected, $uri->getAuthority());
    }

    public function test_withScheme_returns_instance_with_new_scheme()
    {
        $new = $this->defaultUri->withScheme('https');
        $this->assertNotSame($this->defaultUri, $new);
        $this->assertEquals('https', $new->getScheme());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_withScheme_raises_exception_for_invalid_port()
    {
        $this->defaultUri->withScheme('#bogus#');
    }

    public function test_withUserInfo_returns_instance_with_new_user_and_password()
    {
        $new = $this->defaultUri->withUserInfo('foo');
        $this->assertNotSame($this->defaultUri, $new);
        $this->assertEquals('foo', $new->getUserInfo());

        $new = $this->defaultUri->withUserInfo('foo', 'bar');
        $this->assertEquals('foo:bar', $new->getUserInfo());
    }

    public function test_withHost_returns_instance_with_new_host()
    {
        $new = $this->defaultUri->withHost('foobar.com');
        $this->assertNotSame($this->defaultUri, $new);
        $this->assertEquals('foobar.com', $new->getHost());
    }

    public function test_withPort_returns_instance_with_new_port()
    {
        $new = $this->defaultUri->withPort(9090);
        $this->assertNotSame($this->defaultUri, $new);
        $this->assertEquals(9090, $new->getPort());

        $new = $this->defaultUri->withPort(null);
        $this->assertNotSame($this->defaultUri, $new);
        $this->assertEquals(null, $new->getPort());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_withPort_raises_exception_for_invalid_port()
    {
        $this->defaultUri->withPort('bogus');
    }

    public function test_withPath_return_instance_with_new_path()
    {
        $new = $this->defaultUri->withPath('/abc/def');
        $this->assertNotSame($this->defaultUri, $new);
        $this->assertEquals('/abc/def', $new->getPath());
    }

    public function test_withQuery_returns_instance_with_new_query()
    {
        $new = $this->defaultUri->withQuery('foo=bar&baz=qux');
        $this->assertNotSame($this->defaultUri, $new);
        $this->assertEquals('foo=bar&baz=qux', $new->getQuery());
    }

    public function test_withFragment_returns_instance_with_new_fragment()
    {
        $new = $this->defaultUri->withFragment('foobar');
        $this->assertNotSame($this->defaultUri, $new);
        $this->assertEquals('foobar', $new->getFragment());
    }

    public function encodedHosts()
    {
        return [
            ['baz?bat=quz', 'baz%3Fbat=quz'],
            ['baz#bat', 'baz%23bat'],
            ['foo^bar', 'foo%5Ebar'],
            ['k^ey&key[]=valu`&f<>=`bar', 'k%5Eey&key[]=valu%60&f%3C%3E=%60bar'],
        ];
    }

    /**
     * @dataProvider encodedHosts
     */
    public function test_withHost_encodes_correctly($host, $encoded)
    {
        $new = $this->defaultUri->withHost($host);
        $this->assertEquals($encoded, $new->getHost());
    }

    /**
     * @dataProvider encodedHosts
     */
    public function test_withHost_prevents_double_encoding($host, $encoded)
    {
        $new = $this->defaultUri->withHost($encoded);
        $this->assertEquals($encoded, $new->getHost());
    }

    public function encodedPaths()
    {
        return [
            ['/bar/baz?bat=quz', '/bar/baz%3Fbat=quz'],
            ['/bar/baz#bat', '/bar/baz%23bat'],
            ['/foo^bar', '/foo%5Ebar'],
            ['k^ey&key[]=valu`&f<>=`bar', 'k%5Eey&key%5B%5D=valu%60&f%3C%3E=%60bar'],
        ];
    }

    /**
     * @dataProvider encodedPaths
     */
    public function test_withPath_encodes_correctly($path, $encoded)
    {
        $new = $this->defaultUri->withPath($path);
        $this->assertEquals($encoded, $new->getPath());
    }

    /**
     * @dataProvider encodedPaths
     */
    public function test_withPath_prevents_double_encoding($path, $encoded)
    {
        $new = $this->defaultUri->withPath($encoded);
        $this->assertEquals($encoded, $new->getPath());
    }

    public function encodedQueries()
    {
        return [
            ['baz=bat?quz', 'baz=bat?quz'],
            ['baz=bat#quz', 'baz=bat%23quz'],
            ['k^ey', 'k%5Eey'],
            ['k^ey=valu`', 'k%5Eey=valu%60'],
            ['key[]', 'key[]'],
            ['key[]=valu`', 'key[]=valu%60'],
            ['k^ey&key[]=valu`&f<>=`bar', 'k%5Eey&key[]=valu%60&f%3C%3E=%60bar'],
        ];
    }

    /**
     * @dataProvider encodedQueries
     */
    public function test_withQuery_encodes_correctly($query, $encoded)
    {
        $new = $this->defaultUri->withQuery($query);
        $this->assertEquals($encoded, $new->getQuery());
    }

    /**
     * @dataProvider encodedQueries
     */
    public function test_withQuery_prevents_double_encoding($query, $encoded)
    {
        $new = $this->defaultUri->withQuery($encoded);
        $this->assertEquals($encoded, $new->getQuery());
    }

    public function encodedFragments()
    {
        return [
            ['baz=bat?quz', 'baz=bat?quz'],
            ['baz=bat#quz', 'baz=bat%23quz'],
            ['k^ey', 'k%5Eey'],
            ['k^ey=valu`', 'k%5Eey=valu%60'],
            ['key[]', 'key[]'],
            ['key[]=valu`', 'key[]=valu%60'],
            ['k^ey&key[]=valu`&f<>=`bar', 'k%5Eey&key[]=valu%60&f%3C%3E=%60bar'],
        ];
    }

    /**
     * @dataProvider encodedFragments
     */
    public function test_withFragment_encodes_correctly($fragment, $encoded)
    {
        $new = $this->defaultUri->withFragment($fragment);
        $this->assertEquals($encoded, $new->getFragment());
    }

    /**
     * @dataProvider encodedFragments
     */
    public function test_withFragment_prevents_double_encoding($fragment, $encoded)
    {
        $new = $this->defaultUri->withFragment($encoded);
        $this->assertEquals($encoded, $new->getFragment());
    }

    public function test_withScheme_removes_delimiter()
    {
        $uri = URI::parse('http://example.com');
        $new = $uri->withScheme('https://');
        $this->assertEquals('https', $new->getScheme());
    }

    public function test_parse_trims_leading_slashes()
    {
        $uri = URI::parse('http://example.com//foo');
        $this->assertEquals('/foo', $uri->getPath());
        $this->assertEquals('http://example.com/foo', (string) $uri);
    }

    public function test_withPath_trims_leading_slashes()
    {
        $uri = URI::parse('http://example.com');
        $new = $uri->withPath('//');
        $this->assertEquals('/', $new->getPath());
        $this->assertEquals('http://example.com/', (string) $new);
    }

    public function test_withPath_keeps_root_slash()
    {
        $uri = URI::parse('http://example.com/foo');
        $new = $uri->withPath('');
        $this->assertEquals('', $new->getPath());
        $this->assertEquals('http://example.com', (string) $new);

        $new = $uri->withPath('/');
        $this->assertEquals('/', $new->getPath());
        $this->assertEquals('http://example.com/', (string) $new);
    }

    public function test_withPath_keeps_trailing_slash()
    {
        $uri = URI::parse('http://example.com');
        $new = $uri->withPath('foo/bar/');
        $this->assertEquals('foo/bar/', $new->getPath());
        $this->assertEquals('http://example.com/foo/bar/', (string) $new);
    }

    public function test_path_is_not_prefixed_with_slash()
    {
        $uri = URI::parse('http://example.com');
        $new = $uri->withPath('foo/bar');
        $this->assertEquals('foo/bar', $new->getPath());
        $this->assertEquals('http://example.com/foo/bar', (string) $new);

        $uri = URI::parse('mailto:foo@example.com');
        $new = $uri->withPath('bar@example.com');
        $this->assertEquals('bar@example.com', $new->getPath());
        $this->assertEquals('mailto:bar@example.com', (string) $new);
    }

    public function test_withQuery_removes_prefix()
    {
        $uri = URI::parse('http://example.com');
        $new = $uri->withQuery('');
        $this->assertEquals('http://example.com', (string) $new);

        $new = $uri->withQuery('?');
        $this->assertEquals('', $new->getQuery());

        $new = $uri->withQuery('?foo=bar');
        $this->assertEquals('foo=bar', $new->getQuery());

        $new = $uri->withQuery('??foo=bar');
        $this->assertEquals('?foo=bar', $new->getQuery());
    }

    public function test_withFragment_removes_prefix()
    {
        $uri = URI::parse('http://example.com');
        $new = $uri->withFragment('');
        $this->assertEquals('http://example.com', (string) $new);

        $new = $uri->withFragment('#');
        $this->assertEquals('', $new->getFragment());

        $new = $uri->withFragment('#!/foo/bar');
        $this->assertEquals('!/foo/bar', $new->getFragment());

        $new = $uri->withFragment('##!/foo/bar');
        $this->assertEquals('%23!/foo/bar', $new->getFragment());
    }

    public function knownPorts()
    {
        return [
            'http' => ['http', 80],
            'https' => ['https', 443],
            'ftp' => ['ftp', 21],
        ];
    }

    /**
     * @dataProvider knownPorts
     */
    public function test_removes_known_ports($scheme, $port)
    {
        $uri = new URI($scheme, 'example.com', '', '', '', '', '', $port);
        $this->assertEquals('example.com', $uri->getAuthority());
    }

    public function test_is_hashable()
    {
        $uri1 = URI::parse('http://example.com');
        $uri2 = URI::parse('mailto:foo@example.com');

        $this->assertEquals($uri1->getHash(), $uri1->getHash());
        $this->assertEquals($uri2->getHash(), $uri2->getHash());
        $this->assertNotEquals($uri1->getHash(), $uri2->getHash());
    }

    public function test_is_equatable()
    {
        $uri1a = URI::parse('http://example.com');
        $uri1b = URI::parse('http://example.com');
        $uri2a = URI::parse('mailto:foo@example.com');
        $uri2b = URI::parse('mailto:foo@example.com');

        $this->assertTrue($uri1a->isEqualTo($uri1b));
        $this->assertTrue($uri2a->isEqualTo($uri2b));
        $this->assertFalse($uri1a->isEqualTo($uri2a));
        $this->assertFalse($uri1b->isEqualTo($uri2b));

        $this->assertFalse($uri1a->isEqualTo($this->getMock(Equatable::class)));
    }
}
