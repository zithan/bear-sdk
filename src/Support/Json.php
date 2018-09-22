<?php
/**
 * Created by zithan.
 * User: zithan <zithan@163.com>
 */

namespace Xjw\BearSdk\Support;

class Json
{
    public static function encode(array $data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}