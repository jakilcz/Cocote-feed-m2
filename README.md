Magento 2 Module for Cocote.com website.

This module generates feed and communicates with cocote.com website to let it aggregate your products data.

Installation
To install the module just copy content of the module to app/code folder of your project on server.
After that clear cache and perform setup:upgrade.

There is a new cache type created, Cocote Cache  - you should enable it in admin after you’ve installed module.

First you need to configure the module in store->configuration->Cocote and from there you can also generate feed .xml file.
File will be refreshed each day at 3.00 A.M. by cron tasks.
