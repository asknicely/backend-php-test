<?php

class Utils {

    public static function createJson(array $data) {

        $output = '{';

        $separator = '';

        foreach($data as $key=>$val) {
            $output .= $separator . $key . ': ';

            if(is_int($val)) {
                $output .= $val;
            } elseif(is_string($val)) {
                $output .= '"' . str_replace( '"', '\"', $val) . '"';
            } elseif(is_bool($val)) {
                $output .= $val ? 'true' : 'false';
            } else {
                $output .= $val;
            }
            $separator = ', ';
        }

        $output .= '}';

        return $output;
    }


    public static function bchexdec($hex) {
        if(strlen($hex) == 1) {
            return hexdec($hex);
        } else {
            $remain = substr($hex, 0, -1);
            $last = substr($hex, -1);
            return bcadd(bcmul(16, self::bchexdec($remain)), hexdec($last));
        }
    }

    public static function bcdechex($dec) {
        $last = bcmod($dec, 16);
        $remain = bcdiv(bcsub($dec, $last), 16);

        if($remain == 0) {
            return dechex($last);
        } else {
            return self::bcdechex($remain).dechex($last);
        }
    }
}
