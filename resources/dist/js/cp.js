/* Foorintodev Form Import — bouton « Export dédoublonné » sur la page d'un
   formulaire (CP → Formulaires → <formulaire>), à côté du bouton natif
   « Exporter les soumissions ». Le CP est une app Vue/Inertia (navigation sans
   rechargement, re-rendus) : on observe le DOM et on (ré)insère le bouton quand
   il manque. Élément créé en vrai DOM → non nettoyé par Vue. */
(function () {
    const BTN_ID = 'fi-dedupe-export-btn';
    const NATIVE_LABELS = ['exporter les soumissions', 'export submissions', 'soumissionen exportieren'];

    function formPage() {
        // …/cp/forms/{handle} (pas les sous-pages : soumission, édition, blueprint…)
        const m = window.location.pathname.match(/^(.*)\/forms\/([^/]+)\/?$/);
        return m && m[2] !== 'create' ? { cpBase: m[1], handle: m[2] } : null;
    }

    function inject() {
        const page = formPage();
        if (!page || document.getElementById(BTN_ID)) return;

        const native = Array.from(document.querySelectorAll('button')).find(
            (b) => NATIVE_LABELS.includes(b.textContent.trim().toLowerCase())
        );
        if (!native) return;

        const link = document.createElement('a');
        link.id = BTN_ID;
        link.className = 'fi-dedupe-btn';
        link.href = page.cpBase + '/form-import/export?form=' + encodeURIComponent(page.handle);
        link.innerHTML =
            '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>' +
            '<span>Export dédoublonné</span>';

        native.insertAdjacentElement('beforebegin', link);
    }

    new MutationObserver(inject).observe(document.documentElement, { childList: true, subtree: true });
    document.addEventListener('DOMContentLoaded', inject);
})();
