Shopify_FSI-Fulfillment
=======================

This is an app that provides a simple interface through which store owners can select orders or groups of orders to be added to a CSV file and uploaded to [FSI](http://www.fsi-intl.com/)'s servers using their own FTP login information.

This is intended to be deployed to [Heroku](http://www.heroku.com/) using the [ClearDB](https://addons.heroku.com/cleardb) add-on.

Be sure to copy `lib/config.lib.php.dist` to `lib/config.lib.php` and replace the X's with your own settings.
You will also need to force the addition of the new config file to the repo (`git add -f lib/config.lib.php`) or remove it from `.gitignore`.

* The SHOPIFY_* values should be from your app's [settings page](https://app.shopify.com/services/partners/api_clients/)

* The FTP_* values should be for the FTP server the batch-generated CSV file should be uploaded to.
