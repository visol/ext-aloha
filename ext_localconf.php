<?php

if (!defined("TYPO3_MODE")) {
	die("Access denied.");
}

// Add additional stdWrap properties
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['stdWrap'][$_EXTKEY] =
	'Pixelant\\Aloha\\Hook\\EditIcons';

// Hook to render menu panel
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][$_EXTKEY] =
	'Pixelant\\Aloha\\Hook\\ContentPostProc->main';

/**
 * Hooks to change content before saving back into DB
 */

// Cleanup
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cleanup'] =
	'Pixelant\\Aloha\\Hook\\RequestPreProcess\\Cleanup';
// Save core content element "Bullets"
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cobjbullets'] =
	'Pixelant\\Aloha\\Hook\\RequestPreProcess\\CeBullets';
// Save core content element "Table"
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cobjtable'] =
	'Pixelant\\Aloha\\Hook\\RequestPreProcess\\CeTable';
// Save core content special element "Plaintext"
// Activated by setting field to to bodytext-plaintext, which will be restored to bodytext in the hook
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-Plaintext'] =
	'Pixelant\\Aloha\\Hook\\RequestPreProcess\\Plaintext';
// Save Fluid content, field is targeted by pi_flexform-flexformfieldname
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-CeFluidContent'] =
	'Pixelant\\Aloha\\Hook\\RequestPreProcess\\CeFluidContent';
// Save headers, field header, will also affect field header_layout
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-CeHeader'] =
	'Pixelant\\Aloha\\Hook\\RequestPreProcess\\CeHeader';
// Save RTE content (CEs "Text", "Text with Images") respecting typolinks
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-CeRteLinks'] =
	'Pixelant\\Aloha\\Hook\\RequestPreProcess\\CeRteLinks';

if (!function_exists('isAlohaEnabledForUser')) {
	function isAlohaEnabledForUser() {
		return \Pixelant\Aloha\UserFunc\Aloha::isAlohaEnabledForUser();
	}
}

// Allow BackendUserSettings to be accessed through ExtDirect
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerExtDirectComponent('TYPO3.BackendUserSettings.ExtDirect', 'TYPO3\\CMS\\Backend\\User\\ExtDirect\\BackendUserSettingsDataProvider');

// Use pageRenderer hook to pass BackendUserSettings to the Frontend
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['aloha'] = 'Pixelant\\Aloha\\Hook\\PageRenderer->preProcessPageRenderer';

// Add our UserTSConfig
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:aloha/Configuration/TypoScript/userTsConfig.ts">');

?>