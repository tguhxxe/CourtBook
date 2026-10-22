import { chromium, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { mkdir, writeFile } from 'node:fs/promises';

const base = process.env.COURTBOOK_BASE_URL || 'http://127.0.0.1:8000';
const output = process.env.COURTBOOK_BROWSER_OUTPUT || 'output/home-refresh/browser';
await mkdir(output, { recursive: true });
const browser = await chromium.launch({ executablePath: process.env.COURTBOOK_BROWSER_EXECUTABLE || undefined });
const context = await browser.newContext({ locale: 'id-ID', timezoneId: 'Asia/Jakarta', viewport: { width: 1440, height: 1000 } });
const page = await context.newPage();
const report = { base, checks: [], accessibility: [], errors: [] };
page.on('pageerror', error => report.errors.push(error.message));
const check = async (name, action) => { await action(); report.checks.push(name); console.log('PASS', name); };
const noOverflow = async () => expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual((await page.viewportSize()).width);
const audit = async name => {
    const result = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    report.accessibility.push({ name, violations: result.violations });
    expect(result.violations).toEqual([]);
};

try {
    await page.goto(base);
    await page.evaluate(() => document.fonts.ready);
    await check('Homepage has one main heading, loaded images and local typefaces', async () => {
        await expect(page.getByRole('heading', { level: 1 })).toHaveCount(1);
        expect(await page.evaluate(() => document.fonts.check('700 30px "Barlow Condensed"') && document.fonts.check('500 16px Manrope'))).toBe(true);
        expect(await page.locator('.home-hero-image').evaluate(image => image.complete && image.naturalWidth > 0)).toBe(true);
        await page.screenshot({ path: `${output}/home-desktop-clean.png`, fullPage: true });
    });
    await audit('Desktop homepage');
    await check('Primary action opens the existing court catalogue', async () => {
        await page.locator('.home-hero-actions').getByRole('link', { name: 'Cari lapangan', exact: true }).click();
        await expect(page).toHaveURL(`${base}/courts`);
        expect(await page.locator('body').evaluate(body => body.classList.contains('home-page'))).toBe(false);
        await page.goto(base);
        await page.locator('.home-schedule-link').first().click();
        await expect(page).toHaveURL(/\/courts\/\d+$/);
        await page.goto(base);
    });
    await check('FAQ opens with keyboard and keeps payment rules readable', async () => {
        const faq = page.locator('.home-faq-list details').nth(1);
        await faq.locator('summary').focus();
        await page.keyboard.press('Enter');
        await expect(faq).toHaveAttribute('open', '');
        await expect(faq).toContainText('pembayaran terverifikasi');
        await audit('FAQ expanded');
        await page.keyboard.press('Enter');
        await expect(faq).not.toHaveAttribute('open', '');
    });
    for (const width of [320, 390, 768, 1024, 1440]) {
        await page.setViewportSize({ width, height: 900 });
        await page.goto(base);
        await page.evaluate(() => document.fonts.ready);
        await check(`No horizontal page overflow at ${width}px`, noOverflow);
        if (width === 390) await page.screenshot({ path: `${output}/home-mobile-clean.png`, fullPage: true });
    }
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(base);
    await check('Mobile navigation opens with keyboard and reaches booking instructions', async () => {
        const menu = page.locator('.home-mobile-menu');
        await menu.locator('summary').focus();
        await page.keyboard.press('Enter');
        await expect(menu).toHaveAttribute('open', '');
        await expect(menu.getByRole('link', { name: 'Buat akun' })).toBeVisible();
        await noOverflow();
        await audit('Mobile navigation expanded');
        await page.screenshot({ path: `${output}/home-mobile-menu.png`, fullPage: false });
        await menu.getByRole('link', { name: 'Cara booking', exact: true }).click();
        await expect(page).toHaveURL(`${base}/#cara-booking`);
        await expect(page.locator('#cara-booking')).toBeInViewport();
    });
    await check('Long court names wrap without hiding the schedule action', async () => {
        await page.goto(base);
        await page.locator('.home-court h3 a').first().evaluate(link => { link.textContent = 'LapanganTenisDenganNamaPanjang'.repeat(5); });
        await noOverflow();
        await expect(page.locator('.home-schedule-link').first()).toBeVisible();
    });
    await check('Reduced motion disables image transitions', async () => {
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.goto(base);
        expect(await page.locator('.home-court-image').first().evaluate(image => getComputedStyle(image).transitionDuration)).toBe('0s');
    });
    await page.emulateMedia({ reducedMotion: 'no-preference' });
    await page.setViewportSize({ width: 1440, height: 1000 });
    for (const account of ['pelanggan', 'admin']) {
        await check(`Homepage account menu respects the ${account} role`, async () => {
            await page.goto(`${base}/login`);
            await page.getByLabel('Email', { exact: true }).fill(`${account}@courtbook.test`);
            await page.getByLabel('Kata sandi', { exact: true }).fill('CourtBook123!');
            await page.getByRole('button', { name: 'Masuk', exact: false }).click();
            await page.waitForURL(account === 'admin' ? '**/admin' : '**/bookings');
            await page.goto(base);
            await page.locator('.home-account-menu summary').click();
            const nav = page.locator('.home-account-menu nav');
            await expect(nav.getByRole('link', { name: 'Booking saya' })).toBeVisible();
            await expect(nav.getByRole('link', { name: 'Admin', exact: true })).toHaveCount(account === 'admin' ? 1 : 0);
            await audit(`Homepage ${account} menu`);
            await nav.getByRole('button', { name: 'Keluar', exact: true }).click();
        });
    }
    expect(report.errors).toEqual([]);
    console.log(JSON.stringify({ passed: true, checks: report.checks.length, axeScans: report.accessibility.length, errors: report.errors.length }));
} finally {
    await writeFile(`${output}/home-report.json`, JSON.stringify(report, null, 2));
    await browser.close();
}
