/* ════════════════════════════════════════════════════
   public/js/superadmin/settings.js
   ════════════════════════════════════════════════════ */
'use strict';

/* ─── TABS ─── */
function switchTab(name) {
    // Désactiver tous les boutons
    document.querySelectorAll('.sa-tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Cacher tous les panneaux
    document.querySelectorAll('.sa-tab-panel').forEach(panel => {
        panel.classList.remove('active');
    });

    // Activer le bouton cliqué
    const activeBtn = document.getElementById('btn-' + name);
    if (activeBtn) activeBtn.classList.add('active');

    // Afficher le panneau correspondant
    const activePanel = document.getElementById('tab-' + name);
    if (activePanel) activePanel.classList.add('active');

    // Fermer le formulaire si on quitte l'onglet clients
    if (name !== 'clients') closeForm();
}

/* ─── FILTRE CLIENTS (côté navigateur) ─── */
function filterClients(query) {
    const q = query.trim().toLowerCase();
    document.querySelectorAll('#client-list .sa-client-row').forEach(row => {
        const name   = row.dataset.name   || '';
        const email  = row.dataset.email  || '';
        const tenant = row.dataset.tenant || '';
        const match  = !q || name.includes(q) || email.includes(q) || tenant.includes(q);
        row.style.display = match ? '' : 'none';
    });
}

/* ─── SÉLECTION CLIENT ─── */
function selectClient(id, name, email, company) {
    document.querySelectorAll('.sa-client-row').forEach(r => r.classList.remove('selected'));

    const clicked = document.querySelector(`.sa-client-row[onclick*="selectClient(${id},"]`);
    if (clicked) clicked.classList.add('selected');

    document.getElementById('ef-avatar').textContent  = name.substring(0, 2).toUpperCase();
    document.getElementById('ef-name').textContent    = name;
    document.getElementById('ef-company').textContent = company || email;

    document.getElementById('access-form').action =
        `/superadmin/settings/clients/${id}/access`;

    clearFields();
    hideAlert();

    const form = document.getElementById('edit-form');
    form.classList.add('visible');
    form.setAttribute('aria-hidden', 'false');
    form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/* ─── FERMER FORMULAIRE ─── */
function closeForm() {
    document.querySelectorAll('.sa-client-row').forEach(r => r.classList.remove('selected'));
    const form = document.getElementById('edit-form');
    if (form) {
        form.classList.remove('visible');
        form.setAttribute('aria-hidden', 'true');
    }
    clearFields();
    hideAlert();
}

/* ─── VALIDATION AVANT SOUMISSION ─── */
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('access-form');
    if (!form) return;

    form.addEventListener('submit', e => {
        const email        = document.getElementById('field-email').value.trim();
        const emailConfirm = document.getElementById('field-email-confirm').value.trim();
        const pwd          = document.getElementById('field-pwd').value;
        const pwdConfirm   = document.getElementById('field-pwd-confirm').value;

        if (!email && !pwd) {
            e.preventDefault();
            showAlert('Veuillez remplir au moins un champ (email ou mot de passe).', 'error');
            return;
        }
        if (email) {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                showAlert("L'adresse email n'est pas valide.", 'error');
                return;
            }
            if (email !== emailConfirm) {
                e.preventDefault();
                showAlert('Les adresses email ne correspondent pas.', 'error');
                return;
            }
        }
        if (pwd) {
            if (pwd.length < 8) {
                e.preventDefault();
                showAlert('Le mot de passe doit contenir au moins 8 caractères.', 'error');
                return;
            }
            if (pwd !== pwdConfirm) {
                e.preventDefault();
                showAlert('Les mots de passe ne correspondent pas.', 'error');
                return;
            }
        }
    });
});

/* ─── HELPERS ─── */
function clearFields() {
    ['field-email', 'field-email-confirm', 'field-pwd', 'field-pwd-confirm'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
}

function showAlert(msg, type) {
    const box = document.getElementById('js-alert');
    if (!box) return;
    document.getElementById('js-alert-msg').textContent = msg;
    box.className = 'sa-js-alert ' + type;
}

function hideAlert() {
    const box = document.getElementById('js-alert');
    if (box) box.className = 'sa-js-alert';
}

function togglePwd(fieldId) {
    const input = document.getElementById(fieldId);
    if (input) input.type = input.type === 'password' ? 'text' : 'password';
}
