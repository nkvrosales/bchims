<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<div class="page-breadcrumb">
    <a href="<?php echo base_url('dashboard'); ?>">Dashboard</a>
    <span class="separator">/</span>
    <span class="current">Reports</span>
</div>

<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Reports</h1>
        </div>
    </div>
</div>

<!-- Date Scoping & Export Bar -->
<form method="GET" action="<?php echo base_url('reports'); ?>" id="reportFilterForm" class="fade-in-up mb-4" style="animation-delay: 0.03s;">
    <div class="db-search-bar" style="flex-wrap: wrap; gap: 8px;">
        <div class="db-search-field" style="flex: 0 0 210px;">
            <input type="date" id="start_date" name="start_date" class="db-search-input" value="<?php echo htmlspecialchars($start_date ?? ''); ?>" placeholder=" ">
            <label for="start_date">From Date</label>
        </div>
        <div class="db-search-field" style="flex: 0 0 210px;">
            <input type="date" id="end_date" name="end_date" class="db-search-input" value="<?php echo htmlspecialchars($end_date ?? ''); ?>" placeholder=" ">
            <label for="end_date">To Date</label>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="submit" class="btn-db-search">
                Search
            </button>
            <a href="<?php echo base_url('reports'); ?>" class="btn-db-clear">
                Clear
            </a>
        </div>
        <div class="d-flex align-items-center gap-2" style="margin-left: auto;">
            <div class="db-search-separator"></div>
            <button type="button" class="btn btn-success-custom" onclick="exportAllTables()">
                Download Reports
            </button>
        </div>
    </div>
</form>

<!-- KPI Cards -->
<div class="row g-3 mb-4 fade-in-up" style="animation-delay: 0.04s;">
    <div class="col-lg-6 col-xl-3 col-12">
        <div class="kpi-card h-100 d-flex justify-content-between align-items-center" style="border-radius: 8px; border-left: 6px solid #f59e0b; border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.25rem;">
            <div>
                <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #64748b !important;">Near Expiry Items</div>
                <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo $near_expiry_count; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-3 col-12">
        <div class="kpi-card h-100 d-flex justify-content-between align-items-center" style="border-radius: 8px; border-left: 6px solid #f97316; border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.25rem;">
            <div>
                <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #64748b !important;">Low Stock Items</div>
                <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo $low_stock_count; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-3 col-12">
        <div class="kpi-card h-100 d-flex justify-content-between align-items-center" style="border-radius: 8px; border-left: 6px solid var(--danger); border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.25rem;">
            <div>
                <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #64748b !important;">Out of Stock Items</div>
                <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo $no_stock_count; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-3 col-12">
        <div class="kpi-card h-100 d-flex justify-content-between align-items-center" style="border-radius: 8px; border-left: 6px solid var(--danger); border-top: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 1.25rem;">
            <div>
                <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #64748b !important;">Expired Items</div>
                <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo $expired_count; ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 fade-in-up" style="animation-delay: 0.05s;">
    <!-- Near Expiry Items -->
    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled"><span>Near Expiry Items</span></h5>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100 report-sortable" id="nearExpiryTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="cursor:pointer;" data-col="0">Item Name <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="1">Item Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="2">Inventory Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="3">Stock <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="4">Unit <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="5">Expiry <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($near_expiry_items)): ?>
                            <?php foreach ($near_expiry_items as $item): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_code']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['inventory_code'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo (int) ($item['quantity_on_hand'] ?? 0); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td>
                                    <td class="text-center"><?php echo !empty($item['expiration_date']) ? date('M d, Y', strtotime($item['expiration_date'])) : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <span>No items near expiry.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-3 fade-in-up" style="animation-delay: 0.1s;">
    <!-- Low Stock Items -->
    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled"><span>Low Stock Items</span></h5>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100 report-sortable" id="lowStockTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="cursor:pointer;" data-col="0">Item Name <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="1">Item Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="2">Inventory Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="3">Stock <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="4">Unit <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="5">Expiry <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($low_stock_items)): ?>
                            <?php foreach ($low_stock_items as $item): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_code']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['inventory_code'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo (int) ($item['quantity_on_hand'] ?? 0); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td>
                                    <td class="text-center"><?php echo !empty($item['expiration_date']) ? date('M d, Y', strtotime($item['expiration_date'])) : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <span>No low stock items.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-3 fade-in-up" style="animation-delay: 0.15s;">
    <!-- Out of Stock Items -->
    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled"><span>Out of Stock Items</span></h5>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100 report-sortable" id="outOfStockTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="cursor:pointer;" data-col="0">Item Name <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="1">Item Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="2">Inventory Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="3">Stock <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="4">Unit <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="5">Expiry <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($no_stock_items)): ?>
                            <?php foreach ($no_stock_items as $item): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_code']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['inventory_code'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo (int) ($item['quantity_on_hand'] ?? 0); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td>
                                    <td class="text-center"><?php echo !empty($item['expiration_date']) ? date('M d, Y', strtotime($item['expiration_date'])) : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <span>All items are in stock.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-3 fade-in-up" style="animation-delay: 0.2s;">
    <!-- Expired Items -->
    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled"><span>Expired Items</span></h5>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100 report-sortable" id="expiredTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="cursor:pointer;" data-col="0">Item Name <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="1">Item Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="2">Inventory Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="3">Stock <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="4">Unit <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="5">Expiry <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($expired_items)): ?>
                            <?php foreach ($expired_items as $item): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_code']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['inventory_code'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo (int) ($item['quantity_on_hand'] ?? 0); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td>
                                    <td class="text-center"><?php echo !empty($item['expiration_date']) ? date('M d, Y', strtotime($item['expiration_date'])) : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <span>No expired items.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function exportAllTables() {
    var wb = XLSX.utils.book_new();
    var tables = [
        {id: 'nearExpiryTable', name: 'Near Expiry Items'},
        {id: 'lowStockTable', name: 'Low Stock Items'},
        {id: 'outOfStockTable', name: 'Out of Stock Items'},
        {id: 'expiredTable', name: 'Expired Items'}
    ];
    tables.forEach(function(t) {
        var el = document.getElementById(t.id);
        if (el) {
            var clone = el.cloneNode(true);

            // Remove hidden rows
            var hiddenRows = clone.querySelectorAll('tr[style*="display: none"], tr[style*="display:none"]');
            hiddenRows.forEach(function(r) { r.remove(); });

            // Remove icon elements from cloned headers
            var icons = clone.querySelectorAll('i');
            icons.forEach(function(icon) { icon.remove(); });

            var ws = XLSX.utils.table_to_sheet(clone, { raw: true });

            // Set column widths to prevent Excel '#########' date overflow
            ws['!cols'] = [
                { wch: 25 }, // Item Name
                { wch: 15 }, // Item Code
                { wch: 24 }, // Inventory Code
                { wch: 10 }, // Stock
                { wch: 10 }, // Unit
                { wch: 18 }  // Expiry
            ];

            XLSX.utils.book_append_sheet(wb, ws, t.name);
        }
    });
    var datePart = filterDatePart || new Date().toISOString().slice(0,10);
    XLSX.writeFile(wb, 'BCH_Inventory_Reports_' + datePart + '.xlsx');
}

