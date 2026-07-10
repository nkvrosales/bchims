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

<div class="row g-4 fade-in-up" style="animation-delay: 0.05s;">
    <!-- Pending Requests -->
    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled"><span>Pending Requests</span></h5>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100 report-sortable">
                    <thead>
                        <tr>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="0">ID <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="1">Date <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="2">Requester <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="3">Department <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="4">Item <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="5">Quantity <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="6">Unit <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pending_requests)): ?>
                            <?php foreach ($pending_requests as $req): ?>
                                <tr>
                                    <td class="text-center">#<?php echo $req['request_id']; ?></td>
                                    <td class="text-center"><?php echo date('M j, Y', strtotime($req['request_date'])); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($req['requester_full_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($req['department_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($req['item_name']); ?></td>
                                    <td class="text-center"><?php echo (int)$req['quantity_served']; ?> / <?php echo (int)$req['quantity_requested']; ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($req['item_unit']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <span>No pending requests.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Partially Served Requests -->
    <div class="col-12">
        <div class="standard-card">
            <div class="card-header-styled">
                <h5 class="card-title-styled"><span>Partially Served Requests</span></h5>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100 report-sortable">
                    <thead>
                        <tr>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="0">ID <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="1">Date <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="2">Requester <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="3">Department <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="4">Item <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="5">Quantity <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer; width: calc(100% / 7);" data-col="6">Unit <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($partial_requests)): ?>
                            <?php foreach ($partial_requests as $req): ?>
                                <tr>
                                    <td class="text-center">#<?php echo $req['request_id']; ?></td>
                                    <td class="text-center"><?php echo date('M j, Y', strtotime($req['request_date'])); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($req['requester_full_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($req['department_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($req['item_name']); ?></td>
                                    <td class="text-center"><?php echo (int)$req['quantity_served']; ?> / <?php echo (int)$req['quantity_requested']; ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($req['item_unit']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <span>No partially served requests.</span>
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
    <!-- Near Expiry Items -->
    <div class="col-lg-6 col-12">
        <div class="standard-card h-100">
            <div class="card-header-styled">
                <h5 class="card-title-styled"><span>Near Expiry Items</span></h5>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100 report-sortable">
                    <thead>
                        <tr>
                            <th style="cursor:pointer;" data-col="0">Item Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="1">Item Name <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="2">Expiration Date <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="3">Qty On Hand <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($near_expiry_items)): ?>
                            <?php foreach ($near_expiry_items as $item): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_code']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td class="text-center"><?php echo date('M d, Y', strtotime($item['expiration_date'])); ?></td>
                                    <td class="text-center"><?php echo (int) ($item['quantity_on_hand'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <span>No items near expiry.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Items Arrived This Month -->
    <div class="col-lg-6 col-12">
        <div class="standard-card h-100">
            <div class="card-header-styled">
                <h5 class="card-title-styled"><span>Items Arrived This Month</span></h5>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100 report-sortable">
                    <thead>
                        <tr>
                            <th class="text-center" style="cursor:pointer;" data-col="0">Item Code <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="1">Item Name <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="2">Unit <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="3">Stock <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                            <th class="text-center" style="cursor:pointer;" data-col="4">Date <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.5;"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($arrived_items)): ?>
                            <?php foreach ($arrived_items as $a): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($a['item_code']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($a['item_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($a['unit'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo (int) ($a['quantity_on_hand'] ?? 0); ?></td>
                                    <td class="text-center"><?php echo !empty($a['created_at']) ? date('M j', strtotime($a['created_at'])) : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No items arrived this month.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
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
