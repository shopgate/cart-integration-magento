# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Fixed
- Compatibility with Magento Marketplace

## [2.9.68] - 2018-05-16
### Fixed
- Missing shipping methods in cart validation for configurable products
- Missing shipping costs

## [2.9.67] - 2018-04-17
### Fixed
- Products being exported twice in cart validation
- Authorized Braintree orders are not directly invoiced on order import anymore
- Auth/Captured Braintree orders can now be properly captured from Magento
- Triggering the checkout observer

## [2.9.66] - 2018-03-12
### Fixed
- Invalidation of inactive products
- Invoice amount for Braintree orders in case of extended payment review
### Added
- Firing the sales order place events when importing an order
- Compatibility to Payone Plugin version 4

## [2.9.65] - 2018-29-01
### Fixed
- Now dispatching missing quote submit events on placing Payone orders
### Changed
- Updated shopgate_orders table to use InnoDB as engine
### Added
- Set SameAsBilling flag in quote shipping address

## [2.9.64] - 2017-11-22
### Added
- Cloud API controller for /shopgate/v2/ access
### Changed
- Moved the library to sub-folder inside lib/Shopgate in preparation for future libraries
### Fixed
- prevent redirect, if the module is disabled for the active storeview
- issue with modman not deploying files to the right places

## 2.9.63
### Added
- payment mapping for Braintree module
### Changed
- migrated Shopgate integration for Magento to GitHub
### Fixed
- tier prices in item export, in case that the price index is not used

## 2.9.62
- Fixed error in cart validation that caused oversellings
- Fixed item quantities in synchronization of partial order cancellations

## 2.9.61
- No longer creates a free shipping coupon on mobile website if not all shipping options are free

## 2.9.60
- Added reference ID for Payolution Invoices
- Added an attribute group to the Shopgate Coupon Attribute Set
- Added partial support for QualityUnit's Post Affiliate Pro plugin
- Added a configuration dropdown to include cart rule labels in check_cart response
- Cleaned up German translations

## 2.9.59
- Full refactor of redirect script
- Now we are able to differentiate between HTTP & JS redirects properly
- Fixed 1 Cent rounding issue in case use Shopgate prices is active
- Added missing getter for review XML filename in the configuration
- Added PHP-Version checkup to avoid errors in the stack trace default generator
- Implemented Shopgate cache to store the redirect script code, needs to be enabled
- Fixed issues with mage < 1.4.2.0 redirect script print
- Implemented clearing of SG Cache on SG configuration save
- Added support for Magestore Affiliate Coupons & Program Coupons
- Improved error reporting for item based errors

## 2.9.58
- Removed deprecated Plugin Action redeem_coupons
- Fixed usage of advanced error handler
- Uses shopgate library 2.9.63

## 2.9.57
- Fixed typos in error messages
- Fixed the bundled qty option calculation when the price is dynamic
- Fixed check_cart plugin shipping

## 2.9.56
- Refactoring of shipping mapping
- First-Class Mail Parcel USPS method is processed properly now
- Fix for PayOne configuration mapping based on store
- Remove currently not supported input validation

## 2.9.55
- Refactor of product export and product collection count
- Added amount authorized in payment table for PayPal orders

## 2.9.54
- Fixed issue with check_cart plugin shipping tax export
- Fixed redeem_coupon call error with a few payment methods
- Added support for mapping data to Itabs_Debit module
- Fixed a bug with tier/group price calculations
- Added client based cart rule settings
- Fixed missing PayPal order payment data, namely "amount_ordered"
- Fixed issues with magento compilation & front page errors
- Fixed tax issue when using shopgate prices in order import
- Implemented exclusion of specific products from the item export
- Uses Shopgate Library 2.9.59
- Fixed order status in order export
- Fixed issues with affiliate routing php5.2 compatibility

## 2.9.53
- Fixed tax issue with plugin shipping
- Fixed simple item import settings & get_orders parent item lookup
- Fixed Sofort v2.1.1 order status bug
- Fixed a bug in group/tier price export calculations
- Fixed Sys > Configuration > Shopgate page links that point to Shopgate docs
- Fixed issues with affiliate routing & php5.2 compatibility
- Added support for Magestore Affiliate Plus versions below v0.4.3
- Improved order import for Magento versions lower than v1.6
- Updated Shopgate Library to v2.9.58

## 2.9.52
- Fixed get_orders product id export for configurable products
- Fixed cart rule validation for guests
- Fixed issue with updateOrder call
- Now skips for faulty product attributes on item export
- Fixed discount calculation in order import
- Added support to older versions of BillSAFE
- Added support for CMS redirect mapping for magento 1.4
- Added support to extra payment fee in Phoenix COD v0.4.8+
- Fixed issue with missing tier prices

## 2.9.51
- Added CMS page mapping configuration
-  Adjusted redirect to home page even if landing page is a product page

## 2.9.50
- Fixed 500 error when Magestore Affiliate version is below 4.1
- Adjustments to PayPal Plus transaction ID print
- Fix for redeem_coupons API call throwing an exception
- Authorized orders that were declined in Authorize are cancelled in mage
- Improved coupon validation
- Improved mapping of shipping methods in order import
- Added xml feed generator to tag creation

## 2.9.49
- Placing orders with removed customers as guest orders
- Fixed detectionof cart rules which depend on the payment method
- Added missing parameters to PayPal WSPP/Express payment object
- Move getting of attribute values in it's own function
- Improved payment mapping for sofort ueberweisung
- Fixed bug with missing prefix in customer registration

