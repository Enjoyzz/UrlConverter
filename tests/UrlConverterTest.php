<?php

namespace Tests\Enjoys;

use Enjoys\UrlConverter;
use PHPUnit\Framework\TestCase;

class UrlConverterTest extends TestCase
{
    public function data()
    {
        return [
            ['http://yandex.ru/test.css', '1/2/3/test.css', 'http://yandex.ru/1/2/3/test.css'],
            ['http://yandex.ru/test.css', '1/2/3/', 'http://yandex.ru/1/2/3/'],
            ['http://yandex.ru/test.css', '../3/', 'http://yandex.ru/3/'],
            ['http://yandex.ru/test.css', '../../../../3/', 'http://yandex.ru/3/'],
            ['http://yandex.ru/1/2/3/test.css', '../4/', 'http://yandex.ru/1/2/4/'],
            ['http://yandex.ru/1/2/3/test.css', './4/', 'http://yandex.ru/1/2/3/4/'],
            ['http://yandex.ru/1/2/3/test.css', '../../4/', 'http://yandex.ru/1/4/'],
            ['http://yandex.ru/1/2/3/test.css', '/4', 'http://yandex.ru/4'],
            [
                'http://usr:pss@example.com:81/mypath/myfile.html?a=b&b[]=2&b[]=3#myfragment',
                '/4',
                'http://usr:pss@example.com:81/4?a=b&b[]=2&b[]=3#myfragment'
            ],
            [
                'http://usr:pss@example.com:81/mypath/myfile.html?a=b&b[]=2&b[]=3#myfragment',
                '4.txt',
                'http://usr:pss@example.com:81/mypath/4.txt?a=b&b[]=2&b[]=3#myfragment'
            ],
            [
                'http:///fail',
                '/../test.css',
                false
            ],
            [
                'http://text.com',
                'http:///fail',
                false
            ],
            [
                'http://text.com',
                'http://domain.com',
                'http://domain.com'
            ],
        ];
    }

    /**
     * @dataProvider data
     */
    public function test($baseUrl, $relativeUrl, $expect)
    {
        $this->assertSame($expect, (new UrlConverter())->relativeToAbsolute($baseUrl, $relativeUrl));
    }

}

