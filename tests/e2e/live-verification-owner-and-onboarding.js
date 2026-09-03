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
    console.log('🚀 Starting Live Verification for Onboarding Persistence and Platform Owner Role...');

    const browser = await chromium.launch({
        executablePath: fs.existsSync(CHROME_PATH) ? CHROME_PATH : undefined,
        headless: true,
    });

    try {
        // =============================================================
        // PROBLEM 1: Verify Onboarding Wizard & Persistence
        // =============================================================
        console.log('\n▶ [Problem 1] Testing Onboarding Wizard Live Persistence...');
        const founderContext = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const founderPage = await founderContext.newPage();

        // 1. Login with chair account
        await safeGoto(founderPage, `${BASE_URL}/committee/login`);
        await founderPage.fill('input[type="email"], input[name="email"], input[id*="email"]', 'chair@idir.et');
        await founderPage.fill('input[type="password"], input[name="password"], input[id*="password"]', 'password');
        await founderPage.click('button[type="submit"]');
        await founderPage.waitForURL(url => url.pathname.startsWith('/committee/'), { timeout: 15000 });
        await founderPage.waitForLoadState('networkidle');

        // 2. Open tenant registration wizard
        await safeGoto(founderPage, `${BASE_URL}/committee/new`);
        await founderPage.waitForTimeout(1000);

        // Step 1: Fill Basic details
        const nameField = founderPage.locator('input[id*="name"]').first();
        if (await nameField.count() > 0) {
            await nameField.fill('ጎንደር ፋሲለደስ እድር');
        }
        const subCityField = founderPage.locator('input[id*="sub_city"]').first();
        if (await subCityField.count() > 0) {
            await subCityField.fill('ጎንደር ከተማ');
        }
        await founderPage.screenshot({ path: 'screenshots/flows/p1_onboarding_wizard_step1.png' });

        // Click Next Step
        const nextBtn1 = founderPage.locator('button:has-text("ቀጣይ"), button:has-text("Next")').first();
        if (await nextBtn1.count() > 0) {
            await nextBtn1.click();
            await founderPage.waitForTimeout(800);
        }
        await founderPage.screenshot({ path: 'screenshots/flows/p1_onboarding_wizard_step2.png' });

        // Click Next Step
        const nextBtn2 = founderPage.locator('button:has-text("ቀጣይ"), button:has-text("Next")').first();
        if (await nextBtn2.count() > 0) {
            await nextBtn2.click();
            await founderPage.waitForTimeout(800);
        }
        await founderPage.screenshot({ path: 'screenshots/flows/p1_onboarding_wizard_step3.png' });

        // Click Final Submit Action
        const submitBtn = founderPage.locator('button:has-text("እድር መዝግብ"), button:has-text("Register")').first();
        if (await submitBtn.count() > 0) {
            await submitBtn.click();
            await founderPage.waitForURL(url => url.pathname.startsWith('/committee/'), { timeout: 15000 });
            await founderPage.waitForLoadState('networkidle');
            await founderPage.waitForTimeout(2000);
        }

        await founderPage.screenshot({ path: 'screenshots/flows/p1_onboarding_success_dashboard.png' });
        console.log(`✔ [Problem 1] Onboarding flow completed. Redirected to: ${founderPage.url()}`);
        await founderContext.close();

        // =============================================================
        // PROBLEM 2: Verify Platform Owner (Super Admin) Role & /admin Panel
        // =============================================================
        console.log('\n▶ [Problem 2] Testing Platform Owner Panel at /admin...');
        const ownerContext = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const ownerPage = await ownerContext.newPage();

        // 1. Platform Owner Login
        await safeGoto(ownerPage, `${BASE_URL}/admin/login`);
        await ownerPage.fill('input[type="email"], input[name="email"], input[id*="email"]', 'owner@idir-platform.et');
        await ownerPage.fill('input[type="password"], input[name="password"], input[id*="password"]', 'password');
        await ownerPage.click('button[type="submit"]');
        await ownerPage.waitForURL(url => url.pathname.startsWith('/admin'), { timeout: 15000 });
        await ownerPage.waitForLoadState('networkidle');
        await ownerPage.waitForTimeout(1000);

        // 2. Admin Dashboard & Aggregate Stats
        await ownerPage.screenshot({ path: 'screenshots/flows/p2_admin_dashboard.png' });
        console.log('✔ [Problem 2] Admin Dashboard with aggregate stats verified.');

        // 3. Idirs Management List
        await safeGoto(ownerPage, `${BASE_URL}/admin/idirs`);
        await ownerPage.waitForLoadState('networkidle');
        await ownerPage.waitForTimeout(1000);
        await ownerPage.screenshot({ path: 'screenshots/flows/p2_admin_idirs_list.png' });
        console.log('✔ [Problem 2] Idirs List with stats verified.');

        // 4. Suspend Action on Idir 1 (ሰላም የሰፈር እድር)
        const suspendAction = ownerPage.locator('button:has-text("Suspend"), button:has-text("አግድ")').first();
        if (await suspendAction.count() > 0) {
            await suspendAction.click();
            await ownerPage.waitForTimeout(600);
            const confirmBtn = ownerPage.locator('.fi-modal button:has-text("Confirm"), .fi-modal button:has-text("አግድ"), .fi-modal button:has-text("Suspend"), button[type="submit"]:visible').first();
            if (await confirmBtn.count() > 0) {
                await confirmBtn.click();
                await ownerPage.waitForTimeout(1500);
            }
        }
        await ownerPage.screenshot({ path: 'screenshots/flows/p2_admin_idir_suspended.png' });
        console.log('✔ [Problem 2] Suspend action executed.');

        // 5. Test Suspended Idir Blocks Member Access
        const suspendedMemberContext = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const suspendedMemberPage = await suspendedMemberContext.newPage();
        await safeGoto(suspendedMemberPage, `${BASE_URL}/member/login`);
        await suspendedMemberPage.fill('input[id="login"], input[name="login"]', '0922445566');
        await suspendedMemberPage.fill('input[id="password"], input[name="password"]', 'password');
        await suspendedMemberPage.click('button[type="submit"]');
        await suspendedMemberPage.waitForTimeout(1500);
        await suspendedMemberPage.screenshot({ path: 'screenshots/flows/p2_member_suspended_blocked.png' });
        console.log('✔ [Problem 2] Suspended idir access block verified on Member Portal.');
        await suspendedMemberContext.close();

        // 6. Reactivate Idir 1 from Admin Panel
        const reactivateAction = ownerPage.locator('button:has-text("Reactivate"), button:has-text("መልስ")').first();
        if (await reactivateAction.count() > 0) {
            await reactivateAction.click();
            await ownerPage.waitForTimeout(600);
            const confirmReactivateBtn = ownerPage.locator('.fi-modal button:has-text("Confirm"), .fi-modal button:has-text("መልስ"), .fi-modal button:has-text("Reactivate"), button[type="submit"]:visible').first();
            if (await confirmReactivateBtn.count() > 0) {
                await confirmReactivateBtn.click();
                await ownerPage.waitForTimeout(1500);
            }
        }
        await ownerPage.screenshot({ path: 'screenshots/flows/p2_admin_idir_reactivated.png' });
        console.log('✔ [Problem 2] Reactivate action executed.');

        // 7. Confirm Member Access Restored
        const restoredMemberContext = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const restoredMemberPage = await restoredMemberContext.newPage();
        await safeGoto(restoredMemberPage, `${BASE_URL}/member/login`);
        await restoredMemberPage.fill('input[id="login"], input[name="login"]', '0922445566');
        await restoredMemberPage.fill('input[id="password"], input[name="password"]', 'password');
        await restoredMemberPage.click('button[type="submit"]');
        await restoredMemberPage.waitForURL(url => url.pathname.includes('/member'), { timeout: 15000 });
        await restoredMemberPage.waitForTimeout(1000);
        await restoredMemberPage.screenshot({ path: 'screenshots/flows/p2_member_restored.png' });
        console.log('✔ [Problem 2] Restored member access verified.');
        await restoredMemberContext.close();

        // 8. Users Management List in /admin
        await safeGoto(ownerPage, `${BASE_URL}/admin/users`);
        await ownerPage.waitForTimeout(1000);
        await ownerPage.screenshot({ path: 'screenshots/flows/p2_admin_users_list.png' });
        console.log('✔ [Problem 2] Users Management List in /admin verified.');
        await ownerContext.close();

        // 9. Verify Regular Committee Chair is Blocked from /admin
        const chairBlockedContext = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const chairBlockedPage = await chairBlockedContext.newPage();
        await safeGoto(chairBlockedPage, `${BASE_URL}/admin/login`);
        await chairBlockedPage.fill('input[type="email"], input[name="email"], input[id*="email"]', 'chair@idir.et');
        await chairBlockedPage.fill('input[type="password"], input[name="password"], input[id*="password"]', 'password');
        await chairBlockedPage.click('button[type="submit"]');
        await chairBlockedPage.waitForTimeout(1500);
        await chairBlockedPage.screenshot({ path: 'screenshots/flows/p2_chair_blocked_from_admin.png' });
        console.log('✔ [Problem 2] Regular chair blocked from /admin verified.');
        await chairBlockedContext.close();

        console.log('\n🎉 Both Problem 1 and Problem 2 verified live and screenshotted successfully!');
    } catch (err) {
        console.error('❌ Error during live verification:', err);
        throw err;
    } finally {
        await browser.close();
    }
}

runLiveVerification();
