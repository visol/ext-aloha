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

// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH . 'init.php');
require_once($BACK_PATH . 'template.php');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');

/**
 * Browselink module
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Tx_Aloha_BrowseLink_Content extends t3lib_SCbase {

	/**
	 * Main function which is called by outside
	 *
	 * @return void
	 */
	public function main() {
		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];

		$GLOBALS['LANG']->includeLLFile('EXT:lang/locallang_browse_links.xml');
		$GLOBALS['WEBMOUNTS'] = $GLOBALS['BE_USER']->returnWebmounts();

		t3lib_div::requireOnce(PATH_typo3 . '/template.php');
		t3lib_div::requireOnce(PATH_typo3 . '/class.browse_links.php');

		$this->browser = t3lib_div::makeInstance('browse_links');
		$this->browser->init();

		$modData = $GLOBALS['BE_USER']->getModuleData('browse_links.php', 'ses');
		list($modData, $store) = $this->browser->processSessionData($modData);
		$GLOBALS['BE_USER']->pushModuleData('browse_links.php', $modData);

		$content = $this->browser->main_rte(1);
		$content .= '<script type="text/javascript">
							function renderPopup_addLink(theLink, cur_target, cur_class, cur_title) {
								opener.renderPopup_addLink2(theLink, cur_target, cur_class, cur_title);
								self.close();
							}
						</script>';

		echo $content;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/aloha/Classes/BrowseLink/Content.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/aloha/Classes/BrowseLink/Content.php']);
}

$SOBE = t3lib_div::makeInstance('Tx_Aloha_BrowseLink_Content');
$SOBE->init();
$SOBE->main();

?>