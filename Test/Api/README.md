# REST API tests for Amazon Pay SPC

## Setup
1. Make sure that the Magento/Adobe Commerce installation was installed with the core sample data
2. Update the following files relative to this README
   1. `phpunit_rest.xml` to include the testing server's `TESTS_BASE_URL`
   2. `config/install-config-mysql.php` to include the `base-url`, as well as database connection details

## Running
Run the following command from the Magento/Adobe Commerce root directory

```vendor/bin/phpunit -c /full/path/to/magento/app/code/Amazon/Pay/Test/Api/phpunit_rest.xml```
