Swipe Checkout for Magento

Version:	1.7.0 / 6 May 2014
Copyright:	(c) 2012-2014, Optimizer Ltd.
Link:		http://www.swipehq.com/
			http://www.magentocommerce.com/magento-connect/catalog/product/view/id/15121/


REQUIREMENTS
---

* Swipe account
* Magento


INSTALLATION
---

1. Please install this extension through the normal Magento installation process (System -> Magento Connect -> Magento Connect Manager, then
	Install New Extensions, grabbing the key from the plugin page linked above or Direct Package File Upload, selecting this zip)
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
	see Settings -> API Credentials for a list of currencies your Merchant Account supports. And see System -> Manage Currency -> Symbols
	to see which currency your Magento is using.
* Magento allows this plugin to set the order state to only a limited subset of all order states. You must mark orders as complete manually once you ship
	them.


CHANGE LOG
---

1.3.0:
- first version with this readme
- fixing bug where magento does not allow you to set an order's state to complete
- tested against Magento-1.7.0.2

1.4.0:
- Added test configuration button, fixed bug in package.xml file and added the missing file Permittedorderstates.php in Model directory

1.5.0:
- Fixing bug, changed php short tag to long tag

1.6.0:
- Fixing issue with check config for installs with .htaccess enabled which deny direct access to magento files

1.7.0:
- Bug fixes for updating order status