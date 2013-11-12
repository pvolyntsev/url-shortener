<?php

/**
 * Filter prototype
 *
 * Filter that removes HTML tags from string
 * This protects from XSS Injection
 */
class StripTags {

	public function filter($value) {
		return strip_tags($value);
	}
}