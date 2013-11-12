<?php

class UrlForm extends AppForm {

	protected $_method = 'POST';

	public function getRules() {
		return array(
			'url' => array(
				'type' => 'text',
				'label' => 'Long URL',

				// input filters
				'filter' => array(
					// any characters are allowed
					'StripTags', // except HTML tags
				),

				// validators
				'validate' => array(
					'required', // can not be empty
					array('Url', 'Incorrect URL'), // value must be valid URL
				),
			),

			'shortUrl' => array(
				'type' => 'text',
				'label' => 'Short URL',
				'filter' => 'StripTags',
			)
		);
	}


}