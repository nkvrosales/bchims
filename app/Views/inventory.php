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
            <label for="inv_search_keyword">Search Keyword</label>
            <input
                type="text"
                id="inv_search_keyword"
                name="search"
                class="db-search-input"
                placeholder="Enter Name / Item code"
                value="<?php echo htmlspecialchars($search ?? ''); ?>"
                autocomplete="off"
            >
        </div>
        <div class="db-search-field db-search-field--dropdown">
            <label for="inv_search_category">Category</label>
            <select id="inv_search_category" name="category_id" class="db-search-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>"
                        <?php echo (isset($category_id) && (string)$category_id === (string)$cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_description']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="db-search-field db-search-field--dropdown">
            <label for="inv_search_status">Stock Status</label>
            <select id="inv_search_status" name="stock_status" class="db-search-select">
                <option value="">All Statuses</option>
                <option value="in_stock"   <?php echo (($stock_status ?? '') === 'in_stock')   ? 'selected' : ''; ?>>In Stock</option>
                <option value="low_stock"  <?php echo (($stock_status ?? '') === 'low_stock')  ? 'selected' : ''; ?>>Low Stock</option>
                <option value="out_of_stock" <?php echo (($stock_status ?? '') === 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                <option value="expired"    <?php echo (($stock_status ?? '') === 'expired')    ? 'selected' : ''; ?>>Expired</option>
                <option value="near_expiry" <?php echo (($stock_status ?? '') === 'near_expiry') ? 'selected' : ''; ?>>Near Expiry</option>
            </select>
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
                    <i class="fa-solid fa-plus"></i>
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
                    <th style="width: 20%">Name</th>
                    <th style="width: 14%" class="text-center">Item Code</th>
                    <th style="width: 12%">Category</th>
                    <th style="width: 8%" class="text-end">On Hand</th>
                    <th style="width: 8%" class="text-center">Unit</th>
                    <th style="width: 12%" class="text-center">Stock Status</th>
                    <th style="width: 8%" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="text-dark"><?php echo htmlspecialchars($item['item_name']); ?></div>
                            </td>
                            <td class="text-dark text-center" style="font-size: 0.85rem; color: var(--text-secondary);">
                                <?php echo htmlspecialchars($item['item_code']); ?>
                            </td>
                            <td>
                                <span class="text-dark"><?php echo htmlspecialchars($item['category_description'] ?? 'N/A'); ?></span>
                            </td>
                            <td class="text-end">
                                <span class="fs-6 text-dark">
                                    <?php echo (int)$item['quantity_on_hand']; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-dark"><?php echo htmlspecialchars($item['unit'] ?? 'N/A'); ?></span>
                            </td>
                            <td class="text-center">
                                <?php
                                    $stockQty = (int)$item['quantity_on_hand'];
                                    $expDate = $item['expiration_date'] ?? '';
                                    $totalQty = (int)$item['total_quantity'];
                                    $isExpired = !empty($expDate) && $expDate < date('Y-m-d') && $stockQty > 0;
                                    $isNearExpiry = !empty($expDate) && $expDate >= date('Y-m-d') && $expDate <= date('Y-m-d', strtotime('+30 days')) && $stockQty > 0 && !$isExpired;
                                    $lowStockThreshold = $totalQty > 0 ? max(1, (int)ceil($totalQty * 0.15)) : 0;
                                    if ($isExpired) {
                                        $badge  = 'bg-dark-subtle text-dark border border-dark-subtle';
                                        $status = 'Expired';
                                    } elseif ($isNearExpiry) {
                                        $badge  = 'bg-warning-subtle text-dark border border-warning-subtle';
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
                                        ]); ?>)' title="Manage Item">Manage</a></li>
                                        <?php endif; ?>
                                        <?php if ($isAdmin): ?>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick='openItemModal("manage-batches", <?php echo json_encode([
                                            "item_code" => $item["item_code"],
                                            "name" => $item["item_name"],
                                        ]); ?>)' title="Manage Item">Manage</a></li>
                                        <?php endif; ?>
                                        <?php if (strtolower((string) session()->get('role')) === 'viewer'): ?>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick='openItemModal("view", <?php echo json_encode([
                                            "inventory_code" => $item["inventory_code"] ?? "",
                                            "item_code" => $item["item_code"],
                                            "name" => $item["item_name"],
                                            "category" => $item["category_description"] ?? "",
                                            "quantity" => $item["quantity"],
                                            "unit" => $item["unit"] ?? "",
                                            "source_type" => str_replace(" ", "_", strtolower($item["source_type"] ?? "supplier")),
                                            "source_name" => $item["supplier_name"] ?? "",
                                            "expiration_date" => $item["expiration_date"] ?? "",
                                            "manufacturing_date" => $item["manufacturing_date"] ?? "",
                                            "batch_num" => $item["batch_num"] ?? "",
                                            "lot_num" => $item["lot_num"] ?? "",
                                            "remarks" => $item["remarks"] ?? "",
                                        ]); ?>)' title="View Item">View</a></li>
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
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
                                <label class="small fw-semibold text-secondary d-block">Source Type</label>
                                <span class="text-dark" id="view_item_source_type"></span>
                            </div>
                            <div class="col-6">
                                <label class="small fw-semibold text-secondary d-block">Source</label>
                                <span class="text-dark" id="view_item_source_name"></span>
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
                                        <?php echo htmlspecialchars($category['category_description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-50"></div>
                        <div class="col-lg-4 col-12">
                            <label for="item_inventory_code" class="form-label small fw-semibold text-secondary">Inventory Code</label>
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
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_code"
                                   name="item_code"
                                   style="text-transform: uppercase;"
                                   value="<?php echo old('item_code'); ?>"
                                   required>
                        </div>

                        <div class="col-lg-4 col-12">
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

                        <div class="col-lg-4 col-12">
                            <label for="item_source_type" class="form-label small fw-semibold text-secondary">
                                Source Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select input-custom"
                                    id="item_source_type"
                                    name="source_type"
                                    onchange="toggleSourceName()"
                                    required>
                                <option value="" disabled selected hidden>Select Source Type</option>
                                <option value="supplier" <?php echo (old('source_type') === 'supplier') ? 'selected' : ''; ?>>Supplier</option>
                                <option value="donation" <?php echo (old('source_type') === 'donation') ? 'selected' : ''; ?>>Donation</option>
                                <option value="others" <?php echo (old('source_type') === 'others') ? 'selected' : ''; ?>>Others</option>
                            </select>
                        </div>

                        <div class="col-lg-4 col-12" id="sourceNameCol">
                            <label for="item_source_name_select" class="form-label small fw-semibold text-secondary">
                                Source <span class="text-danger">*</span>
                            </label>
                            <select class="form-select input-custom"
                                    id="item_source_name_select"
                                    name="source_name"
                                    required>
                                <option value="" disabled selected hidden>Select Source</option>
                            </select>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_source_name_text"
                                   name="source_name"
                                   placeholder="Type source name..."
                                   style="display:none;">
                        </div>

                        <div class="col-lg-6 col-12">
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

                        <div class="col-lg-6 col-12">
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
                        Cancel
                    </button>
                    <button type="submit" id="itemFormSubmitBtn"
                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer;"
                            onmouseover="this.style.background='#059669'"
                            onmouseout="this.style.background='#10b981'">
                        Add Item
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
var allSources = <?php echo json_encode($sources ?? []); ?>;
var itemBatches = <?php echo json_encode($batches_by_code ?? []); ?>;

var _manageBatchesData = null;
function openItemModal(mode, data) {
    var form = document.getElementById('itemForm');
    var label = document.getElementById('itemModalLabel');
    var btn = document.getElementById('itemFormSubmitBtn');
    var cancelBtn = document.getElementById('itemModalCancelBtn');
    var fields = ['item_inventory_code','item_code','item_name','item_category_id','item_quantity','item_unit','item_source_type','item_expiration_date','item_manufacturing_date','item_batch_num','item_lot_num','item_remarks'];
    function disableFields(disabled) {
        fields.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.disabled = disabled;
        });
        document.getElementById('item_source_name_select').disabled = disabled;
        document.getElementById('item_source_name_text').disabled = disabled;
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
        document.getElementById('view_item_source_type').textContent = data.source_type ? data.source_type.charAt(0).toUpperCase() + data.source_type.slice(1) : 'N/A';
        document.getElementById('view_item_source_name').textContent = data.source_name || 'N/A';
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
        var html = '<table class="table table-sm table-hover mb-0" style="font-size:0.85rem;"><thead><tr class="text-muted"><th>Inventory Code</th><th>On Hand</th><th>Unit</th><th>Expiry</th><th>Stock Status</th><th class="text-end">Actions</th></tr></thead><tbody>';
        for (var i = 0; i < batches.length; i++) {
            var b = batches[i];
            var bqty = parseInt(b.quantity_on_hand) || 0;
            var bexp = b.expiration_date || '';
            var bexpired = bexp && bexp < getToday() && bqty > 0;
            var bnear = bexp && bexp >= getToday() && bexp <= getNearDate() && bqty > 0 && !bexpired;
            var btotal = parseInt(b.quantity) || 0;
            var bthreshold = btotal > 0 ? Math.max(1, Math.ceil(btotal * 0.15)) : 0;
            var bbadge, bstatus;
            if (bexpired) { bbadge = 'bg-dark-subtle text-dark border border-dark-subtle'; bstatus = 'Expired'; }
            else if (bnear) { bbadge = 'bg-warning-subtle text-dark border border-warning-subtle'; bstatus = 'Near Expiry'; }
            else if (bqty === 0) { bbadge = 'bg-danger-subtle text-dark border border-danger-subtle'; bstatus = 'Out of Stock'; }
            else if (bqty <= bthreshold) { bbadge = 'bg-warning-subtle text-dark border border-warning-subtle'; bstatus = 'Low Stock'; }
            else { bbadge = 'bg-success-subtle text-dark border border-success-subtle'; bstatus = 'In Stock'; }
            var expDisplay = bexp ? new Date(bexp).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : 'N/A';
            html += '<tr>';
            html += '<td class="small text-muted">' + (b.inventory_code || 'N/A') + '</td>';
            html += '<td>' + bqty + '</td>';
            html += '<td class="small text-muted">' + (b.unit || 'N/A') + '</td>';
            html += '<td class="small">' + expDisplay + '</td>';
            html += '<td><span class="badge badge-action rounded-pill ' + bbadge + '">' + bstatus + '</span></td>';
            html += '<td class="text-end"><div class="dropdown d-inline-block">';
            html += '<button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" style="padding:2px 8px;font-size:0.7rem;font-weight:600;">Actions</button>';
            html += '<ul class="dropdown-menu dropdown-menu-end" style="font-size:0.75rem;">';
            <?php if ($isAdmin): ?>
            html += '<li><a class="dropdown-item" href="javascript:void(0)" onclick=\'event.stopPropagation(); openItemModal("edit", ' + JSON.stringify({id: b.id, item_code: b.item_code, inventory_code: b.inventory_code, name: b.item_name, category_id: b.category_id, quantity: b.quantity, unit: b.unit, source_type: "supplier", source_name: "", expiration_date: b.expiration_date, manufacturing_date: b.manufacturing_date, batch_num: b.batch_num, lot_num: b.lot_num, remarks: b.remarks}) + ')\'>Manage</a></li>';
            <?php endif; ?>
            <?php if (strtolower((string) session()->get('role')) === 'encoder'): ?>
            if (bqty > 0) {
            html += '<li><a class="dropdown-item" href="javascript:void(0)" onclick=\'openConsumeModal(' + JSON.stringify({id: b.id, item_code: b.item_code, item_name: b.item_name}) + ')\'>Consume</a></li>';
            }
            <?php endif; ?>
            html += '<li><a class="dropdown-item" href="javascript:void(0)" onclick="event.stopPropagation(); new bootstrap.Modal(document.getElementById(\'archiveItemModal' + b.id + '\')).show();">Archive</a></li>';
            html += '</ul></div></td>';
            html += '</tr>';
        }
        html += '</tbody></table>';
        if (batches.length === 0) {
            html = '<div class="text-muted text-center py-3">No batches found for this item.</div>';
        }
        wrapper.innerHTML = html;
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
        document.getElementById('item_name').value = data.name || '';
        document.getElementById('item_category_id').value = data.category_id || '';
        document.getElementById('item_quantity').value = data.quantity !== undefined ? data.quantity : '';
        document.getElementById('item_unit').value = data.unit || '';
        document.getElementById('item_source_type').value = data.source_type || 'supplier';
        toggleSourceName();
        var sel = document.getElementById('item_source_name_select');
        var txt = document.getElementById('item_source_name_text');
        if (sel.style.display !== 'none') {
            sel.value = data.source_name || '';
        } else {
            txt.value = data.source_name || '';
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
        document.getElementById('item_category_id').value = '';
        document.getElementById('item_quantity').value = '';
        document.getElementById('item_unit').value = '';
        document.getElementById('item_source_type').value = '';
        document.getElementById('item_source_name_select').value = '';
        document.getElementById('item_source_name_text').value = '';
        toggleSourceName();
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

function toggleSourceName() {
    var typeSelect = document.getElementById('item_source_type');
    var sel = document.getElementById('item_source_name_select');
    var txt = document.getElementById('item_source_name_text');
    sel.innerHTML = '<option value="" disabled selected hidden>Select Source</option>';
    txt.value = '';
    if (typeSelect.value === 'supplier' || typeSelect.value === 'donation') {
        sel.style.display = '';
        txt.style.display = 'none';
        sel.required = true;
        txt.required = false;
        var filterType = typeSelect.value === 'supplier' ? 'Supplier' : 'Donation';
        for (var i = 0; i < allSources.length; i++) {
            if (allSources[i].source_type === filterType) {
                var opt = document.createElement('option');
                opt.value = allSources[i].supplier_name;
                opt.textContent = allSources[i].supplier_name;
                sel.appendChild(opt);
            }
        }
    } else if (typeSelect.value === 'others') {
        sel.style.display = 'none';
        txt.style.display = '';
        sel.required = false;
        txt.required = true;
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
        source_type: '<?php echo addslashes(old('source_type', 'supplier')); ?>',
        source_name: '<?php echo addslashes(old('source_name', '')); ?>',
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
    generateInventoryCode();
    document.getElementById('item_quantity').value = '<?php echo addslashes(old('quantity', '')); ?>';
    document.getElementById('item_unit').value = '<?php echo addslashes(old('unit', '')); ?>';
    document.getElementById('item_source_type').value = '<?php echo addslashes(old('source_type', '')); ?>';
    toggleSourceName();
    var sel = document.getElementById('item_source_name_select');
    var txt = document.getElementById('item_source_name_text');
    if (sel.style.display !== 'none') {
        sel.value = '<?php echo addslashes(old('source_name', '')); ?>';
    } else {
        txt.value = '<?php echo addslashes(old('source_name', '')); ?>';
    }
    document.getElementById('item_expiration_date').value = '<?php echo addslashes(old('expiration_date', '')); ?>';
    document.getElementById('item_manufacturing_date').value = '<?php echo addslashes(old('manufacturing_date', '')); ?>';
    document.getElementById('item_batch_num').value = '<?php echo addslashes(old('batch_num', '')); ?>';
    document.getElementById('item_lot_num').value = '<?php echo addslashes(old('lot_num', '')); ?>';
    toggleSourceName();
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
    form.querySelectorAll('[disabled]').forEach(function(el) { el.disabled = false; });
    document.getElementById('itemFormSubmitBtn').style.display = '';
    document.getElementById('batchManageContainer').style.display = 'none';
});

document.getElementById('itemModal')?.addEventListener('hidden.bs.modal', function () {
    var err = this.querySelector('.modal-body .alert.alert-danger');
    if (err) err.remove();
});
</script>

<!-- ===================== ARCHIVE ITEM MODALS ===================== -->
<?php if (!empty($items)): ?>
    <?php foreach ($items as $item): ?>
        <div class="modal fade" id="archiveItemModal<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="archiveItemModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                        <div class="d-flex align-items-center">
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="archiveItemModalLabel<?php echo $item['id']; ?>" style="color: #0f172a; font-size: 1.25rem; letter-spacing: 0;">
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
                                    <div class="text-muted small">Code: <?php echo htmlspecialchars($item['item_code']); ?></div>
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
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                        <div class="modal-header border-bottom-0 px-4 pb-0 pt-4">
                            <h5 class="modal-title fw-bold mb-0" style="color: #0f172a; font-size: 1.25rem;">Archive Item</h5>
                            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                        </div>
                        <div class="modal-body px-4 pt-3 pb-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width: 44px; height: 44px; background: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                </div>
                                <p class="text-secondary mb-0" style="font-size: 0.925rem; line-height: 1.5;">Are you sure you want to archive this inventory item?</p>
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
                        <label for="consumeReason" class="form-label small fw-semibold text-secondary">Reason</label>
                        <textarea class="form-control input-custom" id="consumeReason" name="reason" rows="3" placeholder="Reason for consumption..."></textarea>
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
                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; height: 38px;"
                            onmouseover="this.style.background='#059669'"
                            onmouseout="this.style.background='#10b981'">
                        Consume
                    </button>
                </div>
            </form>
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
    document.getElementById('consumeReason').value = '';

    new bootstrap.Modal(document.getElementById('consumeModal')).show();
}
</script>

