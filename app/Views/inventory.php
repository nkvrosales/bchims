<?php $isAdmin = !in_array(strtolower((string) session()->get('role')), ['viewer', 'encoder'], true); ?>
<?php $invTitle = $title ?? ($isAdmin ? 'Central Inventory' : 'My Inventory'); ?>
<!-- Page Title Section -->
<div class="page-breadcrumb">
    <a href="<?php echo base_url('dashboard'); ?>">Dashboard</a>
    <span class="separator">/</span>
    <span class="current"><?php echo $invTitle; ?></span>
</div>

<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1"><?php echo $invTitle; ?></h1>
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


<!-- Inventory Search Bar -->
<form method="GET" action="<?php echo base_url('inventory'); ?>" id="inventorySearchForm">
    <div class="db-search-bar">
        <div class="db-search-field db-search-field--keyword">
            <input
                type="text"
                id="inv_search_keyword"
                name="search"
                class="db-search-input"
                placeholder=" "
                value="<?php echo htmlspecialchars($search ?? ''); ?>"
                autocomplete="off"
            >
            <label for="inv_search_keyword">Enter Name / Item Code</label>
        </div>
        <div class="db-search-field db-search-field--dropdown">
            <select id="inv_search_category" name="category_id" class="db-search-select">
                <option value="">- Select Category -</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>"
                        <?php echo (isset($category_id) && (string)$category_id === (string)$cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="inv_search_category">Category</label>
        </div>
        <div class="db-search-field db-search-field--dropdown">
            <select id="inv_search_status" name="stock_status" class="db-search-select">
                <option value="">- Select Status -</option>
                <option value="in_stock"   <?php echo (($stock_status ?? '') === 'in_stock')   ? 'selected' : ''; ?>>In Stock</option>
                <option value="low_stock"  <?php echo (($stock_status ?? '') === 'low_stock')  ? 'selected' : ''; ?>>Low Stock</option>
                <option value="out_of_stock" <?php echo (($stock_status ?? '') === 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                <option value="expired"    <?php echo (($stock_status ?? '') === 'expired')    ? 'selected' : ''; ?>>Expired</option>
                <option value="near_expiry" <?php echo (($stock_status ?? '') === 'near_expiry') ? 'selected' : ''; ?>>Near Expiry</option>
            </select>
            <label for="inv_search_status">Stock Status</label>
        </div>
        <div class="db-search-actions">
            <button type="submit" class="btn-db-search" id="btnInvSearch">
                 Search
            </button>
            <a href="<?php echo base_url('inventory'); ?>" class="btn-db-clear" id="btnInvClear">
                Clear
            </a>
            <?php if (!in_array(strtolower((string) session()->get('role')), ['viewer', 'encoder'], true)): ?>
                <div class="db-search-separator"></div>
                <button type="button"
                        class="btn btn-db-search d-inline-flex align-items-center gap-2"
                        id="btnAddNewItem"
                        onclick="openItemModal('create')">
                    <span>Add Item</span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Inventory Items Table Area -->

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100" id="inventoryTable">
            <thead>
                <tr>
                    <th style="width: 18%" class="text-center">Item Name</th>
                    <th style="width: 14%" class="text-center">Item Code</th>
                    <th style="width: 12%">Category</th>
                    <th style="width: 10%" class="text-center">Stock</th>
                    <th style="width: 14%" class="text-center">Stock Status</th>
                    <th style="width: 10%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="text-center">
                                <div class="text-dark"><?php echo htmlspecialchars($item['item_name']); ?></div>
                            </td>
                            <td class="text-dark text-center" style="font-size: 0.85rem; color: var(--text-secondary);">
                                <?php echo htmlspecialchars($item['item_code']); ?>
                            </td>
                            <td>
                                <span class="text-dark"><?php echo htmlspecialchars($item['category_name'] ?? 'N/A'); ?></span>
                            </td>
                            <td class="text-center">
                                <span class="fs-6 text-dark">
                                    <?php echo (int)$item['quantity_on_hand']; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php
                                    $stockQty = (int)$item['quantity_on_hand'];
                                    $expDate = $item['expiration_date'] ?? '';
                                    $totalQty = (int)$item['total_quantity'];
                                    $isExpired = !empty($expDate) && $expDate < date('Y-m-d') && $stockQty > 0;
                                    $isNearExpiry = !empty($expDate) && $expDate >= date('Y-m-d') && $expDate <= date('Y-m-d', strtotime('+30 days')) && $stockQty > 0 && !$isExpired;
                                    $lowStockThreshold = $totalQty > 0 ? max(1, (int)ceil($totalQty * 0.15)) : 0;

                                    // Check batch-level statuses for this item
                                    $hasIssue = false;
                                    $allExpired = true;
                                    $allOutOfStock = true;
                                    $allNearExpiry = true;
                                    $itemBatches = $batches_by_code[$item['item_code']] ?? [];
                                    foreach ($itemBatches as $batch) {
                                        $bQty = (int)$batch['quantity_on_hand'];
                                        $bExp = $batch['expiration_date'] ?? '';
                                        $bExpired = !empty($bExp) && $bExp < date('Y-m-d') && $bQty > 0;
                                        $bNear = !empty($bExp) && $bExp >= date('Y-m-d') && $bExp <= date('Y-m-d', strtotime('+30 days')) && $bQty > 0 && !$bExpired;
                                        if ($bQty > 0 && !$bExpired) $allExpired = false;
                                        if ($bQty > 0) $allOutOfStock = false;
                                        if ($bQty > 0 && !$bNear) $allNearExpiry = false;
                                        if ($bExpired || $bNear || $bQty === 0) {
                                            $hasIssue = true;
                                        }
                                    }

                                    if ($allOutOfStock && !empty($itemBatches)) {
                                        $badge  = 'bg-danger-subtle text-dark border border-danger-subtle';
                                        $status = 'Out of Stock';
                                    } elseif ($allExpired && !empty($itemBatches)) {
                                        $badge  = 'bg-dark-subtle text-dark border border-dark-subtle';
                                        $status = 'Expired';
                                    } elseif ($allNearExpiry && !empty($itemBatches)) {
                                        $badge  = 'bg-danger-subtle text-dark border border-danger-subtle';
                                        $status = 'Near Expiry';
                                    } elseif ($hasIssue) {
                                        $badge  = 'bg-info-subtle text-dark border border-info-subtle';
                                        $status = 'View Details';
                                    } elseif ($isExpired) {
                                        $badge  = 'bg-dark-subtle text-dark border border-dark-subtle';
                                        $status = 'Expired';
                                    } elseif ($isNearExpiry) {
                                        $badge  = 'bg-danger-subtle text-dark border border-danger-subtle';
                                        $status = 'Near Expiry';
                                    } elseif ($stockQty === 0) {
                                        $badge  = 'bg-danger-subtle text-dark border border-danger-subtle';
                                        $status = 'Out of Stock';
                                    } elseif ($stockQty <= $lowStockThreshold) {
                                        $badge  = 'bg-warning-subtle text-dark border border-warning-subtle';
                                        $status = 'Low Stock';
                                    } else {
                                        $badge  = 'bg-success-subtle text-dark border border-success-subtle';
                                        $status = 'In Stock';
                                    }
                                ?>
                                    <span class="badge badge-action rounded-pill <?php echo $badge; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" style="padding: 4px 12px; font-size: 0.75rem; font-weight: 600;">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.8rem;">
                                         <?php if (strtolower((string) session()->get('role')) === 'encoder'): ?>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick='openItemModal("manage-batches", <?php echo json_encode([
                                            "item_code" => $item["item_code"],
                                            "name" => $item["item_name"],
                                            "quantity_served" => $item["quantity_served"] ?? 0,
                                        ]); ?>)' title="Manage Item">Manage</a></li>
                                        <?php endif; ?>
                                        <?php if ($isAdmin): ?>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick='openItemModal("manage-batches", <?php echo json_encode([
                                            "item_code" => $item["item_code"],
                                            "name" => $item["item_name"],
                                            "quantity_served" => $item["quantity_served"] ?? 0,
                                        ]); ?>)' title="Manage Item">Manage</a></li>
                                        <?php endif; ?>
                                        <?php if (strtolower((string) session()->get('role')) === 'viewer'): ?>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick='openItemModal("manage-batches", <?php echo json_encode([
                                            "item_code" => $item["item_code"],
                                            "name" => $item["item_name"],
                                            "quantity_served" => $item["quantity_served"] ?? 0,
                                        ]); ?>)' title="View Item Batches">Manage</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
