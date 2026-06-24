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
                placeholder="Search by item code or name..."
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
            </select>
        </div>
        <div class="db-search-actions">
            <button type="submit" class="btn-db-search" id="btnInvSearch">
                <i class="fa-solid fa-magnifying-glass"></i> Search
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
                    <th style="width: 14%">Inventory Code</th>
                    <th style="width: 20%">Name</th>
                    <th style="width: 12%">Category</th>
                    <th style="width: 8%">Unit</th>
                    <th style="width: 10%">Total Inventory</th>
                    <th style="width: 10%"><?php echo $isAdmin ? 'Served' : 'Total Consumption'; ?></th>
                    <th style="width: 10%">On Hand</th>
                    <th style="width: 12%">Stock Status</th>
                    <th style="width: 8%" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="text-dark" style="font-size: 0.85rem; color: var(--text-secondary);">
                                <?php echo htmlspecialchars($item['item_code']); ?>
                            </td>
                            <td>
                                <div class="text-dark"><?php echo htmlspecialchars($item['item_name']); ?></div>
                            </td>
                            <td>
                                <span class="text-dark"><?php echo htmlspecialchars($item['category_description'] ?? 'N/A'); ?></span>
                            </td>
                            <td>
                                <span class="text-dark"><?php echo htmlspecialchars($item['unit'] ?? 'N/A'); ?></span>
                            </td>
                            <td class="text-end">
                                <span class="fs-6 text-dark">
                                    <?php echo (int)$item['total_quantity']; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="fs-6 text-dark">
                                    <?php
                                        $consumed = (int)$item['total_quantity'] - (int)$item['quantity_on_hand'];
                                        echo max(0, $consumed);
                                    ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="fs-6 text-dark">
                                    <?php echo (int)$item['quantity_on_hand']; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                    $stockQty = (int)$item['quantity_on_hand'];
                                    $expDate = $item['expiration_date'] ?? '';
                                    $totalQty = (int)$item['total_quantity'];
                                    $isExpired = !empty($expDate) && $expDate < date('Y-m-d') && $stockQty > 0;
                                    $lowStockThreshold = $totalQty > 0 ? max(1, (int)ceil($totalQty * 0.15)) : 0;
                                    if ($isExpired) {
                                        $badge  = 'bg-dark-subtle text-dark border border-dark-subtle';
                                        $status = 'Expired';
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
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" style="padding: 4px 12px; font-size: 0.75rem; font-weight: 600;">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.8rem;">
                                        <?php if (strtolower((string) session()->get('role')) === 'encoder'): ?>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick='openConsumeModal(<?php echo json_encode([
                                            "id" => $item["id"],
                                            "item_code" => $item["item_code"],
                                            "item_name" => $item["item_name"],
                                        ]); ?>)' title="Consume Item">Consume</a></li>
                                        <?php endif; ?>
                                        <?php if ($isAdmin): ?>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick='openItemModal("edit", <?php echo json_encode([
                                            "id" => $item["id"],
                                            "item_code" => $item["item_code"],
                                            "name" => $item["item_name"],
                                            "category_id" => $item["category_id"] ?? "",
                                            "quantity" => $item["quantity"],
                                            "unit" => $item["unit"] ?? "",
                                            "source_type" => str_replace(" ", "_", strtolower($item["source_type"] ?? "supplier")),
                                            "source_name" => $item["supplier_name"] ?? "",
                                            "expiration_date" => $item["expiration_date"] ?? "",
                                            "manufacturing_date" => $item["manufacturing_date"] ?? "",
                                            "batch_num" => $item["batch_num"] ?? "",
                                            "lot_num" => $item["lot_num"] ?? "",
                                            "remarks" => $item["remarks"] ?? "",
                                        ]); ?>)' title="Manage Item">Manage</a></li>
                                        <?php endif; ?>
                                        <?php if (strtolower((string) session()->get('role')) !== 'viewer'): ?>
                                        <li><a class="dropdown-item" href="<?php echo base_url('inventory/archive/' . $item['id']); ?>" data-bs-toggle="modal" data-bs-target="#archiveItemModal<?php echo $item['id']; ?>" title="Archive Item">Archive</a></li>
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

                    <div class="row g-3">

                        <div class="col-lg-4 col-12">
                            <label for="item_category_id" class="form-label small fw-semibold text-secondary">
                                Category <span class="text-danger">*</span>
                            </label>
                             <select class="form-select input-custom"
                                     id="item_category_id"
                                     name="category_id"
                                     onchange="generateItemCode()"
                                     required>
                                <option value="" disabled selected hidden>Select Category</option>
                                <?php foreach (($categories ?? []) as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>" <?php echo ((string)old('category_id') === (string)$category['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-4 col-12">
                            <label for="item_code" class="form-label small fw-semibold text-secondary">
                                Inventory Code <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_code"
                                   name="item_code"
                                   style="text-transform: uppercase;"
                                   value="<?php echo old('item_code'); ?>"
                                   >
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

                        <div class="col-lg-3 col-12">
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

                        <div class="col-lg-3 col-12">
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

                        <div class="col-lg-3 col-12">
                            <label for="item_batch_num" class="form-label small fw-semibold text-secondary">Batch No.</label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_batch_num"
                                   name="batch_num"
                                   value="<?php echo old('batch_num'); ?>">
                        </div>

                        <div class="col-lg-3 col-12">
                            <label for="item_lot_num" class="form-label small fw-semibold text-secondary">Lot No.</label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_lot_num"
                                   name="lot_num"
                                   value="<?php echo old('lot_num'); ?>">
                        </div>

                        <div class="col-lg-6 col-12">
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

                        <div class="col-lg-6 col-12" id="sourceNameCol">
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
                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s;"
                            onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                            onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'">
                        Add Item
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
var allSources = <?php echo json_encode($sources ?? []); ?>;

function openItemModal(mode, data) {
    var form = document.getElementById('itemForm');
    var label = document.getElementById('itemModalLabel');
    var btn = document.getElementById('itemFormSubmitBtn');
    var fields = ['item_code','item_name','item_category_id','item_quantity','item_unit','item_source_type','item_expiration_date','item_manufacturing_date','item_batch_num','item_lot_num','item_remarks'];
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
        disableFields(true);
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
        document.getElementById('item_remarks').disabled = true;
        document.getElementById('itemModalCancelBtn').textContent = 'Close';
    } else if (mode === 'edit') {
        form.action = '<?php echo base_url('inventory/edit'); ?>/' + data.id;
        label.textContent = 'Manage Item';
        btn.textContent = 'Update Item';
        btn.style.display = '';
        disableFields(false);
        document.getElementById('itemModalCancelBtn').textContent = 'Close';
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
        document.getElementById('itemModalCancelBtn').textContent = 'Close';
        document.getElementById('item_code').value = '';
        document.getElementById('item_name').value = '';
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
    
    var modal = new bootstrap.Modal(document.getElementById('itemModal'));
    modal.show();
    
    // Auto-generate item code after modal is shown (for create mode)
    if (mode === 'create') {
        setTimeout(function() {
            generateItemCode();
        }, 100);
    }
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

<?php if ($modal_mode === 'edit' && $modal_edit_id): ?>
document.addEventListener('DOMContentLoaded', function () {
    openItemModal('edit', {
        id: <?php echo $modal_edit_id; ?>,
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
    document.getElementById('item_code').value = '<?php echo addslashes(old('item_code', '')); ?>';
    document.getElementById('item_name').value = '<?php echo addslashes(old('name', '')); ?>';
    document.getElementById('item_category_id').value = '<?php echo addslashes(old('category_id', '')); ?>';
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
    new bootstrap.Modal(document.getElementById('itemModal')).show();
});
<?php endif; ?>

// Reset modal state on close
document.getElementById('itemModal')?.addEventListener('hidden.bs.modal', function () {
    var form = document.getElementById('itemForm');
    form.reset();
    form.querySelectorAll('[disabled]').forEach(function(el) { el.disabled = false; });
    document.getElementById('itemFormSubmitBtn').style.display = '';
});

// Auto-generate item code via AJAX when category is selected
function generateItemCode() {
    var categorySelect = document.getElementById('item_category_id');
    var itemCodeField = document.getElementById('item_code');
    
    if (!categorySelect || !itemCodeField) return;
    
    var categoryId = categorySelect.value;
    if (!categoryId) return;

    var formData = new FormData();
    formData.append('category_id', categoryId);
    
    fetch('<?php echo base_url('inventory/generate_item_code'); ?>', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success && data.item_code) {
            itemCodeField.value = data.item_code;
        }
    })
    .catch(function() {});
}

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
                           style="background: #ef4444; color: #fff; border: 1px solid transparent; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; text-decoration: none; cursor: pointer; box-shadow: 0 2px 8px rgba(245,158,11,0.3); transition: background 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; height: 38px;"
                           onmouseover="this.style.background='#dc2626';this.style.boxShadow='0 4px 12px rgba(245,158,11,0.4)'"
                           onmouseout="this.style.background='#ef4444';this.style.boxShadow='0 2px 8px rgba(245,158,11,0.3)'">
                            Archive Item
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<!-- ============================================================= -->

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
                            style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; height: 38px;"
                            onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                            onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'">
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

<style>
    #btnAddNewItem,
    #btnAddNewItem:hover,
    #btnAddNewItem:focus,
    #btnAddNewItem:active,
    #btnAddNewItem:focus-visible {
        color: #fff !important;
        box-shadow: none !important;
    }
    #btnAddNewItem:hover { background: #059669 !important; }
</style>
