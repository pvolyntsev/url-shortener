<?php

class AlphaId {

	protected static $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

	// Convert alphabetical code to decimal number
	public static function toId($in, $pad_up = false) {
		$base = strlen(static::$index);

		$in = strrev($in);
		$out = 0;
		$len = strlen($in) - 1;
		for ($t = 0; $t <= $len; $t++) {
			$bcpow = bcpow($base, $len - $t);
			$out = $out + strpos(static::$index, substr($in, $t, 1)) * $bcpow;
		}

		if (is_numeric($pad_up)) {
			$pad_up--;
			if ($pad_up > 0) {
				$out -= pow($base, $pad_up);
			}
		}
		$out = sprintf('%F', $out);
		$out = substr($out, 0, strpos($out, '.'));

		return $out;
	}

	/**
	 * Convert decimal number to alphabetical code
	 */
	public static function toAlpha($in, $pad_up = false) {
		$base = strlen(static::$index);

		if (is_numeric($pad_up)) {
			$pad_up--;
			if ($pad_up > 0) {
				$in += pow($base, $pad_up);
			}
		}

		$out = "";
		for ($t = floor(log($in, $base)); $t >= 0; $t--) {
			$bcp = bcpow($base, $t);
			$a = floor($in / $bcp) % $base;
			$out = $out . substr(static::$index, $a, 1);
			$in = $in - ($a * $bcp);
		}
		$out = strrev($out); // reverse

		return $out;
	}
}