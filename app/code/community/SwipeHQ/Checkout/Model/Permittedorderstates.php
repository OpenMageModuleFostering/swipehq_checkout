<?php
class SwipeHQ_Checkout_Model_Permittedorderstates {
	
  public function toOptionArray(){
  	$orig = new Mage_Adminhtml_Model_System_Config_Source_Order_Status();
  	$origOptions = $orig->toOptionArray();
  	
  	$order = new Mage_Sales_Model_Order();
  	
  	$r = array();
  	foreach($origOptions as $opt){
  		$orderState = $opt['value'];
  		if(!$orderState) continue; // omit blank option, we require one to be selected
  		if($order->isStateProtected($orderState)) continue; // omit options that will cause errors when we try to set the order state
  		$r[] = $opt;
  	}
  	return $r;
  }
  
}