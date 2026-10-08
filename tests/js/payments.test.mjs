import test from 'node:test';
import assert from 'node:assert/strict';
import vm from 'node:vm';
import { readFileSync } from 'node:fs';
const source = readFileSync(new URL('../../resources/js/payments.js', import.meta.url), 'utf8');

function setup(responses, { pay = true } = {}) {
    const handlers = {};
    const button = { dataset: { payToken: 'token' }, disabled: false, addEventListener: (name, fn) => handlers[name] = fn };
    const watcher = { dataset: { statusUrl: '/payments/1/reconcile' }, setAttribute() {}, removeAttribute() {} };
    const message = { textContent: '' };
    const retry = { hidden: true, addEventListener: (name, fn) => handlers.retry = fn };
    const timers = new Map();
    const requests = [];
    const destinations = [];
    let callbacks;
    let id = 0;
    vm.runInNewContext(source, {
        document: { hidden: false, addEventListener() {}, querySelector: selector => ({
            '[data-payment-watch]': watcher, '[data-pay-token]': pay ? button : null,
            '[data-payment-message]': message, '[data-payment-retry]': retry,
            'meta[name="csrf-token"]': { content: 'csrf' },
        })[selector] },
        window: { addEventListener() {}, snap: { pay: (token, options) => { callbacks = options; } }, location: { replace: url => destinations.push(url) } },
        AbortController,
        setTimeout: (fn, delay) => { timers.set(++id, { fn, delay }); return id; },
        clearTimeout: key => timers.delete(key),
        fetch: async (url, options) => {
            requests.push({ url, options });
            const next = responses.shift() ?? { status: 'pending', terminal: false, message: 'Pending' };
            if (next instanceof Error) throw next;
            if (next.http) return { status: next.http, ok: false };
            return { status: 200, ok: true, json: async () => next };
        },
    });
    return { button, message, retry, handlers, requests, destinations, timers,
        callbacks: () => callbacks,
        tick: async () => { const [key, timer] = [...timers].find(([, timer]) => timer.delay === 5000) ?? []; assert.ok(timer); timers.delete(key); timer.fn(); await flush(); },
    };
}
const flush = () => new Promise(resolve => setImmediate(resolve));
const success = { terminal: true, status: 'paid', message: 'Pembayaran berhasil', redirect_url: '/bookings/1' };

test('Snap success verifies with CSRF and redirects only after backend success', async () => {
    const ui = setup([{ terminal: false, message: 'Pending' }, success]);
    await flush();
    ui.handlers.click();
    assert.equal(ui.button.disabled, true);
    ui.callbacks().onSuccess({ transaction_status: 'settlement', redirect_url: 'https://untrusted.test' });
    await flush();
    assert.deepEqual(ui.destinations, ['/bookings/1']);
    assert.equal(ui.requests[1].options.method, 'POST');
    assert.equal(ui.requests[1].options.headers['X-CSRF-TOKEN'], 'csrf');
});

test('browser success cannot override backend pending', async () => {
    const ui = setup([]);
    await flush();
    ui.handlers.click();
    ui.callbacks().onSuccess({ transaction_status: 'settlement' });
    await flush();
    assert.deepEqual(ui.destinations, []);
    assert.equal(ui.message.textContent, 'Pending');
});

test('booking page automatically refreshes when payment is verified', async () => {
    const ui = setup([success], { pay: false });
    await flush();
    assert.deepEqual(ui.destinations, ['/bookings/1']);
});

test('network errors stop after bounded checks and allow retry', async () => {
    const ui = setup(Array.from({ length: 12 }, () => new Error('offline')));
    await flush();
    for (let i = 1; i < 12; i++) await ui.tick();
    assert.equal(ui.requests.length, 12);
    assert.equal(ui.retry.hidden, false);
    assert.equal(ui.timers.size, 0);
    assert.deepEqual(ui.destinations, []);
    ui.handlers.retry();
    await flush();
    assert.equal(ui.requests.length, 13);
});

test('session expiry stops checks without showing success', async () => {
    const ui = setup([{ http: 419 }]);
    await flush();
    assert.match(ui.message.textContent, /masuk kembali/);
    assert.equal(ui.retry.hidden, false);
    assert.equal(ui.timers.size, 0);
    assert.deepEqual(ui.destinations, []);
});


test('VA settlement after more than one minute still redirects automatically', async () => {
    const ui = setup([...Array.from({ length: 15 }, () => ({ terminal: false, message: 'Pending' })), success]);
    await flush();
    for (let i = 1; i < 16; i++) await ui.tick();
    assert.equal(ui.requests.length, 16);
    assert.deepEqual(ui.destinations, ['/bookings/1']);
});

test('long pending checks remain bounded and expose manual retry', async () => {
    const ui = setup([]);
    await flush();
    for (let i = 1; i < 180; i++) await ui.tick();
    assert.equal(ui.requests.length, 180);
    assert.equal(ui.retry.hidden, false);
    assert.equal(ui.timers.size, 0);
    assert.deepEqual(ui.destinations, []);
});
