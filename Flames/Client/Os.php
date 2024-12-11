<?php

namespace Flames\Client;

class Os
{
    const WINDOWS = 'Windows';
    const LINUX = 'Linux';
    const ANDROID = 'Android';
    const BSD = 'BSD';
    const IOS = 'iOS';
    const NINTENDO = 'Nintento';
    const PLAYSTATION = 'Playstation';
    const XBOX = 'Xbox';

    public static function getName()
    {
        $platform = Platform::getName();

        if (
            $platform === Platform::WINDOWS ||
            $platform === Platform::WINDOWS_PHONE

        ) {
            return self::WINDOWS;
        }
        elseif (
            $platform === Platform::ANDROID ||
            $platform === Platform::CHROME_OS ||
            $platform === Platform::KINDLE ||
            $platform === Platform::KINDLE_FIRE

        ) {
            return self::ANDROID;
        }
        elseif (
            $platform === Platform::MACINTOSH ||
            $platform === Platform::IPAD ||
            $platform === Platform::IPHONE ||
            $platform === Platform::IPOD

        ) {
            return self::IOS;
        }
        elseif (
            $platform === Platform::FREEBSD ||
            $platform === Platform::NETBSD ||
            $platform === Platform::OPENBSD

        ) {
            return self::BSD;
        }
        elseif ($platform === Platform::LINUX) {
            return self::LINUX;
        }
        elseif (
            $platform === Platform::NEW_NINTENDO_3DS ||
            $platform === Platform::NINTENDO_3DS ||
            $platform === Platform::NINTENDO_DS ||
            $platform === Platform::NINTENDO_SWITCH ||
            $platform === Platform::NINTENDO_WII ||
            $platform === Platform::NINTENDO_WIIU

        ) {
            return self::NINTENDO;
        }
        elseif (
            $platform === Platform::PLAYSTATION_3 ||
            $platform === Platform::PLAYSTATION_4 ||
            $platform === Platform::PLAYSTATION_5 ||
            $platform === Platform::PLAYSTATION_VITA

        ) {
            return self::PLAYSTATION;
        }
        elseif (
            $platform === Platform::XBOX ||
            $platform === Platform::XBOX_ONE

        ) {
            return self::XBOX;
        }

        return $platform;
    }
}