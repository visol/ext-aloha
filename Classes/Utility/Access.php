<?php
namespace Pixelant\Aloha\Utility;

/***************************************************************
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
 ***************************************************************/

/**
 * Check access of the user to display only those actions which are allowed
 * and needed
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Access {

	/**
	 * Checks if aloha editor is enabled, checking UserTsConfig and TS
	 *
	 * @return boolean
	 */
	public static function isEnabled() {
		$isEnabled = FALSE;
		// aloha needs to be enabled also by admins
		// this is the only way how to temporarily turn on/off the editor
		if (isset($GLOBALS['BE_USER']) && $GLOBALS['TSFE']->config['config']['aloha'] == 1) {
			$isEnabled = ($GLOBALS['BE_USER']->uc['tx_aloha_enable'] == 1);
		}
		// Aloha is not compatible with the Workspaces preview in Frontend, so it is deactivated in this context
		$isWorkspacePreview = (bool)\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('ADMCMD_previewWS');
		if ($isWorkspacePreview) {
			$isEnabled = FALSE;
		}
		return $isEnabled;
	}


	public static function checkAccess($table, array $dataArray, $config) {
		if (empty($table) || empty($dataArray) || empty($config)) {
			return FALSE;
		}

		if (!isset($GLOBALS['BE_USER'])) {
			return FALSE;
		}

		if ($GLOBALS['BE_USER']->isAdmin()) {
			return TRUE;
		}

		// not needed: $GLOBALS['TSFE']->displayFieldEditIcons
		if (self::allowedToEdit($table, $dataArray, $config)
			&& self::allowedToEditLanguage($table, $dataArray)
		) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Checks whether the user is allowed to edit the requested table.
	 *
	 * @param    string    The name of the table.
	 * @param    array    The data array.
	 * @param    array    The configuration array for the edit panel.
	 * @param    boolean    Boolean indicating whether recordEditAccessInternals should not be checked. Defaults
	 *                     to TRUE but doesn't makes sense when creating new records on a page.
	 * @return    boolean
	 */
	protected function allowedToEdit($table, array $dataArray, array $conf, $checkEditAccessInternals = TRUE) {
		// Unless permissions specifically allow it, editing is not allowed.
		$mayEdit = FALSE;

		// Basic check if use is allowed to edit a record of this kind (based on TCA configuration)
		if ($checkEditAccessInternals) {
			$editAccessInternals = $GLOBALS['BE_USER']->recordEditAccessInternals($table, $dataArray, FALSE, FALSE);
		} else {
			$editAccessInternals = TRUE;
		}


		if ($editAccessInternals) {
			if ($table === 'pages') {
				// 2 = permission to edit the page
				if ($GLOBALS['BE_USER']->isAdmin() || $GLOBALS['BE_USER']->doesUserHaveAccess($dataArray, 2)) {
					$mayEdit = TRUE;
				}
			} elseif ($table === 'tt_content') {
				// 16 = permission to edit content on the page
				if ($GLOBALS['BE_USER']->isAdmin() || $GLOBALS['BE_USER']->doesUserHaveAccess(\TYPO3\CMS\Backend\Utility\BackendUtility::getRecord('pages', $dataArray['pid']), 16)) {
					$mayEdit = TRUE;
				}
			} else {
				// neither page nor content
				$mayEdit = TRUE;
			}

			if (!$conf['onlyCurrentPid'] || ($dataArray['pid'] == $GLOBALS['TSFE']->id)) {

				// Permissions:
				$types = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', \TYPO3\CMS\Core\Utility\GeneralUtility::strtolower($conf['allow']), 1);
				$allow = array_flip($types);

				$perms = $GLOBALS['BE_USER']->calcPerms($GLOBALS['TSFE']->page);

				if ($table === 'pages') {
					$allow = $this->getAllowedEditActions($table, $conf, $dataArray['pid'], $allow);

					// Can only display editbox if there are options in the menu
					if (count($allow)) {
						$mayEdit = TRUE;
					}
				} else {

					if ($table === 'tt_content') {
						// user may edit the content if he has an allowed edit action and if the permission for the content is odd and not 1
						// explanation of permissions: show=1,edit=2,delete=4,new=8,editcontent=16
						// assuming that show must be set to have content editable, each permission is odd, but show itself isn't sufficient
						$mayEdit = count($allow) && ($perms & 1 && $perms !== 1) ? TRUE : FALSE;
					} else {
						// user may edit the content if he has an allowed edit action and if the permission for the content is odd and not 1
						// explanation of permissions: show=1,edit=2,delete=4,new=8,editcontent=16
						// assuming that show must be set to have content editable, each permission is odd, but show itself isn't sufficient
						$mayEdit = ($perms & 1 && $perms !== 1);
					}

				}
			}
		}

		return $mayEdit;
	}

	/**
	 * Checks whether the user has access to edit the language for the
	 * requested record.
	 *
	 * @param    string        The name of the table.
	 * @param    array        The record.
	 * @return    boolean
	 */
	protected function allowedToEditLanguage($table, array $currentRecord) {
		$languageUid = -1;
		$languageAccess = FALSE;

		// If no access right to record languages, return immediately
		if ($table === 'pages') {
			$languageUid = $GLOBALS['TSFE']->sys_language_uid;
		} elseif ($table === 'tt_content') {
			$languageUid = $GLOBALS['TSFE']->sys_language_content;
		} elseif ($GLOBALS['TCA'][$table]['ctrl']['languageField']) {
			$languageUid = $currentRecord[$GLOBALS['TCA'][$table]['ctrl']['languageField']];
		}

		if ($GLOBALS['BE_USER']->checkLanguageAccess($languageUid)) {
			$languageAccess = TRUE;
		}

		return $languageAccess;
	}
}

?>