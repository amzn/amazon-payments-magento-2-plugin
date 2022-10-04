export const vars = {
    productSearchTerm: 'Simple Product 7'
};

export const selectors = {
    search: '#search',
    productThumbnail: '//ol/li[1]/div',
    productLink: '//*/a[contains(@class, "product-item-link") and contains(text(), "Simple Product 7")]',
    pdpAddToCart: '#product-addtocart-button',
    successMessage: 'div.message-success.success.message',
    minicartCounter: '.counter-number',
    shippingMethodCheckbox: '[value="flatrate_flatrate"]',
    shippingMethod: '#shipping-method-buttons-container > div > button',
    checkoutSuccess: '.checkout-success',
    amazonPdpButton: '#PayWithAmazon-Product > div',
    amazonMinicartButton: '#PayWithAmazon-Cart > div',
    amazonButtonShadow: 'div > div.amazonpay-button-view1.amazonpay-button-view1-gold',
    apEmail: '#ap_email',
    apPassword: '#ap_password',
    apSubmit: '#signInSubmit',
    apContinueText: '//span[text()[contains(., "Continue")]]',
    apContinueButton: '.a-button-input',
    amazonPlaceOrder: '#amazon-payment > div.payment-method-content > div.actions-toolbar > div > button'
};
