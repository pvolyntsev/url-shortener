<?php

/**
 * Web form with to input elements: 'url' and 'shortUrl'
 * 
 * It will render using view /views/forms/url.phtml
 */
class UrlForm extends AppForm {

	protected $_method = 'POST';

	/**
	 * Returns list of form elements with extra info: text label, data type, list of filters and list of validators
	 * 
	 * Filters and validators will be applied when call $this->setValue('elementName', 'newValue');
	 * Filters will remove wrong characters or convert element value
	 * Validators will check element value to have correct content
	 */
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
