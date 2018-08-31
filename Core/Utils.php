<?php
namespace Core;

class Utils
{
    /**
     * Return the last element of an array
     *
     * @param array $a
     *
     * @return array $lastElement
     */
    public static function getLastElement($a)
    {
        return $a[count($a) - 1];
    }

    /**
     * Write logs
     * @return void
     */
    public static function writelogs($log, $level = 'debug') {
        $appLogsDir = LOG_DIR;
        if (!is_dir($appLogsDir)) {
            mkdir($appLogsDir);
        }
        if (is_writable(LOG_DIR.DS.date("Ymd").'.log')) {
            $handle = fopen(LOG_DIR.DS.date("Ymd").'.log', "a+");
            fwrite($handle, date("Y/m/d H:i:s") . ' ' . $level . ': ' .$log);
            fclose($handle);
        }
    }

    /**
     * Test if not empty
     *
     * @param mixed $v value to test
     *
     * @return boolean
     */
    public static function notEmpty($v)
    {
        return empty($v) ? false : true;
    }

    /**
     * Function called to remove empty elements in an array
     *
     * @param mixed $a
     *
     * @return boolean
     */
    public static function removeEmptyElements($a)
    {
        return array_filter($a, array(get_called_class(), 'notEmpty'));
    }


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
