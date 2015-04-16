<?php
namespace Pixelant\Aloha\Hook;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module 'about' shows some standard information for TYPO3 CMS: About-text, version number and so on.
 *
 * @author Lorenz Ulrich <lorenz.ulrich@visol.ch>
 */
class PageRenderer {

	/**
	 * @param $params
	 * @param $parentObject \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer
	 */
	public function preProcessPageRenderer($params, $parentObject) {
		if (\Pixelant\Aloha\Utility\Access::isEnabled()) {
			$parentObject->loadExtJS();
			$parentObject->addExtDirectCode(array(
				'TYPO3.BackendUserSettings',
			));
		}
	}

}

