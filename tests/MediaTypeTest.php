<?php

namespace BinSoul\Test\Net;

use BinSoul\Common\Equatable;
use BinSoul\Net\MediaType;

class MediaTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $tempFile;

    public function tearDown()
    {
        if (file_exists($this->tempFile)) {
            @unlink($this->tempFile);
        }

        $this->tempFile = null;
    }

    public function validType()
    {
        return [
            ['image/vnd.adobe.photoshop'],
            ['application/postscript'],
            ['application/x-rar-compressed'],
            ['application/vnd.rn-realmedia'],
            ['application/rss+xml'],
            ['image/svg+xml'],
            ['image/tiff'],
            ['application/x-tar'],
            ['text/plain; charset=utf-8'],
            ['text/x-vcalendar'],
            ['text/x-vcard'],
            ['audio/x-wav'],
            ['application/x-font-woff'],
            ['image/webp'],
            ['application/xhtml+xml'],
            ['application/xml'],
            ['application/xslt+xml'],
        ];
    }

    /**
     * @dataProvider validType
     */
    public function test_constructor_parses_correctly($type)
    {
        $this->assertEquals($type, (new MediaType($type))->__toString());
    }

    /**
     * @dataProvider validType
     */
    public function test_isValid_returns_true($type)
    {
        $this->assertTrue(MediaType::isValid($type));
    }

    public function invalidType()
    {
        return [
            ['image/vnd/adobe.photoshop'],
            ['applicat;ion/postscript'],
            ['application/pgp-signature+x+y'],
            ['application/x-rar-compressed;foo;bar'],
            ['application+audio/x-rar-compressed'],
        ];
    }

    /**
     * @dataProvider invalidType
     * @expectedException \InvalidArgumentException
     */
    public function test_constructor_validates_data($type)
    {
        new MediaType($type);
    }

    /**
     * @dataProvider invalidType
     */
    public function test_isValid_returns_false($type)
    {
        $this->assertFalse(MediaType::isValid($type));
    }

    public function test_fromExtension_returns_media_type()
    {
        $this->assertInstanceOf(MediaType::class, MediaType::fromExtension('html'));
        $this->assertInstanceOf(MediaType::class, MediaType::fromExtension('HTML'));
        $this->assertInstanceOf(MediaType::class, MediaType::fromExtension('Xml'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_fromExtension_raises_exception()
    {
        $this->assertInstanceOf(MediaType::class, MediaType::fromExtension('abc'));
    }

    public function test_guess_from_file_content()
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'BinSoul').'.html';
        file_put_contents($this->tempFile, "<doctype html>\n<html></html>");

        $this->assertInstanceOf(MediaType::class, MediaType::fromFile($this->tempFile, false));
    }

    public function test_guess_from_file_extension()
    {
        ini_set('disable_functions', 'file');

        $this->tempFile = tempnam(sys_get_temp_dir(), 'BinSoul').'.html';
        file_put_contents($this->tempFile, '<!>');

        $this->assertInstanceOf(MediaType::class, MediaType::fromFile($this->tempFile, true));
    }

    public function test_guess_fallback()
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'BinSoul').'.foobar';
        file_put_contents($this->tempFile, '<!>');

        $this->assertInstanceOf(MediaType::class, MediaType::fromFile($this->tempFile, true));

        $this->assertInstanceOf(MediaType::class, MediaType::fromFile('php://memory', true));
    }

    public function test_is_hashable()
    {
        $type1 = new MediaType('text/plain; charset=utf-8');
        $type2 = new MediaType('application/xhtml+xml');

        $this->assertEquals($type1->getHash(), $type1->getHash());
        $this->assertEquals($type2->getHash(), $type2->getHash());
        $this->assertNotEquals($type1->getHash(), $type2->getHash());
    }

    public function test_is_equatable()
    {
        $type1a = new MediaType('text/plain; charset=utf-8');
        $type1b = new MediaType('text/plain; charset=utf-8');
        $type2a = new MediaType('application/xhtml+xml');
        $type2b = new MediaType('application/xhtml+xml');

        $this->assertTrue($type1a->isEqualTo($type1b));
        $this->assertTrue($type2a->isEqualTo($type2b));
        $this->assertFalse($type1a->isEqualTo($type2a));
        $this->assertFalse($type1b->isEqualTo($type2b));

        $this->assertFalse($type1a->isEqualTo($this->getMock(Equatable::class)));
    }
}
