import { chromium } from 'playwright';

async function inspect() {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    await page.goto('http://127.0.0.1:8000/committee/login');
    await page.fill('input[type="email"], input[name="email"], input[id*="email"]', 'chair@idir.et');
    await page.fill('input[type="password"], input[name="password"], input[id*="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(u => u.pathname.startsWith('/committee/'));
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    
    await page.goto('http://127.0.0.1:8000/committee/new');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    
    console.log('--- Step 1 ---');
    console.log('Buttons:', await page.locator('button').allInnerTexts());
    
    // Fill step 1
    await page.locator('input[id*="name"]').first().fill('ደብረ ብርሃን ማህበር እድር');
    await page.locator('input[id*="sub_city"]').first().fill('ደብረ ብርሃን');
    
    // Click Next
    await page.locator('button:has-text("ቀጣይ"), button:has-text("Next")').first().click();
    await page.waitForTimeout(1000);
    
    console.log('--- Step 2 ---');
    console.log('Buttons:', await page.locator('button').allInnerTexts());
    
    // Click Next
    await page.locator('button:has-text("ቀጣይ"), button:has-text("Next")').first().click();
    await page.waitForTimeout(1000);
    
    console.log('--- Step 3 ---');
    console.log('Buttons:', await page.locator('button').allInnerTexts());
    
    // Click the submit button inside the wizard
    const submitBtn = page.locator('button:has-text("እድር መዝግብ"), button:has-text("Register")').first();
    console.log('Submit button found:', await submitBtn.count());
    if (await submitBtn.count() > 0) {
        console.log('Clicking submit button...');
        await submitBtn.click();
        await page.waitForTimeout(4000);
    }
    
    console.log('Current URL after submit:', page.url());
    await page.screenshot({ path: 'screenshots/flows/inspect_after_submit.png' });
    
    await browser.close();
}

inspect();
