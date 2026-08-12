<script src="<?php echo base_url('assets/vendor/xlsx/xlsx.bundle.js'); ?>"></script>

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

<!-- Toast Notification -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="reportToast" class="toast align-items-center border-0 text-white" role="alert" aria-live="assertive" aria-atomic="true"
        style="min-width: 300px; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="reportToastMsg" style="font-size: 0.9rem;"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Report Filter Card -->
<div class="row fade-in-up mb-4" style="animation-delay: 0.03s;">
    <div class="col-lg-6 col-12">
        <div class="standard-card">
            <h5 class="fw-semibold mb-4" style="font-size: 1rem; color: #1e293b; letter-spacing: 0.01em;">
                Report Filter
            </h5>
            <form method="GET" action="<?php echo base_url('reports'); ?>" id="reportFilterForm">
                <div class="row g-3">
                    <!-- Start Date -->
                    <div class="col-lg-6 col-12">
                        <div class="db-search-field w-100">
                            <input type="date" id="start_date" name="start_date" class="db-search-input w-100"
                                value="<?php echo htmlspecialchars($start_date ?? date('Y-01-01')); ?>" placeholder=" ">
                            <label for="start_date">Start Date</label>
                        </div>
                    </div>
                    <!-- End Date -->
                    <div class="col-lg-6 col-12">
                        <div class="db-search-field w-100">
                            <input type="date" id="end_date" name="end_date" class="db-search-input w-100"
                                value="<?php echo htmlspecialchars($end_date ?? date('Y-m-d')); ?>" placeholder=" ">
                            <label for="end_date">End Date</label>
                        </div>
                    </div>
                    <!-- Report Type -->
                    <div class="col-12">
                        <div class="db-search-field w-100">
                            <select id="export_report_select" name="report_type" class="db-search-input w-100" aria-label="Select Report Type">
                                <option value="" disabled <?php echo empty($report_type) ? 'selected' : ''; ?> hidden>- Select Report Type -</option>
                                <option value="all" <?php echo (($report_type ?? '') === 'all') ? 'selected' : ''; ?>>All Reports</option>
                                <option value="near_expiry" <?php echo (($report_type ?? '') === 'near_expiry') ? 'selected' : ''; ?>>Near Expiry Items</option>
                                <option value="low_stock" <?php echo (($report_type ?? '') === 'low_stock') ? 'selected' : ''; ?>>Low Stock Items</option>
                                <option value="out_of_stock" <?php echo (($report_type ?? '') === 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock Items</option>
                                <option value="expired" <?php echo (($report_type ?? '') === 'expired') ? 'selected' : ''; ?>>Expired Items</option>
                                <?php if (is_admin_role()): ?>
                                <option value="top_requested" <?php echo (($report_type ?? '') === 'top_requested') ? 'selected' : ''; ?>>Top 10 Requested Items</option>
                                <option value="top_requesting_depts" <?php echo (($report_type ?? '') === 'top_requesting_depts') ? 'selected' : ''; ?>>Top 5 Requesting Departments</option>
                                <option value="top_consumed" <?php echo (($report_type ?? '') === 'top_consumed') ? 'selected' : ''; ?>>Top 10 Consumed Items per Category</option>
                                <?php endif; ?>
                            </select>
                            <label for="export_report_select">Report Type</label>
                        </div>
                    </div>
                    <!-- Buttons (own row, right-aligned) -->
                    <div class="col-12 d-flex justify-content-end gap-2 pt-1">
                        <button type="button" class="btn px-4 py-2 fw-semibold" id="btnGenerate"
                            onclick="exportAllTables()"
                            style="background: #1d6adb; color: #fff; border: none; border-radius: 6px; font-size: 0.92rem; letter-spacing: 0.02em; transition: background 0.18s;">
                            Generate
                        </button>
                        <a href="<?php echo base_url('reports'); ?>" class="btn px-4 py-2 fw-semibold" id="btnClearFilters"
                            style="background: #c0392b; color: #fff; border: none; border-radius: 6px; font-size: 0.92rem; letter-spacing: 0.02em; transition: background 0.18s;">
                            Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>



<?php
// ---------------------------------------------------------------------------
// Build report sheet data for client-side XLSX export (no visible tables)
// ---------------------------------------------------------------------------
function _fmtExpiry($date)
{
    return !empty($date) ? date('M d, Y', strtotime($date)) : 'N/A';
}

$exportSheets = [];

if (!empty($near_expiry_items)) {
    $rows = [];
    foreach ($near_expiry_items as $item) {
        $rows[] = [$item['item_name'], $item['item_code'], $item['inventory_code'] ?? 'N/A', (int) ($item['quantity_on_hand'] ?? 0), $item['unit'] ?? '', _fmtExpiry($item['expiration_date'] ?? '')];
    }
    $exportSheets[] = ['reportType' => 'near_expiry', 'name' => 'Near Expiry Items', 'headers' => ['Item Name', 'Item Code', 'Inventory Code', 'Stock', 'Unit', 'Expiry'], 'rows' => $rows];
}

if (!empty($low_stock_items)) {
    $rows = [];
    foreach ($low_stock_items as $item) {
        $rows[] = [$item['item_name'], $item['item_code'], $item['inventory_code'] ?? 'N/A', (int) ($item['quantity_on_hand'] ?? 0), $item['unit'] ?? '', _fmtExpiry($item['expiration_date'] ?? '')];
    }
    $exportSheets[] = ['reportType' => 'low_stock', 'name' => 'Low Stock Items', 'headers' => ['Item Name', 'Item Code', 'Inventory Code', 'Remaining Stock', 'Unit', 'Expiry'], 'rows' => $rows];
}

if (!empty($no_stock_items)) {
    $rows = [];
    foreach ($no_stock_items as $item) {
        $rows[] = [$item['item_name'], $item['item_code'], $item['inventory_code'] ?? 'N/A', $item['unit'] ?? '', _fmtExpiry($item['expiration_date'] ?? '')];
    }
    $exportSheets[] = ['reportType' => 'out_of_stock', 'name' => 'Out of Stock Items', 'headers' => ['Item Name', 'Item Code', 'Inventory Code', 'Unit', 'Expiry'], 'rows' => $rows];
}

if (!empty($expired_items)) {
    $rows = [];
    foreach ($expired_items as $item) {
        $rows[] = [$item['item_name'], $item['item_code'], $item['inventory_code'] ?? 'N/A', (int) ($item['quantity_on_hand'] ?? 0), $item['unit'] ?? '', _fmtExpiry($item['expiration_date'] ?? '')];
    }
    $exportSheets[] = ['reportType' => 'expired', 'name' => 'Expired Items', 'headers' => ['Item Name', 'Item Code', 'Inventory Code', 'Stock', 'Unit', 'Expiry'], 'rows' => $rows];
}

if (!empty($top_requested_by_category)) {
    $rows = [];
    foreach ($top_requested_by_category as $item) {
        $rows[] = [(int) $item['rank'], $item['item_name'] ?? 'N/A', $item['item_code'] ?? 'N/A', $item['unit'] ?? '', (int) $item['total_quantity']];
    }
    $exportSheets[] = ['reportType' => 'top_requested', 'name' => 'Top 10 Requested Items', 'headers' => ['Rank', 'Item Name', 'Item Code', 'Unit', 'Total Requested'], 'rows' => $rows];
}

if (!empty($top_requesting_departments)) {
    $rows = [];
    foreach ($top_requesting_departments as $dept) {
        $rows[] = [(int) $dept['rank'], $dept['department_name'], $dept['department_code'] ?? '—', (int) $dept['total_requests'], (int) $dept['total_requested']];
    }
    $exportSheets[] = ['reportType' => 'top_requesting_depts', 'name' => 'Top 5 Requesting Departments', 'headers' => ['Rank', 'Department Name', 'Department Code', 'Total Requests', 'Quantity Requested'], 'rows' => $rows];
}

if (!empty($top_consumed_by_category)) {
    foreach ($top_consumed_by_category as $category) {
        $rows = [];
        foreach ($category['items'] as $item) {
            $rows[] = [(int) $item['rank'], $item['item_name'] ?? 'N/A', $item['item_code'] ?? 'N/A', $item['unit'] ?? '', (int) $item['total_quantity']];
        }
        $catLabel = trim(($category['category_code'] ?? '') . ' - ' . ($category['category_name'] ?? 'Uncategorized'), ' -');
        $exportSheets[] = ['reportType' => 'top_consumed', 'name' => 'Top Consumed - ' . $catLabel, 'headers' => ['Rank', 'Item Name', 'Item Code', 'Unit', 'Total Consumed'], 'rows' => $rows];
    }
}
?>

<script>
var reportSheets = <?php echo json_encode($exportSheets, JSON_UNESCAPED_UNICODE); ?>;

function showReportToast(message, color) {
    var toastEl = document.getElementById('reportToast');
    var toastMsg = document.getElementById('reportToastMsg');
    if (toastEl && toastMsg) {
        toastEl.style.background = color || '#c0392b';
        toastMsg.textContent = message;
        var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    }
}

function exportAllTables() {
    var selectEl = document.getElementById('export_report_select');
    var selectedReport = selectEl ? selectEl.value : '';

    if (!selectedReport) {
        showReportToast('Please select a report type before generating.', '#c0392b');
        if (selectEl) selectEl.focus();
        return;
    }

    var sheets = selectedReport === 'all'
        ? reportSheets
        : reportSheets.filter(function(s) { return s.reportType === selectedReport; });

    if (!sheets.length) {
        showReportToast('No data available to export for the selected report.', '#c0392b');
        return;
    }

    var wb = XLSX.utils.book_new();
    var usedSheetNames = {};

    sheets.forEach(function(sheet, index) {
        var aoa = [sheet.headers].concat(sheet.rows);
        var ws = XLSX.utils.aoa_to_sheet(aoa);

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

        var sheetName = (sheet.name || ('Report ' + (index + 1))).replace(/[\\/?*[\]:]/g, '').slice(0, 31) || 'Report';
        var uniqueSheetName = sheetName;
        var suffix = 2;
        while (usedSheetNames[uniqueSheetName]) {
            uniqueSheetName = sheetName.slice(0, 28) + ' ' + suffix++;
        }
        usedSheetNames[uniqueSheetName] = true;
        XLSX.utils.book_append_sheet(wb, ws, uniqueSheetName);
    });

    var datePart = getDatePart();
    var exportFileName = selectedReport === 'all'
        ? 'BCH_Inventory_Reports_' + datePart + '.xlsx'
        : 'BCH_Report_' + selectedReport + '_' + datePart + '.xlsx';
    XLSX.writeFile(wb, exportFileName);
}

var filterDatePart = (function() {
    var sd = '<?php echo $start_date ?? ''; ?>';
    var ed = '<?php echo $end_date ?? ''; ?>';
    if (sd && ed) return sd + '_to_' + ed;
    if (sd) return sd;
    if (ed) return ed;
    return new Date().toISOString().slice(0, 10);
})();

function getDatePart() {
    var sd = (document.getElementById('start_date') || {}).value || '';
    var ed = (document.getElementById('end_date') || {}).value || '';
    if (sd && ed) return sd + '_to_' + ed;
    if (sd) return sd;
    if (ed) return ed;
    return filterDatePart || new Date().toISOString().slice(0, 10);
}

document.addEventListener('DOMContentLoaded', function() {
    // Button hover effects
    var btnGenerate = document.getElementById('btnGenerate');
    if (btnGenerate) {
        btnGenerate.addEventListener('mouseover', function() { this.style.background = '#1558c0'; });
        btnGenerate.addEventListener('mouseout', function() { this.style.background = '#1d6adb'; });
    }
    var btnClear = document.getElementById('btnClearFilters');
    if (btnClear) {
        btnClear.addEventListener('mouseover', function() { this.style.background = '#cf5648'; });
        btnClear.addEventListener('mouseout', function() { this.style.background = '#c0392b'; });
    }
});
</script>
