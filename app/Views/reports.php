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

<form method="GET" action="<?php echo base_url('reports'); ?>" id="reportsFilterForm">
    <div class="db-search-bar">
        <div class="db-search-field db-search-field--dropdown">
            <input type="date" id="reports_from" name="from" class="db-search-input" placeholder=" " value="<?php echo htmlspecialchars($from); ?>">
            <label for="reports_from">From</label>
        </div>
        <div class="db-search-field db-search-field--dropdown">
            <input type="date" id="reports_to" name="to" class="db-search-input" placeholder=" " value="<?php echo htmlspecialchars($to); ?>">
            <label for="reports_to">To</label>
        </div>
        <div class="db-search-actions">
            <button type="submit" class="btn-db-search" id="btnReportsSearch">Apply</button>
            <a href="<?php echo base_url('reports'); ?>" class="btn-db-clear" id="btnReportsClear">Clear</a>
        </div>
    </div>
</form>

<div class="row g-4 mb-4 fade-in-up" style="animation-delay: 0.05s;">
    <div class="col-lg-3 col-md-6 col-12">
        <div class="kpi-card h-100" style="border-radius: 8px; border-left: 6px solid var(--primary); padding: 1.25rem;">
            <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;"><?php echo htmlspecialchars($report_scope); ?> Items</div>
            <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo (int) ($summary['item_count'] ?? 0); ?></h3>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="kpi-card h-100" style="border-radius: 8px; border-left: 6px solid #10b981; padding: 1.25rem;">
            <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Quantity On Hand</div>
            <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo (int) ($summary['quantity_on_hand'] ?? 0); ?></h3>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="kpi-card h-100" style="border-radius: 8px; border-left: 6px solid #f59e0b; padding: 1.25rem;">
            <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Near Expiry</div>
            <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo (int) ($summary['near_expiry'] ?? 0); ?></h3>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12">
        <div class="kpi-card h-100" style="border-radius: 8px; border-left: 6px solid var(--danger); padding: 1.25rem;">
            <div class="kpi-label text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Out of Stock</div>
            <h3 class="kpi-value text-dark fw-bold mt-1"><?php echo (int) ($summary['out_of_stock'] ?? 0); ?></h3>
        </div>
    </div>
</div>

<div class="row g-4 fade-in-up" style="animation-delay: 0.1s;">
    <div class="col-lg-5 col-12">
        <div class="standard-card h-100">
            <div class="card-header-styled">
                <h5 class="card-title-styled"><span>Request Summary</span></h5>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100">
                    <tbody>
                        <tr><td>Pending</td><td class="text-end fw-bold"><?php echo (int) $request_summary['pending']; ?></td></tr>
                        <tr><td>Served</td><td class="text-end fw-bold"><?php echo (int) $request_summary['served']; ?></td></tr>
                        <tr><td>Partial</td><td class="text-end fw-bold"><?php echo (int) $request_summary['partial']; ?></td></tr>
                        <tr><td>Rejected</td><td class="text-end fw-bold"><?php echo (int) $request_summary['rejected']; ?></td></tr>
                        <tr><td>Cancelled</td><td class="text-end fw-bold"><?php echo (int) $request_summary['cancelled']; ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-7 col-12">
        <div class="standard-card h-100">
            <div class="card-header-styled">
                <h5 class="card-title-styled"><span>Top Stock On Hand</span></h5>
            </div>
            <div class="table-responsive-custom">
                <table class="table table-custom table-hover w-100" id="reportsTopItemsTable">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th class="text-center">Unit</th>
                            <th class="text-center">On Hand</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($top_items)): ?>
                            <?php foreach ($top_items as $item): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($item['item_code']); ?></td>
                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($item['unit'] ?? 'N/A'); ?></td>
                                    <td class="text-center fw-bold"><?php echo (int) ($item['quantity_on_hand'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
