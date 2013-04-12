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
class Tx_Aloha_BrowseLink_Index extends t3lib_SCbase {

	/**
	 * Get a real simple page including an iframe which holds the real
	 * link implementation
	 * This wrapper is needed because the core can't handle links very good + JS
	 * callback calls self.parent().parent().
	 *
	 * @return void
	 */
	public function main() {
		$url = 'Content.php';

		$html = '<html>
					<head>
						<style type="text/css">
							* {
								padding:0 0 0 5px;
								margin:0;
								background:#F8F8F8;
							}
						</style>
					</head>
					<body>
						<iframe style="width:100%;height:100%;border:0;padding:0;margin:0;" height="500px" src=' . htmlspecialchars($url) . '></iframe>
						<script type="text/javascript">
							function renderPopup_addLink(theLink, cur_target, cur_class, cur_title) {
								opener.renderPopup_addLink2(theLink, cur_target, cur_class, cur_title);
								self.close();
							}
						</script>
					</body>
				</html>';
		echo $html;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/aloha/Classes/BrowseLink/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/aloha/Classes/BrowseLink/index.php']);
}

$SOBE = t3lib_div::makeInstance('Tx_Aloha_BrowseLink_Index');
$SOBE->init();
$SOBE->main();

?>