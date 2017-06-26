<?php
namespace Core;

class Utils
{
    public static function bytesToHumanReadable($bytes, $unit = '')
    {
        $units = array(
            array('unit' => 'B', 'factor' => 1),
            array('unit' => 'KiB', 'factor' => 1024),
            array('unit' => 'MiB', 'factor' => 1024*1024),
            array('unit' => 'GiB', 'factor' => 1024*1024*1024),
            array('unit' => 'TiB', 'factor' => 1024*1024*1024*1024)
        );

        $res = $bytes.' B';
        foreach ($units as $u) {
            $v = $bytes / $u['factor'];
            if ($u['unit'] == $unit || $v < 1) {
                return $res;
            }
            $res = number_format($v, 2).' '.$u['unit'];
        }

        return $res;
    }
}
