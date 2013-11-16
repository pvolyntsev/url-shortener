<?php

/**
 * Controller to do convert the long URL into the short one
 */
class FormController extends AppController {

	protected function init() {
		parent::init();
	}

	protected function formAction() {
		$form = new UrlForm;
		if (App::request()->isPost()) {
			$form->setValue('url', App::request()->getPostVar('url'));
			if ($form->isValid()) { // if URL is valid

				// find or generate short URL
				$existsUrlRecord = UrlModel::findOneByLongurl($form->getValue('url'));
				if (false!==$existsUrlRecord) {
					// alredy exists - use it
					$shortURI = App::alphaid()->toAlpha($existsUrlRecord->id);
				} else {
					// not exists - create new
					$urlRecord = new UrlModel;
					$urlRecord->longurl = $form->getValue('url');
					$urlRecord->save();
					$shortURI = App::alphaid()->toAlpha($urlRecord->id);
				}

				$shortURL = App::router()->createUrl('Redirector', 'redirect', array('url' => $shortURI));
				$form->setValue('shortUrl', $shortURL);
			}
		}
		if (App::request()->isAjaxRequest()) {
			$this->setLayout('ajax');
			$this->view->form = $form->getData();
		} else {
			$this->view->form = $form;
			$this->render();
		}
	}
}