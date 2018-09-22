<?php

/**
 * Created by zithan.
 * User: zithan <zithan@163.com>
 */

namespace Xjw\BearSdk\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Xjw\BearSdk\Bear;

class BearTest extends TestCase
{
    public function testGetHttpClient()
    {
        $bear = new Bear([]);

        $this->assertInstanceOf(ClientInterface::class, $bear->getHttpClient());
    }

    // 'base_uri' => 'http://open.logistics.com/v1/'
    public function testSetGuzzleOptions()
    {
        $bear = new Bear([]);

        $this->assertNull($bear->getHttpClient()->getConfig('timeout'));

        $bear->setGuzzleOptions(['timeout' => 5000]);

        $this->assertSame(5000, $bear->getHttpClient()->getConfig('timeout'));
    }

    public function testGetToken()
    {
        $config = [
            'base_uri' => 'http://open.logistics.com/v1/',
            'timeout' => 5.0,
            'bear_params' => [
                'notify_url' => 'http://www.030.cn',
                'grant_type' => 'authorization_code',
                'access_key_id' => '123213123',
                'access_key_secret' => 'ByxFuZqI2u+123123123',
                'aes_key' => "ByxFuZqI2u+123"
            ]
        ];

        $response = new Response(200, [], '{"success": true}');

        $client = \Mockery::mock(Client::class, [['base_uri' => 'http://open.beartms.com/v1/', 'timeout' => 5.0]])->makePartial();
        $client->allows()->post('token/get', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'access_key_id' => '9plwBwovLuaCgwkLA1',
                'access_key_secret' => 'cyNZX6HAtIHI8KOBZcHYRb1TeyBITveM',
                'timestamp' => time(),
            ]
        ])->andReturn($response);

        $bear = \Mockery::mock(Bear::class, [$config])->makePartial();
        $bear->allows()->getHttpClient()->andReturn($client);

        $this->assertSame(['success' => true], $bear->getToken());
    }
}