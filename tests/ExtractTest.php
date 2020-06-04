<?php

use Samaya\Embed\Extract;
use PHPUnit\Framework\TestCase;

class ExtractTest extends TestCase
{
    public function testGetImage()
    {
        $expected = 'https://samaya.azurewebsites.net/samaya_mc/assets/presentations/CR_S57_P05/videos/Video_de_producto1.png';
        $url = 'https://social.samaya.mx/share-video/1082?signature=e23e51cde3671b5d979b753f7b51a15e7552136a020108171d6d56c4dcc441cb';

        $extract = new Extract();
        $actual = $extract->getOGImage($url);

        $this->assertEquals(
          $expected,
          $actual,
          'Extracted Image doesn\'t matches expected url'
        );
    }

    public function testInvalidUrl()
    {
        $this->expectException(\InvalidArgumentException::class);
        $extract = new Extract();
        $actual = $extract->getOGImage('naaaah');
    }
}
