<?php
namespace Gm\LandingPageEngine\TwigGlobals;

class ThaiDate
{
    protected static $thaiMonths = [
        'มกราคม',
        'กุมภาพันธ์',
        'มีนาคม',
        'เมษายน',
        'พฤษภาคม',
        'มิถุนายน',
        'กรกฎาคม',
        'สิงหาคม',
        'กันยายน',
        'ตุลาคม',
        'พฤศจิกายน',
        'ธันวาคม'
    ];

    public function  __toString()
    {
        $unixTimeNow = time();
        return date('j', $unixTimeNow) . ' '
             . self::$thaiMonths[(int) date('n', $unixTimeNow) - 1] . ' '
             . strval((int) date('Y', $unixTimeNow) + 543);
    }
}
