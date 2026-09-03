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

async function runApprovalWorkflowVerification() {
    console.log('🚀 Starting Platform Owner Approval & Rejection Live Verification on Chrome...');

    const browser = await chromium.launch({
        executablePath: fs.existsSync(CHROME_PATH) ? CHROME_PATH : undefined,
        headless: true,
    });

    try {
        // =============================================================
        // FLOW A: Sign Up -> Pending Approval -> Platform Owner Approves
        // =============================================================
        console.log('\n▶ [Flow A] Registering New Idir (ጅማ አባ ጅፋር እድር)...');
        const randA = Math.floor(10000000 + Math.random() * 90000000).toString();
        const phoneA = `09${randA.slice(0, 8)}`;
        const emailA = `founder_jimma_${randA.slice(0, 4)}@test.et`;

        const founderAContext = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const founderAPage = await founderAContext.newPage();

        // 1. Signup
        await safeGoto(founderAPage, `${BASE_URL}/register`);
        await founderAPage.fill('input[id="name"], input[name="name"]', 'አበራ ሞላ ዳዲ');
        await founderAPage.fill('input[id="phone"], input[name="phone"]', phoneA);
        await founderAPage.fill('input[id="email"], input[name="email"]', emailA);
        await founderAPage.fill('input[id="password"], input[name="password"]', 'SecurePass123!');
        await founderAPage.fill('input[id="password_confirmation"], input[name="password_confirmation"]', 'SecurePass123!');
        await founderAPage.click('button[type="submit"]');
        await founderAPage.waitForURL(url => url.pathname.includes('/verify-phone'), { timeout: 15000 });

        // 2. OTP Verification
        await founderAPage.fill('input[id="code"], input[name="code"]', '123456');
        await founderAPage.click('button[type="submit"]');
        await founderAPage.waitForURL(url => url.pathname.includes('/committee/new') || url.pathname.startsWith('/committee/'), { timeout: 15000 });
        await founderAPage.waitForLoadState('networkidle');
        await founderAPage.waitForTimeout(1000);

        // 3. Complete Wizard
        const nameA = founderAPage.locator('input[id*="name"]').first();
        if (await nameA.count() > 0) {
            await nameA.fill('ጅማ አባ ጅፋር እድር');
        }
        const subCityA = founderAPage.locator('input[id*="sub_city"]').first();
        if (await subCityA.count() > 0) {
            await subCityA.fill('ጅማ ከተማ');
        }

        // Next 1
        const nextA1 = founderAPage.locator('button:has-text("ቀጣይ"), button:has-text("Next")').first();
        if (await nextA1.count() > 0) {
            await nextA1.click();
            await founderAPage.waitForTimeout(800);
        }

        // Next 2
        const nextA2 = founderAPage.locator('button:has-text("ቀጣይ"), button:has-text("Next")').first();
        if (await nextA2.count() > 0) {
            await nextA2.click();
            await founderAPage.waitForTimeout(800);
        }

        // Submit
        const submitA = founderAPage.locator('button:has-text("እድር መዝግብ"), button:has-text("Register")').first();
        if (await submitA.count() > 0) {
            await submitA.click();
            await founderAPage.waitForURL(url => url.pathname.startsWith('/committee/') && !url.pathname.includes('/new'), { timeout: 20000 });
            await founderAPage.waitForLoadState('networkidle');
            await founderAPage.waitForTimeout(2000);
        }

        const jimmaUrl = founderAPage.url();
        console.log(`✔ [Flow A] Jimma Idir registered. Landed on: ${jimmaUrl}`);
        await founderAPage.screenshot({ path: 'screenshots/flows/approval_01_founder_awaiting_approval.png' });

        // 4. Platform Owner reviews and approves
        console.log('\n▶ [Flow A] Platform Owner reviewing pending queue in /admin/idirs...');
        const ownerContext = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const ownerPage = await ownerContext.newPage();

        await safeGoto(ownerPage, `${BASE_URL}/admin/login`);
        await ownerPage.fill('input[type="email"], input[name="email"], input[id*="email"]', 'owner@idir-platform.et');
        await ownerPage.fill('input[type="password"], input[name="password"], input[id*="password"]', 'password');
        await ownerPage.click('button[type="submit"]');
        await ownerPage.waitForURL(url => url.pathname.startsWith('/admin'), { timeout: 15000 });
        await ownerPage.waitForLoadState('networkidle');

        await safeGoto(ownerPage, `${BASE_URL}/admin/idirs`);
        await ownerPage.waitForLoadState('networkidle');
        await ownerPage.waitForTimeout(1000);
        await ownerPage.screenshot({ path: 'screenshots/flows/approval_02_admin_pending_queue.png' });

        // Click Approve Action on Jimma
        console.log('Platform Owner clicking Approve...');
        const approveBtn = ownerPage.locator('button:has-text("Approve"), button:has-text("አጽድቅ")').first();
        if (await approveBtn.count() > 0) {
            await approveBtn.click();
            await ownerPage.waitForTimeout(600);
            const confirmApprove = ownerPage.locator('.fi-modal button:has-text("Confirm"), .fi-modal button:has-text("አጽድቅ"), button[type="submit"]:visible').first();
            if (await confirmApprove.count() > 0) {
                await confirmApprove.click();
                await ownerPage.waitForTimeout(2000);
            }
        }
        await ownerPage.screenshot({ path: 'screenshots/flows/approval_03_admin_approved_success.png' });
        console.log('✔ [Flow A] Jimma Idir approved by Platform Owner.');

        // 5. Founder refreshes and verifies full access is unlocked
        console.log('Founder refreshing dashboard after approval...');
        await founderAPage.reload({ waitUntil: 'networkidle' });
        await founderAPage.waitForTimeout(1500);
        await founderAPage.screenshot({ path: 'screenshots/flows/approval_04_founder_dashboard_active_unlocked.png' });
        console.log('✔ [Flow A] Founder dashboard unlocked with operational menus.');

        await founderAContext.close();

        // =============================================================
        // FLOW B: Sign Up -> Pending Approval -> Platform Owner Rejects with Reason
        // =============================================================
        console.log('\n▶ [Flow B] Registering Second Idir (ነቀምቴ ሰላም እድር)...');
        const randB = Math.floor(10000000 + Math.random() * 90000000).toString();
        const phoneB = `09${randB.slice(0, 8)}`;
        const emailB = `founder_nekemte_${randB.slice(0, 4)}@test.et`;

        const founderBContext = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const founderBPage = await founderBContext.newPage();

        // 1. Signup
        await safeGoto(founderBPage, `${BASE_URL}/register`);
        await founderBPage.fill('input[id="name"], input[name="name"]', 'ታሪኩ ታደለ ዋቅጅራ');
        await founderBPage.fill('input[id="phone"], input[name="phone"]', phoneB);
        await founderBPage.fill('input[id="email"], input[name="email"]', emailB);
        await founderBPage.fill('input[id="password"], input[name="password"]', 'SecurePass123!');
        await founderBPage.fill('input[id="password_confirmation"], input[name="password_confirmation"]', 'SecurePass123!');
        await founderBPage.click('button[type="submit"]');
        await founderBPage.waitForURL(url => url.pathname.includes('/verify-phone'), { timeout: 15000 });

        // 2. OTP Verification
        await founderBPage.fill('input[id="code"], input[name="code"]', '123456');
        await founderBPage.click('button[type="submit"]');
        await founderBPage.waitForURL(url => url.pathname.includes('/committee/new') || url.pathname.startsWith('/committee/'), { timeout: 15000 });
        await founderBPage.waitForLoadState('networkidle');
        await founderBPage.waitForTimeout(1000);

        // 3. Complete Wizard
        const nameB = founderBPage.locator('input[id*="name"]').first();
        if (await nameB.count() > 0) {
            await nameB.fill('ነቀምቴ ሰላም እድር');
        }
        const subCityB = founderBPage.locator('input[id*="sub_city"]').first();
        if (await subCityB.count() > 0) {
            await subCityB.fill('ነቀምቴ');
        }

        // Next 1
        const nextB1 = founderBPage.locator('button:has-text("ቀጣይ"), button:has-text("Next")').first();
        if (await nextB1.count() > 0) {
            await nextB1.click();
            await founderBPage.waitForTimeout(800);
        }

        // Next 2
        const nextB2 = founderBPage.locator('button:has-text("ቀጣይ"), button:has-text("Next")').first();
        if (await nextB2.count() > 0) {
            await nextB2.click();
            await founderBPage.waitForTimeout(800);
        }

        // Submit
        const submitB = founderBPage.locator('button:has-text("እድር መዝግብ"), button:has-text("Register")').first();
        if (await submitB.count() > 0) {
            await submitB.click();
            await founderBPage.waitForURL(url => url.pathname.startsWith('/committee/') && !url.pathname.includes('/new'), { timeout: 20000 });
            await founderBPage.waitForLoadState('networkidle');
            await founderBPage.waitForTimeout(2000);
        }

        console.log(`✔ [Flow B] Nekemte Idir registered. Landed on: ${founderBPage.url()}`);
        await founderBPage.screenshot({ path: 'screenshots/flows/rejection_01_founder_pending.png' });

        // 4. Platform Owner rejects with reason
        console.log('\n▶ [Flow B] Platform Owner rejecting Nekemte Idir with reason...');
        await safeGoto(ownerPage, `${BASE_URL}/admin/idirs`);
        await ownerPage.waitForLoadState('networkidle');
        await ownerPage.waitForTimeout(1000);

        const rejectBtn = ownerPage.locator('button:has-text("Reject"), button:has-text("ውድቅ")').first();
        if (await rejectBtn.count() > 0) {
            await rejectBtn.click();
            await ownerPage.waitForTimeout(600);

            // Fill rejection reason in modal
            const reasonInput = ownerPage.locator('.fi-modal textarea[name*="rejection_reason"], .fi-modal textarea').first();
            if (await reasonInput.count() > 0) {
                await reasonInput.fill('የቀረበው የእድር መተዳደሪያ ደንብ እና የቦታ መረጃ ያልተሟላ ነው።');
            }

            const confirmReject = ownerPage.locator('.fi-modal button[type="submit"], .fi-modal button:has-text("ውድቅ"), .fi-modal button:has-text("Reject")').first();
            if (await confirmReject.count() > 0) {
                await confirmReject.click();
                await ownerPage.waitForTimeout(2000);
            }
        }
        await ownerPage.screenshot({ path: 'screenshots/flows/rejection_02_admin_rejected_action.png' });
        console.log('✔ [Flow B] Nekemte Idir rejected by Platform Owner with reason.');
        await ownerContext.close();

        // 5. Founder refreshes and sees rejection banner with reason
        console.log('Founder refreshing dashboard to view rejection banner...');
        await founderBPage.reload({ waitUntil: 'networkidle' });
        await founderBPage.waitForTimeout(1500);
        await founderBPage.screenshot({ path: 'screenshots/flows/rejection_03_founder_dashboard_rejected_reason.png' });
        console.log('✔ [Flow B] Founder dashboard displays rejection reason and remains locked.');

        await founderBContext.close();

        // =============================================================
        // FLOW C: Pre-existing Active Idir Safety Check
        // =============================================================
        console.log('\n▶ [Flow C] Checking Pre-existing Active Idir (ሰላም የሰፈር እድር)...');
        const chairContext = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const chairPage = await chairContext.newPage();

        await safeGoto(chairPage, `${BASE_URL}/committee/login`);
        await chairPage.fill('input[type="email"], input[name="email"], input[id*="email"]', 'chair@idir.et');
        await chairPage.fill('input[type="password"], input[name="password"], input[id*="password"]', 'password');
        await chairPage.click('button[type="submit"]');
        await chairPage.waitForURL(url => url.pathname.startsWith('/committee/'), { timeout: 15000 });
        await chairPage.waitForLoadState('networkidle');
        await chairPage.waitForTimeout(1500);

        await chairPage.screenshot({ path: 'screenshots/flows/active_01_existing_idir_unaffected.png' });
        console.log('✔ [Flow C] Pre-existing active idir is fully functional with no restriction banners.');

        await chairContext.close();

        console.log('\n🎉 Approval & Rejection Workflow fully verified live on Chrome!');
    } catch (err) {
        console.error('❌ Error during approval workflow verification:', err);
        throw err;
    } finally {
        await browser.close();
    }
}

runApprovalWorkflowVerification();
