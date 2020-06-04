<?php

use Samaya\Embed\Extract;
use PHPUnit\Framework\TestCase;

class ExtractTest extends TestCase
{
    /**
     * @dataProvider imagesFetchProvider
     * @group image
     * @param string $url
     * @param string $urlImageExpected
     */
    public function testGetImage(string $url, string $urlImageExpected, string $meta = null)
    {
        $extract = new Extract();
        $actual = $extract->getImage($url, $meta);

        $this->assertEquals(
          $urlImageExpected,
          $actual,
          'Extracted Image doesn\'t matches expected url'
        );
    }

    public function imagesFetchProvider()
    {
        return [
            [
                'https://social.samaya.mx/share-video/1082?signature=e23e51cde3671b5d979b753f7b51a15e7552136a020108171d6d56c4dcc441cb',
                'https://samaya.azurewebsites.net/samaya_mc/assets/presentations/CR_S57_P05/videos/Video_de_producto1.png',
            ],
            [
                'https://www.facebook.com/FCAMexico/videos/501224354109405/',
                'https://lookaside.fbsbx.com/lookaside/crawler/media/?media_id=501224354109405&get_thumbnail=1',
                'twitter:image',
            ],
            [
                'https://www.youtube.com/watch?v=3N4hD86RjeM',
                'https://i.ytimg.com/vi/3N4hD86RjeM/maxresdefault.jpg',
            ],
            [
                'https://vimeo.com/243244233',
                'https://i.vimeocdn.com/filter/overlay?src0=https%3A%2F%2Fi.vimeocdn.com%2Fvideo%2F748767326_1280x720.jpg&src1=https%3A%2F%2Ff.vimeocdn.com%2Fimages_v6%2Fshare%2Fplay_icon_overlay.png',
            ]
        ];
    }

    /**
     * @dataProvider htmlFetchProvider
     * @group embed
     * @param string $url
     * @param string $expectedHtml
     */
    public function testGetHtml(string $url, string $expectedHtml)
    {
        $extract = new Extract();
        $actual = $extract->getHtml($url);

        $this->assertEquals(
          $expectedHtml,
          $actual,
          'Extracted Html doesn\'t matches expected string'
        );
    }

    public function htmlFetchProvider()
    {
        return [
            [
                'https://social.samaya.mx/share-video/1082?signature=e23e51cde3671b5d979b753f7b51a15e7552136a020108171d6d56c4dcc441cb',
                '<iframe frameborder="0" src="https://social.samaya.mx/embed/1082?signature=c4bc5cb2427c4576dbaa49692902982b972936957083857249881d8335850bc6" width="480" height="270" allowfullscreen  allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"></iframe>',
            ],
            [
                'https://www.facebook.com/FCAMexico/videos/501224354109405/',
                '<div id="fb-root"></div><script async="1" defer="1" crossorigin="anonymous" src="https://connect.facebook.net/es_LA/sdk.js#xfbml=1&amp;version=v7.0"></script><div class="fb-video" data-href="https://www.facebook.com/FCAMexico/videos/501224354109405/"><blockquote cite="https://www.facebook.com/FCAMexico/videos/501224354109405/" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/FCAMexico/videos/501224354109405/">Capacidades Jeep Wrangler Moparizado</a><p>Bosco&#039;s Camp 4x4 nos enseña las capacidades inigualables del Jeep México Wrangler moparizado www.facebook.com/MoparMexico/</p>Publicado por <a href="https://www.facebook.com/FCAMexico/">FCA México</a> en Lunes, 24 de febrero de 2020</blockquote></div>',
            ],
            [
                'https://www.youtube.com/watch?v=3N4hD86RjeM',
                '<iframe width="480" height="270" src="https://www.youtube.com/embed/3N4hD86RjeM?feature=oembed" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
            ],
            [
                'https://vimeo.com/243244233',
                '<iframe src="https://player.vimeo.com/video/243244233?app_id=122963" width="426" height="240" frameborder="0" allow="autoplay; fullscreen" allowfullscreen title="Unexpected Discoveries"></iframe>',
            ]
        ];
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
