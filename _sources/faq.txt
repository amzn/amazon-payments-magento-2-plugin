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
.. _`responsive.html in the magento2 repository` : https://github.com/magento/magento2/blob/2.2/lib/web/css/docs/responsive.html
.. _`Magento DevDocs` : http://devdocs.magento.com/guides/v2.2/frontend-dev-guide/responsive-web-design/rwd_overview.html


Amazon Pay Widgets are not surfaced
-----------------------------------
Please check if you are using a theme, which is not based on Magento's Luma or Blank theme first and follow the advice above.

If this is not the case and you need help, please file an issue with us.