var filterDatePart = (function() {
    var sd = '<?php echo $start_date ?? ''; ?>';
    var ed = '<?php echo $end_date ?? ''; ?>';
    if (sd && ed) return sd + '~' + ed;
    if (sd) return sd;
    if (ed) return ed;
    return '';
})();

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.report-sortable').forEach(function(table) {
        var headers = table.querySelectorAll('thead th');
        headers.forEach(function(th, colIndex) {
            th.addEventListener('click', function() {
                var tbody = table.querySelector('tbody');
                var rows = Array.from(tbody.querySelectorAll('tr'));
                if (rows.length === 0 || (rows.length === 1 && rows[0].querySelector('td[colspan]'))) return;

                var asc = th.getAttribute('data-sort-dir') !== 'asc';
                th.setAttribute('data-sort-dir', asc ? 'asc' : 'desc');

                headers.forEach(function(h) { h.querySelector('i').className = 'fa-solid fa-sort'; });
                th.querySelector('i').className = asc ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down';

                rows.sort(function(a, b) {
                    var aText = (a.cells[colIndex] ? a.cells[colIndex].textContent.trim() : '').replace('#', '');
                    var bText = (b.cells[colIndex] ? b.cells[colIndex].textContent.trim() : '').replace('#', '');
                    var aNum = parseFloat(aText.replace(/[^0-9.\-]/g, ''));
                    var bNum = parseFloat(bText.replace(/[^0-9.\-]/g, ''));
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return asc ? aNum - bNum : bNum - aNum;
                    }
                    return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
                });

                rows.forEach(function(row) { tbody.appendChild(row); });
            });
        });
    });
});
</script>
