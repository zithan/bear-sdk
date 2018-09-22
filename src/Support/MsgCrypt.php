<?php

/**
 * Created by zithan.
 * User: zithan <zithan@163.com>
 */

namespace Xjw\BearSdk\Support;

class MsgCrypt
{
    public static function encrypt($text, $key, $iv, $method = 'AES-256-CBC', $padding = PKCS7_TEXT)
    {
        return base64_encode(openssl_encrypt($text, $method, $key, $padding, $iv));
    }

    public static function decrypt($text, $key, $iv, $method = 'AES-256-CBC', $padding = PKCS7_TEXT)
    {
        return openssl_decrypt(base64_decode($text), $method, $key, $padding, $iv);
    }
}