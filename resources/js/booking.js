const form = document.querySelector('[data-booking]');
if (form) {
    const money = value => 'Rp' + new Intl.NumberFormat('id-ID').format(value);
    const update = () => {
        const total = Number(form.dataset.rate) * Number(form.querySelector('[name=duration]').value);
        const dp = Math.ceil(total * Number(form.dataset.dp) / 100);
        form.querySelector('[data-price-total]').textContent = money(total); form.querySelector('[data-price-dp]').textContent = money(dp); form.querySelector('[data-price-balance]').textContent = money(total - dp);
        document.querySelectorAll('[data-slot]').forEach(button => { const selected = button.dataset.slot === form.querySelector('[name=hour]').value; button.classList.toggle('selected', selected); button.setAttribute('aria-pressed', String(selected)); });
    };
    form.addEventListener('change', update);
    document.querySelectorAll('[data-slot]').forEach(button => button.addEventListener('click', () => { form.querySelector('[name=hour]').value = button.dataset.slot; update(); })); update();
}