</div>

<!-- ===================== SINGLE ITEM MODAL (Add/Edit/View) ===================== -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" id="itemModalDialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <div class="d-flex align-items-center">
                    <h5 class="modal-title fw-bold mb-0" id="itemModalLabel" style="color: #0f172a; font-size: 1.4rem; letter-spacing: -0.01em;">
                        Add New Item
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
            </div>

            <form method="POST" action="<?php echo base_url('inventory/create'); ?>" id="itemForm">
                <div class="modal-body px-4 py-4">

                    <?php
                        $modal_errors = session()->getFlashdata('modal_errors');
                        $modal_mode   = session()->getFlashdata('modal_mode');
                        $modal_edit_id = session()->getFlashdata('modal_edit_id');
                    ?>
                    <?php if ($modal_errors): ?>
                    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-4 py-3">
                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.75rem; top: 0.5rem; right: 0.5rem;"></button>
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                            <div>
                                <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                                <div class="small"><?php echo $modal_errors; ?></div>
                            </div>
                    </div>
                </div>
                    <?php endif; ?>

                    <!-- View mode container (hidden by default) -->
                    <div id="itemViewContainer" style="display:none;">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Item Code</label>
                                <span class="fw-bold text-dark" id="view_item_code"></span>
                            </div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Category</label>
                                <span class="text-dark" id="view_item_category"></span>
                            </div>
                            <div class="col-12">
                                <label class="small fw-semibold text-secondary d-block">Item Name</label>
                                <span class="text-dark fw-medium" id="view_item_name"></span>
                            </div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Quantity</label>
                                <span class="text-dark fw-bold" id="view_item_quantity"></span>
                            </div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Unit</label>
                                <span class="text-dark" id="view_item_unit"></span>
                            </div>
                            <div class="col-12"><hr class="my-1"></div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Batch No.</label>
                                <span class="text-dark" id="view_item_batch"></span>
                            </div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Lot No.</label>
                                <span class="text-dark" id="view_item_lot"></span>
                            </div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Supplier Type</label>
                                <span class="text-dark" id="view_item_supplier_type"></span>
                            </div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Supplier</label>
                                <span class="text-dark" id="view_item_supplier_name"></span>
                            </div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Expiration Date</label>
                                <span class="text-dark" id="view_item_expiration"></span>
                            </div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Manufacturing Date</label>
                                <span class="text-dark" id="view_item_manufacturing"></span>
                            </div>
                            <div class="col-12" id="view_item_remarks_row" style="display:none;">
                                <hr class="my-1">
                                <div class="col-12">
                                    <label class="small fw-semibold text-secondary d-block mb-1">Remarks</label>
                                    <div class="bg-light rounded-3 p-3 border" style="white-space: pre-line; font-size: 0.95rem; color: #1f2937; line-height: 1.6;" id="view_item_remarks"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Batches management container (hidden by default) -->
                    <div id="batchManageContainer" style="display:none;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold mb-0" id="batchManageTitle"></h6>
                        </div>
                        <div id="batchManageTableWrapper"></div>
                    </div>

                    <div class="row g-3" id="itemFormFields">

                        <div class="col-lg-4 col-12">
                            <label for="item_category_id" class="form-label small fw-semibold text-secondary">
                                Category <span class="text-danger">*</span>
                            </label>
                             <select class="form-select input-custom"
                                      id="item_category_id"
                                      name="category_id"
                                      onchange="generateInventoryCode()"
                                      required>
                                <option value="" disabled selected hidden>Select Category</option>
                                <?php foreach (($categories ?? []) as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>" <?php echo ((string)old('category_id') === (string)$category['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-4 col-12">
                            <label for="item_inventory_code" class="form-label small fw-semibold text-secondary">Inventory Code <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_inventory_code"
                                   name="inventory_code"
                                   readonly
                                   style="background-color: #f3f4f6; cursor: default;"
                                   value="<?php echo old('inventory_code'); ?>">
                        </div>

                        <div class="col-lg-4 col-12">
                            <label for="item_code" class="form-label small fw-semibold text-secondary">
                                Item Code <span class="text-danger">*</span>
                            </label>
                            <div class="position-relative" style="position:relative;">
                                <input type="text"
                                       class="form-control input-custom"
                                       id="item_code"
                                       name="item_code"
                                       autocomplete="off"
                                       style="text-transform: uppercase; padding-right: 30px;"
                                       value="<?php echo old('item_code'); ?>"
                                       required>
                                <i class="fa-solid fa-xmark position-absolute top-50 end-0 translate-middle-y me-2 item-code-clear" style="color: #9ca3af; font-size: 0.9rem; cursor: pointer; display: none;"></i>
                                <div id="itemCodeDropdown" class="item-code-dropdown" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:9999; background:#fff; border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 4px 16px rgba(0,0,0,0.1); margin-top:4px; max-height:180px; overflow-y:auto;"></div>
                            </div>
                        </div>

                        <div class="col-lg-12 col-12">
                            <label for="item_name" class="form-label small fw-semibold text-secondary">
                                Item Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_name"
                                   name="name"
                                   value="<?php echo old('name'); ?>"
                                   required>
                        </div>

                        <div class="col-lg-4 col-12">
                            <label for="item_unit" class="form-label small fw-semibold text-secondary">
                                Unit <span class="text-danger">*</span>
                            </label>
                            <select class="form-select input-custom"
                                    id="item_unit"
                                    name="unit"
                                    required>
                                <option value="" disabled selected hidden>Select Unit</option>
                                <option value="pcs" <?php echo (old('unit') === 'pcs') ? 'selected' : ''; ?>>Pieces (pcs)</option>
                                <option value="box" <?php echo (old('unit') === 'box') ? 'selected' : ''; ?>>Box</option>
                                <option value="pack" <?php echo (old('unit') === 'pack') ? 'selected' : ''; ?>>Pack</option>
                                <option value="bottle" <?php echo (old('unit') === 'bottle') ? 'selected' : ''; ?>>Bottle</option>
                                <option value="vial" <?php echo (old('unit') === 'vial') ? 'selected' : ''; ?>>Vial</option>
                                <option value="ampoule" <?php echo (old('unit') === 'ampoule') ? 'selected' : ''; ?>>Ampoule</option>
                                <option value="tube" <?php echo (old('unit') === 'tube') ? 'selected' : ''; ?>>Tube</option>
                                <option value="syringe" <?php echo (old('unit') === 'syringe') ? 'selected' : ''; ?>>Syringe</option>
                                <option value="kit" <?php echo (old('unit') === 'kit') ? 'selected' : ''; ?>>Kit</option>
                                <option value="set" <?php echo (old('unit') === 'set') ? 'selected' : ''; ?>>Set</option>
                                <option value="liter" <?php echo (old('unit') === 'liter') ? 'selected' : ''; ?>>Liter (L)</option>
                                <option value="ml" <?php echo (old('unit') === 'ml') ? 'selected' : ''; ?>>Milliliter (ml)</option>
                                <option value="kg" <?php echo (old('unit') === 'kg') ? 'selected' : ''; ?>>Kilogram (kg)</option>
                                <option value="g" <?php echo (old('unit') === 'g') ? 'selected' : ''; ?>>Gram (g)</option>
                                <option value="mg" <?php echo (old('unit') === 'mg') ? 'selected' : ''; ?>>Milligram (mg)</option>
                                <option value="unit" <?php echo (old('unit') === 'unit') ? 'selected' : ''; ?>>Unit</option>
                                <option value="tank" <?php echo (old('unit') === 'tank') ? 'selected' : ''; ?>>Tank</option>
                                <option value="roll" <?php echo (old('unit') === 'roll') ? 'selected' : ''; ?>>Roll</option>
                                <option value="tray" <?php echo (old('unit') === 'tray') ? 'selected' : ''; ?>>Tray</option>
                                <option value="bag" <?php echo (old('unit') === 'bag') ? 'selected' : ''; ?>>Bag</option>
                                <option value="gallon" <?php echo (old('unit') === 'gallon') ? 'selected' : ''; ?>>Gallon</option>
                                <option value="cartridge" <?php echo (old('unit') === 'cartridge') ? 'selected' : ''; ?>>Cartridge</option>
                                <option value="pouch" <?php echo (old('unit') === 'pouch') ? 'selected' : ''; ?>>Pouch</option>
                            </select>
                        </div>

                        <div class="col-lg-4 col-12">
                            <label for="item_quantity" class="form-label small fw-semibold text-secondary">
                                Quantity <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control input-custom"
                                   id="item_quantity"
                                   name="quantity"
                                   min="0"
                                   value="<?php echo old('quantity', ''); ?>"
                                   required>
                        </div>
                         
                        <div class="w-55"></div>
                        <div class="col-lg-4 col-12">
                            <label for="item_batch_num" class="form-label small fw-semibold text-secondary">Batch No.</label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_batch_num"
                                   name="batch_num"
                                   value="<?php echo old('batch_num'); ?>">
                        </div>

                        <div class="col-lg-4 col-12">
                            <label for="item_lot_num" class="form-label small fw-semibold text-secondary">Lot No.</label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_lot_num"
                                   name="lot_num"
                                   value="<?php echo old('lot_num'); ?>">
                        </div>

                        <div class="w-55"></div>
                        <div class="col-lg-4 col-12">
                            <label for="item_supplier_type" class="form-label small fw-semibold text-secondary">
                                Supplier Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select input-custom"
                                    id="item_supplier_type"
                                    name="supplier_type"
                                    onchange="toggleSupplierName()"
                                    required>
                                <option value="" disabled selected hidden>Select Supplier Type</option>
                                <option value="supplier" <?php echo (old('supplier_type') === 'supplier') ? 'selected' : ''; ?>>Supplier</option>
                                <option value="donation" <?php echo (old('supplier_type') === 'donation') ? 'selected' : ''; ?>>Donation</option>
                                <option value="others" <?php echo (old('supplier_type') === 'others') ? 'selected' : ''; ?>>Others</option>
                            </select>
                        </div>

                        <div class="col-lg-8 col-12" id="supplierNameCol">
                            <label for="item_supplier_name_select" class="form-label small fw-semibold text-secondary">
                                Supplier <span class="text-danger">*</span>
                            </label>
                            <select class="form-select input-custom"
                                    id="item_supplier_name_select"
                                    name="supplier_name"
                                    required>
                                <option value="" disabled selected hidden>Select Supplier</option>
                            </select>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_supplier_name_text"
                                   name="supplier_name"
                                   placeholder="Enter supplier name..."
                                   style="display:none;">
                        </div>

                        <div class="col-lg-4 col-12">
                            <label for="item_expiration_date" class="form-label small fw-semibold text-secondary">
                                Expiration Date <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   class="form-control input-custom"
                                   id="item_expiration_date"
                                   name="expiration_date"
                                   value="<?php echo old('expiration_date'); ?>"
                                   required>
                        </div>

                        <div class="col-lg-4 col-12">
                            <label for="item_manufacturing_date" class="form-label small fw-semibold text-secondary">Manufacturing Date</label>
                            <input type="date"
                                   class="form-control input-custom"
                                   id="item_manufacturing_date"
                                   name="manufacturing_date"
                                   value="<?php echo old('manufacturing_date'); ?>">
                        </div>

                        

                        <div class="col-12">
                            <label for="item_remarks" class="form-label small fw-semibold text-secondary">Remarks</label>
                            <textarea class="form-control input-custom"
                                      id="item_remarks"
                                      name="remarks"
                                      rows="2"><?php echo old('remarks'); ?></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end">
                    <button type="button" id="itemModalCancelBtn"
                            data-bs-dismiss="modal"
                            style="background: #fff; color: #374151; border: 1.5px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.4rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s, border-color 0.15s;"
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='#fff'">
                        Close
                    </button>
                    <button type="submit" id="itemFormSubmitBtn"
                            class="btn btn-success-custom">
                        Add Item
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
var allSuppliers = <?php echo json_encode($suppliers ?? []); ?>;
var itemBatches = <?php echo json_encode($batches_by_code ?? []); ?>;
var allItemCodes = <?php echo json_encode(array_column($all_item_codes ?? [], 'item_code')); ?>;

var _manageBatchesData = null;
var _batchManageBatches = [];
var _batchManageSortCol = -1;
var _batchManageSortAsc = true;

function _batchManageRenderTable() {
    var wrapper = document.getElementById('batchManageTableWrapper');
    var batches = _batchManageBatches.slice();
    var sortCol = _batchManageSortCol;
    var sortAsc = _batchManageSortAsc;

    if (sortCol >= 0) {
        var keys = ['item_name', 'inventory_code', 'quantity_on_hand', 'unit', 'expiration_date', 'stock_status'];
        <?php if ($isAdmin): ?>
        keys = ['item_name', 'inventory_code', 'quantity_on_hand', 'unit', 'expiration_date', 'stock_status'];
        <?php elseif (strtolower((string) session()->get('role')) === 'encoder'): ?>
        keys = ['item_name', 'inventory_code', 'quantity_on_hand', 'quantity_used', 'unit', 'expiration_date', 'stock_status'];
        <?php endif; ?>
        var key = keys[sortCol];
        batches.sort(function(a, b) {
            var va, vb;
            if (key === 'quantity_on_hand' || key === 'quantity_used') {
                va = parseInt(a[key]) || 0;
                vb = parseInt(b[key]) || 0;
            } else if (key === 'stock_status') {
                var order = {'In Stock':0,'Low Stock':1,'Near Expiry':2,'Out of Stock':3,'Expired':4,'Archived':5};
                var bqtyA = parseInt(a.quantity_on_hand)||0, bqtyB = parseInt(b.quantity_on_hand)||0;
                var bexpA = a.expiration_date||'', bexpB = b.expiration_date||'';
                var btotalA = parseInt(a.quantity)||0, btotalB = parseInt(b.quantity)||0;
                var bthA = btotalA>0?Math.max(1,Math.ceil(btotalA*0.15)):0;
                var bthB = btotalB>0?Math.max(1,Math.ceil(btotalB*0.15)):0;
                var aArch = parseInt(a.status)===0, bArch = parseInt(b.status)===0;
                va = aArch ? 'Archived' : _batchManageGetStatus(bexpA, bqtyA, bthA);
                vb = bArch ? 'Archived' : _batchManageGetStatus(bexpB, bqtyB, bthB);
                va = order[va] ?? 6;
                vb = order[vb] ?? 6;
            } else if (key === 'expiration_date') {
                va = a[key] || '9999-99-99';
                vb = b[key] || '9999-99-99';
            } else {
                va = (a[key] || '').toLowerCase();
                vb = (b[key] || '').toLowerCase();
            }
            if (va < vb) return sortAsc ? -1 : 1;
            if (va > vb) return sortAsc ? 1 : -1;
            return 0;
        });
    } else {
        // Default sort: by stock status, then expiration date
        batches.sort(function(a, b) {
            var order = {'In Stock':0,'Low Stock':1,'Near Expiry':2,'Out of Stock':3,'Expired':4,'Archived':5};
            var bqtyA = parseInt(a.quantity_on_hand)||0, bqtyB = parseInt(b.quantity_on_hand)||0;
            var bexpA = a.expiration_date||'', bexpB = b.expiration_date||'';
            var btotalA = parseInt(a.quantity)||0, btotalB = parseInt(b.quantity)||0;
            var bthA = btotalA>0?Math.max(1,Math.ceil(btotalA*0.15)):0;
            var bthB = btotalB>0?Math.max(1,Math.ceil(btotalB*0.15)):0;
            var aArch = parseInt(a.status)===0, bArch = parseInt(b.status)===0;
            var sA = aArch ? 'Archived' : _batchManageGetStatus(bexpA, bqtyA, bthA);
            var sB = bArch ? 'Archived' : _batchManageGetStatus(bexpB, bqtyB, bthB);
            var va = order[sA] ?? 6;
            var vb = order[sB] ?? 6;
            if (va !== vb) return va - vb;
            var expA = bexpA || '9999-99-99';
            var expB = bexpB || '9999-99-99';
            if (expA < expB) return -1;
            if (expA > expB) return 1;
            return 0;
        });
    }

    var colHeaders = ['Inventory Name', 'Inventory Code', 'Stock', 'Unit', 'Expiry', 'Stock Status', 'Actions'];
    var sortKeys = ['item_name', 'inventory_code', 'quantity_on_hand', 'unit', 'expiration_date', 'stock_status', ''];
    <?php if ($isAdmin): ?>
    colHeaders = ['Inventory Name', 'Inventory Code', 'Stock', 'Consumed', 'Unit', 'Expiry', 'Stock Status', 'Actions'];
    sortKeys = ['item_name', 'inventory_code', 'quantity_on_hand', '', 'unit', 'expiration_date', 'stock_status', ''];
    <?php elseif (strtolower((string) session()->get('role')) === 'encoder'): ?>
    colHeaders = ['Inventory Name', 'Inventory Code', 'Stock', 'Consumed', 'Unit', 'Expiry', 'Stock Status', 'Actions'];
    sortKeys = ['item_name', 'inventory_code', 'quantity_on_hand', 'quantity_used', 'unit', 'expiration_date', 'stock_status', ''];
    <?php endif; ?>

    var html = '<div class="mb-3"><input type="text" class="form-control form-control-sm" id="batchSearchInput" placeholder="Type to search..." style="border-radius: 8px; font-size: 0.85rem;"></div>';
    html += '<div class="table-responsive-custom"><table class="table table-custom table-hover mb-0" id="batchManageTable"><thead><tr>';
    for (var c = 0; c < colHeaders.length; c++) {
        var isSortable = sortKeys[c] !== '';
        var cls = isSortable ? ' style="cursor:pointer;"' : '';
        var arrow = '';
        if (isSortable && _batchManageSortCol === c) {
            arrow = _batchManageSortAsc ? ' <i class="fa-solid fa-sort-up" style="font-size:0.65rem; opacity:0.7;"></i>' : ' <i class="fa-solid fa-sort-down" style="font-size:0.65rem; opacity:0.7;"></i>';
        } else if (isSortable) {
            arrow = ' <i class="fa-solid fa-sort" style="font-size:0.65rem; opacity:0.4;"></i>';
        }
        html += '<th class="text-center"' + cls + ' data-col="' + c + '">' + colHeaders[c] + arrow + '</th>';
    }
    html += '</tr></thead><tbody>';

    for (var i = 0; i < batches.length; i++) {
        var b = batches[i];
        var bqty = parseInt(b.quantity_on_hand) || 0;
        var bexp = b.expiration_date || '';
        var bexpired = bexp && bexp < getToday();
        var bnear = bexp && bexp >= getToday() && bexp <= getNearDate() && bqty > 0;
        var btotal = parseInt(b.quantity) || 0;
        var bthreshold = btotal > 0 ? Math.max(1, Math.ceil(btotal * 0.15)) : 0;
        var barchived = parseInt(b.status) === 0;
        var bbadge, bstatus;
        if (barchived) { bbadge = 'bg-secondary-subtle text-muted border border-secondary-subtle'; bstatus = 'Archived'; }
        else if (bexpired) { bbadge = 'bg-dark-subtle text-dark border border-dark-subtle'; bstatus = 'Expired'; }
        else if (bnear) { bbadge = 'bg-danger-subtle text-dark border border-danger-subtle'; bstatus = 'Near Expiry'; }
        else if (bqty === 0) { bbadge = 'bg-danger-subtle text-dark border border-danger-subtle'; bstatus = 'Out of Stock'; }
        else if (bqty <= bthreshold) { bbadge = 'bg-warning-subtle text-dark border border-warning-subtle'; bstatus = 'Low Stock'; }
        else { bbadge = 'bg-success-subtle text-dark border border-success-subtle'; bstatus = 'In Stock'; }
        var expDisplay = bexp ? new Date(bexp).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : 'N/A';
        html += '<tr>';
        html += '<td class="text-center small">' + (b.item_name || 'N/A') + '</td>';
        html += '<td class="text-center small text-muted">' + (b.inventory_code || 'N/A') + '</td>';
        html += '<td class="text-center">' + bqty + '</td>';
        <?php if ($isAdmin): ?>
        html += '<td class="text-center small text-muted">' + (parseInt(b.quantity_served) || 0) + '</td>';
        <?php elseif (strtolower((string) session()->get('role')) === 'encoder'): ?>
        html += '<td class="text-center small">' + (b.quantity_used || 0) + '</td>';
        <?php endif; ?>
        html += '<td class="text-center small text-muted">' + (b.unit || 'N/A') + '</td>';
        html += '<td class="text-center small">' + expDisplay + '</td>';
        html += '<td class="text-center"><span class="badge badge-action rounded-pill ' + bbadge + '">' + bstatus + '</span></td>';
        html += '<td class="text-center"><div class="dropdown d-inline-block">';
        html += '<button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" style="padding:2px 8px;font-size:0.7rem;font-weight:600;">Actions</button>';
        html += '<ul class="dropdown-menu dropdown-menu-end" style="font-size:0.75rem;">';
        <?php if ($isAdmin): ?>
        if (barchived) {
        html += '<li><a class="dropdown-item" href="<?php echo base_url('inventory/restore'); ?>/' + b.id + '">Restore</a></li>';
        } else {
        html += '<li><a class="dropdown-item" href="javascript:void(0)" onclick=\'event.stopPropagation(); openItemModal("edit", ' + JSON.stringify({id: b.id, item_code: b.item_code, inventory_code: b.inventory_code, name: b.item_name, category_id: b.category_id, quantity: b.quantity, unit: b.unit, supplier_type: (b.supplier_type ? b.supplier_type.toLowerCase().replace(" ", "_") : "supplier"), supplier_name: (b.supplier_name || ""), expiration_date: b.expiration_date, manufacturing_date: b.manufacturing_date, batch_num: b.batch_num, lot_num: b.lot_num, remarks: b.remarks}) + ')\'>Manage</a></li>';
        }
        <?php elseif (strtolower((string) session()->get('role')) === 'encoder'): ?>
        if (barchived) {
        html += '<li><a class="dropdown-item" href="<?php echo base_url('inventory/restore'); ?>/' + b.id + '">Restore</a></li>';
        } else {
        if (bqty > 0) {
        html += '<li><a class="dropdown-item" href="javascript:void(0)" onclick=\'openConsumeModal(' + JSON.stringify({id: b.id, item_code: b.item_code, item_name: b.item_name}) + ')\'>Consume</a></li>';
        }
        }
        <?php endif; ?>
        <?php if (strtolower((string) session()->get('role')) === 'viewer'): ?>
        html += '<li><a class="dropdown-item" href="javascript:void(0)" onclick=\'event.stopPropagation(); showViewBatchModal(' + JSON.stringify(b) + ')\'>View</a></li>';
        <?php else: ?>
        if (!barchived) {
        html += '<li><a class="dropdown-item" href="javascript:void(0)" onclick="event.stopPropagation(); new bootstrap.Modal(document.getElementById(\'archiveItemModal' + b.id + '\')).show();">Archive</a></li>';
        }
        <?php endif; ?>
        html += '</ul></div></td>';
        html += '</tr>';
    }
    html += '</tbody></table></div></div>';
    if (batches.length === 0) {
        html = '<div class="text-muted text-center py-3">No batches found for this item.</div>';
    }
    wrapper.innerHTML = html;

    var ths = wrapper.querySelectorAll('#batchManageTable thead th');
    ths.forEach(function(th) {
        var col = parseInt(th.getAttribute('data-col'));
        if (isNaN(col)) return;
        th.addEventListener('click', function() {
            if (_batchManageSortCol === col) {
                _batchManageSortAsc = !_batchManageSortAsc;
            } else {
                _batchManageSortCol = col;
                _batchManageSortAsc = true;
            }
            _batchManageRenderTable();
        });
    });

    var searchInput = document.getElementById('batchSearchInput');
    if (searchInput && batches.length > 0) {
        searchInput.addEventListener('input', function () {
            var filter = this.value.toLowerCase();
            var rows = wrapper.querySelectorAll('#batchManageTable tbody tr');
            rows.forEach(function (row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
}

function _batchManageGetStatus(exp, qty, threshold) {
    var expired = exp && exp < getToday();
    var near = exp && exp >= getToday() && exp <= getNearDate() && qty > 0;
    if (expired) return 'Expired';
    if (qty <= 0) return 'Out of Stock';
    if (near) return 'Near Expiry';
    if (qty <= threshold) return 'Low Stock';
    return 'In Stock';
}
function openItemModal(mode, data) {
    var form = document.getElementById('itemForm');
    var label = document.getElementById('itemModalLabel');
    var btn = document.getElementById('itemFormSubmitBtn');
    var cancelBtn = document.getElementById('itemModalCancelBtn');
    var modalDialog = document.getElementById('itemModalDialog');
    modalDialog.classList.remove('modal-xl');
    modalDialog.classList.add('modal-lg');
    var fields = ['item_inventory_code','item_code','item_name','item_category_id','item_quantity','item_unit','item_supplier_type','item_expiration_date','item_manufacturing_date','item_batch_num','item_lot_num','item_remarks'];
    function disableFields(disabled) {
        fields.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.disabled = disabled;
        });
        document.getElementById('item_supplier_name_select').disabled = disabled;
        document.getElementById('item_supplier_name_text').disabled = disabled;
    }
    if (mode === 'view') {
        form.action = '#';
        label.textContent = 'View Item';
        btn.style.display = 'none';
        document.getElementById('itemFormFields').style.display = 'none';
        document.getElementById('itemViewContainer').style.display = '';
        document.getElementById('batchManageContainer').style.display = 'none';
        cancelBtn.textContent = 'Close';
        cancelBtn.setAttribute('data-bs-dismiss', 'modal');
        cancelBtn.onclick = null;
        document.getElementById('view_item_code').textContent = data.inventory_code || '';
        document.getElementById('view_item_category').textContent = data.category || '';
        document.getElementById('view_item_name').textContent = data.name || '';
        document.getElementById('view_item_quantity').textContent = data.quantity !== undefined ? data.quantity : '';
        document.getElementById('view_item_unit').textContent = data.unit || '';
        document.getElementById('view_item_batch').textContent = data.batch_num || 'N/A';
        document.getElementById('view_item_lot').textContent = data.lot_num || 'N/A';
        document.getElementById('view_item_supplier_type').textContent = data.supplier_type ? data.supplier_type.charAt(0).toUpperCase() + data.supplier_type.slice(1) : 'N/A';
        document.getElementById('view_item_supplier_name').textContent = data.supplier_name || 'N/A';
        document.getElementById('view_item_expiration').textContent = data.expiration_date || 'N/A';
        document.getElementById('view_item_manufacturing').textContent = data.manufacturing_date || 'N/A';
        var remarksEl = document.getElementById('view_item_remarks');
        var remarksRow = document.getElementById('view_item_remarks_row');
        if (data.remarks) {
            remarksEl.textContent = data.remarks;
            remarksRow.style.display = '';
        } else {
            remarksRow.style.display = 'none';
        }
    } else if (mode === 'manage-batches') {
        _manageBatchesData = data;
        form.action = '#';
        modalDialog.classList.remove('modal-lg');
        modalDialog.classList.add('modal-xl');
        label.textContent = 'Manage Item';
        btn.style.display = 'none';
        document.getElementById('itemFormFields').style.display = 'none';
        document.getElementById('itemViewContainer').style.display = 'none';
        document.getElementById('batchManageContainer').style.display = '';
        cancelBtn.textContent = 'Close';
        cancelBtn.setAttribute('data-bs-dismiss', 'modal');
        cancelBtn.onclick = null;
        document.getElementById('batchManageTitle').textContent = data.name || 'Item Batches';
        var wrapper = document.getElementById('batchManageTableWrapper');
        var batches = itemBatches[data.item_code] || [];
        _batchManageBatches = batches;
        _batchManageSortCol = -1;
        _batchManageSortAsc = true;
        _batchManageRenderTable();
    } else {
        document.getElementById('itemFormFields').style.display = '';
        document.getElementById('itemViewContainer').style.display = 'none';
        document.getElementById('batchManageContainer').style.display = 'none';
        if (mode === 'edit') {
        form.action = '<?php echo base_url('inventory/edit'); ?>/' + data.id;
        label.textContent = 'Manage Item';
        btn.textContent = 'Update Item';
        btn.style.display = '';
        disableFields(false);
        cancelBtn.textContent = 'Close';
        cancelBtn.removeAttribute('data-bs-dismiss');
        if (_manageBatchesData) {
            cancelBtn.onclick = function() { openItemModal('manage-batches', _manageBatchesData); };
        } else {
            cancelBtn.setAttribute('data-bs-dismiss', 'modal');
            cancelBtn.onclick = null;
        }
        document.getElementById('item_inventory_code').value = data.inventory_code || '';
        document.getElementById('item_code').value = data.item_code || '';
        var icClear = document.querySelector('.item-code-clear');
        if (icClear) icClear.style.display = data.item_code ? 'block' : 'none';
        document.getElementById('item_name').value = data.name || '';
        document.getElementById('item_category_id').value = data.category_id || '';
        document.getElementById('item_quantity').value = data.quantity !== undefined ? data.quantity : '';
        document.getElementById('item_unit').value = data.unit || '';
        document.getElementById('item_supplier_type').value = data.supplier_type || 'supplier';
        toggleSupplierName();
        var sel = document.getElementById('item_supplier_name_select');
        var txt = document.getElementById('item_supplier_name_text');
        if (sel.style.display !== 'none') {
            sel.value = data.supplier_name || '';
        } else {
            txt.value = data.supplier_name || '';
        }
        document.getElementById('item_expiration_date').value = data.expiration_date || '';
        document.getElementById('item_manufacturing_date').value = data.manufacturing_date || '';
        document.getElementById('item_batch_num').value = data.batch_num || '';
        document.getElementById('item_lot_num').value = data.lot_num || '';
        document.getElementById('item_remarks').value = data.remarks || '';
        document.getElementById('item_remarks').disabled = false;
        document.getElementById('itemModalCancelBtn').textContent = 'Close';
    } else {
        form.action = '<?php echo base_url('inventory/create'); ?>';
        label.textContent = 'Add New Item';
        btn.textContent = 'Add Item';
        btn.style.display = '';
        disableFields(false);
        cancelBtn.textContent = 'Close';
        cancelBtn.setAttribute('data-bs-dismiss', 'modal');
        cancelBtn.onclick = null;
        document.getElementById('item_inventory_code').value = '';
        document.getElementById('item_code').value = '';
        var icClear = document.querySelector('.item-code-clear');
        if (icClear) icClear.style.display = 'none';
        document.getElementById('item_category_id').value = '';
        document.getElementById('item_quantity').value = '';
        document.getElementById('item_unit').value = '';
        document.getElementById('item_supplier_type').value = '';
        document.getElementById('item_supplier_name_select').value = '';
        document.getElementById('item_supplier_name_text').value = '';
        toggleSupplierName();
        document.getElementById('item_expiration_date').value = '';
        document.getElementById('item_manufacturing_date').value = '';
        document.getElementById('item_batch_num').value = '';
        document.getElementById('item_lot_num').value = '';
        document.getElementById('item_remarks').value = '';
        document.getElementById('item_remarks').disabled = false;
        }
    }
    
    var modalEl = document.getElementById('itemModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) {
        modal = new bootstrap.Modal(modalEl);
    }
    modal.show();
}

function toggleSupplierName() {
    var typeSelect = document.getElementById('item_supplier_type');
    var sel = document.getElementById('item_supplier_name_select');
    var txt = document.getElementById('item_supplier_name_text');
    sel.innerHTML = '<option value="" disabled selected hidden>Select Supplier</option>';
    txt.value = '';
    if (typeSelect.value === 'supplier' || typeSelect.value === 'donation') {
        sel.style.display = '';
        txt.style.display = 'none';
        sel.required = true;
        txt.required = false;
        sel.disabled = false;
        txt.disabled = true;
        var filterType = typeSelect.value === 'supplier' ? 'Supplier' : 'Donation';
        for (var i = 0; i < allSuppliers.length; i++) {
            if (allSuppliers[i].supplier_type === filterType) {
                var opt = document.createElement('option');
                opt.value = allSuppliers[i].supplier_name;
                opt.textContent = allSuppliers[i].supplier_name;
                sel.appendChild(opt);
            }
        }
    } else if (typeSelect.value === 'others') {
        sel.style.display = 'none';
        txt.style.display = '';
        sel.required = false;
        txt.required = true;
        sel.disabled = true;
        txt.disabled = false;
    } else {
        sel.disabled = true;
        txt.disabled = true;
    }
}

function generateInventoryCode() {
    var categoryId = document.getElementById('item_category_id').value;
    var invCodeField = document.getElementById('item_inventory_code');
    if (!categoryId) {
        invCodeField.value = '';
        return;
    }
    fetch('<?php echo base_url('inventory/generate_inventory_code'); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'category_id=' + encodeURIComponent(categoryId)
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            invCodeField.value = data.inventory_code;
        }
    });
}

function getToday() {
    var d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
}
function getNearDate() {
    var d = new Date();
    d.setDate(d.getDate() + 30);
    return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
}

<?php if ($modal_mode === 'edit' && $modal_edit_id): ?>
document.addEventListener('DOMContentLoaded', function () {
    openItemModal('edit', {
        id: <?php echo $modal_edit_id; ?>,
        inventory_code: '<?php echo addslashes(old('inventory_code', '')); ?>',
        item_code: '<?php echo addslashes(old('item_code', '')); ?>',
        name: '<?php echo addslashes(old('name', '')); ?>',
        category_id: '<?php echo addslashes(old('category_id', '')); ?>',
        quantity: '<?php echo addslashes(old('quantity', '')); ?>',
        unit: '<?php echo addslashes(old('unit', '')); ?>',
        supplier_type: '<?php echo addslashes(old('supplier_type', 'supplier')); ?>',
        supplier_name: '<?php echo addslashes(old('supplier_name', '')); ?>',
        expiration_date: '<?php echo addslashes(old('expiration_date', '')); ?>',
        manufacturing_date: '<?php echo addslashes(old('manufacturing_date', '')); ?>',
        batch_num: '<?php echo addslashes(old('batch_num', '')); ?>',
        lot_num: '<?php echo addslashes(old('lot_num', '')); ?>'
    });
});
<?php elseif ($modal_mode === 'create'): ?>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('item_inventory_code').value = '<?php echo addslashes(old('inventory_code', '')); ?>';
    document.getElementById('item_code').value = '<?php echo addslashes(old('item_code', '')); ?>';
    document.getElementById('item_category_id').value = '<?php echo addslashes(old('category_id', '')); ?>';
    document.getElementById('item_name').value = '<?php echo addslashes(old('name', '')); ?>';
    generateInventoryCode();
    document.getElementById('item_quantity').value = '<?php echo addslashes(old('quantity', '')); ?>';
    document.getElementById('item_unit').value = '<?php echo addslashes(old('unit', '')); ?>';
    document.getElementById('item_supplier_type').value = '<?php echo addslashes(old('supplier_type', '')); ?>';
    toggleSupplierName();
    var sel = document.getElementById('item_supplier_name_select');
    var txt = document.getElementById('item_supplier_name_text');
    if (sel.style.display !== 'none') {
        sel.value = '<?php echo addslashes(old('supplier_name', '')); ?>';
    } else {
        txt.value = '<?php echo addslashes(old('supplier_name', '')); ?>';
    }
    document.getElementById('item_expiration_date').value = '<?php echo addslashes(old('expiration_date', '')); ?>';
    document.getElementById('item_manufacturing_date').value = '<?php echo addslashes(old('manufacturing_date', '')); ?>';
    document.getElementById('item_batch_num').value = '<?php echo addslashes(old('batch_num', '')); ?>';
    document.getElementById('item_lot_num').value = '<?php echo addslashes(old('lot_num', '')); ?>';
    (bootstrap.Modal.getInstance(document.getElementById('itemModal')) || new bootstrap.Modal(document.getElementById('itemModal'))).show();
});
<?php endif; ?>