## 2.9.48
- Fixed tax issue with coupons
- Added KProject ShareASale to supported affiliate plugins
- Cleaned up admin notification feed code
- Added transaction ids to PayPal & Sofort imports
- Cleaning up warnings on product tier/group price export
- Fixed positioning of product sort order in categories

## 2.9.47
- fixed possible missing gross prices for shipping in cart validation
- Improved order import performance

## 2.9.46
- fixed product and category redirect

## 2.9.45
- Added support for cart price rule blocking for older magento versions
- Removed references of external order id in PayPal orders
- Fixed issue missing shipping costs in order import
- Fixed missing payment method in checkCart request
- Fixed order total amount when shopgate coupons are used
- Fixed tax issue when using shopgate prices in order import

## 2.9.44
- fixed shipping costs in case of taxes being based on the origin
- Fixed character \n appearing in customer address

## 2.9.43
- Fix for AuthNet pending review orders being stuck in 'processing' after ship
- mapped Merchant API PayPal WSPP to PayFlow Pro mage module
- Fixed bug with custom option validation errors in check_cart requests
- added free shipping capability for shopgate coupons

## 2.9.42
- fixed redirect of cms pages
- Removed bot indexing of mobile shop for stores using mobile app only

## 2.9.41
- Improved error printing for configurable products in checkCart
- Removed coupon title from displaying in shopgate mobile site
- added support for catalog price rule customer group pricing for simple & config. products
- Removed coupon descriptions from appearing on mobile site
- Fixed collector total exception for quotes without shopgate object

## 2.9.40
- Fixed mage cart page exception for collector calls without a Shopgate object in session

## 2.9.39
- Updated to Library Version 2.9.44
- Orders are no longer on hold if a Prepay order is mapped to an enabled plugin
- Added an info block to View Order page to output Shopgate related warnings and general info
- Added Shopgate extra payment fee to order totals calculation inside Order_View/Invoice/Credit_Memo sections
- Made an adjustment for USAePay by providing the LastTransactionId on auth_capture only
- Optimized category image export by removing empty image url tags

## 2.9.38
- Fixed placing of configurable products in order import
- Prevent unnecessary error logging in stock validation

## 2.9.37
- Magestore Affiliate Pro discount parameter support added
- Fixed get_categories by UID retriever
- Fixed configurable child product weight calculation
- Fixes for order 'shipping_method' mapping in the database
- Orders will print even if shopgate data is not present
- Fix for catalog price rules not honoring multi-store specific rules
- Fixed display error for big orders
- Fixed tax_percent calculation for shipping methods
- Fixed issue with virtual item import + custom options
- Fixed error in stock validation for items with backorders
- Fixed API order shipping updates in Authorize & Payoli orders
- Fixed BillSAFE order import no response

## 2.9.36
- Update to library Version 2.9.42
- Added support of shopping cart price rules for mobile orders

## 2.9.35
- Fixed PayPal Website Payments Pro missing IPN data & no invoice
- Fixed bug with getting BillSAFE orders to ship
- BillSAFE orders no longer use Shopgate's order number as increment ID
- Fixed shipping error when an adaption sub-module is overwriting the observer

## 2.9.34
- remove mage_usa module dependency
- improved payment mapping for authorize.net
- improved export of tier prices for bundle products

## 2.9.33
- improved detection of redirect exclusions
- Redirect script cleanup
- Improved error handling for the mobile redirect
- Mobile script maintenance

## 2.9.32
- Shopgate dropdown is now visible with proper permissions set
- Shopgate menu moved under the Systems dropdown
- Fixed issue with PayOne Shopgate order not passed as array
- Fixed partial shipping refunds
- Fixed a bug with oAuth store connection & URI's with SID
- Fixed amount and itemNumber in checkCart
- Fixed bug in magento order detail page

## 2.9.31
- improve discount detection
- update to library version 2.9.41
- set amount for external coupons depending on net countries
- fixed check_stock for products with custom options
- allow set_settings with value 0, false and empty string
- attributes with 0 string and integers allowed into export

## 2.9.30
- Added PHP 7 support
- Update to Shopgate library Version 2.9.40
- Fixed return of invalid JSON on get_items
- Made layout handle unique to avoid conflicts with 3rd party modules

## 2.9.29
- Fixed wrong shipping tax in case that tax class is 'none'
- Added support for php5.2 on mobileRedirect
- Fix for Payone initialization exception
- Bugfix for rounding MRSP in product export
- Added a helpful error log entry for empty product XML exports

## 2.9.28
- Fixed error in get_settings, when setting values from the shopgate plugin config
- Fixed value in default payment configuration

## 2.9.27
- Cleaned up tax export/coupon logic
- Fixed a bug with custom option percent type calculations
- Improved recognition of active payment methods in get_settings
- Custom fields are only added to the order comments in case they are not empty
- Skip baseprice if DerModPro_BasePrice throws an error
- Deactivating export of variant prices now includes tierprices
- Added support for payment model overwriting via config.xml
- Fixed bug on updating from plugin Version 2.8.0
- Support html_tags for App Linking on the Desktop Website
- Set_settings: solved issue with saving bools to config
- Fixed bug in bundled product price option export
- Add_order: fixed problem with credit card payment (PayPal Website Payments Pro)
- Improved mobile redirect class to allow better rewrites/adaptions

