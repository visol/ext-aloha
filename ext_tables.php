<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

/* * *************
 * TypoScript Files
 */
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Basic', 'Aloha Basic');
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Modification', 'Aloha Modification');

// Add BE User setting
$GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_aloha_enable'] = array(
        'label' => 'LLL:EXT:aloha/Resources/Private/Language/locallang_db.xlf:be_users.tx_aloha_enable',
        'type' => 'check',
        'default' => 0,
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToUserSettings(
        'LLL:EXT:examples/Resources/Private/Language/locallang_db.xlf:be_users.tx_aloha_enable,tx_aloha_enable',
        'after:edit_RTE'
);

?>