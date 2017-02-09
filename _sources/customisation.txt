Frontend Customisation
========================

Q: I'd like to add a `Amazon Pay` or `Login with Amazon` button elsewhere on my site
-------------------------

If you are wanting to add one of these buttons to a new location on your site you need to update the **XML** for the page you want to add it to.

For instance if you wish to add a `Login with Amazon` button to the category page you would update the following file.

In your theme directory, you would create:
``app/design/frontend/<your_namspace>/<your_theme>/Magento_Catalog/layout/catalog_category_view.xml``

.. code-block:: xml

    <page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" layout="1column" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
        <body>
            <referenceBlock name="category.products">
                <block class="Amazon\Login\Block\Login" name="amazon_login" after="category.products.list" template="Amazon_Login::login.phtml"/>
            </referenceBlock>
        </body>
    </page>

Here you are referencing the block **category.products** and then simply inserting the `Login with Amazon` button template into this block to be positioned after the block **category.products.list**


Amazon Pay

If you wish to add a `Amazon Pay` button elsewhere in your store, you can follow the same process above, using a different template, like so.


.. code-block:: xml

    <block class="Amazon\Payment\Block\PaymentLink" name="amazon.pay.button" after="-" template="Amazon_Payment::payment-link.phtml" />


You can position and target which block the button appears in as with the `Login with Amazon` button. If you need more information on how to position and add new blocks into Magento 2 please see the `Magento 2 documentation <http://devdocs.magento.com/guides/v2.1/frontend-dev-guide/layouts/layout-overview.html>`_

Q: I'd like to customise the authorisation decline e-mail
-------------------------

You can easily customise the authorisation decline e-mails by creating a version in your own theme. To do this you need to copy the current template into your own theme:

``vendor/amazon/module-payment/src/Payment/view/frontend/email/hard-declined-auth.html``

Copy to

``app/design/frontend/<your_namspace>/<your_theme>/Amazon_Payment/email/hard-declined-auth.html``

You can then customise this new template however you wish by adding images and new text or additional styling. You can read more about customising the Magento 2 email templates in the official `Magento 2 documentation <http://devdocs.magento.com/guides/v2.1/frontend-dev-guide/templates/template-email.html>`_