Shopify_FSI-Fulfillment
=======================

This is an app that provides a simple interface through which store owners can select orders or groups of orders to be added to a CSV file and uploaded to [FSI](http://www.unitedfsi.com/)'s servers using their own FTP login information.

This is intended to be deployed to [Heroku](http://www.heroku.com/) using the [ClearDB](https://addons.heroku.com/cleardb) add-on.

Be sure to set the correct [Heroku Config Vars](https://devcenter.heroku.com/articles/config-vars) as used in `config.lib.php`.

* The SHOPIFY_* values should be from your app's [settings page](https://app.shopify.com/services/partners/api_clients/)