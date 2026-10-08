const watcher = document.querySelector('[data-payment-watch]');
const button = document.querySelector('[data-pay-token]');
const message = document.querySelector('[data-payment-message]');
const retry = document.querySelector('[data-payment-retry]');
let timer;
let busy = false;
let stopped = false;
let checks = 0;
let failures = 0;
let controller;

function pause(text) {
    stopped = true;
    clearTimeout(timer);
    message.textContent = text;
    retry.hidden = false;
}

async function checkPayment() {
    if (!watcher || busy || stopped || document.hidden) return;
    clearTimeout(timer);
    busy = true;
    retry.hidden = true;
    watcher.setAttribute('aria-busy', 'true');
    controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 20000);
    try {
        const response = await fetch(watcher.dataset.statusUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            signal: controller.signal,
        });
        if ([401, 403, 419].includes(response.status)) {
            pause('Sesi atau akses Anda berubah. Muat ulang halaman dan masuk kembali untuk memeriksa pembayaran.');
            return;
        }
        if (response.status === 429) {
            pause('Pemeriksaan terlalu sering. Tunggu satu menit lalu periksa status lagi.');
            return;
        }
        if (!response.ok) throw new Error('Status unavailable');
        const result = await response.json();
        failures = 0;
        message.textContent = result.message;
        if (result.terminal) {
            stopped = true;
            if (button) button.disabled = true;
            // Destination and payment status come only from our authenticated backend.
            window.location.replace(result.redirect_url);
            return;
        }
    } catch {
        if (stopped) return;
        failures += 1;
        message.textContent = 'Status pembayaran belum dapat diperiksa. Akan dicoba lagi; jangan membayar ulang.';
    } finally {
        clearTimeout(timeout);
        busy = false;
        watcher.removeAttribute('aria-busy');
    }
    checks += 1;
    if (failures >= 12 || checks >= 180) {
        pause('Pemeriksaan otomatis dijeda. Jika sudah membayar, periksa status lagi; jangan membayar ulang.');
    } else if (!stopped) {
        timer = setTimeout(checkPayment, 5000);
    }
}

function resume() {
    checks = 0;
    failures = 0;
    stopped = false;
    checkPayment();
}

retry?.addEventListener('click', resume);
button?.addEventListener('click', () => {
    if (!window.snap) {
        message.textContent = 'Jendela pembayaran belum dapat dimuat. Periksa koneksi dan coba kembali.';
        return;
    }
    button.disabled = true;
    const finish = () => {
        button.disabled = false;
        message.textContent = 'Memverifikasi pembayaran…';
        resume();
    };
    try {
        window.snap.pay(button.dataset.payToken, {
            onSuccess: finish,
            onPending: finish,
            onError: finish,
            onClose: finish,
        });
    } catch {
        button.disabled = false;
        message.textContent = 'Jendela pembayaran gagal dibuka. Periksa status sebelum mencoba kembali.';
        resume();
    }
});
if (watcher) {
    checkPayment();
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && !stopped) checkPayment();
    });
    window.addEventListener('pagehide', () => {
        stopped = true;
        clearTimeout(timer);
        controller?.abort();
    });
    window.addEventListener('pageshow', event => {
        if (event.persisted) resume();
    });
}