## 2.9.26
- Store deletion will not cause shopgate plugin to redirect to 404 page
- Exclude unsupported input types from item export
- Added support to Creativestyle_AmazonPayments in Mage v1.4.0.0+
- Fix custom description attribute codes
- Shopgate payment orders import with status Processing when shipping is not blocked
- Shopgate payment orders import with Shopgate configuration status when shipping is blocked

## 2.9.25
- map payment method Paypal Plus
- fix multiple payment and shipping execution
- adding credit card expiration date for authorize.net payment method
- Added configuration, whether the magento or shopgate product prices should be used during addOrder
- fix custom description attribute codes

## 2.9.24
- fix Quote order problems
- fixed payment fees are now possible for cash on delivery
- set shipping amount net or gross depending on the shop settings
- fixed authorize_only Auth.NET orders getting stuck "In Review", now correctly set to be in "processing"
- fixed order import bug for PHP versions 5.2 and below

## 2.9.23
- export images for sub products of grouped products
- Bug fix: PayOne (v330+) order's will now be processed by PayOne module
- Feature: PayOne paid orders will have an invoice created automatically
- Feature: PayOne PayPal orders will have an invoice independent of paid status
- Use amount_net to set shipping costs in add_order
- Library update to 2.9.31
- item export: skip inactive child products
- use_stock was always false for parent items, is now set correctly
- Added support to BillSAFE for magento Version 1.4.0.0
- New Shopgate payment orders now have the correct state in Processing status
- Now prints customer custom fields in order history
- Now saves customer custom fields if customer attribute exists in magento
- Fixed compatibility issue in get_settings with magento 1.4.x
- changed identification of shopgate coupons
- added custom fields on get_orders

## 2.9.22
- Totals collector Invalid Block Type exception fix
- Added redirect for quick search query links to mobile site
- Fixed compatibility issue in get_settings with magento 1.4.x
- Translated magento order status to shopgate order status
- New Shopgate payment orders now have the correct state in Processing status
- Added htpassw user & pass data in oAuth calls
- Now prints customer custom fields in order history
- Now saves customer custom fields if customer attribute exists in magento
- added custom fields on get_orders
- check_cart now returns the maximum quantity buyable if requested quantity is bigger than stock quantity

## 2.9.21
- Fixed compatibility problem with Magento 1.4.0
- fixed plugin not active error during oAuth registration
- Totals collector Invalid Block Type exception fix
- Fixed problem with payment mapping for paypal_express
- implement sort order for bundle products
- Surcharges for child products will now take care of catalog price rueles
- Payone_Core new order email bug fix
- Group product associated children are sorted by position number
- Fixed missing payment methods in check_cart response

## 2.9.20
- Added a quote to getpaymentmethods
- Import of order /w coupon bug fixed
- Ignore faulty shipping methods
- Improve/simplify shopgate detection logic
- Fixed bug that caused taxes for shipping costs being collected twice
- Fixed stock calculation for group products
- Added support to Payolution Payment method
- Fixed problem with wrong tax amount on desktop site

## 2.9.19
- Fixed invoicing bug when display_errors is enabled
- COD mapping added for mage native payment
- Updated Shopgate Coupon creation logic
- PayPal Standard optimizations
- Fixed missing special prices based on catalog price rules for child products
- Added payment model mapping to classes
- Added Prepay fallback to use CheckMoneyOrder in mage v1.4-1.7
- Fixed bug with printing Mobile Payment when empty array is passed
- Changed PHP dependency from 5.2.0 to 5.3.0
- Optimized error handling when creating shipments

## 2.9.18
- Overhaul of payment & status mapping in add_order calls
- Added debug logs to vital functions of order/status mapping
- Adjusted order status/state translation for magento v.1.4.1.1+
- Implemented ParadoxLabs_AuthorizeNetCim mapping /w fallback to AuthorizeNET
- Cleaned up Shopgate/mobilePayment variable printing
- Added support for Payone_Core for online capture & online refund
- Payone_Core implementation tested on v3.2.0 - 3.3.3 & mage 1.4.1.1+
- Added exception throw when add_cart/check_cart prod ID is not in the system
- Fixed problems with establishing connections via oAuth
- Fixed issue with Payone and listing of get_settings payment methods
- Update to shopgate library Version 2.9.21

## 2.9.17
- solved issue with free products
- get_settings was extended to export payment_methods

## 2.9.16
- Fixed bug with export tax classes
- implement own payment fee
- optimize OAuth connection
- Update to library Version 2.9.19

## 2.9.15
- Added payment mapping support to v1.1.8+ for Paymentnetwork_Pnsofortueberweisung plugin, tested on magento 1.4.1.1+
- Added support to Mage_BankPayment (lower versions of Phoenix_BankPayment)
- Removed invoice creation from auth_only Authorize.net orders
- Fixed issue with cancellations, refund will not ba captured as full cancellation anymore
- Fixed issue with missing/wrong product weights in shipping rate calculation process
- Fixed issue with wrong order states for Magento Banktransfer payment method
- Added fix for wrong prices of child products in case the price was modified after the child product was created

## 2.9.14
- Fixes problems in price calculation for child products
- Fixes problems bundle options
- Fixes problems whit the magento compiler
- Fixes problems with the status from bilsafe orders

## 2.9.13
- Fixes problems with wrong order status in combination with payment method sofortueberweisung
- Fixes wrong tax classes on child products during product export
- Improves export of custom options
- Improves mapping of order status/state during addOrder, orders placed with prepayment will now also take care of the "mark unblocked orders as paid" configuration
- Upsell products of parent products will now also be exported on related childproducts
- Fixes problem with differing prices of variants
- Adds new configuration option for also exporting low resolution product images
- Fixes problems with missing order status on paypal orders

