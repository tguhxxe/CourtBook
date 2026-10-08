const form = document.querySelector('[data-booking]');
if (form) {
    const money = value => 'Rp' + new Intl.NumberFormat('id-ID').format(value);
    const slots = [...form.querySelectorAll('[data-slot]')];
    const originalLabels = new Map(slots.map(input => [input, input.closest('label').querySelector('[data-slot-label]').textContent]));
    const dateInput = document.querySelector('[data-schedule-date] [name=date]');
    const update = () => {
        const dateChanged = dateInput && dateInput.value !== form.querySelector('[name=date]').value;
        const duration = Number(form.querySelector('[name=duration]').value);
        const total = Number(form.dataset.rate) * duration;
        const dp = Math.ceil(total * Number(form.dataset.dp) / 100);
        form.querySelector('[data-price-total]').textContent = money(total);
        form.querySelector('[data-price-dp]').textContent = money(dp);
        form.querySelector('[data-price-balance]').textContent = money(total - dp);
        slots.forEach(input => {
            const hour = Number(input.value);
            const available = hour + duration <= Number(form.dataset.closeHour) && Array.from({ length: duration }, (_, offset) => slots.find(slot => Number(slot.value) === hour + offset)).every(slot => slot && slot.dataset.unavailable === 'false');
            input.disabled = !available;
            if (!available) input.checked = false;
            input.closest('label').querySelector('[data-slot-label]').textContent = input.dataset.unavailable === 'true' ? originalLabels.get(input) : available ? (input.checked ? 'Dipilih' : 'Tersedia') : 'Durasi tidak muat';
        });
        const selected = slots.find(input => input.checked);
        const time = hour => String(hour).padStart(2, '0') + '.00';
        form.querySelector('[data-time-summary]').textContent = selected ? `${time(Number(selected.value))}–${time(Number(selected.value) + duration)} WIB · ${duration} jam` : 'Pilih salah satu jam yang tersedia untuk melanjutkan.';
        form.querySelector('[data-no-slots]').hidden = slots.some(input => !input.disabled);
        const next = form.querySelector('[data-booking-continue]');
        if (dateChanged) form.querySelector('[data-time-summary]').textContent = 'Tanggal diubah. Klik Lihat jadwal untuk menampilkan jam pada tanggal baru.';
        if (next) next.disabled = !selected || dateChanged;
    };
    dateInput?.addEventListener('change', update);
    form.addEventListener('change', update);
    window.addEventListener('pageshow', () => setTimeout(update, 0));
    update();
}
const paymentChoices = document.querySelector('[data-payment-choices]');
if (paymentChoices) {
    const update = () => {
        const choice = paymentChoices.querySelector('[name=kind]:checked');
        if (choice) paymentChoices.querySelector('[data-pay-amount]').textContent = 'Rp' + new Intl.NumberFormat('id-ID').format(Number(choice.dataset.amount));
    };
    paymentChoices.addEventListener('change', update);
    update();
}