// Reset modal state on close
document.getElementById('itemModal')?.addEventListener('hidden.bs.modal', function () {
    document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
    var form = document.getElementById('itemForm');
    form.reset();
    var fieldsToClear = [
        'item_inventory_code', 'item_code', 'item_name', 'item_category_id', 
        'item_quantity', 'item_unit', 'item_supplier_type', 'item_supplier_name_select', 
        'item_supplier_name_text', 'item_expiration_date', 'item_manufacturing_date', 
        'item_batch_num', 'item_lot_num', 'item_remarks'
    ];
    fieldsToClear.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.value = '';
        }
    });
    toggleSupplierName();
    form.querySelectorAll('[disabled]').forEach(function(el) { el.disabled = false; });
    document.getElementById('itemFormSubmitBtn').style.display = '';
    document.getElementById('batchManageContainer').style.display = 'none';
});

document.getElementById('itemModal')?.addEventListener('hidden.bs.modal', function () {
    var err = this.querySelector('.modal-body .alert.alert-danger');
    if (err) err.remove();
});

// Item Code combobox
(function() {
    var input = document.getElementById('item_code');
    var dropdown = document.getElementById('itemCodeDropdown');
    var clearBtn = document.querySelector('.item-code-clear');
    if (!input || !dropdown) return;

    function renderItemCodes(query) {
        var q = (query || '').toUpperCase();
        var filtered = allItemCodes.filter(function(c) {
            return !q || c.indexOf(q) !== -1;
        });
        if (filtered.length === 0) {
            dropdown.innerHTML = '<div style="padding:8px 12px; font-size:0.85rem; color:#9ca3af;">No codes found</div>';
        } else {
            dropdown.innerHTML = filtered.map(function(c) {
                return '<div class="item-code-option" data-code="' + c.replace(/"/g, '&quot;') + '" style="padding:8px 12px;border-radius:6px;cursor:pointer;font-size:0.85rem;color:#1f2937;transition:background 0.12s;" onmouseover="this.style.background=\'#f3f4f6\'" onmouseout="this.style.background=\'transparent\'">' + c + '</div>';
            }).join('');
            dropdown.querySelectorAll('.item-code-option').forEach(function(opt) {
                opt.addEventListener('click', function() {
                    input.value = this.getAttribute('data-code');
                    dropdown.style.display = 'none';
                    if (clearBtn) clearBtn.style.display = 'block';
                });
            });
        }
    }

    input.addEventListener('focus', function() { renderItemCodes(input.value); dropdown.style.display = 'block'; });
    input.addEventListener('input', function() { renderItemCodes(input.value); dropdown.style.display = 'block'; });
    input.addEventListener('blur', function() { setTimeout(function() { dropdown.style.display = 'none'; }, 200); });

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            input.value = '';
            clearBtn.style.display = 'none';
            input.focus();
        });
    }

    // Also register on modal shown to re-bind
    document.getElementById('itemModal')?.addEventListener('shown.bs.modal', function() {
        if (input.value) { if (clearBtn) clearBtn.style.display = 'block'; }
    });
})();
</script>

