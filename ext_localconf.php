<?php

if (!defined("TYPO3_MODE")) {
	die("Access denied.");
}

	// Add additional stdWrap properties
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['stdWrap'][$_EXTKEY] =
	'EXT:aloha/Classes/Hooks/EditIcons.php:&Tx_Aloha_Hooks_Editicons';

	// Override locallang file of admin panel to get own elements into it
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:lang/locallang_tsfe.php'][$_EXTKEY] =
	'EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml';

	// Hook to render menu panel
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][$_EXTKEY] =
	'EXT:aloha/Classes/Hooks/ContentPostProc.php:&Tx_Aloha_Hooks_ContentPostProc->main';

/**
 * Hooks to change content before saving back into DB
 */

	// Cleanup
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cleanup'] =
	'EXT:aloha/Classes/Hooks/RequestPreProcess/Cleanup.php:&Tx_Aloha_Hooks_RequestPreProcess_Cleanup';
	// Save core content element "Bullets"
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cobjbullets'] =
	'EXT:aloha/Classes/Hooks/RequestPreProcess/CeBullets.php:&Tx_Aloha_Hooks_RequestPreProcess_CeBullets';
	// Save core content element "Table"
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cobjtable'] =
	'EXT:aloha/Classes/Hooks/RequestPreProcess/CeTable.php:&Tx_Aloha_Hooks_RequestPreProcess_CeTable';
	// Save core content special element "Plaintext"
	// Activated by setting field to to bodytext-plaintext, which in hook will be restored to bodytext
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-Plaintext'] =
	'EXT:aloha/Classes/Hooks/RequestPreProcess/Plaintext.php:&Tx_Aloha_Hooks_RequestPreProcess_Plaintext';
	// Save fluidcontent , field is targeted by pi_flexform-flexformfieldname
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-CeFluidContent'] =
	'EXT:aloha/Classes/Hooks/RequestPreProcess/CeFluidContent.php:&Tx_Aloha_Hooks_RequestPreProcess_CeFluidContent';
	// Save headers, field header, will also affect field header_layout
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-CeHeader'] =
	'EXT:aloha/Classes/Hooks/RequestPreProcess/CeHeader.php:&Tx_Aloha_Hooks_RequestPreProcess_CeHeader';
	// Check link params in rte of text and textpic content elements
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-CeRteLinks'] =
	'EXT:aloha/Classes/Hooks/RequestPreProcess/CeRteLinks.php:&Tx_Aloha_Hooks_RequestPreProcess_CeRteLinks';

function isAlohaEnabledForUser() {
	return \Pixelant\Aloha\UserFunc\AlohaUserFunc::isAlohaEnabledForUser();
}
?>