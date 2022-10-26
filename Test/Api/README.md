# REST API tests for Amazon Pay SPC

## Setup
1. Make sure that the Magento/Adobe Commerce installation was installed with the core sample data
2. Install Chromium and check that it is installed in `/usr/bin/chromium`
   1. Otherwise, update `js/actions.js` to reflect the location
3. Run `npm install` from this directory
4. Update the following files relative to this README
   1. `phpunit_rest.xml` to include the testing server's `TESTS_BASE_URL`
   2. `config/install-config-mysql.php` to include the `base-url`, as the test server domain, and database details
   3. `scripts/config/selenium-config.json` and add Amazon Pay test credentials, as well as the domain of the test server
## Running
Run the following command from the directory of this README

```npm run e2e-local```
