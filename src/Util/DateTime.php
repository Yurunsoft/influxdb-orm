<?php
namespace Yurun\InfluxDB\ORM\Util;

abstract class DateTime
{
    private const YMD = 1;

    private const HIS = 2;

    private const SMALLER = 3;

    public static function format($format, $time)
    {
        if(preg_match('/(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2})\.?(\d+)?Z/', $time, $matches) <= 0)
        {
            return false;
        }
        $smallerValue = $matches[self::SMALLER] ?? '';
        if($smallerValue > 99999999)
        {
            $timestamp = strtotime($matches[self::YMD] . ' ' . $matches[self::HIS]);
        }
        else
        {
            $timestamp = strtotime($time);
        }
        if(false !== strpos($format, 'v') || false !== strpos($format, '{ms}'))
        {
            $format = str_replace(['{ms}', 'v'], str_pad(substr($smallerValue, 0, 3), 3, '0', STR_PAD_RIGHT), $format);
        }
        if(false !== strpos($format, 'u') || false !== strpos($format, '{us}'))
        {
            $format = str_replace(['{us}', 'u'], str_pad(substr($smallerValue, 0, 6), 6, '0', STR_PAD_RIGHT), $format);
        }
        if(false !== strpos($format, '{ns}'))
        {
            $format = str_replace('{ns}', str_pad(substr($smallerValue, 0, 9), 9, '0', STR_PAD_RIGHT), $format);
        }
        return date($format, $timestamp);
    }

}
