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
 * Hook for saving content element "rte with links"
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Tx_Aloha_Hooks_RequestPreProcess_CeRteLinks implements Tx_Aloha_Interfaces_RequestPreProcess {

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

		// only allowed for text and textpic element (at least for now)
		if ($parentObject->getTable() === 'tt_content'
			&& $parentObject->getField() == 'bodytext'
			&& ($record['CType'] === 'text' || $record['CType'] === 'textpic')
		) {

			$content = $this->removeUnwantedLinkVars($request['content']);
			// Send links thru RteHtmlParser
			$parseHTML = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Html\\RteHtmlParser');
			$content = $parseHTML->TS_links_db($content);
			$content = $parseHTML->TS_links_rte($content);

			$request['content'] = $content;
		}

		return $request;
	}

	protected function removeUnwantedLinkVars($content) {

		$domDocument = new DOMDocument();
		$domDocument->loadHTML('<?xml encoding="utf-8" ?>' . $content);

		$anchorCollection = $domDocument->getElementsByTagName('a');

		foreach ($anchorCollection as $anchor) {
			// Fetch "original" url and divide into parts
			$originalUrl = $anchor->getAttribute('href');
			$parts = parse_url($originalUrl);
			parse_str($parts['query'], $query);

			if (is_array($query) && count($query) > 0) {
				// Clear unwanted parts
				$unwantedLinkVars = array_flip(explode(",", $GLOBALS['TSFE']->config['config']['linkVars']));
				// Remove unwanted LinkVars from parts
				$cleanedQuery = array_diff_key($query, $unwantedLinkVars);
				// Set the new query part
				$parts['query'] = http_build_query($cleanedQuery);
				// Build replacement url
				$replacementUrl = \TYPO3\CMS\Core\Utility\HttpUtility::buildUrl($parts);
				// Remove questionmark from url if last
				$replacementUrl = rtrim($replacementUrl, '?');
				// Replace links if they dont match
				if ($replacementUrl !== $originalUrl && strlen($replacementUrl) > 0) {
					$content = str_replace(htmlspecialchars($originalUrl), $replacementUrl, $content);
				}

			}
		}
		return $content;
	}
}

?>