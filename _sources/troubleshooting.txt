Troubleshooting
===============

Event logs
----------

The **Amazon Pay and Login with Amazon** extension provides a convenient logging system. It is enabled by default, but you can disable it in the extension settings, refer to the :doc:`configuration` section for more details.

Log files location
------------------

The Logger saves details concerning all API calls and all incoming IPN notifications that occurred within the **Amazon Pay and Login with Amazon** extension scope. Logs are stored in the following locations:

* API call logs:

  ``var/log/paywithamazon.log``

* IPN notificationslogs:

  ``var/log/amazonipn.log``