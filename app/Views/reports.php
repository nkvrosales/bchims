<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>

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
        <div class="db-search-field" style="flex: 0 0 180px;">
            <select id="report_period" name="report_period" class="db-search-input" aria-label="Report period">
                <option value="today" <?php echo ($report_period ?? 'today') === 'today' ? 'selected' : ''; ?>>Today</option>
                <option value="weekly" <?php echo ($report_period ?? '') === 'weekly' ? 'selected' : ''; ?>>This Week</option>
                <option value="monthly" <?php echo ($report_period ?? '') === 'monthly' ? 'selected' : ''; ?>>This Month</option>
                <option value="yearly" <?php echo ($report_period ?? '') === 'yearly' ? 'selected' : ''; ?>>This Year</option>
                <option value="custom" <?php echo ($report_period ?? '') === 'custom' ? 'selected' : ''; ?>>Custom Date Range</option>
            </select>
            <label for="report_period">Report Period</label>
        </div>
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
                <table class="table table-custom table-hover w-100 report-sortable report-exportable" id="nearExpiryTable" data-export-name="Near Expiry Items">
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
                <table class="table table-custom table-hover w-100 report-sortable report-exportable" id="lowStockTable" data-export-name="Low Stock Items">
                    <thead>
                        <tr>
                            <th class="text-center" style="cursor:pointer;" data-col="0">Item Name <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="1">Item Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="2">Inventory Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="3">Remaining Stock <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
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
                <table class="table table-custom table-hover w-100 report-sortable report-exportable" id="outOfStockTable" data-export-name="Out of Stock Items">
                    <thead>
                        <tr>
                            <th class="text-center" style="cursor:pointer;" data-col="0">Item Name <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="1">Item Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="2">Inventory Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="3">Unit <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="4">Expiry <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($no_stock_items)): ?>
                            <?php foreach ($no_stock_items as $item): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_code']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['inventory_code'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td>
                                    <td class="text-center"><?php echo !empty($item['expiration_date']) ? date('M d, Y', strtotime($item['expiration_date'])) : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
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
                <table class="table table-custom table-hover w-100 report-sortable report-exportable" id="expiredTable" data-export-name="Expired Items">
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

