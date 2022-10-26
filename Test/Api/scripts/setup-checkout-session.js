import * as fs from 'fs';
import { exit } from 'process';
import { actions } from '../js/actions.js';

const config = JSON.parse(fs.readFileSync('scripts/config/selenium-config.json'));
const base = config.base_url;
const postmanEnvironment = JSON.parse(fs.readFileSync('environments/checkout.postman_env.json'));

// Set environment key names based on API being used, REST by default
config.csidKey = 'checkout_session_id';
config.qimKey = 'quote_id_mask';

(async() => {
    const { browser, page } = await actions.launchBrowsingSession();
    try {
        console.log(`Opening new browser tab and navigating to ${base}...`);
        await page.goto(base);

        await actions.addAnItem(page);

        // Set interceptors for quote mask
        const client = await page.target().createCDPSession();
        await client.send('Network.enable');
        await client.send('Network.setRequestInterception', {
            patterns: [
                {
                    urlPattern: '**/rest/default/V1/guest-carts/*/estimate-shipping-methods',
                    resourceType: 'XHR',
                    interceptionStage: 'Request'
                }
            ]
        });

        // Get unique cart ID from estimate-shipping request
        client.on('Network.requestIntercepted', async ({ interceptionId, request, responseHeaders, resourceType }) => {
            await client.send('Network.continueInterceptedRequest', {interceptionId: interceptionId});

            // Set masked quote ID in in-memory environment
            const cartId = request.url.split('/')[7];
            postmanEnvironment.values
                .filter(value => value.key.toLowerCase() === config.qimKey)
                .map(value => value.value = cartId);

            // console.log(cartId);
        });

        await actions.loginWithAmazon(browser, page);

        // console.log('Monitoring network traffic and obtaining quote id mask...');
        await page.waitForSelector('.step-title');

        console.log('Looking for checkoutSessionID in local storage...');
        await page.evaluate((pm, config) => {
            const csid = window.localStorage.getItem('amzn-checkout-session');
            const parsed = JSON.parse(csid);

            // Set checkout session ID in in-memory environment
            pm.values
                .filter(value => value.key === config.csidKey)
                .map(value => value.value = parsed.id);

            return pm;
        }, postmanEnvironment, config).then((pm) => {
            fs.writeFileSync('environments/quote_mask_id', pm.values.filter(value => value.key === config.qimKey)[0].value);
            fs.writeFileSync('environments/checkout_session_id', pm.values.filter(value => value.key === config.csidKey)[0].value);

            console.log(pm.values.filter(value => value.key === config.qimKey)[0].value);
            console.log(pm.values.filter(value => value.key === config.csidKey)[0].value);
        });

        await page.waitForTimeout(2000);
        await browser.close();
    } catch (ex) {
        await actions.handleFailure(ex, page);
    }
})();
