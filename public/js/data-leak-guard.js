(function () {
    // Une seule exécution par chargement de page (évite le double-init si le script est inclus 2x)
    if (window.__dataLeakGuardInitialized) return;
    window.__dataLeakGuardInitialized = true;

    const currentUser = window.currentUser;

    // Pas de contexte tenant exploitable (non connecté, ou SuperAdmin qui voit du cross-tenant légitimement)
    if (!currentUser || !currentUser.tenantId || currentUser.isSuperAdmin) {
        return;
    }

    let alertSent = false;

    function checkRow(row) {
        if (row.hasAttribute('data-leak-checked')) return;
        row.setAttribute('data-leak-checked', 'true');

        const rowTenantId = row.getAttribute('data-row-tenant-id');

        // Pas d'attribut = hors périmètre de la détection (cf. limites, point 8 de la demande)
        if (rowTenantId === null || rowTenantId === '') return;

        if (String(rowTenantId) !== String(currentUser.tenantId)) {
            row.style.display = 'none';
            row.setAttribute('data-leak-hidden', 'true');
            reportLeak(rowTenantId);
        }
    }

    function scan(root) {
        root.querySelectorAll('[data-row-tenant-id]').forEach(checkRow);
    }

    function reportLeak(leakedTenantId) {
        if (alertSent) return;
        alertSent = true;

        const hiddenRows = document.querySelectorAll('[data-leak-hidden="true"]');
        const rowIds = Array.from(hiddenRows)
            .map(r => r.dataset.employeeId || r.dataset.rowId || r.id || null)
            .filter(Boolean);

        const tokenEl = document.querySelector('meta[name="csrf-token"]');
        if (!tokenEl) return;

        fetch('/api/data-leak/report', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': tokenEl.content,
            },
            body: JSON.stringify({
                leaked_tenant_id: leakedTenantId,
                module: window.currentPageModule || null,
                rows_count: hiddenRows.length,
                row_ids: rowIds,
            }),
            keepalive: true,
        }).catch(() => { /* silencieux : ne jamais casser l'UI pour une alerte */ });
    }

    // Scan initial (rendu serveur)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => scan(document.body));
    } else {
        scan(document.body);
    }

    // Scan continu (lignes injectées en AJAX/JS, quel que soit le tableau ou la page)
    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType !== 1) return;
                if (node.matches && node.matches('[data-row-tenant-id]')) checkRow(node);
                if (node.querySelectorAll) scan(node);
            });
        }
    });
    observer.observe(document.body, { childList: true, subtree: true });
})();
