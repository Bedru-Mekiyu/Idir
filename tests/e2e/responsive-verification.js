import { chromium } from 'playwright';
import fs from 'fs';

const BASE_URL = 'http://127.0.0.1:8000';
const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

const BREAKPOINTS = [
    { width: 375, height: 667, name: '375px_mobile_small' },
    { width: 414, height: 896, name: '414px_mobile_standard' },
    { width: 768, height: 1024, name: '768px_tablet_portrait' },
    { width: 1024, height: 768, name: '1024px_tablet_landscape' },
    { width: 1440, height: 900, name: '1440px_laptop_standard' },
    { width: 1920, height: 1080, name: '1920px_desktop_large' },
];

async function safeGoto(page, url) {
    try {
        await page.goto(url, { waitUntil: 'commit', timeout: 15000 });
        await page.waitForLoadState('domcontentloaded');
        await page.waitForTimeout(500);
    } catch (e) {
        if (!e.message.includes('ERR_ABORTED')) {
            throw e;
        }
        await page.waitForLoadState('domcontentloaded');
    }
}

async function runResponsiveVerification() {
    console.log('📱 Starting Responsive Design Verification across 6 breakpoints...');

    const browser = await chromium.launch({
        executablePath: fs.existsSync(CHROME_PATH) ? CHROME_PATH : undefined,
        headless: true,
    });

    for (const bp of BREAKPOINTS) {
        console.log(`\n🔍 Testing Breakpoint: ${bp.name} (${bp.width}x${bp.height})...`);

        // 1. Committee Panel at Breakpoint
        const commContext = await browser.newContext({
            viewport: { width: bp.width, height: bp.height },
            locale: 'am-ET',
        });
        const commPage = await commContext.newPage();

        await safeGoto(commPage, `${BASE_URL}/committee/login`);
        await commPage.fill('input[type="email"], input[name="email"], input[id*="email"]', 'chair@idir.et');
        await commPage.fill('input[type="password"], input[name="password"], input[id*="password"]', 'password');
        await commPage.click('button[type="submit"]');
        await commPage.waitForURL(url => url.pathname.startsWith('/committee/'), { timeout: 15000 });
        await commPage.waitForTimeout(1000);

        // Dashboard
        await commPage.screenshot({ path: `screenshots/responsive/${bp.name}_01_committee_dashboard.png`, fullPage: false });

        // Member List Table
        await safeGoto(commPage, `${BASE_URL}/committee/1/members`);
        await commPage.screenshot({ path: `screenshots/responsive/${bp.name}_02_member_list.png`, fullPage: false });

        // Contribution Ledger Table
        await safeGoto(commPage, `${BASE_URL}/committee/1/contributions`);
        await commPage.screenshot({ path: `screenshots/responsive/${bp.name}_03_contribution_ledger.png`, fullPage: false });

        await commContext.close();

        // 2. Member Portal at Breakpoint
        const memContext = await browser.newContext({
            viewport: { width: bp.width, height: bp.height },
            locale: 'am-ET',
        });
        const memPage = await memContext.newPage();

        await safeGoto(memPage, `${BASE_URL}/member/login`);
        await memPage.fill('input[id="login"], input[name="login"]', '0922445566');
        await memPage.fill('input[id="password"], input[name="password"]', 'password');
        await memPage.click('button[type="submit"]');
        await memPage.waitForURL(url => url.pathname.includes('/member'), { timeout: 15000 });
        await memPage.waitForTimeout(500);

        // Member Dashboard
        await memPage.screenshot({ path: `screenshots/responsive/${bp.name}_04_member_dashboard.png`, fullPage: false });

        // Member File Claim Form
        await safeGoto(memPage, `${BASE_URL}/member/claims/create`);
        await memPage.screenshot({ path: `screenshots/responsive/${bp.name}_05_member_file_claim.png`, fullPage: false });

        // Printable Receipt
        await safeGoto(memPage, `${BASE_URL}/member/receipt/1`);
        await memPage.screenshot({ path: `screenshots/responsive/${bp.name}_06_printable_receipt.png`, fullPage: false });

        await memContext.close();
        console.log(`✔ Breakpoint ${bp.name} completed successfully.`);
    }

    await browser.close();
    console.log('\n🎉 Responsive verification complete across all 6 breakpoints!');
}

runResponsiveVerification();