## 2.9.12
- Custom options on bundle products are now supported
- Fixed problems on synchronising billsafe orders
- Fixed problems with missing shipping methods in check_cart
- Fixed problems with wrong status of cod orders after import
- Fixed problems with wrong prices of bundle products
- Fixed problems with product relation export
- Stock information of backordered products will now be exported too

## 2.9.11
- Fixed problems with blocked or unpaid orders
- Fixed problems with differing shipping costs and Paypal Express orders
- Fixed problems on create default shipping and billing addresses
- Fixed problems with wrong available text
- Fixed problems with product sorting
- Fixed problems additional cost for options
- Fixed separator for weight
- Fixed stock availability

## 2.9.10
- fixed problem when importing orders into magento
- fixed problem when magento adminnotification module is disabled

## 2.9.9
- additional configuration field for export of UPC
- fixed caching problem with header files
- additional checks for oauth connection
- improved price export for bundle products without required elements
- native paypal mapping if webiste payments pro is active to paypal express
- adjusted xml image export of child products according to config settings

## 2.9.8
- missing js for oauth and redirects
- improved native support for payment methods to 1.4.0.1
- Msp_CashOnDelivery support
- Firegento support (MageSetup and GermanSetup)
- Amasty_Rules support

## 2.9.7
- improved Bundle Options
- adjusted delivery time attribute for older versions
- fixed UPC bug
- improved native support for payment methods to 1.4.0.1
- fixed bug in warehouse management
- Phoenix_Banktransfer as native payment method
- minimized double order as error despite the lack of order
- improved child product data export
- Performance improved significantly in mobile forwarding
- support of group prices from 1.4.x to 1.6.x
- notification system  of Magento used to indicate plugin updates

## 2.9.6
- tax calculation for coupons
- fix for relations of child and parent products on import
- price calculation for additional costs on custom options fixed
- native implementation of billsafe payment
- native implementation of sofort payment
- default title for bundle product relations fixed
- fix for missing order status and total due on paypal website payments pro
- mapping for order status > 1.6
- support of virtual products

## 2.9.5
- fixed bug for catalog price rules in orders
- stock issue with parent products fixed(CSV)
- improved bundle product export, stock and price handling
- prevent export of inactive grouped product children
- fixed register_customer error message
- improved mobile redirect for special product setup
- fixed caching issue of attributes in product export
- improved export of tier prices
- native integration of Amazon and ePay improved
- support for virtual products added
- status mapping on order import improved

## 2.9.4
- stock issue with parent products fixed(XML)
- library update to 2.9.3
- fixed attribute set caching in product export
- support "DerModPro Base Price" extension added
- honor export parent name configuration for product XML export
- native Authorize.net implementation
- native epay implementation
- improved Paypal Website Payments Pro integration
- improved compatibility with Magento Version < 1.5

## 2.9.3
- added caching on export to work with magento cache
- Support for Organic Internet SimpleConfigurableProducts
- Fixed installation problems < 1.4.1.x
- Improved inventory management
- Improved compatibility with catalog price rules on order import
- Paypal Website Payments Pro integrated as native payment method
- Improved tax handling for shipping costs for tax-free countries
- refactored Mobile Redirect to work with native Magento Caching

## 2.9.2
- fixed problem with errors in add_order in combination with shopgate_coupons
- Extended payment information in magentofor PayPal orders
- fixed error product image export for XML
- removed unneeded observer
- improved compatibility to magento <= 1.4.1.0
- added support for PayPal Website Payments Pro in magento 1.4.x.x and 1.5.x.x
- fixed error with wrong prices in add_order when catalog pricerules were used

## 2.9.1
- honor thumbnail image first setting in XML export
- improve product description XML export
- compatibility to usps 3rd party extension added
- exports categories that are excluded from main menu as inactive
- memory issue with Varien_Image_Adapter_Gd2 fixed
- bugfix for missing shipping method in combination with bundle products
- fixed buyable state in check_cart when product is out of stock but backorderable
- tier prices for all groups will be exported for every customergroup separately
- new configuration for first product image
- excluded products with required custom option of type file from csv export
- add configuration to switch between prices form index and from product during item export

## 2.9.0
- bugfix in ping action for magento Version < 1.6
- performance improvement on product export
- improved real time stock check
- bugfix for wrong group product prices in XML
- bugfix for wrong child product prices in case price offset is used in parent product in CSV
- coupon value also honors option prices
- improved stock export for XML
- library update to 2.9.1
- reviews as XML

## 2.8.4
- Update to library Version 2.8.10

## 2.8.3
- extended the sort options of products in categories
- improved export of available text for bundle products
- shipping costs at shopgate now gets refunded only, when full shipping amount is refunded at Magento
- implementation of a new configuration option to force the export of selected product attributes as properties
- XML support for bundle options

## 2.8.2
- better support for 1.4.x to 1.5.x

## 2.8.1
- code style adjustments
- native integration of amazon payments
- library update to 2.8.6
- code cleanup
- additional option to write custom fields into the order status history as comment

## 2.8.0
- library update to 2.8.5
- adjusted grouped product export
- order synchronisation added (new library function get_orders)
- clean up for old code parts
- improved bundle product export
- improved exception handling
- ensure that products get loaded storeview sensitive

## 2.7.5
- if available coupon labels get transferred instead of coupon titles
- updated library to Version 2.7.4

## 2.7.4
- bugfix for fallback shipping method shopgate_fix