<!-- ===================== ARCHIVE ITEM MODALS ===================== -->
<?php if (!empty($items)): ?>
    <?php foreach ($items as $item): ?>
        <div class="modal fade" id="archiveItemMainModal<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="archiveItemMainModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                        <div class="d-flex align-items-center">
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="archiveItemMainModalLabel<?php echo $item['id']; ?>" style="color: #0f172a; font-size: 1.25rem; letter-spacing: 0;">
                                    Archive Item
                                </h5>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                    </div>
                    <div class="modal-body px-4 py-4">
                        <div class="p-3 bg-light rounded-3 border border-light-subtle mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                        <?php echo htmlspecialchars($item['item_name']); ?>
                                    </div>
                                    <div class="text-muted small">Code: <?php echo htmlspecialchars($item['inventory_code'] ?? $item['item_code']); ?></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-secondary mb-0" style="font-size: 0.925rem; line-height: 1.5;">Are you sure you want to archive this inventory item?</p>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                        <button type="button"
                                data-bs-dismiss="modal"
                                style="background: #fff; color: #374151; border: 1.5px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                onmouseover="this.style.background='#f9fafb'"
                                onmouseout="this.style.background='#fff'">
                            Close
                        </button>
                        <a href="<?php echo base_url('inventory/archive/' . $item['id']); ?>"
                           style="background: #ef4444; color: #fff; border: 1px solid transparent; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                           onmouseover="this.style.background='#dc2626'"
                           onmouseout="this.style.background='#ef4444'">
                            Archive Item
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<!-- ===================== BATCH ARCHIVE MODALS ===================== -->
<?php if (isset($batches_by_code) && is_array($batches_by_code)): ?>
    <?php foreach ($batches_by_code as $code => $batches): ?>
        <?php foreach ($batches as $batch): ?>
            <?php if (!empty($batch['id'])): ?>
            <div class="modal fade" id="archiveItemModal<?= $batch['id'] ?>" tabindex="-1" aria-labelledby="archiveItemModalLabel<?= $batch['id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                        <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h5 class="modal-title fw-bold mb-0" id="archiveItemModalLabel<?= $batch['id'] ?>" style="color: #0f172a; font-size: 1.25rem; letter-spacing: 0;">
                                        Archive Item
                                    </h5>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                        </div>
                        <div class="modal-body px-4 py-4">
                            <div class="p-3 bg-light rounded-3 border border-light-subtle mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                            <?= htmlspecialchars($batch['item_name'] ?? '') ?>
                                        </div>
                                        <div class="text-muted small">Code: <?= htmlspecialchars($batch['inventory_code'] ?? $batch['item_code'] ?? $code) ?></div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-secondary mb-0" style="font-size: 0.925rem; line-height: 1.5;">Are you sure you want to archive this inventory item?</p>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                            <button type="button"
                                    data-bs-dismiss="modal"
                                    style="background: #fff; color: #374151; border: 1.5px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                                    onmouseover="this.style.background='#f9fafb'"
                                    onmouseout="this.style.background='#fff'">
                                Close
                            </button>
                            <a href="<?php echo base_url('inventory/archive/' . $batch['id']); ?>"
                               style="background: #ef4444; color: #fff; border: 1px solid transparent; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                               onmouseover="this.style.background='#dc2626'"
                               onmouseout="this.style.background='#ef4444'">
                                Archive Item
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>

