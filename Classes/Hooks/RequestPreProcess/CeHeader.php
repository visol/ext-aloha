<?php

/* **************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Georg Ringer <typo3@ringerge.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('aloha') . 'Classes/Interfaces/RequestPreProcess.php');

/**
 * Hook for saving content element "bullets"
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Tx_Aloha_Hooks_RequestPreProcess_CeHeader implements Tx_Aloha_Interfaces_RequestPreProcess {

	/**
	 * Preprocess the request
	 *
	 * @param array $request save request
	 * @param boolean $finished
	 * @param Tx_Aloha_Aloha_Save $parentObject
	 * @return array
	 */
	public function preProcess(array &$request, &$finished, Tx_Aloha_Aloha_Save &$parentObject) {
	
			// only allowed for field header
		if ( $parentObject->getTable() === 'tt_content' && $parentObject->getField() == 'header' ) {

				// Check if we need to update header-type field
			if ( substr( $request['content'], 0, 2 ) === "<h" ) {
				$headerTypeRequest = $request;

				switch ( substr($request['content'], 0, 3) ) {
					case '<h1':
						$headerTypeRequest['content'] = 1;
						break;
					case '<h2':
						$headerTypeRequest['content'] = 2;
						break;
					case '<h3':
						$headerTypeRequest['content'] = 3;
						break;
					case '<h4':
						$headerTypeRequest['content'] = 4;
						break;
					case '<h5':
						$headerTypeRequest['content'] = 5;
						break;
					case '<h6':
						$headerTypeRequest['content'] = 6;
						break;
					default:
						$headerTypeRequest['content'] = 0;
						break;
					}
						// Do a direct save for the header-type field. 
					$headerTypeRequest['identifier'] = $parentObject->getTable() . '--header_layout--' . $parentObject->getUid();
					$parentObject->directSave($headerTypeRequest,TRUE);

					$parentObject->setField('header');
			}

				// Remove tags so we only have the plaint text.
			$request['content'] = urldecode(strip_tags($request['content']));
		}
		return $request;
	}

}

?>