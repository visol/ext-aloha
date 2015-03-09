<?php
namespace Pixelant\Aloha\Hook;

interface RequestPreProcessInterface {

	/**
	 * Preprocess the request
	 *
	 * @param array $request save request
	 * @param boolean $finished
	 * @param \Pixelant\Aloha\Controller\SaveController $parentObject
	 * @return array
	 */
	public function preProcess(array &$request, &$finished, \Pixelant\Aloha\Controller\SaveController &$parentObject);

}

?>
