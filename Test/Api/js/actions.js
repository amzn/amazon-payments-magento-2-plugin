import puppeteer from 'puppeteer';
import * as fs from 'fs';

import { vars, selectors } from './selectors.js';

const config = JSON.parse(fs.readFileSync('scripts/config/selenium-config.json'));

export const actions = {
    async launchBrowsingSession() {
        const browser = await puppeteer.launch({
            headless: true,
            ignoreHTTPSErrors: true,
            executablePath: '/usr/bin/chromium',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox'
            ]
        });
        const page = await browser.newPage();
        await page.setDefaultTimeout(30000);

        return {
            browser: browser,
            page: page
        };
    },

    async addAnItem(page) {
        // Add an item
        console.log('Adding item to guest cart...');
        const itemString = vars.productSearchTerm + String.fromCharCode(13);

        await page.waitForSelector(selectors.search);
        await page.type(selectors.search, itemString);
        await page.waitForXPath(selectors.productLink);
        const firstItem = await page.$x(selectors.productLink);
        await firstItem[0].click();

        await page.waitForSelector(selectors.amazonPdpButton);
        await page.click(selectors.pdpAddToCart);
        await page.waitForSelector(selectors.successMessage);

        // Let Amazon buttons render
        console.log('Initiating Amazon Pay from minicart...');
        await page.click(selectors.minicartCounter)
        await page.waitForSelector(selectors.amazonMinicartButton);

        await page.waitForTimeout(2000);
    },

    async loginWithAmazon(browser, page) {
        // Kick off Amazon login
        const button = await page.$(selectors.amazonMinicartButton);

        await page.waitForTimeout(5000);

        const newPagePromise = new Promise(x => page.once('popup', x));

        await button.click();

        const popup = await newPagePromise;

        return new Promise(async (resolve, reject) => {
            popup.on('close', () => resolve());

            // Login with Amazon
            console.log('Switching to Amazon authentication popup and entering credentials...');
            // await popup.screenshot({path: 'screens/popup.png'});
            await popup.waitForSelector(selectors.apEmail);
            await popup.focus(selectors.apEmail).then(() => popup.type(selectors.apEmail, config.ap_user));
            await popup.waitForSelector(selectors.apPassword);
            await popup.focus(selectors.apPassword).then(() => popup.type(selectors.apPassword, config.ap_password));

            await popup.click(selectors.apSubmit)

            await popup.waitForTimeout(200).catch(() => reject());
            // await popup.screenshot({path: 'screens/popup2.png'}).catch(() => reject());
            await popup.waitForTimeout(2000).catch(() => reject());
            // await popup.screenshot({path: 'screens/popup3.png'}).catch(() => reject());
            await popup.waitForTimeout(5000).catch(() => reject());

            if (!popup.isClosed()) {
                // await popup.screenshot({path: 'screens/popup4.png'}).catch(() => reject());
                await popup.waitForXPath(selectors.apContinueText).catch(() => reject());
                await popup.click(selectors.apContinueButton).catch(() => reject());
            }
        });
    },

    async handleFailure(ex, page) {
        console.error(`failure: ${ex}`);
        try {
            await page.screenshot({path: 'screens/checkout-session-api-test-setup.png'});
        } catch (ex) {
            console.error(`Could not capture screenshot: ${ex}`);
        }
        process.exit(1);
    }
};
