<?php
namespace Pixelant\Aloha\Hook;

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

/**
 * Hook to enable additional stdWrap function "aloha"
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class EditIcons implements \TYPO3\CMS\Frontend\ContentObject\ContentObjectStdWrapHookInterface {

	/**
	 * Implement a new stdWrap function to get aloha icons
	 *
	 * @param string $content
	 * @param array $configuration
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $parentObject
	 * @return string
	 */
	public function stdWrapProcess($content, array $configuration, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$parentObject) {
		if ($configuration['alohaProcess'] == 1 && \Pixelant\Aloha\Utility\Access::isEnabled()) {
			$alohaIntegration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Pixelant\Aloha\Controller\IntegrationController');
			$content = $alohaIntegration->start($content, $configuration['alohaProcess.'], $parentObject);
		}

		return $content;
	}


	/**
	 * Only needed to meet the requirements of the interface
	 *
	 * @param string $content
	 * @param array $configuration
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $parentObject
	 * @return string
	 */
	public function stdWrapPreProcess($content, array $configuration, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$parentObject) {
		return $content;
	}

	/**
	 * Only needed to meet the requirements of the interface
	 *
	 * @param string $content
	 * @param array $configuration
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $parentObject
	 * @return string
	 */
	public function stdWrapOverride($content, array $configuration, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$parentObject) {
		return $content;
	}

	/**
	 * Hook for modifying $content after core's stdWrap has processed anything but debug
	 *
	 * @param string $content
	 * @param array $configuration
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $parentObject
	 * @return string
	 */
	public function stdWrapPostProcess($content, array $configuration, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$parentObject) {
		if ($configuration['alohaPostProcess'] == 1 && \Pixelant\Aloha\Utility\Access::isEnabled()) {
			$alohaIntegration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Pixelant\Aloha\Controller\IntegrationController');
			$content = $alohaIntegration->start($content, $configuration['alohaPostProcess.'], $parentObject);
		}
		return $content;
	}

}

?>
