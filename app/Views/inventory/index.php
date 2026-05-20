<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Inventory Management</h1>
            <p class="text-secondary mb-0">Monitor, search, filter, and manage medical and hospital items</p>
        </div>
        <div>
            <a href="<?php echo base_url('inventory/create'); ?>" class="btn btn-primary d-flex align-items-center gap-2" id="kpiAddItemBtn">
                <i class="fa-solid fa-plus"></i>
                <span>Add New Item</span>
            </a>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <span><?php echo session()->getFlashdata('success'); ?></span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-exclamation fs-5"></i>
            <span><?php echo session()->getFlashdata('error'); ?></span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Search & Filtering Drawer Card -->
<div class="filter-card fade-in-up" style="animation-delay: 0.1s;">
    <h5 class="font-heading mb-3" style="font-size: 1rem;">
        <span>Search & Filter</span>
    </h5>
    
    <form method="GET" action="<?php echo base_url('inventory'); ?>" class="row g-3" id="inventoryFilterForm">
        <!-- 1. Search Query -->
        <div class="col-12 col-md-4">
            <label for="search" class="form-label small fw-semibold text-secondary">Search Item</label>
            <input type="text" 
                   class="form-control input-custom" 
                   id="search" 
                   name="search" 
                   placeholder="Search by code, name, or description..."
                   value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>">
        </div>

        <!-- 2. Department -->
        <div class="col-12 col-sm-6 col-md-3">
            <label for="department" class="form-label small fw-semibold text-secondary">Department</label>
            <select class="form-select input-custom" id="department" name="department">
                <option value="">-- All Departments --</option>
                <option value="LAB" <?php echo (isset($department) && $department === 'LAB') ? 'selected' : ''; ?>>LAB</option>
                <option value="PHARMA" <?php echo (isset($department) && $department === 'PHARMA') ? 'selected' : ''; ?>>PHARMA</option>
                <option value="SUPPLIES" <?php echo (isset($department) && $department === 'SUPPLIES') ? 'selected' : ''; ?>>SUPPLIES</option>
                <option value="OR/DR COMPLEX" <?php echo (isset($department) && $department === 'OR/DR COMPLEX') ? 'selected' : ''; ?>>OR/DR COMPLEX</option>
            </select>
        </div>

        <!-- 3. Stock Status -->
        <div class="col-12 col-sm-6 col-md-2">
            <label for="stock_status" class="form-label small fw-semibold text-secondary">Stock Status</label>
            <select class="form-select input-custom" id="stock_status" name="stock_status">
                <option value="">-- All Statuses --</option>
                <option value="in_stock" <?php echo (isset($stock_status) && $stock_status === 'in_stock') ? 'selected' : ''; ?>>In Stock</option>
                <option value="low_stock" <?php echo (isset($stock_status) && $stock_status === 'low_stock') ? 'selected' : ''; ?>>Low Stock</option>
                <option value="out_of_stock" <?php echo (isset($stock_status) && $stock_status === 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
            </select>
        </div>

        <!-- 4. Form Submission Buttons -->
        <div class="col-12 col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" id="btnFilterSubmit">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Filter</span>
            </button>
            <a href="<?php echo base_url('inventory'); ?>" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2" id="btnFilterReset">
                <i class="fa-solid fa-rotate-left"></i>
                <span>Reset</span>
            </a>
        </div>
    </form>
</div>

<!-- Inventory Items Table Area -->
<div class="standard-card fade-in-up" style="animation-delay: 0.2s;">
    <div class="card-header-styled mb-4">
        <h5 class="card-title-styled">
            <span>Inventory Stock Database</span>
        </h5>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100">
            <thead>
                <tr>
                    <th style="width: 12%">Code</th>
                    <th style="width: 25%">Name</th>
                    <th style="width: 15%">Department</th>
                    <th style="width: 15%">Quantity</th>
                    <th style="width: 18%">Stock Status</th>
                    <th style="width: 15%" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="font-monospace fw-bold" style="font-size: 0.85rem; color: var(--text-secondary);">
                                <?php echo htmlspecialchars($item['item_code']); ?>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($item['name']); ?></div>
                                <?php if (!empty($item['description'])): ?>
                                    <small class="text-muted d-block text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($item['description']); ?>">
                                        <?php echo htmlspecialchars($item['description']); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-2 px-2 py-1 small">
                                    <?php echo htmlspecialchars($item['department']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold fs-6 text-dark"><?php echo $item['quantity']; ?></span> 
                                <small class="text-muted"><?php echo htmlspecialchars($item['unit']); ?></small>
                            </td>
                            <td>
                                <?php 
                                    if ($item['quantity'] == 0) {
                                        $badge = 'bg-danger-subtle text-danger border border-danger-subtle';
                                        $status = 'Out of Stock';
                                    } elseif ($item['quantity'] <= $item['min_stock']) {
                                        $badge = 'bg-warning-subtle text-warning border border-warning-subtle';
                                        $status = 'Low Stock';
                                    } else {
                                        $badge = 'bg-success-subtle text-success border border-success-subtle';
                                        $status = 'In Stock';
                                    }
                                ?>
                                <span class="badge badge-action <?php echo $badge; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="<?php echo base_url('inventory/edit/' . $item['id']); ?>" 
                                       class="btn btn-sm btn-outline-primary py-1 px-2 d-flex align-items-center gap-1 rounded-2" 
                                       title="Edit Item">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span class="d-none d-sm-inline">Edit</span>
                                    </a>
                                    <a href="<?php echo base_url('inventory/delete/' . $item['id']); ?>" 
                                       class="btn btn-sm btn-outline-danger py-1 px-2 d-flex align-items-center gap-1 rounded-2" 
                                       onclick="return confirm('Are you sure you want to delete this item? This action is recorded in the Audit Logs.');"
                                       title="Delete Item">
                                        <i class="fa-solid fa-trash"></i>
                                        <span class="d-none d-sm-inline">Delete</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-regular fa-folder-open d-block fs-2 mb-2 text-secondary"></i>
                            <span class="fw-medium">No items found matching the current search filters.</span>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
