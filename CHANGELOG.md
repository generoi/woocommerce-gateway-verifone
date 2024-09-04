# Change Log
All notable changes to this project will be documented in this file.

## [1.4.0] 2024.8.6

### Added
- Filter for demo server urls

### Changed
- Verifone logo used on checkout to reflect the new brand

### Fixed
- Support for decimal values in tax

## [1.3.16] 2024.3.5

### Added
- Added Woocommerce cart and checkout blocks support

## [1.3.15] 2024.2.22

### Changed
- Added Woocommerce HPOS compability declaration
- Payment methods are now saved in order of debit only, credit/debit, credit only per regulation
  - For existing installations, 'Refresh Payment Methods' again from plugin settings to re-order

## [1.3.14] 2023.06.06
- Updated AfterPay payment method to use the new Riverty branding

## [1.3.13] 2023.04.20

### Fixed
- Changed empty value '-' to use more API appropriate '?' to indicate anonymous/unavailable data

## [1.3.12] 2021.10.05

### Changed
- Rename GuzzleHttp to GuzzleHttp6

### Fixed
- Compatibility with modules using other versions of GuzzleHttp

## [1.3.11] 2021.08.20
### Added
- Possibility for set payment note in the checkout

### Changed
- Payment methods name
- Set '-' instead of empty value for customer data to avoid exception

### Fixed
- Added missing translations
- Fixes related to PHP 8.0 support
- Issue when correctly paid order is mark first as paid and after some time as unpaid 

## [1.3.10] 2021.05.24
### Changed
- Tested versions for WordPress: 5.7.1, WooCommerce: 5.3.0, PHP: 7.4.16
- Add All in One payment method available always

### Fixed
- Issue with the session_start() reported by Health Check tool
- Issue with dividing by zero when product has zero price
- Issue with Custom Order Numbers plugin

## [1.3.9] 2020.12.18
### Changed
- JQuery: Changed deprecated way of registering handlers for "ready" event. (removed "on('ready') and changed to "$( handler )").

## [1.3.8] 2019.11.12
### Changed
- Provider page url
- Supported version for Wordpress (5.2.4) and WooCommerce (3.8.0)
- Translations in the configuration for Finnish language

## [1.3.7] 2019.11.01
### Changed
- Updated vendor for Core library - compatibility with PSD/2 regulation
- Add billing address into payment request - PSD/2 regulation

### Removed
- Possibility to pay with saved credit card by S2S request - PSD/2 regulation

### Fixed
- Problem with reduce stock when order is cancelled
- Problem with recreate order product with variation

## [1.3.6] 2019.05.11
### Changed
- Update author name

## [1.3.5] 2019.03.11
### Changed
- Logic for load vendors - fix problem with conflict with other module(s)
- Update core library
- Translation update

### Fixed
- Problem with js init in configuration panel, when not loaded fully

## [1.3.4] 2019.01.22
## Added
- Hide select payment method when just AllInOne is available
- Hide "Remember payment method" when is selected other payment method than a card.
- Logic for prevent against process 2 payment responses at same time.

## Changed
- Set payment request locale depends on customer language, and if not available then fetch from the configuration
- Hidden selector for payment method when just All In One available
- Small changes in configuration

## Fixed
- Process payment response just for unpaid orders
- Order create at timestamp in request

## [1.3.3] 2018.11.29
### Changed
- Move delayed url into summary

## [1.3.2] 2018.11.21
### Added
- Possibility to configure simple and advanced mode for key handling
- New fields in configuration
- Possibility to generate new keys for test and live

### Changed
- Summary information about keys
- Logic for fetch keys

## [1.3.1] 2018.10.09
### Added
- Default payment service live key
- Possibility to display payment logo

### Changed
- Updated vendor for Http Client wrapper - increase timeout into 20 seconds

### Fixed
- Problem with rounding order item price and tax for WooCommerce 3.4.5
- Problem with combine basket items for invoice.

## [1.3.0] 2018.10.02
### Added
- New payment methods: AfterPay (Invoice), MobilePay, MasterPass, Vipps
- New field to configuration: directory for stored keys
- Functionality for generate new security keys
- Possibility to display summary for configuration
- Possibility to display shop public key

### Changed
- Updated vendor for Core library - functionality for add refund product and generate RSA key
- Add product into refund requests
- Update payment provider name into full name
- Update translations
- Configuration values for shop and payment service keys

### Fixed
- Increase size of payment method select in admin panel
- Problem with template renderer for foreach function

## [1.2.2] 2018.09.03
### Changed
- Updated vendor for Core library - change exception message when is problem with server response.

## [1.2.1] 2018.08.29
### Fixed
- Apply fix for delayed success, earlier code for it was in unreachable if statement.

## [1.2.0] 2018.08.28
### Added
- This change log file.

### Changed
- Updated vendor for Core library - fix problem with parse response when flag skip confirmation page is set to false. 
