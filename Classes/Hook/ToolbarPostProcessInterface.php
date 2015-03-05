<?php
namespace Pixelant\Aloha\Hook;


interface ToolbarPostProcessInterface {

	/**
	 * Postprocess of toolbar's content
	 *
	 * @param string $content toolbar content
	 * @param \Pixelant\Aloha\Hook\ContentPostProc $parentObject
	 * @return void
	 */
	public function postProcess(&$content, \Pixelant\Aloha\Hook\ContentPostProc &$parentObject);

}

?>