<!-- ===================== CONSUME MODAL ===================== -->
<div class="modal fade" id="consumeModal" tabindex="-1" aria-labelledby="consumeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <h5 class="modal-title fw-bold mb-0" id="consumeModalLabel" style="color: #0f172a; font-size: 1.25rem;">
                    Consume Item
                </h5>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
            </div>
            <form method="POST" action="<?php echo base_url('inventory/consume'); ?>">
                <div class="modal-body px-4 py-4">
                    <div class="p-3 bg-light rounded-3 border border-light-subtle mb-3">
                        <div class="fw-bold text-dark" style="font-size: 0.95rem;" id="consumeItemName"></div>
                        <div class="text-muted small" id="consumeItemCode"></div>
                    </div>

                    <input type="hidden" name="item_name" id="consumeItemNameHidden">
                    <input type="hidden" name="item_code" id="consumeItemCodeHidden">

                    <div class="mb-3">
                        <label for="consumeQuantity" class="form-label small fw-semibold text-secondary">
                            Quantity to Consume <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control input-custom" id="consumeQuantity" name="quantity" min="1" required>
                    </div>

                    <div>
                        <label for="consumeRemarks" class="form-label small fw-semibold text-secondary">Remarks</label>
                        <textarea class="form-control input-custom" id="consumeRemarks" name="remarks" rows="3" placeholder="Remarks about consumption..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end gap-2">
                    <button type="button"
                            data-bs-dismiss="modal"
                            style="background: #fff; color: #374151; border: 1.5px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='#fff'">
                        Close
                    </button>
                    <button type="submit"
                            class="btn btn-success-custom">
                        Consume
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Batch View Modal -->
<div class="modal fade" id="batchViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <h5 class="modal-title fw-bold mb-0" style="color: #0f172a; font-size: 1.25rem;">Batch Details</h5>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="small fw-semibold text-secondary d-block">Inventory Code</label>
                        <span class="text-dark" id="bv_inventory_code"></span>
                    </div>
                    <div class="col-6">
                        <label class="small fw-semibold text-secondary d-block">Item Code</label>
                        <span class="text-dark" id="bv_item_code"></span>
                    </div>
                    <div class="col-12">
                        <label class="small fw-semibold text-secondary d-block">Item Name</label>
                        <span class="text-dark fw-medium" id="bv_item_name"></span>
                    </div>
                    <div class="col-6">
                        <label class="small fw-semibold text-secondary d-block">Stock</label>
                        <span class="text-dark fw-bold" id="bv_stock"></span>
                    </div>
                    <div class="col-6">
                        <label class="small fw-semibold text-secondary d-block">Unit</label>
                        <span class="text-dark" id="bv_unit"></span>
                    </div>
                    <div class="col-12"><hr class="my-1"></div>
                    <div class="col-6">
                        <label class="small fw-semibold text-secondary d-block">Batch No.</label>
                        <span class="text-dark" id="bv_batch"></span>
                    </div>
                    <div class="col-6">
                        <label class="small fw-semibold text-secondary d-block">Lot No.</label>
                        <span class="text-dark" id="bv_lot"></span>
                    </div>
                    <div class="col-6">
                        <label class="small fw-semibold text-secondary d-block">Expiration Date</label>
                        <span class="text-dark" id="bv_expiry"></span>
                    </div>
                    <div class="col-6">
                        <label class="small fw-semibold text-secondary d-block">Manufacturing Date</label>
                        <span class="text-dark" id="bv_mfg"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-3">
                <button type="button"
                        data-bs-dismiss="modal"
                        style="background: #fff; color: #374151; border: 1.5px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; height: 38px;"
                        onmouseover="this.style.background='#f9fafb'"
                        onmouseout="this.style.background='#fff'">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
