Frequently Asked Questions (FAQ)
================================

I am using a custom theme, what do I have to do?
------------------------------------------------

The styles used in the extension are based on Magento's Luma theme. Responsive breakpoints and other variables, like `@screen__m`_, in the LESS files are defined by the Luma theme.
If your custom theme is based on the Magento theme Luma or Blank, you should be fine. If it isn't, you should define all the variables and `Responsive Breakpoints` used.

Magento provides detailed information about `Responsive Breakpoints` and responsive design in general. See `responsive.html in the magento2 repository`_ (vendor/magento/magento2-base/lib/web/css/docs/responsive.html in your Magento 2 installation) for more detailed explanations.

`Magento DevDocs`_ gives additional information around this topic as well.

Amazon Pay provides two LESS files in this extension. They need to be adapted to match your theme's responsive breakpoints.

* https://github.com/amzn/amazon-payments-magento-2-plugin/blob/master/src/Login/view/frontend/web/css/source/_module.less
* https://github.com/amzn/amazon-payments-magento-2-plugin/blob/master/src/Payment/view/frontend/web/css/source/_module.less


.. _`@screen__m` : https://github.com/amzn/amazon-payments-magento-2-plugin/blob/1.2.4/src/Payment/view/frontend/web/css/source/_module.less#L71
.. _`responsive.html in the magento2 repository` : https://github.com/magento/magento2/blob/2.3/lib/web/css/docs/responsive.html
.. _`Magento DevDocs` : http://devdocs.magento.com/guides/v2.3/frontend-dev-guide/responsive-web-design/rwd_overview.html


Amazon Pay Widgets are not surfaced
-----------------------------------
Please check if you are using a theme, which is not based on Magento's Luma or Blank theme first and follow the advice above.

If the widgets are still not surfaced correctly, please double check if the required div containers are available in the DOM tree of the website. For this, please go to the checkout and search for the container "amazon-widget-container" as shown below.

.. image:: /images/amazon-widget-container.png

In the example above, the container exists and also contains the div container for the address book widget ("addressBookWidgetDiv"), so the extension is installed properly and working correctly.

If that's the case, but the widget is not visible, it is most likely because the required CSS style has not been included correctly. Like shown in the screenshot below, the height of the inspected container can be set manually via the browser debug console. If the widget will show up when doing this, the CSS definitions have not been included correctly. For more information on CSS styles and LESS files, please refer to the `Cascading style sheets (CSS) documentation`_ of Magento 2.

.. image:: /images/amazon-widget-container-2.png

.. _`Cascading style sheets (CSS) documentation`: https://devdocs.magento.com/guides/v2.3/frontend-dev-guide/css-topics/css-overview.html
