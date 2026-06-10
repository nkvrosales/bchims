<?php $isAdmin = !in_array(strtolower((string) session()->get('role')), ['viewer', 'encoder'], true); ?>
<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1"> Inventory</h1>
        </div>
        <?php if (!in_array(strtolower((string) session()->get('role')), ['viewer', 'encoder'], true)): ?>
        <div>
            <button type="button"
                    class="btn d-flex align-items-center gap-2"
                    id="btnAddNewItem"
                    onclick="openItemModal('create')"
                    style="background: #10b981; color: #fff; font-weight: 600; border: none; padding: 0.5rem 1.1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(34,197,94,0.3); transition: background 0.2s;">
                <i class="fa-solid fa-plus"></i>
                <span>Add Item</span>
            </button>
        </div>
        <?php endif; ?>
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


<!-- Inventory Items Table Area -->
<div class="standard-card fade-in-up" style="animation-delay: 0.2s;">
    <div class="card-header-styled mb-4">
        <h5 class="card-title-styled">
            
            <span>Inventory</span>
        </h5>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100" id="inventoryTable">
            <thead>
                <tr>
                    <th style="width: 14%">Code</th>
                    <th style="width: 22%">Name</th>
                    <th style="width: 14%">Category</th>
                    <?php if ($isAdmin): ?>
                    <th style="width: 10%">Total Qty</th>
                    <th style="width: 10%">Served</th>
                    <th style="width: 10%">In Stock</th>
                    <?php else: ?>
                    <th style="width: 15%">Qty</th>
                    <?php endif; ?>
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
                            <?php if ($isAdmin): ?>
                            <td>
                                <span class="fs-6 text-dark">
                                    <?php echo (int)$item['total_quantity']; ?>
                                    <?php if (!empty($item['unit'])): ?>
                                        <small class="text-secondary fw-normal ms-1">(<?php echo htmlspecialchars($item['unit']); ?>)</small>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <span class="fs-6 text-dark">
                                    <?php
                                        $served = (int)$item['total_quantity'] - (int)$item['quantity_on_hand'];
                                        echo max(0, $served);
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="fs-6 text-dark">
                                    <?php echo (int)$item['quantity_on_hand']; ?>
                                    <?php if (!empty($item['unit'])): ?>
                                        <small class="text-secondary fw-normal ms-1">(<?php echo htmlspecialchars($item['unit']); ?>)</small>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <?php else: ?>
                            <td>
                                <span class="fs-6 text-dark">
                                    <?php echo (int)$item['quantity_on_hand']; ?>
                                    <?php if (!empty($item['unit'])): ?>
                                        <small class="text-secondary fw-normal ms-1">(<?php echo htmlspecialchars($item['unit']); ?>)</small>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <?php endif; ?>
                            <td>
                                <?php
                                    $stockQty = (int)$item['quantity_on_hand'];
                                    if ($stockQty === 0) {
                                        $badge  = 'bg-danger-subtle text-danger border border-danger-subtle';
                                        $status = 'Out of Stock';
                                    } elseif ($stockQty <= 10) {
                                        $badge  = 'bg-warning-subtle text-warning border border-warning-subtle';
                                        $status = 'Low Stock';
                                    } else {
                                        $badge  = 'bg-success-subtle text-success border border-success-subtle';
                                        $status = 'In Stock';
                                    }
                                ?>
                                <span class="badge badge-action <?php echo $badge; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <button type="button"
                                       class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center rounded-2"
                                        onclick='openItemModal("view", <?php echo json_encode([
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
                                        ]); ?>)'
                                       style="width: 32px; height: 32px; padding: 0 !important; flex-shrink: 0;"
                                       title="View Item">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php if (strtolower((string) session()->get('role')) !== 'viewer'): ?>
                                    <button type="button"
                                       class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center rounded-2"
                                        onclick='openItemModal("edit", <?php echo json_encode([
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
                                        ]); ?>)'
                                       style="width: 32px; height: 32px; padding: 0 !important; flex-shrink: 0;"
                                       title="Edit Item">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="<?php echo base_url('inventory/archive/' . $item['id']); ?>"
                                       class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                       style="width: 32px; height: 32px; padding: 0 !important; flex-shrink: 0;"
                                       data-bs-toggle="modal"
                                       data-bs-target="#archiveItemModal<?php echo $item['id']; ?>"
                                       title="Archive Item">
                                        <i class="fa-regular fa-folder"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== SINGLE ITEM MODAL (Add/Edit/View) ===================== -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
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
                    <div class="alert alert-danger border-0 rounded-3 mb-4 py-3">
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

                        <div class="col-12 col-sm-4">
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

                        <div class="col-12 col-sm-4">
                            <label for="item_code" class="form-label small fw-semibold text-secondary">
                                Item Code <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_code"
                                   name="item_code"
                                   style="text-transform: uppercase;"
                                   value="<?php echo old('item_code'); ?>"
                                   >
                        </div>

                        <div class="col-12 col-sm-4">
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

                        <div class="col-12 col-sm-6">
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
                            </select>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label for="item_quantity" class="form-label small fw-semibold text-secondary">
                                Quantity <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control input-custom"
                                   id="item_quantity"
                                   name="quantity"
                                   min="0"
                                   value="<?php echo old('quantity', '0'); ?>"
                                   required>
                        </div>

                        <div class="col-12 col-sm-6">
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

                        <div class="col-12 col-sm-6" id="sourceNameCol">
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

                        <div class="col-12 col-sm-6">
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

                        <div class="col-12 col-sm-6">
                            <label for="item_manufacturing_date" class="form-label small fw-semibold text-secondary">Manufacturing Date</label>
                            <input type="date"
                                   class="form-control input-custom"
                                   id="item_manufacturing_date"
                                   name="manufacturing_date"
                                   value="<?php echo old('manufacturing_date'); ?>">
                        </div>

                        <div class="col-12 col-sm-6">
                            <label for="item_batch_num" class="form-label small fw-semibold text-secondary">Batch No.</label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_batch_num"
                                   name="batch_num"
                                   value="<?php echo old('batch_num'); ?>">
                        </div>

                        <div class="col-12 col-sm-6">
                            <label for="item_lot_num" class="form-label small fw-semibold text-secondary">Lot No.</label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="item_lot_num"
                                   name="lot_num"
                                   value="<?php echo old('lot_num'); ?>">
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end">
                    <button type="button"
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
    var fields = ['item_code','item_name','item_category_id','item_quantity','item_unit','item_source_type','item_expiration_date','item_manufacturing_date','item_batch_num','item_lot_num'];
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
        document.getElementById('item_quantity').value = data.quantity || 0;
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
    } else if (mode === 'edit') {
        form.action = '<?php echo base_url('inventory/edit'); ?>/' + data.id;
        label.textContent = 'Edit Item';
        btn.textContent = 'Update Item';
        btn.style.display = '';
        disableFields(false);
        document.getElementById('item_code').value = data.item_code || '';
        document.getElementById('item_name').value = data.name || '';
        document.getElementById('item_category_id').value = data.category_id || '';
        document.getElementById('item_quantity').value = data.quantity || 0;
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
    } else {
        form.action = '<?php echo base_url('inventory/create'); ?>';
        label.textContent = 'Add New Item';
        btn.textContent = 'Add Item';
        btn.style.display = '';
        disableFields(false);
        document.getElementById('item_code').value = '';
        document.getElementById('item_name').value = '';
        document.getElementById('item_category_id').value = '';
        document.getElementById('item_quantity').value = '0';
        document.getElementById('item_unit').value = '';
        document.getElementById('item_source_type').value = '';
        document.getElementById('item_source_name_select').value = '';
        document.getElementById('item_source_name_text').value = '';
        toggleSourceName();
        document.getElementById('item_expiration_date').value = '';
        document.getElementById('item_manufacturing_date').value = '';
        document.getElementById('item_batch_num').value = '';
        document.getElementById('item_lot_num').value = '';
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
        quantity: '<?php echo addslashes(old('quantity', '0')); ?>',
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
    document.getElementById('item_quantity').value = '<?php echo addslashes(old('quantity', '0')); ?>';
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
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: #fee2e2; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <i class="fa-solid fa-box" style="color: #b91c1c; font-size: 0.875rem;"></i>
                                </div>
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
                            Cancel
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




<style>
    #btnAddNewItem:hover { background: #059669 !important; box-shadow: 0 4px 12px rgba(34,197,94,0.4) !important; }
</style>
