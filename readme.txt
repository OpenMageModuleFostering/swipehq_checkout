Hikashop Swipe plugin

Version:	1.3.0 / 18 Sep 2013
Copyright:	(c) 2012-2013, Optimizer Ltd.
Link:		http://www.swipehq.com/checkout/



REQUIREMENTS
---

* Swipe account
* Magento


INSTALLATION
---

1. Please install this extension through the normal Magento installation process (System -> Magento Connect -> Magento Connect Manager, then
	Install New Extensions, grabbing the key from http://www.magentocommerce.com/magento-connect/catalog/product/view/id/15121/ or Direct 
	Package File Upload, selecting this zip)
2. After successful installation it will appear in the list of Packages as "SwipeHQ_Checkout"
3. Then configure the plugin, go back to the Magento Admin, then go to System -> Configuration -> Sales -> Payment Methods -> Swipe Checkout, 
	adding the following details from your Swipe Merchant login under Settings -> API Credentials:
		Merchant ID
		API Key
		Payment Page Url
		Api Url
4. And finally configure your Swipe account to send customers back to your shop after they pay. 
	In your Merchant login under Settings -> Payment Notifiers, set:
   		Callback Url:  					%YOUR_WEBSITE%/swipehq/payment/response
   		Callback pass back user data: 	on
   		LPN Url: 						%YOUR_WEBSITE%/swipehq/payment/response
	making sure to replace %YOUR_WEBSITE% with your website url, e.g. www.example.com/my-shop/swipehq/payment/response
5. All done, test it out, add some products to your cart and you will get the option to pay with Swipe.


NOTES
---
* Magento must be configured to use a currency that your Swipe Merchant Account supports for customers to be able to use Swipe to be a payment option,
	see Settings -> API Credentials for a list of currencies your Merchant Account supports. And see System -> Manage Currency -> Symboles
	to see which currency your Magento is using.
* Magento allows this plugin to set the order state to only a limited subset of all order states. You must mark orders as complete manually once you ship
	them.


CHANGE LOG
---

1.3.0:
- first version with this readme
- fixing bug where magento does not allow you to set an order's state to complete
- tested against Magento-1.7.0.2


