<?php

/* * *************************************************************
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

require_once(t3lib_extMgm::extPath('aloha') . 'Classes/Interfaces/RequestPreProcess.php');

/**
 * Hook for saving content element "table"
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Tx_Aloha_Hooks_RequestPreProcess_CeTable implements Tx_Aloha_Interfaces_RequestPreProcess {

	/**
	 * Preprocess the request
	 *
	 * @param array $request save request
	 * @param boolean $finished
	 * @param Tx_Aloha_Aloha_Save $parentObject
	 * @return array
	 */
	public function preProcess(array &$request, &$finished, Tx_Aloha_Aloha_Save &$parentObject) {
		$record = $parentObject->getRecord();

		// only allowed for element "table"
		if ($parentObject->getTable() === 'tt_content'
				&& $parentObject->getField() == 'bodytext'
				&& $record['CType'] === 'table') {

			$finished = TRUE;

			$domDocument = new DOMDocument();
			$domDocument->loadHTML('<?xml encoding="utf-8" ?>' . $request['content']);

			$xPath = new DOMXpath($domDocument);

			$trCollection = $xPath->query('//table/*/tr');
			$tmpCollection = array();
			if (!is_null($trCollection)) {
				foreach ($trCollection as $element) {
					$singleLine = array();

					$nodes = $element->childNodes;
					foreach ($nodes as $node) {
						$value = trim($node->nodeValue);
						if (!empty($value)) {
							$singleLine[] = $value;
						}
					}
					$tmpCollection[] = implode('|', $singleLine);
				}

			}
			$request['content'] = implode(LF, $tmpCollection);


			$captionPath = $xPath->query('//table//caption[1]');
			$captionValue = '';
			foreach ($captionPath as $c) {
				$captionValue = trim($c->nodeValue);
			}
//echo $captionValue;

			$flexformTableSettings = t3lib_div::xml2array($record['pi_flexform']);




// ------------------------------------


			$doc = new DOMDOcument;
			$doc->loadxml($record['pi_flexform']);

			$replacement = $doc->createDocumentFragment();
			$replacement->appendXML('<value index="vDEF">' . $captionValue . '</value>');

			$xpath = new DOMXpath($doc);

			$oldNode = $xpath->query('//field[@index=\'acctables_caption\']//value')->item(0);
			$oldNode->parentNode->replaceChild($replacement, $oldNode);
			$newPiFlexform = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>' . $doc->saveXml($doc->documentElement);

			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'uid=' . $record['uid'], array('pi_flexform' => $newPiFlexform));

// ------------------------------------
		}

		return $request;
	}

}

?>