## 2.7.3
- new configuration to export categories included to navigation menu only
- bugfix for group products without children
- bugfix for fedex api shipping error

## 2.7.2
- Bugfix for Oauth

## 2.7.1
- bugfix for the usage of a not anymore existent class constant

## 2.7.0
- implementation of a connection-manager
- changed the authentication to oauth
- update of the Shopgate library to Version 2.7.2
- bugfix for the export of the default available text
- optimized processing of shipping methods on checkCart
- setting the customer prefix on order processing
- bugfix display name of shipping method in transactional mails
- exports category thumbnail before image
- improved export of backorderable products
- improved product export of parent<->child relations with uncommon child visibility settings

## 2.6.16
- bugfix wrong tax calculation of shipping costs
- processing of the discount tax configuration
- bugfix for the extraction of storeviewid for given shopnumber
- allow grouped products to be in more than one category
- bugfix display name of shipping method
- implemented extended output on get_settings

## 2.6.15
- update of library to Version 2.6.10
- added possibility to choose which product name should be exported (child or parent)
- bugfix for rounding issues with bundle options
- export of unavailable bundle options
- bugfix for missing attributes in child products in the xml export
- bugfix for wrong calculated catalog price rules of configurable products
- bugfix in case of child product price is ignored, ignore child products tax class too

## 2.6.14
- extended the redirect suppression for module routes
- new option to export thumbnail product images at first
- improved customer/guest order mapping
- bugfix for taxes on shipping costs being applied twice in some cases

## 2.6.13
- updated library to version 2.6.8
- extended set_settings for treatment of array's as core_config_data elements
- bugfix for the removal of shopgate-coupon products in relation to flat tables
- implemented a selector to trigger the tax processing for net markets
- bugfix for processing the variatoin surcharges of confgurable products

## 2.6.12
- bugfix for the processing of the inventory after an addOrder request

## 2.6.11
- suppression of mobile redirects is no explicit controllable through the module
- update of the library to version 2.6.7
- bugfix for synchronisation of shippings via plugin method  _cronSetShippingCompleted
- bugfix for the calculation of product prices of bundle options

## 2.6.10
- support of availableText export with attributes containing options
- bugfix for missing products in item csv export
- bugfix for double amount of cancellation items in case of configurable products
- bugfix for mobile redirecting through http header

## 2.6.9
- allows customer related shopping cart price rules
- improved compatibility of old PHP versions

## 2.6.8
- fixed error with shipping costs and delivery addresses without taxrates
- plugin api shipping methods get imported to orders with correct display name

## 2.6.7
- fixed compatibility to phoenix_cashondelivery (missing cod fee in order view)
- Bugfix XML product export (SalesPrice and UID child product)

## 2.6.6
- using attribute label store based on export
- removed thumbnail images for export of excluded gallery images
- removed redundancy for xml and csv export
- bugfix for wrong product prices in combination with catalog price rules and special prices

## 2.6.5
- detailed information for shipping in detail view and mails

## 2.6.4
- added category XML export
- modified category CSV export

## 2.6.3
- bugfix for processing Shopgate Coupons on activated msrp
- removed group product images if produkt is disabled
- update shopgate library to version 2.6.6
- bugfix in synchronization of order cancellations to shopgate
- excluded cms pages from mobile redirect on default

## 2.6.2
- added product XML export
- modified product CSV export
- update shopgate library to version 2.6.3

## 2.6.1
- update shopgate library to version 2.6.2
- export of only in search visible child products

## 2.6.0
- fixed error with orders and delivery addresses without taxrates
- update shopgate library to version 2.6.1
- allow splitted category export
- improved backorder label
- bugfix for handling of mobile redirects of storeview calls without a valid shopgate configuration

## 2.5.14
- possibility to export images which are marked as excluded in gallery
- clean up of old file structure
- possibility to export products in categories sorted by newest to oldest
- added address coupon add to ensure validation

## 2.5.13
- extended get_settings with customergroups
- extended check_cart with customergroups
- extended get_customer with customergroups
- setting of vary header for possibly redirectable pages on dependency of user-agent
- removal of an not needed class rewrite

## 2.5.12
- supporting shipping methods via plugin down to 1.4.x
- correct export of manufacturer suggested retail price

## 2.5.11
- proper displaying of payment data in order details
- code optimization to remove redundancy in price calculation

## 2.5.10
- bugfix for exporting reviews as model to support VoteRating

## 2.5.9
- bugfix for the not properly transmitting options to following products on export
- bugfix for exporting parent-child relations on products
- activating the mobile_redirect only for limited controllers on default

## 2.5.8
- Performance boost on reviews and categories
- Bugfix for tax class export und parent child relations in us module

## 2.5.7
- extended shop information on pinging the module
- update shopgate library to version 2.5.4
- bugfix for storeView filtered review export
- bugfix for exporting vote ratings on review export

## 2.5.6
- bugfix for orderimport including ShopgateCoupons
- bugfix for transmitting order cancelations to shopgate
- bugfix for transmitting shipping updates to shopgate
- improved cost calculation of shipping methods in check_cart

## 2.5.5
- remove of a performance leak on csv export
- bugfix for a semantic error

## 2.5.4
- update Shopgate Library to version 2.5.1
- extended the writeable properties through set_settings (e.g. enable_check_stock)
- inserted license header texts for php documents
- the visibility of the parent category on export of grouped products will be validated

## 2.5.3
- bugfix for wrong taxes on payment method fees
- filterable attributes that are not visible in frontend can be exported now(configurable in shop settings) and are available as filter mobile too

