# Postcode.nl API Magento 2 Extension

With this extension you kan look up and validate Dutch addresses at checkout in you Magento 2 store.

Using this extension, you will have the following advantages
- Improve sale conversion
- Reduce address errors
- Reduce costs from undelivered mail and packages
- Offer extra service for your customers with easy address entry

To use this extension, you need an account with the Postcode.nl API. You [can sign up for an account here](https://account.postcode.nl/).

The extension was created by Wezz e-Commerce B.V. for Postcode.nl B.V.

__Looking for the Magento 1 Extension?__

Check out the [Magento 1 exsension here](https://github.com/postcode-nl/PostcodeNl_Api_MagentoPlugin)

__FAQ__

If you have any questions, please check the [FAQ on this page](https://www.wezz.co/extensions/postcode.nl-api-magento-extension).

__API Documentation__

This extension uses the Postcode.nl API. Full documentation of the API can be found on the following URL
https://api.postcode.nl/documentation/rest-json-endpoint](https://api.postcode.nl/documentation/rest-json-endpoint)

# Installation


## 2. Installation

### 2.1 Installation via Magento Marketplace

Soon this extension will be available on thye Magento Marketplace.

### 2.2 Installation through Composer

1. On the command line, go to your Magento root folder.
2. Run the following command to add the extension to your codebase:

`
composer require wezz/postcodem2
`

3. Enable the extension in Magento using the following commands

`
php bin/magento module:enable Wezz_Postcode
`

`
php bin/magento setup:upgrade
`

`
php bin/magento cache:clean
`

And, when running production mode:

`
php bin/magento setup:static-content:deploy
`

Now, you can log in to the Magento backend and configure and enable the extension.

## Configuration

Navigate to the module configuration using the following path:

__Stores__ -> __Configuration__ -> __Sales__ -> Postcode

### Fields

Fill in the following fields to configure your Postcode.nl API Magento 2 module.

#### General
| Field | Explanation |
| :--- | :--- |
| Enabled | Option to enable/disable the module on your Magento store |
| Application key | Please find your Application key in your Postcode.nl account |
| Application secret | Please find your Application secret in your Postcode.nl account |


## Troubleshooting

For __Frequently Asked Questions__ please visit [https://www.wezz.co/extensions/postcode.nl-api-magento-extension] (The FAQ page).
