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
}