<?php
class SwipeHQ_Checkout_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	protected $_code = 'swipehq';
	
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = false;
	
	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('swipehq/payment/redirect', array('_secure' => true));
	}
}
?>