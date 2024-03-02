<?php

namespace Flames;

class Connection
{
    protected static string|null $currentIp = null;
    protected static string|null $currentIpProxy = null;

    public static function getIp(bool $parseProxy = false) : string|null
    {
        if ($parseProxy === true) {
            if (self::$currentIpProxy !== null) {
                return self::$currentIpProxy;
            }

            $ip = null;

            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }

            if ($ip !== null) {
                self::$currentIpProxy = $ip;
                return self::$currentIpProxy;
            }
        }

        if (self::$currentIp !== null) {
            return self::$currentIp;
        }

        self::$currentIp = $_SERVER['REMOTE_ADDR'];
        return self::$currentIp;
    }
}