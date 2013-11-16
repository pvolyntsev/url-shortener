<?php

/**
 * Class converts big unsigned decimal identifiers into short alphanumeric code
 * And restore decimal identifier from code
 * 
 * @see https://github.com/sagargp/coolsitebro/blob/master/alphaID.php
 */
class AlphaId {

	protected $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

	protected $isEncrypted = false;

	public $min_length = 1;

	public $passkey = '';

	function __construct($config) {
		if (isset($config['alphaid'])) {
			foreach($config['alphaid'] as $key => $value) {
				if (property_exists($this, $key)) {
					$this->{$key} = $value;
				}
			}
		}

		if (!empty($this->passkey)) {
			$this->ecryptIndex($this->passkey);
		}
	}

	public function ecryptIndex($passKey) {
		// Although this function's purpose is to just make the
		// ID short - and not so much secure,
		// with this patch by Simon Franz (http://blog.snaky.org/)
		// you can optionally supply a password to make it harder
		// to calculate the corresponding numeric ID

		if ($this->isEncrypted) {
			return;
		}

		for ($n = 0; $n<strlen($this->index); $n++) {
			$i[] = substr($this->index,$n ,1);
		}

		$passhash = hash('sha256',$passKey);
		$passhash = (strlen($passhash) < strlen($this->index))
			? hash('sha512',$passKey)
			: $passhash;

		for ($n=0; $n < strlen($this->index); $n++) {
			$p[] = substr($passhash, $n ,1);
		}

		array_multisort($p, SORT_DESC, $i);
		$this->index = implode($i);

		$this->isEncrypted = true;
	}

	// Convert alphanumeric code to decimal number
	public function toId($in, $pad_up = false) {
		$base = strlen($this->index);

		if (false===$pad_up) {
			$pad_up = $this->min_length;
		}

		$in = strrev($in);
		$out = 0;
		$len = strlen($in) - 1;
		for ($t = 0; $t <= $len; $t++) {
			$bcpow = bcpow($base, $len - $t);
			$out = $out + strpos($this->index, substr($in, $t, 1)) * $bcpow;
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
	 * Convert decimal number to alphanumeric code
	 */
	public function toAlpha($in, $pad_up = false) {
		$base = strlen($this->index);

		if (false===$pad_up) {
			$pad_up = $this->min_length;
		}

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
			$out = $out . substr($this->index, $a, 1);
			$in = $in - ($a * $bcp);
		}
		$out = strrev($out); // reverse

		return $out;
	}
}
