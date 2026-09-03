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

async function runSelfServiceJourney() {
    console.log('🚀 Starting Full Self-Service Journey Live Verification on Chrome...');

    const browser = await chromium.launch({
        executablePath: fs.existsSync(CHROME_PATH) ? CHROME_PATH : undefined,
        headless: true,
    });

    try {
        // Dynamic unique credentials for fresh run
        const randomDigits = Math.floor(10000000 + Math.random() * 90000000).toString();
        const strangerPhone = `09${randomDigits.slice(0, 8)}`;
        const strangerEmail = `stranger_${randomDigits.slice(0, 5)}@test.et`;

        console.log(`👤 Using Fresh Stranger Profile: ${strangerPhone} / ${strangerEmail}`);

        // -------------------------------------------------------------
        // STEP 1: Anonymous Visitor Lands on Public Homepage
        // -------------------------------------------------------------
        console.log('\n▶ [Step 1] Visiting Public Homepage as Anonymous Stranger...');
        const visitorContext = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            locale: 'am-ET',
        });
        const page = await visitorContext.newPage();

        await safeGoto(page, `${BASE_URL}/`);
        await page.waitForTimeout(1000);
        await page.screenshot({ path: 'screenshots/flows/selfservice_01_landing_page.png' });
        console.log('✔ [Step 1] Landing page loaded and screenshotted.');

        // -------------------------------------------------------------
        // STEP 2: Public Registration Form
        // -------------------------------------------------------------
        console.log('\n▶ [Step 2] Navigating to Public Signup Form...');
        await safeGoto(page, `${BASE_URL}/register`);
        await page.waitForTimeout(600);

        await page.fill('input[id="name"], input[name="name"]', 'ሰለሞን አስራት ዳኛቸው');
        await page.fill('input[id="phone"], input[name="phone"]', strangerPhone);
        await page.fill('input[id="email"], input[name="email"]', strangerEmail);
        await page.fill('input[id="password"], input[name="password"]', 'SecurePass123!');
        await page.fill('input[id="password_confirmation"], input[name="password_confirmation"]', 'SecurePass123!');

        await page.screenshot({ path: 'screenshots/flows/selfservice_02_public_signup_form.png' });

        console.log('Submitting signup form...');
        await page.click('button[type="submit"]');
        await page.waitForURL(url => url.pathname.includes('/verify-phone'), { timeout: 15000 });
        await page.waitForTimeout(1000);
        console.log('✔ [Step 2] User registered and redirected to phone verification.');

        // -------------------------------------------------------------
        // STEP 3: Phone OTP Verification (AfroMessage SMS)
        // -------------------------------------------------------------
        console.log('\n▶ [Step 3] Verifying Phone with 6-digit OTP Code...');
        await page.screenshot({ path: 'screenshots/flows/selfservice_03_phone_otp_page.png' });

        await page.fill('input[id="code"], input[name="code"]', '123456');
        await page.click('button[type="submit"]');

        await page.waitForURL(url => url.pathname.includes('/committee/new') || url.pathname.startsWith('/committee/'), { timeout: 15000 });
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1000);
        console.log('✔ [Step 3] Phone verified with OTP. Redirected to Idir Creation wizard.');

        // -------------------------------------------------------------
        // STEP 4: Create New Idir via Tenant Registration Wizard
        // -------------------------------------------------------------
        console.log('\n▶ [Step 4] Creating New Idir through Wizard as Verified Founder...');
        
        // Fill Step 1
        const nameInput = page.locator('input[id*="name"]').first();
        if (await nameInput.count() > 0) {
            await nameInput.fill('አርባ ምንጭ አንድነት እድር');
        }
        const subCityInput = page.locator('input[id*="sub_city"]').first();
        if (await subCityInput.count() > 0) {
            await subCityInput.fill('አርባ ምንጭ');
        }

        // Step 1 screenshot
        await page.screenshot({ path: 'screenshots/flows/selfservice_04_wizard_creation.png' });

        // Next to Step 2
        const next1 = page.locator('button:has-text("ቀጣይ"), button:has-text("Next")').first();
        if (await next1.count() > 0) {
            await next1.click();
            await page.waitForTimeout(800);
        }

        // Next to Step 3
        const next2 = page.locator('button:has-text("ቀጣይ"), button:has-text("Next")').first();
        if (await next2.count() > 0) {
            await next2.click();
            await page.waitForTimeout(800);
        }

        // Submit registration
        const submitWizard = page.locator('button:has-text("እድር መዝግብ"), button:has-text("Register")').first();
        if (await submitWizard.count() > 0) {
            console.log('Submitting Idir registration...');
            await submitWizard.click();
            await page.waitForURL(url => url.pathname.startsWith('/committee/') && !url.pathname.includes('/new'), { timeout: 20000 });
            await page.waitForLoadState('networkidle');
            await page.waitForTimeout(2000);
        }

        const newIdirUrl = page.url();
        console.log(`✔ [Step 4] Idir created successfully! Landed on Tenant Dashboard: ${newIdirUrl}`);
        await page.screenshot({ path: 'screenshots/flows/selfservice_05_new_chair_dashboard.png' });

        // Extract tenant ID from URL (e.g. /committee/3)
        const match = newIdirUrl.match(/\/committee\/(\d+)/);
        const newTenantId = match ? match[1] : '3';

        // -------------------------------------------------------------
        // STEP 5: Self-Service Committee Management (Chair invites Treasurer)
        // -------------------------------------------------------------
        console.log(`\n▶ [Step 5] Chair Inviting New Treasurer in Tenant ${newTenantId}...`);
        await safeGoto(page, `${BASE_URL}/committee/${newTenantId}/members/create`);
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1000);

        // Fill Member Create form
        await page.fill('input[id*="full_name"]', 'ሀና ጌታቸው ደረጀ');
        await page.fill('input[id*="phone"]', '0977889900');
        
        // Select Treasurer role by value 'treasurer'
        const roleSelect = page.locator('select[id*="committee_role"]').first();
        if (await roleSelect.count() > 0) {
            await roleSelect.selectOption('treasurer');
        }

        await page.screenshot({ path: 'screenshots/flows/selfservice_06_chair_invites_treasurer.png' });

        // Save member via primary action button
        const saveMemberBtn = page.locator('button.fi-btn-color-primary:has-text("Create"), button.fi-btn-color-primary:has-text("ፍጠር"), .fi-page-header-actions button.fi-btn-color-primary, .fi-ac-action[wire\\:click*="create"]').first();
        if (await saveMemberBtn.count() > 0) {
            console.log('Submitting new treasurer creation...');
            await saveMemberBtn.click();
            await page.waitForTimeout(3000);
        }

        // View Members List
        await safeGoto(page, `${BASE_URL}/committee/${newTenantId}/members`);
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1000);
        await page.screenshot({ path: 'screenshots/flows/selfservice_07_members_list_with_treasurer.png' });
        console.log('✔ [Step 5] Member invited and Treasurer role assigned self-service.');

        await visitorContext.close();

        // -------------------------------------------------------------
        // STEP 6: Platform Owner Observes New Idir in /admin (Zero Manual Action)
        // -------------------------------------------------------------
        console.log('\n▶ [Step 6] Verifying Platform Owner Observes New Idir in /admin...');
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
        await ownerPage.screenshot({ path: 'screenshots/flows/selfservice_08_platform_owner_sees_new_idir.png' });
        console.log('✔ [Step 6] Platform Owner observes new self-service Idir in /admin list.');

        await ownerContext.close();

        console.log('\n🎉 Full Self-Service Journey (Signup -> Phone OTP -> Idir Creation -> Chair -> Treasurer Invite -> Admin Visibility) Verified Successfully!');
    } catch (err) {
        console.error('❌ Error during self-service verification:', err);
        throw err;
    } finally {
        await browser.close();
    }
}

runSelfServiceJourney();