## 2.5.2
- availalbeText can now be mapped onto any product attribute

## 2.5.1
- attributes of parent products are also added to the simples

## 2.5.0
- update Shopgate Library to version 2.5.0
- check stock in realtime
- allow weight based table rate or api shipping methods
- add text length validation of individual product options

## 2.4.33
- A few more steps for easier integration and installation

## 2.4.32
- bugfix for overall stock handling in checkCart and integration of qtyIncrements
- bugfix for switched unit_amount prices in checkCart response
- fixed compatibility to phoenix_cashondelivery
- added localisation for de_CH and de_AT
- bugfix for not properly implemented ShopgateConfig properties

## 2.4.31
- Feature implementation for easier setup and integration

## 2.4.30
- bugfix for the proper realization of the redirect configuration flag
- feature for deactivation the mobile redirect for specific controllers, categories or products
- bugfix export of ean code
- bugfix for processing of the correct stockitem on checkCart in conjunction with configurables
- feature implemented to offer automated integration into shopgate after module installation

## 2.4.29
- writing payment information into payment-model for further processing in magento
- bugfix for mobile validation of stock inventory on configureable products

## 2.4.28
- bugfix mobile validation of stock inventory on bundle products

## 2.4.27
- bugfix mapping of USPS shipping method for magento 1.8.0.0
- bugfix for the export of products AvailableText
- improved error handling for coupons
- add support export of category anchor products
- update shopgate library to version 2.4.14
- bugfix for addOrder of bundle products which did result in different prices
- bugfix for checking max option count when there are no options in the shop at all

## 2.4.26
- title for shipping and payment methods from shopgate are configurable now

## 2.4.25
- Corrected the exported inventory for bundle products
- Bugfix for exporting the availability messages
- Including backorders while exporting inventory for bundles
- Bugfix for the test of product availability
- Reintegrate bundles as common produkttype for the export
- Bugfix for exclusion of not available options while exporting

## 2.4.24
- improved error handling if product is out of stock

## 2.4.23
- Bugfix for the export of bundles with dynamic price
- Optimisations and error handling in method check_cart
- Automated retrieval of the maximum optioncount for product export
- removed system configuration 'default_item_row_option_count'

## 2.4.22
- Bugfix for wrong payment fee in combination with Phoenix CashOnDelivery
- Feature custom_fields for orders billing- and shipping addresses now gets transferred and saved to related entity as well
- update Shopgate library to version 2.4.13

## 2.4.21
- update Shopgate library to version 2.4.12

## 2.4.20
- update Shopgate library to version 2.4.10

## 2.4.19
- Add support of tax settings during export of product custom options
- Improved export of grouped products
- Improved compatibility to other modules by removing unneeded model rewrites
- Refunded articles now get transferred to shopgate

## 2.4.18
- update Shopgate library to version 2.4.9
- method check_cart now also returns cart item stock information and shipping and payment methods which are available for cart address

## 2.4.17
- Added support for backorders in items csv
- Added support for Phönix CashOnDelivery
- Added support for individual options at configurable products

## 2.4.16
- Bugfix invalid class constance call in old php versions

## 2.4.15
- Bugfix tax calculation for magento 1.4.x.x

## 2.4.14
- improved error handling of getCustomer method and update library to 2.4.8

## 2.4.13
- customer which register mobile will be transferred to the shop

## 2.4.12
- Bugfix translations in backend incorrect

## 2.4.11
- Product sorting within categories for export now configurable through magento backend

## 2.4.10
- support msrp(merchant suggested retail price) for product export

## 2.4.9
- Added support for crossell and upsell products on item export
- extend support for configurable products to magento version >= 1.4.0.1

## 2.4.8
- Update shopgate library to version 2.4.3

## 2.4.7
- Bugfix setQuoteItems in FrameworkController extended to support configurable products with selected option in quote and order

## 2.4.6
- Bugfix incorrect call of class constant in FrameworkController
- Library update to increase performance and decrease memory allocation
- Bugfix for capturing already invoiced parts and the upcoming fraud suspection
- Bugfix for the check if a payment update is allowed to be captured

## 2.4.5
- Extended the logging for considering the proper shipping methode from ShopgateOrder
- Bugfix for the mobile redirect of visible child products of configurables to the super product
- Bugfix for wrong called helper methods getParentIdsConfigurable and getParentIdsGrouped
- Bugfix wrong typehinting for USPS mapper

## 2.4.4
- fixed error on exporting the additional amount of input fields (custom options)

## 2.4.3
- Adjustment of the module dependencies for the newly implemented extension of the shippment
- Extended the logging for setSettings
- Bugfix clearing the config cache after saving through setSettings

## 2.4.2
- corrected return value of getAvailableMethods at Shopgate shipping method
- excluded products with required custom option of type file from csv export

## 2.4.1
- alteration of the log-statements for a better usability
- extending the load() and save() methods for scope sensitive persistence
- bugfix for saving emtpystrings to the database on stores scope level
- optimization of the cleanup method for scope sensitive persistence
- include of hidden setting values into the processing of setSettings
- calculation of Coupons improved

## 2.4.0
- debug functionality
- bugfix for shopgate coupons at the us plugin
- bugfix for the native USPS shipping methods (more precise method comparison)
- bugfix for the native USPS shipping methods ('Default' carrier mapping added)
- export hidden categories
- implementation of the load() and save() methods for ShopgatePluginApi->setSettings()
- bugfix for not in the class registered class variables with getter and setter methods
- implementation of logging for debugging into the processing triggered by helper('shopgate/data')->setShippingMethod()
- removed the possibility to deactivate the module through the setSettings method of the api

