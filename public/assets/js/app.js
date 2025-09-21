// Basic JavaScript functionality for the inventory app
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('กรุณากรอกข้อมูลให้ครบถ้วน');
            }
        });
    });
});

// Live search: if there's an input with name=q on the page, wire up a debounce fetch
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="q"]');
    if (!searchInput) return;

    let timeout = null;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => doSearch(searchInput.value), 300);
    });

    function doSearch(q) {
        const orderBy = (document.querySelector('#orderBy') || {}).value || 'id';
        const orderDir = (document.querySelector('#orderDir') || {}).value || 'ASC';
        fetch(`search.php?q=${encodeURIComponent(q)}&orderBy=${encodeURIComponent(orderBy)}&orderDir=${encodeURIComponent(orderDir)}`)
            .then(res => res.json())
            .then(data => renderResults(data.items || []))
            .catch(err => console.error('Search error', err));
    }

    // When user changes sort selects, re-run the search immediately
    const orderBySelect = document.querySelector('#orderBy');
    const orderDirSelect = document.querySelector('#orderDir');
    [orderBySelect, orderDirSelect].forEach(el => {
        if (!el) return;
        el.addEventListener('change', function(){
            doSearch(searchInput.value.trim());
        });
    });

    function renderResults(items) {
        const tbody = document.querySelector('#products-table-body');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!items.length) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="7" class="text-center text-muted py-4">ไม่พบรายการ</td>';
            tbody.appendChild(tr);
            return;
        }
        items.forEach((p, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${i+1}</td>
                <td><span class="badge bg-secondary-subtle text-secondary">${escapeHtml(p.product_code)}</span></td>
                <td>${escapeHtml(p.name)}</td>
                <td class="text-center">${p.quantity <= 0 ? '<span class="badge bg-danger">หมด</span>' : '<span class="badge bg-success-subtle text-success">'+p.quantity+'</span>'}</td>
                <td class="text-end">฿${Number(p.price).toFixed(2)}</td>
                <td>${p.expiry_date || '-'}</td>
                <td class="text-end">
                  <a href="edit_product.php?id=${p.id}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a>
                  <a href="delete_product.php?id=${p.id}" class="btn btn-sm btn-outline-danger" onclick="return confirm('ลบสินค้า?');"><i class="bi bi-trash"></i></a>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function escapeHtml(s){
        if (s === null || s === undefined) return '';
        return String(s).replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m]; });
    }
});