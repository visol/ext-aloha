<?php
namespace Pixelant\Aloha\Hook\RequestPreProcess;

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

/**
 * Hook for saving content element "rte with links"
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class CeRteLinks implements \Pixelant\Aloha\Hook\RequestPreProcessInterface {

	/**
	 * Preprocess the request
	 *
	 * @param array $request save request
	 * @param boolean $finished
	 * @param \Pixelant\Aloha\Controller\SaveController $parentObject
	 * @return array
	 */
	public function preProcess(array &$request, &$finished, \Pixelant\Aloha\Controller\SaveController &$parentObject) {
		$record = $parentObject->getRecord();

		// only allowed for text and textpic element (at least for now)
		if ($parentObject->getTable() === 'tt_content'
			&& $parentObject->getField() == 'bodytext'
			&& ($record['CType'] === 'text' || $record['CType'] === 'textpic')
		) {

			// Transform links to typolinks
			$content = $this->transformLinksToTypolinks($request['content']);
			// Send links thru RteHtmlParser
			/** @var \TYPO3\CMS\Core\Html\RteHtmlParser $parseHTML */
			$parseHTML = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Html\\RteHtmlParser');
			// Parse links back to TYPO3 internal "<a href" format to save content with DataHandler
			$content = $parseHTML->TS_links_rte($content);
			$request['content'] = $content;
		}

		return $request;
	}

	/**
	 * All incoming typolinks have the original typolink configuration in attribute "date-typolinkoriginalparameter".
	 * All <a> Links are replaced by typolinks in the proprietary <link 123>Link to internal page</link> format.
	 *
	 * @param string $content
	 * @return mixed
	 */
	protected function transformLinksToTypolinks($content) {

		$domDocument = new \DOMDocument();
		$domDocument->loadHTML('<?xml encoding="utf-8" ?>' . $content);

		/** @var \DOMNodeList $anchorCollection */
		$anchorCollection = $domDocument->getElementsByTagName('a');

		foreach ($anchorCollection as $anchor) {
			/** @var \DomElement $anchor */
			// Get the HTML of the link sent from Aloha
			$oldLink = $anchor->ownerDocument->saveHTML($anchor);

			$typolinkOriginalParameter = $anchor->getAttribute('data-typolinkoriginalparameter');
			if (!empty($typolinkOriginalParameter)) {
				// Link was a typolink before, so we add a typolink in source code format
				$newLink = '<link ' . $typolinkOriginalParameter . '>' . $anchor->nodeValue . '</link>';
			} else {
				// Link was a non-TYPO3 link, we add rtekeep="1"
				$anchor->setAttribute('rtekeep', '1');
				$newLink = $anchor->ownerDocument->saveHTML($anchor);
			}
			$content = str_replace($oldLink, $newLink, $content);
		}
		return $content;
	}

}

?>