## 2.3.17
- bugfix for the processing of USPS shipping models where an array index is read the wrong way

## 2.3.16
- filter for Properties by Export
- while importing orders with non-usps shipping methods, there was an exception caused by an malformed array definition

## 2.3.15
- custom attribute code for description.
- fixed issue with verfication of coupon codes in Magento 1.4.x.x
- order import will use now the native shipping models for USPS shipping instead of the generic (shopgate_fix)

## 2.3.14
- configuration for weight unit (kg,g,lb,oz) and automatic configuration
- new library version. Support of up to 100 options at item export
- EAN Export, attribute code can be defined at configuration page

## 2.3.13
- cancel bug fix (virtual product)

## 2.3.12
- option (Yes/No) for export visible subproducts in configuration
- assignment Original-Ids to Shopgate Ids work now for configurable Products
- Varien_Autoloader issue with magento 1.4 (cod)

## 2.3.11
- payment becomes an virtual item only if payment fee is negative
- removed bundles from the list of exportable product types. Set export of bundles to beta state
- pound (lbs) is considered to be the output parameter for weight export
- weight is not rounded any longer

## 2.3.10
- problem with version 2.3.9. Extension cannot be loaded from magento connect

## 2.3.9
- cancelation of VirtualProduct will be ignored
- get_settings uses the tax class id of the customer group "NOT_LOGGED_IN" as default tax class id

## 2.3.8
- fixed error on exporting the additional amount of input fields (custom options)
- fixed error on exporting the weight of products that have quantity increments enabled
- Magento EE below 1.9.1.0 is now treated like Magento CE below 1.5.0.0 because they share the same code basis

## 2.3.7
- bugfix for 3rd party payment methods

## 2.3.6
- bugfix for magento 1.4. Interfaces cannot be created in merchant area and orders may not be imported (payment cod).

## 2.3.5
- gift wrapping for magento enterprise, version 1.10 and higher

## 2.3.4
- improved handling of additional costs for article options set at parent item

## 2.3.3
- bugfix for redeem_coupon
- payment becomes an virtual item in the orderlist, now

## 2.3.2
- shopgate coupons are no longer items that need to be shipped, thus they don't affect the shipping completed state
- bugfix for Magento 1.4 (issue with autoloader, if model class does not exists)
- bugfix for US, if country code is missing at address
- extended mapping of region codes

## 2.3.1
- if a catalog price rule and a special price is defined for one product the cheaper price is exported
- improvement at tax export, if no tax rule is applied
- bugfix for magento 1.4, mobile redirect

## 2.3.0
- update ShopgateLibrary to version 2.3.2
- optional setting "Always redirect". either redircet always or only if on product detail, category listing and index
- display on shopgate selected shipping method in order
- available text for products can export from a attribute now
- "original price" is set for a shopgate coupon in article

## 2.2.7
- fix error in shopgate connect
- inventory stock can be negative if product or system is configured so

## 2.2.6
- change english descriptions from "child products" to "subproducts"
- order status will always set now
- export of products is now filtered by the selected store
- ignore salesrule on add new order
- use of magento internal payment method

## 2.2.5
- update ShopgateLibrary to version 2.2.1
- plugin for USA and EU is merged to one modul
- API credentials can be empty now
- if API credentials are empty then no requests will sent to shopgate
- bug fix on update stock quantity if article has no stock object
- stock quantity will not update if new stock is less than 0
- use payment method "checkmo" on prepay

## 2.2.4
- reworked export of configurable products for better performance
- fixed a bug in calculating coupons
- fixed a bug in exporting store labels

## 2.2.3
- fixed a bug that in certain conditions rendered the processing of non-Shopgate orders impossible

## 2.2.2
- new setting to activate sending order confirmations via email by Magento
- order status for PayPal orders will now be set to status "processing"
- optionally order confirmation will send to Shopgate on order status is "completed" and no shipping order exists
- order shipping confirmations can now optionally be sent to Shopgate on order status "completed" even without shipping orders existing
- fixed bug in coupons system
- fixed bug on calculating cash on delivery fees. Double calculation of fees have been fixed
- accessing stores by shop number on different domains has been fixed
- adding of orders with checkbox options has been fixed

## 2.2.1
- added template for payment method for frontend and emails
- set street2 in address entries

## 2.2.0
- update ShopgateLibrary to version 2.2.0
- added actions check_cart and redeem_coupons. Live synchronization of shop coupons is supported now
- change payment type 'shopgate_generic' to 'mobile_payment'
- payment setting for payment types 'shopgate' and 'mobile_payment'
- fix error in adding shopgate coupons in magento 1.4
- Fix error in adding shipping and invoice address on add order

## 2.1.51
- bugfixes for update order

## 2.1.50
- options for export of descriptions and images (parent, child, both)

## 2.1.49
- update ShopgateLibrary to version 2.1.26
- fix error on updateOrder - unhold orders by Shopgate
- removed unused functions

## 2.1.48
- show mobile cms page if exists
- better status handling on adding new order from shopgate
- adding support for Module "CashOnDelivery"
- bugfix on adding addresses without a state
- reduce stock of product an new order

## 2.1.47
- update ShopgateLibrary to version 2.1.24
- support for catalog prices on export
- support for "DreamRobot_Checkout" modul
- new sql install script which will execute on installation
- fix bug in calculating product prices if products managed without tax in backend

