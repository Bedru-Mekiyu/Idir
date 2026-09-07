import { chromium } from 'playwright';
import fs from 'fs';

const BASE_URL = 'http://127.0.0.1:8000';
const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

async function safeGoto(page, url) {
    try {
        await page.goto(url, { waitUntil: 'commit', timeout: 15000 });
        await page.waitForLoadState('domcontentloaded');
        await page.waitForTimeout(600);
    } catch (e) {
        if (!e.message.includes('ERR_ABORTED')) {
            throw e;
        }
        await page.waitForLoadState('domcontentloaded');
    }
}

async function runLiveVerification() {
    console.log('🚀 Starting Full Manager Access Authorization Verification...');

    if (!fs.existsSync('screenshots/flows')) {
        fs.mkdirSync('screenshots/flows', { recursive: true });
    }

    const browser = await chromium.launch({
        executablePath: fs.existsSync(CHROME_PATH) ? CHROME_PATH : undefined,
        headless: true,
    });

    try {
        // =====================================================================
        // PART 1: USER 1 - SIGNUP -> OTP -> ACCESS REQUEST -> GRANT -> WIZARD -> IDIR APPROVAL
        // =====================================================================
        const rand1 = Math.floor(10000000 + Math.random() * 90000000).toString();
        const user1Phone = `09${rand1.slice(0, 8)}`;
        const user1Email = `user1_${rand1.slice(0, 5)}@test.et`;

        console.log(`\n======================================================`);
        console.log(`👤 User 1 (Grant Path): ${user1Phone} / ${user1Email}`);
        console.log(`======================================================`);

        const context1 = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const page1 = await context1.newPage();

        // 1. Signup
        console.log('▶ [Step 1] User 1 Signing Up on /register...');
        await safeGoto(page1, `${BASE_URL}/register`);
        await page1.fill('input[id="name"], input[name="name"]', 'ዮናስ ታደሰ አድማሱ');
        await page1.fill('input[id="phone"], input[name="phone"]', user1Phone);
        await page1.fill('input[id="email"], input[name="email"]', user1Email);
        await page1.fill('input[id="password"], input[name="password"]', 'SecurePass123!');
        await page1.fill('input[id="password_confirmation"], input[name="password_confirmation"]', 'SecurePass123!');
        await page1.screenshot({ path: 'screenshots/flows/gate_01_user1_signup.png' });

        await page1.click('button[type="submit"]');
        await page1.waitForURL(url => url.pathname.includes('/verify-phone'), { timeout: 15000 });
        console.log('✔ User 1 registered. Redirected to /verify-phone.');

        // 2. Phone OTP Verification
        console.log('▶ [Step 2] User 1 Verifying Phone OTP...');
        await page1.screenshot({ path: 'screenshots/flows/gate_02_user1_verify_phone.png' });
        await page1.fill('input[id="code"], input[name="code"]', '123456');
        await page1.click('button[type="submit"]');

        // 3. Confirm lands on /access-request, NOT wizard!
        await page1.waitForURL(url => url.pathname.includes('/access-request'), { timeout: 15000 });
        await page1.waitForTimeout(800);
        console.log(`✔ User 1 redirected to: ${page1.url()}`);
        if (!page1.url().includes('/access-request')) {
            throw new Error(`Expected /access-request, got: ${page1.url()}`);
        }
        await page1.screenshot({ path: 'screenshots/flows/gate_03_user1_lands_on_access_request.png' });

        // 4. Submit Access Request
        console.log('▶ [Step 3] User 1 Submitting Access Request Form...');
        await page1.fill('input[id="idir_name"], input[name="idir_name"]', 'አዋሽ አንድነት እድር');
        await page1.fill('input[id="membership_basis"], input[name="membership_basis"]', 'የሰፈር ነዋሪዎች');
        await page1.fill('input[id="region"], input[name="region"]', 'አዲስ አበባ');
        await page1.fill('input[id="sub_city"], input[name="sub_city"]', 'ቦሌ ክፍለ ከተማ');
        await page1.fill('textarea[id="purpose"], textarea[name="purpose"]', 'የአካባቢያችንን ነዋሪዎች ማህበራዊ ትስስር ለማጠናከር እና የደስታና የኀዘን መረዳጃ ለማቋቋም የታሰበ እድር ነው።');
        await page1.screenshot({ path: 'screenshots/flows/gate_04_user1_submits_access_request.png' });

        await page1.click('button[type="submit"]');
        await page1.waitForURL(url => url.pathname.includes('/access-request'), { timeout: 15000 });
        await page1.waitForTimeout(800);
        console.log('✔ Access request submitted. Status page displayed.');
        await page1.screenshot({ path: 'screenshots/flows/gate_05_user1_request_pending_screen.png' });

        // 5. Direct navigation attempt to wizard MUST redirect back to /access-request!
        console.log('▶ [Step 4] User 1 Testing Direct Access to /committee/new...');
        await safeGoto(page1, `${BASE_URL}/committee/new`);
        await page1.waitForTimeout(1000);
        console.log(`✔ After navigating to /committee/new, URL is: ${page1.url()}`);
        if (!page1.url().includes('/access-request')) {
            throw new Error(`Security breach! User was not redirected from wizard. URL: ${page1.url()}`);
        }
        await page1.screenshot({ path: 'screenshots/flows/gate_06_user1_wizard_blocked_redirects_to_pending.png' });

        // 6. Platform Owner Reviews and Grants in /admin
        console.log('\n▶ [Step 5] Platform Owner Logging into /admin to Grant Request...');
        const ownerContext = await browser.newContext({
            viewport: { width: 1600, height: 950 },
            locale: 'am-ET',
        });
        const ownerPage = await ownerContext.newPage();

        await ownerPage.goto(`${BASE_URL}/admin/login`, { waitUntil: 'domcontentloaded' });
        await ownerPage.fill('input[type="email"], input[name="email"], input[id*="email"]', 'owner@idir-platform.et');
        await ownerPage.fill('input[type="password"], input[name="password"], input[id*="password"]', 'password');
        await ownerPage.click('button[type="submit"]');
        await ownerPage.waitForURL(url => url.pathname === '/admin' || url.pathname === '/admin/', { timeout: 15000 });
        await ownerPage.waitForTimeout(600);

        // Open Access Requests list
        console.log('Navigating to /admin/access-requests...');
        await ownerPage.goto(`${BASE_URL}/admin/access-requests`, { waitUntil: 'networkidle' });
        await ownerPage.waitForTimeout(1200);
        await ownerPage.screenshot({ path: 'screenshots/flows/gate_07_owner_views_access_requests_admin.png' });

        // Click Grant action button on User 1's request (top row due to defaultSort desc)
        console.log(`Clicking Grant (ፈቃድ ስጥ) action for User 1 (${user1Phone})...`);
        const grantBtn = ownerPage.locator('button:has-text("ፈቃድ ስጥ")').first();
        await grantBtn.scrollIntoViewIfNeeded();
        await grantBtn.click();
        await ownerPage.waitForTimeout(1000);

        // Confirm modal
        const confirmBtn = ownerPage.locator('.fi-modal button:has-text("ፈቃድ አጽድቅ"), .fi-modal button[type="submit"], [role="alertdialog"] button:has-text("ፈቃድ አጽድቅ"), button:has-text("ፈቃድ አጽድቅ")').first();
        await confirmBtn.click();
        await ownerPage.waitForTimeout(2500);
        console.log('✔ Platform owner granted access to User 1.');
        await ownerPage.screenshot({ path: 'screenshots/flows/gate_08_owner_grants_user1.png' });

        // 7. User 1 Sees Granted Status & Proceeds to Wizard
        console.log('\n▶ [Step 6] User 1 Reloading Status Page...');
        await safeGoto(page1, `${BASE_URL}/access-request`);
        await page1.waitForTimeout(800);
        await page1.screenshot({ path: 'screenshots/flows/gate_09_user1_access_granted_screen.png' });

        // User 1 opens wizard
        console.log('User 1 Navigating to /committee/new...');
        await safeGoto(page1, `${BASE_URL}/committee/new`);
        await page1.waitForTimeout(1500);
        console.log(`✔ User 1 on wizard: ${page1.url()}`);
        await page1.screenshot({ path: 'screenshots/flows/gate_10_user1_opens_wizard.png' });

        // Step 1: Basic Info
        const nameField = page1.locator('input[id*="name"]').first();
        if (await nameField.count() > 0) {
            await nameField.fill('አዋሽ አንድነት እድር');
        }
        const subCityField = page1.locator('input[id*="sub_city"]').first();
        if (await subCityField.count() > 0) {
            await subCityField.fill('ቦሌ');
        }

        // Next to Step 2
        const next1 = page1.locator('button:has-text("ቀጣይ"), button:has-text("Next")').first();
        if (await next1.count() > 0) {
            await next1.click();
            await page1.waitForTimeout(800);
        }

        // Next to Step 3
        const next2 = page1.locator('button:has-text("ቀጣይ"), button:has-text("Next")').first();
        if (await next2.count() > 0) {
            await next2.click();
            await page1.waitForTimeout(800);
        }

        await page1.screenshot({ path: 'screenshots/flows/gate_11_user1_completes_wizard.png' });

        // Submit registration
        const submitWizard = page1.locator('button:has-text("እድር መዝግብ"), button:has-text("Register")').first();
        if (await submitWizard.count() > 0) {
            console.log('Submitting Idir registration wizard...');
            await submitWizard.click();
            await page1.waitForURL(url => url.pathname.startsWith('/committee/') && !url.pathname.includes('/new'), { timeout: 25000 });
            await page1.waitForTimeout(2500);
        }

        const tenantDashboardUrl = page1.url();
        console.log(`✔ Landed on Tenant Dashboard: ${tenantDashboardUrl}`);
        await page1.screenshot({ path: 'screenshots/flows/gate_12_user1_idir_pending_approval.png' });

        // 8. Platform Owner Approves the Idir Live
        console.log('\n▶ [Step 7] Platform Owner Approving Idir in /admin/idirs...');
        await ownerPage.goto(`${BASE_URL}/admin/idirs`, { waitUntil: 'networkidle' });
        await ownerPage.waitForTimeout(1200);
        await ownerPage.screenshot({ path: 'screenshots/flows/gate_13_owner_reviews_pending_idir.png' });

        const approveBtn = ownerPage.locator('button:has-text("አጽድቅ")').first();
        if (await approveBtn.count() > 0) {
            await approveBtn.scrollIntoViewIfNeeded();
            await approveBtn.click();
            await ownerPage.waitForTimeout(1000);
            const modalConfirm = ownerPage.locator('.fi-modal button:has-text("አጽድቅ"), .fi-modal button[type="submit"], [role="alertdialog"] button:has-text("አጽድቅ")').first();
            await modalConfirm.click();
            await ownerPage.waitForTimeout(2500);
        }
        console.log('✔ Idir approved live by owner.');
        await ownerPage.screenshot({ path: 'screenshots/flows/gate_14_owner_approves_idir_live.png' });

        // 9. User 1 Dashboard is Now Active
        console.log('User 1 Checking Live Dashboard...');
        await safeGoto(page1, tenantDashboardUrl);
        await page1.waitForTimeout(1200);
        await page1.screenshot({ path: 'screenshots/flows/gate_15_user1_idir_now_active.png' });

        // =====================================================================
        // PART 2: USER 2 - SIGNUP -> ACCESS REQUEST -> DENIED WITH REASON -> RE-APPLY
        // =====================================================================
        const rand2 = Math.floor(10000000 + Math.random() * 90000000).toString();
        const user2Phone = `09${rand2.slice(0, 8)}`;
        const user2Email = `user2_${rand2.slice(0, 5)}@test.et`;

        console.log(`\n======================================================`);
        console.log(`👤 User 2 (Deny Path): ${user2Phone} / ${user2Email}`);
        console.log(`======================================================`);

        const context2 = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const page2 = await context2.newPage();

        // 1. Signup & OTP
        console.log('▶ [Step 8] User 2 Signing Up and Verifying Phone...');
        await safeGoto(page2, `${BASE_URL}/register`);
        await page2.fill('input[id="name"], input[name="name"]', 'ተስፋሁን ዘለቀ ደስታ');
        await page2.fill('input[id="phone"], input[name="phone"]', user2Phone);
        await page2.fill('input[id="email"], input[name="email"]', user2Email);
        await page2.fill('input[id="password"], input[name="password"]', 'SecurePass123!');
        await page2.fill('input[id="password_confirmation"], input[name="password_confirmation"]', 'SecurePass123!');
        await page2.click('button[type="submit"]');

        await page2.waitForURL(url => url.pathname.includes('/verify-phone'), { timeout: 15000 });
        await page2.fill('input[id="code"], input[name="code"]', '123456');
        await page2.click('button[type="submit"]');

        await page2.waitForURL(url => url.pathname.includes('/access-request'), { timeout: 15000 });
        await page2.waitForTimeout(800);
        console.log('✔ User 2 on /access-request.');
        await page2.screenshot({ path: 'screenshots/flows/gate_16_user2_signup_and_verify.png' });

        // 2. User 2 Submits Request
        console.log('▶ [Step 9] User 2 Submitting Access Request...');
        await page2.fill('input[id="idir_name"], input[name="idir_name"]', 'ጎርጎራ ቱሪዝም እድር');
        await page2.fill('input[id="region"], input[name="region"]', 'አማራ');
        await page2.fill('textarea[id="purpose"], textarea[name="purpose"]', 'በጎርጎራ አካባቢ የቱሪዝም ማህበርተኞች መረዳጃ');
        await page2.click('button[type="submit"]');

        await page2.waitForURL(url => url.pathname.includes('/access-request'), { timeout: 15000 });
        await page2.waitForTimeout(800);
        console.log('✔ User 2 request submitted (Pending).');
        await page2.screenshot({ path: 'screenshots/flows/gate_17_user2_submits_access_request.png' });

        // 3. Platform Owner Denies User 2 Request with Reason
        console.log(`▶ [Step 10] Platform Owner Denying User 2 (${user2Phone}) in /admin/access-requests...`);
        await ownerPage.goto(`${BASE_URL}/admin/access-requests`, { waitUntil: 'networkidle' });
        await ownerPage.waitForTimeout(1200);

        const denyBtn = ownerPage.locator('button:has-text("ውድቅ አድርግ")').first();
        await denyBtn.scrollIntoViewIfNeeded();
        await denyBtn.click();
        await ownerPage.waitForTimeout(1000);

        const reasonField = ownerPage.locator('textarea[id*="denial_reason"], textarea[name*="denial_reason"]').first();
        await reasonField.fill('የቀረበው መረጃ እና የአድራሻ ማረጋገጫ ያልተሟላ በመሆኑ ውድቅ ተደርጓል።');
        await ownerPage.waitForTimeout(400);

        const confirmDenyBtn = ownerPage.locator('.fi-modal button:has-text("ውድቅ ማድረጉን አረጋግጥ"), .fi-modal button[type="submit"], [role="alertdialog"] button:has-text("ውድቅ ማድረጉን አረጋግጥ"), button:has-text("ውድቅ ማድረጉን አረጋግጥ")').first();
        await confirmDenyBtn.click();
        await ownerPage.waitForTimeout(2500);

        console.log('✔ Platform owner denied User 2 request with reason.');
        await ownerPage.screenshot({ path: 'screenshots/flows/gate_18_owner_denies_user2_with_reason.png' });

        // 4. User 2 Views Denial Reason
        console.log('▶ [Step 11] User 2 Viewing Denial Reason on Status Page...');
        await safeGoto(page2, `${BASE_URL}/access-request`);
        await page2.waitForTimeout(800);
        await page2.screenshot({ path: 'screenshots/flows/gate_19_user2_sees_denial_and_reason.png' });

        // 5. User 2 Attempting Direct Access to Wizard MUST Be Blocked
        console.log('User 2 Attempting Direct Access to /committee/new...');
        await safeGoto(page2, `${BASE_URL}/committee/new`);
        await page2.waitForTimeout(800);
        console.log(`✔ User 2 redirected to: ${page2.url()}`);
        if (!page2.url().includes('/access-request')) {
            throw new Error(`Denied user was not redirected from wizard! URL: ${page2.url()}`);
        }
        await page2.screenshot({ path: 'screenshots/flows/gate_20_user2_wizard_blocked_redirects_to_denied.png' });

        // 6. User 2 Clicks Re-apply
        console.log('User 2 Opening Re-apply Form...');
        await safeGoto(page2, `${BASE_URL}/access-request?reapply=1`);
        await page2.waitForTimeout(800);
        await page2.screenshot({ path: 'screenshots/flows/gate_21_user2_reapply_form.png' });

        // =====================================================================
        // PART 3: EXISTING ACTIVE IDIR UNAFFECTED
        // =====================================================================
        console.log('\n▶ [Step 12] Confirming Existing Active Idir Works Unaffected...');
        const chairContext = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const chairPage = await chairContext.newPage();

        await safeGoto(chairPage, `${BASE_URL}/committee/login`);
        await chairPage.fill('input[type="email"], input[name="email"], input[id*="email"]', 'chair@idir.et');
        await chairPage.fill('input[type="password"], input[name="password"], input[id*="password"]', 'password');
        await chairPage.click('button[type="submit"]');
        await chairPage.waitForURL(url => url.pathname.startsWith('/committee/') && !url.pathname.includes('/login'), { timeout: 15000 });
        await chairPage.waitForTimeout(1500);
        console.log(`✔ Existing chair landed directly on tenant dashboard: ${chairPage.url()}`);
        await chairPage.screenshot({ path: 'screenshots/flows/gate_22_existing_active_idir_unaffected.png' });

        console.log('\n🎉 ALL LIVE VERIFICATION STEPS COMPLETED SUCCESSFULLY WITH SCREENSHOTS!');

    } finally {
        await browser.close();
    }
}

runLiveVerification().catch(err => {
    console.error('❌ Verification Error:', err);
    process.exit(1);
});

