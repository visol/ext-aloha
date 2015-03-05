<?php
namespace Pixelant\Aloha\UserFunc;

class Aloha {

	/**
	 * Check if Aloha Editor should be enabled
	 *
	 * @return bool
	 */
	public static function isAlohaEnabledForUser() {
		return ($GLOBALS['BE_USER']->uc['tx_aloha_enable'] === 1);
	}

}

?>