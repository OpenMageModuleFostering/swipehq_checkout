<?php
/**
 * Swipe Checkout Controller
 * 
 * @copyright (c) 2012-2013, OptimizerHQ Ltd.
 * @link http://www.swipehq.com/checkout/
*/

class SwipeHQ_Checkout_PaymentController extends Mage_Core_Controller_Front_Action {
    
	// The redirect action is triggered when someone places an order
	public function redirectAction() {
		
			$api_url = trim(Mage::getStoreConfig('payment/swipehq/api_url'), '/');
            
            //get order data
            $_order = new Mage_Sales_Model_Order();
            $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            $_order->loadByIncrementId($orderId);

            $orderData = $_order->getData();
            $currency = !empty($orderData['order_currency_code']) ? $orderData['order_currency_code'] : false;
            
            $acceptedCurrencies = $this->_getAcceptedCurrencies();
            
            if (in_array($currency, $acceptedCurrencies)) {
                $orderTotal = $orderData['grand_total'];

                //get list of ordered pruducts
                $products = '';
                $items = $_order->getAllItems();
                foreach ($items as $item) {
                    $qty = $item->getQtyOrdered();
                    $name = $item->getProduct()->getName();
                    $products .= $qty . ' x ' . $name . ($item != end($items) ? '<br />' : '');
                }

                //get identifier ID using TransactionIdentifier API
                $params = array (
                    'merchant_id'           => Mage::getStoreConfig('payment/swipehq/merchant_id'),
                    'api_key'               => Mage::getStoreConfig('payment/swipehq/api_key'),
                    'td_item'               => 'Order ID ' . $orderId,
                    'td_description'        => $products,
                    'td_amount'             => $orderTotal,
                    'td_currency'           => $currency,
                    'td_default_quantity'   => 1,
                    'td_user_data'          => $orderId
                );

                $response = $this->_sendRequest($api_url.'/createTransactionIdentifier.php', $params);
                $response_data = json_decode($response); 

                if ($response_data->response_code == 200 && !empty($response_data->data->identifier)) {
                    $session = Mage::getSingleton('checkout/session');
                    $session->swipehq_identifier_id = $response_data->data->identifier;
                }
            } else {
            	Mage::getSingleton('checkout/session')->addError('Swipe does not support currency: '.$currency.'. Swipe supports these currencies: '.join(', ', $acceptedCurrencies).'.');
                return;
            }
            
            $this->loadLayout();
            $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','swipehq',array('template' => 'swipehq/redirect.phtml'));
            $this->getLayout()->getBlock('content')->append($block);
            $this->renderLayout();
	}
	
	// The response action is triggered when your gateway sends back a response after processing the customer's payment
	public function responseAction() {
            $request = $_REQUEST;
            
            $transaction_id = $request['transaction_id'];
            $testMode = Mage::getStoreConfig('payment/swipehq/test_mode');
            $order = Mage::getModel('sales/order');
            
            //Swipe callback
            if (isset($request['td_user_data'])) {
                $order_id = $request['td_user_data'];

                $is_verified = $this->_verifyTransaction($transaction_id);
                if ($is_verified) {
                    // Payment was successful, so update the order's state, send order email and move to the success page
                    
                    $order->loadByIncrementId($order_id);
                    if (!$order->getId()) {
                        Mage::throwException('No order for processing found');
                    }
                    
                    $order_status = Mage::getStoreConfig('payment/swipehq/paid_order_status');
                    if ($testMode) {
                        $order_status = Mage::getStoreConfig('payment/swipehq/test_order_status');
                    }

                    if($order->isStateProtected($order_status)){
                    	Mage::throwException('Swipe configuration error: "Paid Order Status" must not be: "'.$order_status.'". Failed to change state.');
                    }else{
                    	$order->setState($order_status, true, 'Swipe has authorized the payment.');
                    }

                    $order->sendNewOrderEmail();
                    $order->setEmailSent(true);

                    $order->save();

                    Mage::getSingleton('checkout/session')->unsQuoteId();
                } else {
                    // There is a problem in the response we got
                    $this->cancelAction();
                }
                die('LPN OK, transaction_id: '.$transaction_id.', is_verified: '.($is_verified?'y':'n'));
                
            //Result page (user redirect)
            } elseif (isset($request['result']) && isset($request['user_data'])) { 
                if ($request['result'] == 'accepted' || $request['result'] == 'test-accepted') {
                    Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=>true)); 
                } elseif ($request['result'] == 'declined' || $request['result'] == 'test-declined') {
                    Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=>true));
                } else {
                    Mage_Core_Controller_Varien_Action::_redirect('');
                }
            }
	}
	
	// The cancel action is triggered when an order is to be cancelled
	public function cancelAction() {
            if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
                $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
                if($order->getId()) {
                    // Flag the order as 'cancelled' and save it
                    $order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.')->save();
                }
            }
	}
	
	public function testConfigAction(){
		include __DIR__ . '/../etc/test-plugin.php';
	}
	
        /**
         * Transaction verification
         * 
         * @param string $transaction_id
         * @return boolean
         */
        private function _verifyTransaction($transaction_id) {
        	$api_url = trim(Mage::getStoreConfig('payment/swipehq/api_url'), '/');

            //parameters for transaction verification
            $data = array (
                'merchant_id'       => Mage::getStoreConfig('payment/swipehq/merchant_id'),
                'api_key'           => Mage::getStoreConfig('payment/swipehq/api_key'),
                'transaction_id'    => $transaction_id
            );

            $response = $this->_sendRequest($api_url.'/verifyTransaction.php', $data);

            if (!empty($response)) {
                $response_data = json_decode($response);

                if ($response_data->response_code == 200 && 
                	$response_data->data->transaction_id == $transaction_id && 
                	$response_data->data->transaction_approved == 'yes') {
                    	return true;
                }
            }
            return false;
        }
        
        protected function _getAcceptedCurrencies(){
        	$api_url = trim(Mage::getStoreConfig('payment/swipehq/api_url'), '/');
        	
        	$params = array(
	        	'merchant_id' 	=> Mage::getStoreConfig('payment/swipehq/merchant_id'),
	        	'api_key' 		=> Mage::getStoreConfig('payment/swipehq/api_key'),
        	);
        	
        	$resp = $this->_sendRequest($api_url.'/fetchCurrencyCodes.php', $params);
        	$respArr = json_decode($resp, true);
        	return $respArr['data'];
        }
        
        private function _sendRequest($url, $data) {
             $ch = curl_init ($url);
             curl_setopt ($ch, CURLOPT_POST, 1);
             curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
             curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
             curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
             curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
             $response = curl_exec ($ch);
             curl_close ($ch);
             return $response;
        }
}