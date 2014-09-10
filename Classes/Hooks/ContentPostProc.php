<?php

class Tx_Aloha_Hooks_ContentPostProc {
	const ll = 'LLL:EXT:aloha/Resources/Private/Language/locallang.xml:';

	/**
	 * @var tslib_fe
	 */
	protected $tslib_fe = NULL;

	/**
	 * "Plugin" settings
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Extension configuration
	 *
	 * @var array
	 */
	protected $configuration = array();

	public function __construct() {
		$this->configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['aloha']);
	}

	/**
	 * Hook to change page output to add the topbar
	 *
	 * @param array $params
	 * @param tslib_fe $parentObject
	 * @return void
	 */
	public function main(array $params, tslib_fe $parentObject) {
		if (Tx_Aloha_Utility_Access::isEnabled() && $parentObject->type == 0) {
//		if (TRUE && isset($GLOBALS['BE_USER']) && $parentObject->type == 0) {
			$this->tslib_fe = $parentObject;

			$this->settings = $this->tslib_fe->config['config']['tx_aloha.'];

				// Load head parts
			$this->loadResources();

				// Clear staged elements
			Tx_Aloha_Utility_Integration::removeStagedElements($GLOBALS['TSFE']->id);

				// Generate output
			if (!$this->settings['topBar.']['disable']) {
				$output = ' <div id="aloha-not-loaded"></div>
						<div id="aloha-top-bar" style="display:none"><div id="aloha-topbar-inner">
								<div class="aloha-top-bar-left">' . $this->getToolbarLeft() . '</div><!-- end ToolBarLeft -->
								<div class="aloha-top-bar-right">' . $this->getToolbarRight() . '</div><!-- end ToolBarRight -->
						</div></div>';
			} else {
				$needle = '<body';
				$htmlTagStart = '<body style="margin-top:0px;"';
				$pos = strpos($parentObject->content,$needle);
			    $parentObject->content = substr_replace($parentObject->content,$htmlTagStart,$pos,strlen($needle));
			}

			if (is_array($GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Aloha/Integration.php']['toolbarPostProcess'])) {
				$finished = FALSE;
				foreach ($GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Aloha/Integration.php']['toolbarPostProcess'] as $classData) {
					$hookObject = t3lib_div::getUserObj($classData);
					if (!($hookObject instanceof Tx_Aloha_Interfaces_ToolbarPostProcess)) {
						throw new UnexpectedValueException(
							$classData . ' must implement interface Tx_Aloha_Interfaces_ToolbarPostProcess',
							1274563549
						);
					}
					$request = $hookObject->postProcess($output, $this);
				}
			}

			$parentObject->content = str_ireplace('</body>', $output . '</body>', $parentObject->content);
		}
	}

	/**
	 * Load needed js & css files before the end of the body tag
	 *
	 * @return void
	 */
	private function loadResources() {
			// Needed variables for ajax requests
		$styles = '<script type="text/javascript">' . LF .
			TAB . 'var alohaUrl = "' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . 'index.php?id=' . $this->tslib_fe->id . '&type=661' . '";' . LF .
			TAB . 'var typo3BackendUrl = "' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . TYPO3_mainDir . '";' . LF .
			'</script>' . LF;

			// Load template from given path (set in EM settings)
		$configurationArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['aloha']);
		if (is_array($configurationArray) && !empty($configurationArray['headerTemplate'])) {
			$styles .= t3lib_div::getUrl($configurationArray['headerTemplate']);
		}

		$styles .= '
			<script type="text/javascript">
				(function( p, undefined ) {
				    (function( v, undefined ) {
						v.styleDesktop = "' . ($this->settings['responsiveView.']['buttons.']['desktop.']['minWidth'] ? 'min-width:'.$this->settings['responsiveView.']['buttons.']['desktop.']['minWidth'].';' : '' ) . ($this->settings['responsiveView.']['buttons.']['desktop.']['maxWidth'] ? 'max-width:'.$this->settings['responsiveView.']['buttons.']['desktop.']['maxWidth'].';' : '' ) .'";
						v.styleLaptop = "' . ($this->settings['responsiveView.']['buttons.']['laptop.']['minWidth'] ? 'min-width:'.$this->settings['responsiveView.']['buttons.']['laptop.']['minWidth'].';' : '' ) . ($this->settings['responsiveView.']['buttons.']['laptop.']['maxWidth'] ? 'max-width:'.$this->settings['responsiveView.']['buttons.']['laptop.']['maxWidth'].';' : '' ) .'";
						v.styleTablet = "' . ($this->settings['responsiveView.']['buttons.']['tablet.']['minWidth'] ? 'min-width:'.$this->settings['responsiveView.']['buttons.']['tablet.']['minWidth'].';' : '' ) . ($this->settings['responsiveView.']['buttons.']['tablet.']['maxWidth'] ? 'max-width:'.$this->settings['responsiveView.']['buttons.']['tablet.']['maxWidth'].';' : '' ) .'";
						v.styleMobile = "' . ($this->settings['responsiveView.']['buttons.']['mobile.']['minWidth'] ? 'min-width:'.$this->settings['responsiveView.']['buttons.']['mobile.']['minWidth'].';' : '' ) . ($this->settings['responsiveView.']['buttons.']['mobile.']['maxWidth'] ? 'max-width:'.$this->settings['responsiveView.']['buttons.']['mobile.']['maxWidth'].';' : '' ) .'";
					}( p.viewpage = p.viewpage || {} ));
				}( window.pxa = window.pxa || {} ));
			</script>
			';
		$styles .= '
			<script type="text/javascript" src="/typo3conf/ext/aloha/Resources/Public/js/viewpage.js"></script>';

			// Wrap it all in a comment for infos when looking at sources
			// #aloha-resources is used in pxa.aloha.resizeViewFrame so it doesn't remove the resources when changing preview resolutions
		$styles = LF . '<!-- Begin Aloha Files --><div id="aloha-resources">' . LF . $styles . LF . '</div><!-- End Aloha Files -->' . LF . LF;

		$this->tslib_fe->content = str_ireplace('</body>', $styles . '</body>', $this->tslib_fe->content);
	}

	/**
	 * Create content for left side of the bar
	 *
	 * @return string
	 */
	public function getToolbarLeft() {
		$countOfElements = Tx_Aloha_Utility_Integration::getCountOfUnsavedElements($GLOBALS['TSFE']->id);

		$countMessage = sprintf(
							$this->sL(self::ll . 'headerbar.count'),
							'<span id="count" class="' . ($countOfElements > 0 ? 'tobesaved' : '') . '">' . $countOfElements . '</span>'
						);

		$content = '<div id="alohaeditor-welcome" class="welcome">' . $this->getMenu() . '</div>';

		// Get responsive buttons
		$content .= $this->getResponsiveButtons();

			// Output depends on selected save method
		if ($this->configuration['saveMethod'] === 'intermediate') {
			$content .= '<span class="count-holder">' . $countMessage . '</span>
					<span class="button-holder">
						<span id="aloha-saveButton"  class="aloha-button save">' . $this->sL(self::ll . 'headerbar.save_button') . '</span>
						<span id="aloha-discardButton" class="aloha-button discard">' . $this->sL(self::ll . 'headerbar.discard_button') . '</span>
					</span>';
		} elseif (($this->configuration['saveMethod'] === 'direct') && (!$this->settings['topBar.']['warningMessage.']['disable'])) {
			$content .= '<span class="aloha-warning">' . $this->sL(self::ll . 'headerbar.saveMethod.direct', FALSE) . '</span>';

		} elseif (($this->configuration['saveMethod'] === 'none') && (!$this->settings['topBar.']['warningMessage.']['disable'])) {
			$content .= '<span class="aloha-warning">' . $this->sL(self::ll . 'headerbar.saveMethod.none', FALSE) . '</span>';
		}

		return $content;
	}

	/**
	 * Create content for right side of the bar
	 *
	 * @return string
	 */
	public function getToolbarRight() {
		$content = '';

		$perms = $GLOBALS['BE_USER']->calcPerms($GLOBALS['TSFE']->page);
		$langAllowed = $GLOBALS['BE_USER']->checkLanguageAccess($GLOBALS['TSFE']->sys_language_uid);

			//  If mod.web_list.newContentWiz.overrideWithExtension is set, use that extension's create new content wizard instead:
		$tsConfig = t3lib_BEfunc::getModTSconfig($this->pageinfo['uid'], 'mod.web_list');
		$tsConfig = $tsConfig['properties']['newContentWiz.']['overrideWithExtension'];
		$newContentWizScriptPath = t3lib_extMgm::isLoaded($tsConfig) ? (t3lib_extMgm::extRelPath($tsConfig) . 'mod1/db_new_content_el.php') : (TYPO3_mainDir . 'sysext/cms/layout/db_new_content_el.php');


		$id = $GLOBALS['TSFE']->id;

			// Edit page properties
		if (($perms & 2) && (!$this->settings['topBar.']['pageButtons.']['edit.']['disable'])) {
			$params = '&edit[pages][' . $GLOBALS['TSFE']->id . ']=edit&noView=1';
			$url = TYPO3_mainDir . 'alt_doc.php?' . $params . $this->getReturnUrl();

			$content .= '<a onclick="' . $this->lightboxUrl($url) . '" href="' . htmlspecialchars($url) . '">
						<img ' . $this->getIcon('edit_page.gif') . ' title="' . $this->sL('LLL:EXT:cms/layout/locallang.xml:editPageProperties') . '" alt="" />
					</a>';

			if (isset($this->tslib_fe->page['_PAGES_OVERLAY']) && isset($this->tslib_fe->page['_PAGES_OVERLAY_UID']) && $langAllowed) {
				$params = '&edit[pages_language_overlay][' . $this->tslib_fe->page['_PAGES_OVERLAY_UID'] . ']=edit&noView=1';
				$url = TYPO3_mainDir . 'alt_doc.php?' . $params . $this->getReturnUrl();

				$content .= '<a rel="shadowbox" href="' . htmlspecialchars($url) . '">
						<img ' . $this->getIcon('edit.gif') . '  title="' . $this->sL('LLL:EXT:cms/layout/locallang.xml:editPageProperties') . '- overlay' . '" alt="" /></a>

					</a>';
			}
		}

			// @todo: add some permissions for that
		if (TRUE && (!$this->settings['topBar.']['pageButtons.']['history.']['disable'])) {
				// Record history
			$url = TYPO3_mainDir . 'show_rechis.php?element=' . rawurlencode('pages:' . $id) . $this->getReturnUrl();

			$content .= '<a onclick="' . $this->lightboxUrl($url) . '" href="' . htmlspecialchars($url) . '">
					<img ' . $this->getIcon('history2.gif') .
					' title="' . $this->sL('LLL:EXT:cms/layout/locallang.xml:recordHistory') . '" alt="" /></a>';
		}

			// New record
		if (($perms & 16) && $langAllowed && (!$this->settings['topBar.']['pageButtons.']['newContentElement.']['disable'])) {
			$params = '';
			if ($GLOBALS['TSFE']->sys_language_uid) {
				$params = '&sys_language_uid=' . $GLOBALS['TSFE']->sys_language_uid;
			}
			$url = $newContentWizScriptPath . '?id=' . $id . $params . $this->getReturnUrl();

			$content .= '<a onclick="' . $this->lightboxUrl($url) . '" href="' . htmlspecialchars($url) . '">
					<img ' . $this->getIcon('new_record.gif') . '  title="' . $this->sL('LLL:EXT:cms/layout/locallang.xml:newContentElement') . '" alt="" /></a>';
		}

			// Move
		if (($perms & 2) && (!$this->settings['topBar.']['pageButtons.']['move.']['disable'])) {
			$url = TYPO3_mainDir . 'move_el.php?table=pages&uid=' . $GLOBALS['TSFE']->id . $this->getReturnUrl();

			$content .= '<a onclick="' . $this->lightboxUrl($url) . '" href="' . htmlspecialchars($url) . '">
					<img ' . $this->getIcon('move_page.gif') . ' title="' . $this->sL('LLL:EXT:cms/layout/locallang.xml:move_page') . '" alt="" /></a>';
		}

			// New Page
		if (($perms & 8) && (!$this->settings['topBar.']['pageButtons.']['newPage.']['disable'])) {
			$url = TYPO3_mainDir . 'db_new.php?id=' . $id . '&pagesOnly=1' . $this->getReturnUrl();
			$content .= '<a onclick="' . $this->lightboxUrl($url) . '" href="' . htmlspecialchars($url) . '">
					<img ' . $this->getIcon('new_page.gif') . ' title="' . $this->sL('LLL:EXT:cms/layout/locallang.xml:newPage') . '" alt="" /></a>';
		}

			// Wrap title and classes around
		if (!empty($content)) {
			$content = '<span class="page-edit-header">' .
							$this->sL('LLL:EXT:lang/locallang_tca.php:pages') .
						':</span>
						<span class="page-edit-buttons">' . $content . '</span>';
		}

		return $content;
	}

	/**
	 * Get responsive control buttons
	 *
	 * @return string
	 */
	public function getResponsiveButtons() {
		$responsiveItemsControlArray = array('desktop','laptop','tablet','mobile');

		$output = '';

		foreach ($responsiveItemsControlArray as $key => $button) {
			if (!$this->settings['responsiveView.']['buttons.'][$button.'.']['disable']) {
				$classOfButton = $button == 'mobile' ? 'mobile-phone' : $button;
				$output .= '
					<a title="'.ucfirst($button).' view" onclick="pxa.aloha.resizeViewFrame(\''.$button.'\'); return false;" href="#">
						<i class="alohaicon-'.$classOfButton.' alohaicon-large"></i>
					</a>';
			}
		}

		if (!empty($output)) {
			$output = '<div class="aloha-viewpage-controls">' . $output . '</div>';
		}

		return $output;
	}

	/**
	 * 
	 * @return string
	 * @todo make items configurable by TsConfig
	 */

	public function getMenu() {
		$content = '';

		//Logout
		$logoutUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . TYPO3_mainDir . 'logout.php?redirect=' . rawurlencode(t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'));
		$content .= '<a href="' . htmlspecialchars($logoutUrl) . '" class="btn btn-danger"><i class="alohaicon-power-off"></i></a>';

		//Open Backend
		$backendUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . TYPO3_mainDir;
		$content .= '<a target="_top" href="' . htmlspecialchars($backendUrl) . '" class="btn btn-success">' . $this->sL('LLL:EXT:lang/locallang_login.xml:interface.backend') . '</a>';

		if(!empty($content)) {
			$content = '<div class="aloha-menu-wrap">' . $content . '</div>';
		}

		return $content;
	}

	/**
	 * Generate JS url
	 *
	 * @param string $url
	 * @return string
	 */
	public function jsUrl($url) {
		$url = "var vHWin = window.open('$url','FEquickEditWindow', 'width=690,height=500,status=0,menubar=0,scrollbars=1,resizable=1');vHWin.focus();";

		return $url;
	}

	/**
	 * Get the icon path
	 *
	 * @param string $icon
	 * @return string
	 * @todo do it correctly
	 */
	public function getIcon($icon) {
		return t3lib_iconWorks::skinImg('/' . TYPO3_mainDir, 'sysext/t3skin/icons/gfx/' . $icon, 'width="16" height="16"');
		return t3lib_iconWorks::skinImg('/' . TYPO3_mainDir, 'gfx/' . $icon, 'width="11" height="12"');
	}

	/**
	 * Short hand syntax for $GLOBALS['LANG']->sL() with hsc() by default
	 *
	 * @param string $key path to translation
	 * @param boolean $hsc htmlspecialchars by default
	 */
	public function sL($key, $hsc = TRUE) {
			// it can happen that this is null, no wonder why
		if (is_null($GLOBALS['LANG'])) {
			$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
			$GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
		}
		return $GLOBALS['LANG']->sL($key, $hsc);
	}

	/**
	 * Lightbox url
	 *
	 * @param string $url
	 * @return string
	 */
	public function lightboxUrl($url) {
		return 'Shadowbox.open({ content: \'' . htmlspecialchars($url) .'\', player:\'iframe\' }); return false;';
	}

	/**
	 * Get return url with custom close.html
	 *
	 * @return string
	 */
	public function getReturnUrl() {
		return '&returnUrl=' . TYPO3_mainDir . '../../typo3conf/ext/aloha/Resources/Public/Contrib/shadowbox/close.html';
	}

}

?>