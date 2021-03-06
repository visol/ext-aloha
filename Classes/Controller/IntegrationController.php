<?php
namespace Pixelant\Aloha\Controller;

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
use Pixelant\Aloha\Utility\Helper;

/**
 * Integration class of aloha into TYPO3
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class IntegrationController {

	protected $table = NULL;
	protected $field = NULL;
	protected $uid = NULL;
	protected $dataArray = array();
	protected $alohaConfig = array();

	public function start($content, array $configuration, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$parentObject) {

		try {
			$alohaConfig = $configuration;
			$this->init($parentObject, $alohaConfig);

			$access = \Pixelant\Aloha\Utility\Access::checkAccess($this->table, $this->dataArray, $this->alohaConfig);
			if ($access) {
				if (empty($content)) {
					$alohaConfig['class'] .= 'aloha-empty-content';
				}
				if ($this->dataArray['hidden'] == 1) {
					$alohaConfig['class'] .= ' aloha-preview-content';
				}

				$classList = array('alohaeditable');
				$this->getAllowedActions($alohaConfig, $classList);

				$attributes = array(
					'id' => Helper::getUniqueId($this->table, $this->field, $this->uid),
					'class' => implode(' ', $classList),
					'style' => $alohaConfig['style']
				);

				$content = \Pixelant\Aloha\Utility\Integration::renderAlohaWrap($content, $attributes, $alohaConfig['tag']);
			}
		} catch (\Exception $e) {
			$errorMsg = sprintf('Error with AlohaEditor: %s', $e->getMessage());
			$content .= '<div style="color:red;padding:2px;margin:2px;font-weight:bold;">' . htmlspecialchars($errorMsg) . '</div>';
		}

		return $content;
	}

	/**
	 * Modify classList and add the actions which are allowed
	 *
	 * @param array $alohaConfig
	 * @param array $classList
	 * @return void
	 */
	private function getAllowedActions(array $alohaConfig, array &$classList) {
		$allowedActions = array_flip(\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $alohaConfig['allow']));

		// Hiding in workspaces because implementation is incomplete
		// @todo: check that
		if ((isset($allowedActions['all']) || isset($allowedActions['move'])) && $GLOBALS['TCA'][$this->table]['ctrl']['sortby'] && $GLOBALS['BE_USER']->workspace === 0) {
			array_push($classList, 'action-up');
			array_push($classList, 'action-down');
			array_push($classList, 'action-move');
		}
		// edit action
		if ($this->checkAccess($allowedActions, 'edit')) {
			array_push($classList, 'action-edit');
		}

		// link action
		if ($this->checkAccess($allowedActions, 'link')) {
			array_push($classList, 'action-link');
		}
		if (isset($GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['disabled'])) {
			$disabledField = $GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['disabled'];
			if ($this->checkAccess($allowedActions, 'hide') && $this->dataArray[$disabledField] == 0) {
				array_push($classList, 'action-hide');
			}
			if ($this->checkAccess($allowedActions, 'unhide') && $this->dataArray[$disabledField] == 1) {
				array_push($classList, 'action-unhide');
			}
		}

		// Add new content elements underneath
		if ($this->checkAccess($allowedActions, 'newContentElementBelow')) {
			array_push($classList, 'action-newContentElementBelow');
		}

		// @todo: && $GLOBALS['BE_USER']->workspace === 0 && !$dataArr['_LOCALIZED_UID']
		// still true, check that
		if ($this->checkAccess($allowedActions, 'delete')) {
			array_push($classList, 'action-delete');
		}

		// Additional class by TS
		if (isset($alohaConfig['class'])) {
			array_push($classList, htmlspecialchars($alohaConfig['class']));
		}

		// Restrict editor by removing all styles
		if ($alohaConfig['nostyles'] == 1) {
			array_push($classList, 'nostyles');
		}
	}

	/**
	 * Initialize the integration to get needed configs
	 *
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $parentObject
	 * @param array $alohaConfig
	 * @throws \Exception
	 * @return void
	 */
	private function init(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $parentObject, array $alohaConfig) {
		list($table, $id) = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(':', $parentObject->currentRecord);
		$currentRecord = $parentObject->data;

		if (empty($parentObject->currentRecord)) {
			return FALSE;
		}

		if (isset($currentRecord['_LOCALIZED_UID'])) {
			$id = $currentRecord['_LOCALIZED_UID'];
		}
		if (empty($table)) {
			throw new \Exception(Helper::ll('error.integration.table'));
		} elseif (empty($id)) {
			throw new \Exception(Helper::ll('error.integration.uid'));
		} elseif (empty($alohaConfig['field'])) {
			throw new \Exception(Helper::ll('error.integration.field'));
		}

		$this->table = $table;
		$this->field = $alohaConfig['field'];
		$this->uid = $id;
		$this->dataArray = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $table, 'uid=' . (int)$id);
		$this->alohaConfig = $alohaConfig;
	}

	/**
	 * Check the access for a single given access
	 *
	 * @param array $allowedActions configuration array
	 * @param string $access access type
	 * @return boolean
	 */
	private function checkAccess(array $allowedActions, $access) {
		if (isset($allowedActions['all']) || isset($allowedActions[$access])) {
			return TRUE;
		}
		return FALSE;
	}

}

?>