const button = document.querySelector('[data-pay-token]');
button?.addEventListener('click', () => {
    const message = document.querySelector('#payment-message');
    if (!window.snap) { message.textContent = 'Jendela pembayaran belum dapat dimuat. Periksa koneksi dan coba kembali.'; return; }
    button.disabled = true;
    const finish = () => { message.textContent = 'Periksa status booking untuk hasil verifikasi pembayaran dari server.'; button.disabled = false; };
    window.snap.pay(button.dataset.payToken, { onSuccess: finish, onPending: finish, onError: () => { message.textContent = 'Pembayaran belum selesai. Kembali ke booking dan periksa status sebelum mencoba kembali.'; button.disabled = false; }, onClose: finish });
});
