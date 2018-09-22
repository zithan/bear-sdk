### example

```php
require __DIR__ .'/vendor/autoload.php';

use Xjw\BearSdk\Bear;

$config = [
    'base_uri' => 'http://api-open.logistics.me/',
    'timeout' => 5.0,
    'bear_params' => [
        'notify_url' => 'http://www.030.cn',
        'grant_type' => 'authorization_code',
        'access_key_id' => '9plwBwovLuaCgwkLA1',
        'access_key_secret' => 'cyNZX6HAtIHI8KOBZcHYRb1TeyBITveM',
        'aes_key' => "ByxFuZqI2u+Zr2rKCF6WTbmRiZWCeiwW",
        'aes_iv' => substr('ByxFuZqI2u+Zr2rKCF6WTbmRiZWCeiwW', 9, 16),
        'maker_unique_id' => '5b117fa5ec177',
    ]
];

$bear = new Bear($config);

echo '**************** 获取token ****************' . PHP_EOL;

try {
    $tokenDt = $bear->getToken();
    print_r($tokenDt);
} catch (\Xjw\BearSdk\Exceptions\Exception $e) {
    echo $e->getMessage();
}

echo "\n";
echo '**************** 推送商品 ****************' . PHP_EOL;

try {
    $authData = [
        'openid' => $tokenDt['openid'],
        'token' => $tokenDt['token'],
    ];
    $pushData = [
        'orders_sn' => 'sn' . date('YmdHis') . mt_rand(1,1000),
        'logistics_title' => '德邦物流',
        'linkman' => '黄百万',
        'mobile' => '18502099886',
        'address' => '广州天河中心广场88号',
        'deliver_expired_at' => strtotime('+3 day'),
        'goods_list' => [
            [
                "title" => "测试商品标题A-001",
                "length" => 1000,
                "width" => 2000,
                "height" => 3000,
                "weight" => 4000,
                "remark" => "测试商品备注信息",
                "total" => 12,
            ]
        ],
    ];
    $rsOfPush = $bear->push($authData, $pushData);

    echo json_encode($rsOfPush, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (\Xjw\BearSdk\Exceptions\Exception $e) {
    echo $e->getMessage();
}

echo "\n";

exit;
```