## 2.1.46
- check compatibility to magento ee 1.13.0
- fix error on mysql upgrade script

## 2.1.45
- partial cancelations are reported to shopgate
- fix report partial shipping to shopgate
- global search for shopgate order number in backend

## 2.1.44
- BugFixes

## 2.1.43
- update ShopgateLibrary to version 2.1.24
- handle shopgate coupons separately
- fix price error on add order
- change update order process

## 2.1.42
- stacked products are now splitted into separate product quantities

## 2.1.41
- products, that have qty increments enabled and an increment value greater than one set are now exported as stacked products

## 2.1.40
- fix bug on product detail page in compiled mode
- show magento endition in plugin info
- mobile redirect can now also be performed exclusively using javascript

## 2.1.39
- update ShopgateLibrary to version 2.1.23
- fix errors in mobile redirect

## 2.1.38
- update ShopgateLibrary to version 2.1.22
- option to export multiple stores
- group payment methods
- fix bug with coupon handling

## 2.1.37
- update ShopgateLibrary to version 2.1.19
- use new redirect logic
- BugFixes for Magento 1.4

## 2.1.36
- BugFixes

## 2.1.35
- import checkout prices to magento order from shopgate
- calculate tax for shipping costs
- bugfixes
- call event dispatcher in add_order and update_order

## 2.1.34
- fix encoding problem with euro sign
- fix calculating tax fur merchants outside germany
- version of plugin is showing in all setting views
- weight is export as integer value

## 2.1.33
- update library to 2.1.18
- settings are expandet to website and are available for all views
- support for external module for german base prices (http://www.magentocommerce.com/magento-connect/grundpreis-modul-pangv.html)
- some improvements in payment system

## 2.1.32
- fixed problem on using json functions
- change order of some settings
- line breaks at the beginning or end of option names now get cut off
- fixed problem on creating refunds

## 2.1.31
- changed "Debug Settings" to "Connection Settings"
- re-integrated support for Magento 1.4.x

## 2.1.30
- stock quantities are now always exported as whole numbers; decimal places get cut off
- revised descriptions in module configuration and comments for orders

## 2.1.29
- set orders to on-hold if shipping is not approved

## 2.1.28
- reduced prices from shopgate will apply in orders
- update library to 2.1.17

## 2.1.27
- Fix url_deep for products whicht are not visible

## 2.1.26
- check image in export if image gallery value ist set
- delay in add status history to sort them by time correctly
- workaround on including storecode in url on caling shopgate plugin api
- added option to insert htaccess information for images

## 2.1.25
- seperate some functions to observer
- added option to mark orders as paid which are not blocked any more
- added option "is your shop released on shopgate"

## 2.1.24
- Export of products only visible in search

## 2.1.23
- BugFix on import with options is null
- ignore stock on shopgate orders

## 2.1.22
- Export / Import of input fields
- export configurable images to linked child products

## 2.1.21
- BugFix

## 2.1.20
- BugFix

## 2.1.19
- ignore placeholder images on child products
- fix order status
- user description of grouped products
- export images of group products
- cancle order at shopgate (beta)

## 2.1.18
- BugFixes

## 2.1.17
- export reviews csv fix
- fix bug for configureable products

## 2.1.16
- fix export options for downloads

## 2.1.15
- update library to 2.1.12

## 2.1.14
- Update Library to 2.1.11
- Export Downloadable Products
- Export filter

## 2.1.13
- fixed bug for magento compiler

## 2.1.12
- Bugfix on export "Short description + description"

## 2.1.11
- Update Library to 2.1.9

## 2.1.10
- Fix Bug an Checkout for Magento 1.4

## 2.1.9
- the base image of a product will now be exported as the first image even if it's excluded from the product images gallery

## 2.1.8
- Export GroupedProduct without SKU correctly with id

## 2.1.7
- Fix generell error
- Fix Bug on generate redirect-keywords

## 2.1.6
- Settings Bugfix. The saved currency will be export
- uses Shopgate Library 2.1.8

## 2.1.5
- extendet log on addOrder/updateOrder
- Option to ignore prices of root-products

## 2.1.4
- Update Library to 2.1.2.1.6
- active_status in csv export
- ignore min/max on import orders

## 2.1.3
- Option to convert linebreaks in short and long description

## 2.1.2
- Fix Bug on Export child product images
- Option to ignore child description

## 2.1.1
- Update Library to 2.1.4 to fix an internal error

## 2.1.0
- New Library 2.1.0

## 1.0.11
- Ignore item stock on add_order

## 1.0.8
- Fix upload for Connect1.0 and Connect2.0

## 1.0.7
- update library to version 2.0.29
- export grouped products as category

## 1.0.6
- update Library to version 2.0.26
- include cron-action for automated services
- add delivery notes with carrier to shopgate
- bug-fixes

[Unreleased]: https://github.com/shopgate/cart-integration-magento/compare/2.9.68...HEAD
[2.9.68]: https://github.com/shopgate/cart-integration-magento/compare/2.9.67...2.9.68
[2.9.67]: https://github.com/shopgate/cart-integration-magento/compare/2.9.66...2.9.67
[2.9.66]: https://github.com/shopgate/cart-integration-magento/compare/2.9.65...2.9.66
[2.9.65]: https://github.com/shopgate/cart-integration-magento/compare/2.9.64...2.9.65
[2.9.64]: https://github.com/shopgate/cart-integration-magento/compare/2.9.63...2.9.64