<div class="row g-4 mt-1 fade-in-up" style="animation-delay: 0.08s;">
    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled"><h5 class="card-title-styled"><span>Top 10 Requested Items</span></h5></div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover mb-0 w-100 report-exportable" data-export-name="Top 10 Requested Items">
                    <thead><tr><th class="text-center" style="width: 12%;">Rank</th><th class="text-center">Item Name</th><th class="text-center" style="width: 20%;">Item Code</th><th class="text-center" style="width: 14%;">Unit</th><th class="text-center" style="width: 18%;">Total Requested</th></tr></thead>
                    <tbody>
                        <?php if (!empty($top_requested_by_category)): ?>
                            <?php foreach ($top_requested_by_category as $item): ?>
                                <tr><td class="text-center"><?php echo (int) $item['rank']; ?></td><td><?php echo htmlspecialchars($item['item_name'] ?? 'N/A'); ?></td><td class="text-center"><?php echo htmlspecialchars($item['item_code'] ?? 'N/A'); ?></td><td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td><td class="text-center fw-semibold"><?php echo (int) $item['total_quantity']; ?></td></tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No requested items found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled"><h5 class="card-title-styled"><span>Top 10 Consumed Items per Category</span></h5></div>
            <div class="p-3">
                <?php if (!empty($top_consumed_by_category)): ?>
                    <div class="row g-3">
                        <?php foreach ($top_consumed_by_category as $category): ?>
                            <div class="col-12">
                                <div class="border rounded-3 overflow-hidden">
                                    <div class="px-3 py-2 fw-semibold" style="background: rgba(16, 185, 129, 0.10); color: #047857;"><?php echo htmlspecialchars(trim(($category['category_code'] ?? '') . ' - ' . ($category['category_name'] ?? 'Uncategorized'), ' -')); ?></div>
                                    <div class="table-responsive-custom">
                                        <table class="table table-custom table-hover mb-0 w-100 report-exportable" data-export-name="Top Consumed - <?php echo htmlspecialchars($category['category_code'] ?? $category['category_name'] ?? 'Category', ENT_QUOTES); ?>">
                                            <thead><tr><th class="text-center" style="width: 12%;">Rank</th><th class="text-center">Item Name</th><th class="text-center" style="width: 20%;">Item Code</th><th class="text-center" style="width: 14%;">Unit</th><th class="text-center" style="width: 18%;">Total Consumed</th></tr></thead>
                                            <tbody>
                                                <?php foreach ($category['items'] as $item): ?>
                                                    <tr><td class="text-center"><?php echo (int) $item['rank']; ?></td><td><?php echo htmlspecialchars($item['item_name'] ?? 'N/A'); ?></td><td class="text-center"><?php echo htmlspecialchars($item['item_code'] ?? 'N/A'); ?></td><td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td><td class="text-center fw-semibold"><?php echo (int) $item['total_quantity']; ?></td></tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">No consumed items found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function exportAllTables() {
    var wb = XLSX.utils.book_new();
    var usedSheetNames = {};
    var tables = Array.from(document.querySelectorAll('.report-exportable'));
    tables.forEach(function(el, index) {
        if (el) {
            var clone = el.cloneNode(true);

            // Remove hidden rows
            var hiddenRows = clone.querySelectorAll('tr[style*="display: none"], tr[style*="display:none"]');
            hiddenRows.forEach(function(r) { r.remove(); });

            // Remove icon elements from cloned headers
            var icons = clone.querySelectorAll('i');
            icons.forEach(function(icon) { icon.remove(); });

            var ws = XLSX.utils.table_to_sheet(clone, { raw: true });

            var range = XLSX.utils.decode_range(ws['!ref']);
            var headerStyle = {
                fill: { patternType: 'solid', fgColor: { rgb: '10B981' } },
                font: { color: { rgb: 'FFFFFF' }, bold: true },
                alignment: { horizontal: 'center', vertical: 'center', wrapText: true },
                border: {
                    top: { style: 'thin', color: { rgb: '059669' } },
                    bottom: { style: 'thin', color: { rgb: '059669' } },
                    left: { style: 'thin', color: { rgb: '059669' } },
                    right: { style: 'thin', color: { rgb: '059669' } }
                }
            };
            for (var col = range.s.c; col <= range.e.c; col++) {
                var headerCell = ws[XLSX.utils.encode_cell({ r: range.s.r, c: col })];
                if (headerCell) headerCell.s = headerStyle;
            }
            ws['!rows'] = ws['!rows'] || [];
            ws['!rows'][range.s.r] = { hpt: 24 };

            ws['!cols'] = [];
            for (var widthCol = range.s.c; widthCol <= range.e.c; widthCol++) {
                var maxWidth = 10;
                for (var row = range.s.r; row <= range.e.r; row++) {
                    var cell = ws[XLSX.utils.encode_cell({ r: row, c: widthCol })];
                    if (cell && cell.v !== undefined && cell.v !== null) {
                        maxWidth = Math.max(maxWidth, String(cell.v).length + 2);
                    }
                }
                ws['!cols'][widthCol] = { wch: Math.min(maxWidth, 35) };
            }

            var sheetName = (el.dataset.exportName || ('Report ' + (index + 1))).replace(/[\\\\/?*\[\]:]/g, '').slice(0, 31) || 'Report';
            var uniqueSheetName = sheetName;
            var suffix = 2;
            while (usedSheetNames[uniqueSheetName]) {
                uniqueSheetName = sheetName.slice(0, 28) + ' ' + suffix++;
            }
            usedSheetNames[uniqueSheetName] = true;
            XLSX.utils.book_append_sheet(wb, ws, uniqueSheetName);
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
    var reportPeriod = document.getElementById('report_period');
    var startDate = document.getElementById('start_date');
    var endDate = document.getElementById('end_date');
    if (reportPeriod) {
        reportPeriod.addEventListener('change', function() {
            if (this.value !== 'custom') document.getElementById('reportFilterForm').submit();
        });
    }
    [startDate, endDate].forEach(function(input) {
        if (input) input.addEventListener('change', function() { reportPeriod.value = 'custom'; });
    });

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
