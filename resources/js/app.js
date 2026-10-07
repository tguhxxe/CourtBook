import './booking';
import './payments';
const dialog = document.querySelector('#confirm-dialog');
let pendingAction = null;
let origin = null;
function ask(message, label, action, trigger) {
    if (!dialog) return;
    origin = trigger || document.activeElement;
    pendingAction = action;
    dialog.querySelector('#confirm-description').textContent = message;
    dialog.querySelector('[data-dialog-accept]').textContent = label;
    dialog.showModal(); dialog.querySelector('[data-dialog-cancel]').focus();
}
dialog?.querySelector('[data-dialog-cancel]').addEventListener('click', () => dialog.close());
dialog?.querySelector('[data-dialog-accept]').addEventListener('click', () => { const action = pendingAction; pendingAction = null; dialog.close(); action?.(); });
dialog?.addEventListener('close', () => { pendingAction = null; origin?.focus(); });
document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => {
    if (form.dataset.approved) return;
    event.preventDefault(); ask(form.dataset.confirm, form.dataset.actionLabel || 'Lanjutkan', () => { form.dataset.approved = 'true'; form.requestSubmit(); }, event.submitter);
}));
document.querySelectorAll('form').forEach(form => form.addEventListener('submit', event => {
    if (event.defaultPrevented || event.isComposing) return;
    if (form.dataset.busy) { event.preventDefault(); return; }
    form.dataset.busy = 'true'; form.dataset.dirty = ''; form.setAttribute('aria-busy', 'true');
    form.querySelectorAll('button[type="submit"],button:not([type])').forEach(button => { button.disabled = true; });
}));
window.addEventListener('pageshow', () => document.querySelectorAll('form[data-busy]').forEach(form => { delete form.dataset.busy; form.removeAttribute('aria-busy'); form.querySelectorAll('button').forEach(button => button.disabled = false); }));
document.querySelectorAll('[data-password]').forEach(button => button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.password); const show = input.type === 'password'; input.type = show ? 'text' : 'password';
    button.textContent = show ? 'Sembunyikan' : 'Tampilkan'; button.setAttribute('aria-pressed', String(show));
    button.setAttribute('aria-label', `${show ? 'Sembunyikan' : 'Tampilkan'} ${document.querySelector(`label[for="${input.id}"]`)?.textContent.toLowerCase()}`);
}));
document.querySelector('[data-error-summary]')?.focus();
document.querySelectorAll('input[name="q"]').forEach(input => {
    const button = document.createElement('button'); button.type = 'button'; button.className = 'search-clear'; button.textContent = '\u00d7'; button.setAttribute('aria-label', 'Hapus pencarian'); input.parentElement.append(button);
    const sync = () => button.hidden = !input.value; sync(); input.addEventListener('input', sync);
    button.addEventListener('click', () => { input.value = ''; sync(); input.focus(); input.form.requestSubmit(); });
});
document.querySelectorAll('form[data-dirty]').forEach(form => form.addEventListener('input', () => form.dataset.dirty = 'true'));
window.addEventListener('beforeunload', event => { if (document.querySelector('form[data-dirty="true"]')) { event.preventDefault(); event.returnValue = ''; } });
document.addEventListener('click', event => {
    const link = event.target.closest('a[href]'); const form = document.querySelector('form[data-dirty="true"]');
    if (!link || !form || event.ctrlKey || event.metaKey || link.hash || link.target === '_blank') return;
    event.preventDefault(); ask('Perubahan belum disimpan. Tinggalkan halaman dan buang perubahan?', 'Tinggalkan halaman', () => { form.dataset.dirty = ''; window.location.assign(link.href); }, link);
});
