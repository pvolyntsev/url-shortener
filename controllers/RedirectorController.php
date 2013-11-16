<?php

/**
 * Controller to do redirect from the short URL into the long URL
 */
class RedirectorController extends AppController {

	protected function redirectAction() {

		$shortURI = App::request()->getQueryVar('url');
		$shortURIId = App::alphaid()->toId($shortURI);

		/** @var $urlRecord UrlModel */
		$urlRecord = UrlModel::findOneByPk($shortURIId);

		if (false!==$urlRecord && !empty($urlRecord->longurl)) {
			// TODO cache
			// TODO statictics (hits/lastuse)
			App::response()->redirectAndExit($urlRecord->longurl, 301); // SEO friendly redirect
		} else {
			// redirect failed
			App::response()->sendNotFoundAndExit();
		}
	}

}