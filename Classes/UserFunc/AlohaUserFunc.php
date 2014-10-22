<?php
namespace Pixelant\Aloha\UserFunc;

class AlohaUserFunc {

	/**
	 * Check if Aloha Editor should be enabled
	 * 
	 * @return bool
	 */
	public function isAlohaEnabledForUser() {
		return ( $GLOBALS['BE_USER']->uc['tx_aloha_enable'] === 1 );
	}

}
?>