var departmentBatches = <?php echo json_encode($department_batches ?? []); ?>;

function openConsumeModal(data) {
    document.getElementById('consumeItemName').textContent = data.item_name;
    document.getElementById('consumeItemCode').textContent = 'Code: ' + data.item_code;
    document.getElementById('consumeItemNameHidden').value = data.item_name;
    document.getElementById('consumeItemCodeHidden').value = data.item_code;
    document.getElementById('consumeQuantity').value = '';
    document.getElementById('consumeRemarks').value = '';

    new bootstrap.Modal(document.getElementById('consumeModal')).show();
}

function showViewBatchModal(b) {
    document.getElementById('bv_inventory_code').textContent = b.inventory_code || 'N/A';
    document.getElementById('bv_item_code').textContent = b.item_code || 'N/A';
    document.getElementById('bv_item_name').textContent = b.item_name || 'N/A';
    document.getElementById('bv_stock').textContent = b.quantity_on_hand || 0;
    document.getElementById('bv_unit').textContent = b.unit || 'N/A';
    document.getElementById('bv_batch').textContent = b.batch_num || 'N/A';
    document.getElementById('bv_lot').textContent = b.lot_num || 'N/A';
    document.getElementById('bv_expiry').textContent = b.expiration_date || 'N/A';
    document.getElementById('bv_mfg').textContent = b.manufacturing_date || 'N/A';
    new bootstrap.Modal(document.getElementById('batchViewModal')).show();
}
</script>

