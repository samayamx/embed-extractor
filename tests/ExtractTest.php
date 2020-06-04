<?php

use Samaya\Embed\Extract;
use PHPUnit\Framework\TestCase;

class ExtractTest extends TestCase
{
    /**
     * @group image
     */
    public function testGetImage()
    {
        $expected = 'https://samaya.azurewebsites.net/samaya_mc/assets/presentations/CR_S57_P05/videos/Video_de_producto1.png';
        $url = 'https://social.samaya.mx/share-video/1082?signature=e23e51cde3671b5d979b753f7b51a15e7552136a020108171d6d56c4dcc441cb';

        $extract = new Extract();
        $actual = $extract->getImage($url);

        $this->assertEquals(
          $expected,
          $actual,
          'Extracted Image doesn\'t matches expected url'
        );
    }

    /**
     * @group embed
     */
    public function testGetHtml()
    {
        // $expected = 'https://social.samaya.mx/oembed?format=json&url=https%3A%2F%2Fsocial.samaya.mx%2Fembed%2F1082%3Fsignature%3Dc4bc5cb2427c4576dbaa49692902982b972936957083857249881d8335850bc6&signature=01dffb922dc52fdd17a113249edc07c8d773bc3999b2bea4e621ee7896b34d11';
        $expected = '<iframe frameborder="0" src="https://social.samaya.mx/embed/1082?signature=c4bc5cb2427c4576dbaa49692902982b972936957083857249881d8335850bc6" width="480" height="270" allowfullscreen  allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"></iframe>';
        $url = 'https://social.samaya.mx/share-video/1082?signature=e23e51cde3671b5d979b753f7b51a15e7552136a020108171d6d56c4dcc441cb';

        $extract = new Extract();
        $actual = $extract->getHtml($url);

        $this->assertEquals(
          $expected,
          $actual,
          'Extracted Html doesn\'t matches expected string'
        );
    }

    /**
     * @group exception
     */
    public function testInvalidUrl()
    {
        $this->expectException(\InvalidArgumentException::class);
        $extract = new Extract();
        $actual = $extract->validateUrl('naaaah');
    }
}
