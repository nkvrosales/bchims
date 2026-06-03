<?php
    $editItemModalOpen = session()->getFlashdata('edit_item_modal_open');
    $editItemValidationErrors = session()->getFlashdata('edit_item_validation_errors');
?>

<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1"> Inventory</h1>
        </div>
        <div>
            <button type="button"
                    class="btn d-flex align-items-center gap-2"
                    id="btnAddNewItem"
                    data-bs-toggle="modal"
                    data-bs-target="#createItemModal"
                    style="background: #10b981; color: #fff; font-weight: 600; border: none; padding: 0.5rem 1.1rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(34,197,94,0.3); transition: background 0.2s;">
                <i class="fa-solid fa-plus"></i>
                <span>Add Item</span>
            </button>
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
        <span>Search &amp; Filter</span>
    </h5>
    
    <form method="GET" action="<?php echo base_url('inventory'); ?>" class="row g-3" id="inventoryFilterForm">
        <!-- 1. Search Query -->
        <div class="col-12 col-md-6">
            <label for="search" class="form-label small fw-semibold text-secondary">Search Item</label>
            <input type="text" 
                   class="form-control input-custom" 
                   id="search" 
                   name="search" 
                   placeholder="Search"
                   value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>">
        </div>

        <!-- 3. Stock Status -->
        <div class="col-12 col-sm-6 col-md-3">
            <label for="stock_status" class="form-label small fw-semibold text-secondary">Stock Status</label>
            <select class="form-select input-custom" id="stock_status" name="stock_status">
                <option value="">Status</option>
                <option value="in_stock" <?php echo (isset($stock_status) && $stock_status === 'in_stock') ? 'selected' : ''; ?>>In Stock</option>
                <option value="low_stock" <?php echo (isset($stock_status) && $stock_status === 'low_stock') ? 'selected' : ''; ?>>Low Stock</option>
                <option value="out_of_stock" <?php echo (isset($stock_status) && $stock_status === 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
            </select>
        </div>

        <!-- 4. Form Submission Buttons -->
        <div class="col-12 col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2" id="btnFilterSubmit" style="padding: 0.5rem 1.4rem !important; font-size: 0.9rem !important; font-weight: 500 !important; border-radius: 8px !important; border: 1.5px solid transparent !important; height: 50px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <span>Filter</span>
            </button>
            <a href="<?php echo base_url('inventory'); ?>" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2" id="btnFilterReset" style="height: 50px;">
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
            <i class="bi bi-box2-fill text-primary"></i>
            <span>Inventory</span>
        </h5>
    </div>

    <div class="table-responsive-custom">
        <table class="table table-custom table-hover w-100" id="inventoryTable">
            <thead>
                <tr>
                    <th style="width: 14%">Code</th>
                    <th style="width: 30%">Name</th>
                    <th style="width: 16%">Category</th>
                    <th style="width: 12%">Quantity</th>
                    <th style="width: 16%">Stock Status</th>
                    <th style="width: 12%" class="text-end">Actions</th>
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
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($item['item_name']); ?></div>
                            </td>
                            <td>
                                <span class="text-dark"><?php echo htmlspecialchars($item['category_code'] ?? 'N/A'); ?></span>
                            </td>
                            <td>
                                <span class="fw-bold fs-6 text-dark"><?php echo (int)$item['quantity']; ?></span>
                            </td>
                            <td>
                                <?php
                                    $stockQty = isset($item['quantity_on_hand']) ? (int)$item['quantity_on_hand'] : (int)$item['quantity'];
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
                                       class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center rounded-2"
                                       data-bs-toggle="modal"
                                       data-bs-target="#viewItemModal<?php echo $item['id']; ?>"
                                       style="width: 32px; height: 32px;"
                                       title="View Item">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button"
                                       class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center rounded-2"
                                       data-bs-toggle="modal"
                                       data-bs-target="#editItemModal<?php echo $item['id']; ?>"
                                       style="width: 32px; height: 32px;"
                                       title="Edit Item">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="<?php echo base_url('inventory/delete/' . $item['id']); ?>" 
                                       class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center rounded-2"
                                       style="width: 32px; height: 32px;"
                                       data-bs-toggle="modal"
                                       data-bs-target="#deleteItemModal<?php echo $item['id']; ?>"
                                       title="Delete Item">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== VIEW ITEM MODALS ===================== -->
<?php if (!empty($items)): ?>
    <?php foreach ($items as $item): ?>
        <?php
            $viewStockQty = isset($item['quantity_on_hand']) ? (int)$item['quantity_on_hand'] : (int)$item['quantity'];
            if ($viewStockQty === 0) {
                $viewBadge  = 'bg-danger-subtle text-danger border border-danger-subtle';
                $viewStatus = 'Out of Stock';
            } elseif ($viewStockQty <= 10) {
                $viewBadge  = 'bg-warning-subtle text-warning border border-warning-subtle';
                $viewStatus = 'Low Stock';
            } else {
                $viewBadge  = 'bg-success-subtle text-success border border-success-subtle';
                $viewStatus = 'In Stock';
            }
        ?>
        <div class="modal fade" id="viewItemModal<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="viewItemModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                    <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                        <div class="d-flex align-items-center">
                            <div style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-eye" style="color: #0f172a; font-size: 1rem;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="viewItemModalLabel<?php echo $item['id']; ?>" style="color: #0f172a; font-size: 1.4rem; letter-spacing: 0;">
                                    Stock Details
                                </h5>
                                <div class="small text-muted"><?php echo htmlspecialchars($item['item_name']); ?></div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                    </div>

                    <div class="modal-body px-4 py-4">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <div class="small fw-semibold text-secondary mb-1">Item Code</div>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($item['item_code']); ?></div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="small fw-semibold text-secondary mb-1">Product Name</div>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($item['item_name']); ?></div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="small fw-semibold text-secondary mb-1">Category</div>
                                <div class="fw-semibold text-dark">
                                    <?php echo htmlspecialchars($item['category_code'] ?? 'N/A'); ?>
                                    <?php if (!empty($item['category_description'])): ?>
                                        <span class="text-muted fw-normal">- <?php echo htmlspecialchars($item['category_description']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="small fw-semibold text-secondary mb-1">Quantity</div>
                                <div class="fw-semibold text-dark"><?php echo $viewStockQty; ?></div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="small fw-semibold text-secondary mb-1">Stock Status</div>
                                <span class="badge badge-action <?php echo $viewBadge; ?>"><?php echo $viewStatus; ?></span>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="small fw-semibold text-secondary mb-1">Batch No.</div>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($item['batch_num'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="small fw-semibold text-secondary mb-1">Lot No.</div>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($item['lot_num'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="small fw-semibold text-secondary mb-1">Expiration Date</div>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($item['expiration_date'] ?? 'N/A'); ?></div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="small fw-semibold text-secondary mb-1">Source</div>
                                <div class="fw-semibold text-dark">
                                    <?php echo htmlspecialchars($item['source_type'] ?? 'N/A'); ?>
                                    <?php if (!empty($item['supplier_name'])): ?>
                                        <span class="text-muted fw-normal">- <?php echo htmlspecialchars($item['supplier_name']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end">
                        <button type="button"
                                data-bs-dismiss="modal"
                                style="background: #fff; color: #374151; border: 1.5px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.4rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s, border-color 0.15s;"
                                onmouseover="this.style.background='#f9fafb'"
                                onmouseout="this.style.background='#fff'">
                            Close
                        </button>
                    </div>

                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<!-- ============================================================= -->

<!-- ===================== EDIT ITEM MODALS ===================== -->
<?php if (!empty($items)): ?>
    <?php foreach ($items as $item): ?>
        <?php
            $isEditModalOpen = ((string)$editItemModalOpen === (string)$item['id']);
            $editItemCode = $isEditModalOpen ? old('item_code', $item['item_code']) : $item['item_code'];
            $editItemName = $isEditModalOpen ? old('name', $item['item_name']) : $item['item_name'];
            $editCategoryId = $isEditModalOpen ? old('category_id', $item['category_id'] ?? '') : ($item['category_id'] ?? '');
            $editSourceType = $isEditModalOpen ? old('source_type', str_replace(' ', '_', strtolower($item['source_type'] ?? 'supplier'))) : str_replace(' ', '_', strtolower($item['source_type'] ?? 'supplier'));
            $editBatchNum = $isEditModalOpen ? old('batch_num', $item['batch_num'] ?? '') : ($item['batch_num'] ?? '');
            $editLotNum = $isEditModalOpen ? old('lot_num', $item['lot_num'] ?? '') : ($item['lot_num'] ?? '');
            $editExpirationDate = $isEditModalOpen ? old('expiration_date', $item['expiration_date'] ?? '') : ($item['expiration_date'] ?? '');
            $editQuantity = $isEditModalOpen ? old('quantity', $item['quantity']) : $item['quantity'];
        ?>
        <div class="modal fade" id="editItemModal<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="editItemModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                    <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                        <div class="d-flex align-items-center">
                            <div style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fa-solid fa-pen-to-square" style="color: #000000ff; font-size: 1rem;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="editItemModalLabel<?php echo $item['id']; ?>" style="color: #0f172a; font-size: 1.4rem; letter-spacing: 0;">
                                    Edit Item
                                </h5>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                    </div>

                    <form method="POST" action="<?php echo base_url('inventory/edit/' . $item['id']); ?>">
                        <div class="modal-body px-4 py-4">

                            <?php if ($isEditModalOpen && !empty($editItemValidationErrors)): ?>
                                <div class="alert alert-danger border-0 rounded-3 mb-4 py-3">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                                        <div>
                                            <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                                            <div class="small"><?php echo $editItemValidationErrors; ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <label for="edit_item_code_<?php echo $item['id']; ?>" class="form-label small fw-semibold text-secondary">
                                        Item Code <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control input-custom"
                                           id="edit_item_code_<?php echo $item['id']; ?>"
                                           name="item_code"
                                           style="text-transform: uppercase;"
                                           value="<?php echo htmlspecialchars($editItemCode); ?>"
                                           required>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label for="edit_item_name_<?php echo $item['id']; ?>" class="form-label small fw-semibold text-secondary">
                                        Item Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control input-custom"
                                           id="edit_item_name_<?php echo $item['id']; ?>"
                                           name="name"
                                           value="<?php echo htmlspecialchars($editItemName); ?>"
                                           required>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label for="edit_category_id_<?php echo $item['id']; ?>" class="form-label small fw-semibold text-secondary">
                                        Category <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select input-custom"
                                            id="edit_category_id_<?php echo $item['id']; ?>"
                                            name="category_id"
                                            required>
                                        <option value="">Select Category</option>
                                        <?php foreach (($categories ?? []) as $category): ?>
                                            <option value="<?php echo $category['category_id']; ?>" <?php echo ((string)$editCategoryId === (string)$category['category_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['category_code'] . ' - ' . $category['category_description']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label for="edit_quantity_<?php echo $item['id']; ?>" class="form-label small fw-semibold text-secondary">
                                        Quantity <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           class="form-control input-custom"
                                           id="edit_quantity_<?php echo $item['id']; ?>"
                                           name="quantity"
                                           min="0"
                                           value="<?php echo htmlspecialchars($editQuantity); ?>"
                                           required>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label for="edit_source_type_<?php echo $item['id']; ?>" class="form-label small fw-semibold text-secondary">
                                        Source <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select input-custom"
                                            id="edit_source_type_<?php echo $item['id']; ?>"
                                            name="source_type"
                                            required>
                                        <option value="">Select Source</option>
                                        <option value="supplier" <?php echo ($editSourceType === 'supplier') ? 'selected' : ''; ?>>Supplier</option>
                                        <option value="donation" <?php echo ($editSourceType === 'donation') ? 'selected' : ''; ?>>Donation</option>
                                        <option value="old_stock" <?php echo ($editSourceType === 'old_stock') ? 'selected' : ''; ?>>Old Stock</option>
                                    </select>
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label for="edit_expiration_date_<?php echo $item['id']; ?>" class="form-label small fw-semibold text-secondary">Expiration Date</label>
                                    <input type="date"
                                           class="form-control input-custom"
                                           id="edit_expiration_date_<?php echo $item['id']; ?>"
                                           name="expiration_date"
                                           value="<?php echo htmlspecialchars($editExpirationDate); ?>">
                                </div>

                                <div class="col-12 col-sm-6">
                                    <label for="edit_batch_num_<?php echo $item['id']; ?>" class="form-label small fw-semibold text-secondary">Batch No.</label>
                                    <input type="text"
                                           class="form-control input-custom"
                                           id="edit_batch_num_<?php echo $item['id']; ?>"
                                           name="batch_num"
                                           value="<?php echo htmlspecialchars($editBatchNum); ?>">
                                </div>


                                <div class="col-12 col-sm-6">
                                    <label for="edit_lot_num_<?php echo $item['id']; ?>" class="form-label small fw-semibold text-secondary">Lot No.</label>
                                    <input type="text"
                                           class="form-control input-custom"
                                           id="edit_lot_num_<?php echo $item['id']; ?>"
                                           name="lot_num"
                                           value="<?php echo htmlspecialchars($editLotNum); ?>">
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
                            <button type="submit"
                                    style="background: #10b981; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 2px 8px rgba(16,185,129,0.3); transition: background 0.15s, box-shadow 0.15s;"
                                    onmouseover="this.style.background='#059669';this.style.boxShadow='0 4px 12px rgba(16,185,129,0.4)'"
                                    onmouseout="this.style.background='#10b981';this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'">
                                Update Item
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<!-- ============================================================= -->

<!-- ===================== DELETE ITEM MODALS ===================== -->
<?php if (!empty($items)): ?>
    <?php foreach ($items as $item): ?>
        <div class="modal fade" id="deleteItemModal<?php echo $item['id']; ?>" tabindex="-1" aria-labelledby="deleteItemModalLabel<?php echo $item['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

                    <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                        <div class="d-flex align-items-center">
                            <div style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fa-solid fa-trash" style="color: #dc2626; font-size: 1rem;"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="deleteItemModalLabel<?php echo $item['id']; ?>" style="color: #0f172a; font-size: 1.25rem; letter-spacing: 0;">
                                    Delete Item
                                </h5>
                                <div class="small text-muted">This action will be recorded in the audit logs.</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
                    </div>

                    <div class="modal-body px-4 py-4">
                        <p class="mb-2 text-secondary">Are you sure you want to delete this inventory item?</p>
                        <div class="border rounded-3 p-3 bg-light">
                            <div class="fw-semibold text-dark"><?php echo htmlspecialchars($item['item_name']); ?></div>
                            <div class="small text-muted">Code: <?php echo htmlspecialchars($item['item_code']); ?></div>
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
                        <a href="<?php echo base_url('inventory/delete/' . $item['id']); ?>"
                           style="background: #dc2626; color: #fff; border: none; border-radius: 8px; padding: 0.5rem 1.5rem; font-size: 0.9rem; font-weight: 600; cursor: pointer; text-decoration: none; box-shadow: 0 2px 8px rgba(220,38,38,0.24); transition: background 0.15s, box-shadow 0.15s;"
                           onmouseover="this.style.background='#b91c1c';this.style.boxShadow='0 4px 12px rgba(220,38,38,0.32)'"
                           onmouseout="this.style.background='#dc2626';this.style.boxShadow='0 2px 8px rgba(220,38,38,0.24)'">
                            Delete Item
                        </a>
                    </div>

                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<!-- ============================================================= -->


<!-- ===================== ADD ITEM MODAL ===================== -->
<div class="modal fade" id="createItemModal" tabindex="-1" aria-labelledby="createItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4" style="padding-top: 1.1rem; padding-bottom: 1.1rem;">
                <div class="d-flex align-items-center ">
                    <div style="width: 40px; height: 40px;  display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fa-solid fa-plus" style="color: #000000ff; font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="createItemModalLabel" style="color: #0f172a; font-size: 1.4rem; letter-spacing: -0.01em;">
                            Add New Item
                        </h5>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.6;"></button>
            </div>

            <!-- Form -->
            <form method="POST" action="<?php echo base_url('inventory/create'); ?>">
                <div class="modal-body px-4 py-4">

                    <!-- Validation Errors -->
                    <?php if ($create_errors = session()->getFlashdata('create_item_validation_errors')): ?>
                    <div class="alert alert-danger border-0 rounded-3 mb-4 py-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                            <div>
                                <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                                <div class="small"><?php echo $create_errors; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row g-3">

                        <!-- Item Code -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_item_code" class="form-label small fw-semibold text-secondary">
                                Item Code <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="modal_item_code"
                                   name="item_code"
                                   style="text-transform: uppercase;"
                                   value="<?php echo old('item_code'); ?>"
                                   required>
                        </div>

                        <!-- Item Name -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_item_name" class="form-label small fw-semibold text-secondary">
                                Item Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="modal_item_name"
                                   name="name"
                                   value="<?php echo old('name'); ?>"
                                   required>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label for="modal_category_id" class="form-label small fw-semibold text-secondary">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select input-custom"
                                    id="modal_category_id"
                                    name="category_id"
                                    required>
                                <option value="">Select Category</option>
                                <?php foreach (($categories ?? []) as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>" <?php echo ((string)old('category_id') === (string)$category['category_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category_code'] . ' - ' . $category['category_description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Initial Quantity -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_quantity" class="form-label small fw-semibold text-secondary">
                                Quantity <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control input-custom"
                                   id="modal_quantity"
                                   name="quantity"
                                   min="0"
                                   value="<?php echo old('quantity', '0'); ?>"
                                   required>
                        </div>


                        <div class="col-12 col-sm-6">
                            <label for="modal_source_type" class="form-label small fw-semibold text-secondary">
                                Source <span class="text-danger">*</span>
                            </label>
                            <select class="form-select input-custom"
                                    id="modal_source_type"
                                    name="source_type"
                                    required>
                                <option value="">Select Source</option>
                                <option value="supplier" <?php echo (old('source_type') === 'supplier') ? 'selected' : ''; ?>>Supplier</option>
                                <option value="donation" <?php echo (old('source_type') === 'donation') ? 'selected' : ''; ?>>Donation</option>
                                <option value="old_stock" <?php echo (old('source_type') === 'old_stock') ? 'selected' : ''; ?>>Old Stock</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label for="modal_expiration_date" class="form-label small fw-semibold text-secondary">Expiration Date</label>
                            <input type="date"
                                   class="form-control input-custom"
                                   id="modal_expiration_date"
                                   name="expiration_date"
                                   value="<?php echo old('expiration_date'); ?>">
                        </div>

                        <!-- Batch Number -->
                        <div class="col-12 col-sm-6">
                            <label for="modal_batch_num" class="form-label small fw-semibold text-secondary">Batch No.</label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="modal_batch_num"
                                   name="batch_num"
                                   value="<?php echo old('batch_num'); ?>">
                        </div>

                        <div class="col-12 col-sm-6">
                            <label for="modal_lot_num" class="form-label small fw-semibold text-secondary">Lot No.</label>
                            <input type="text"
                                   class="form-control input-custom"
                                   id="modal_lot_num"
                                   name="lot_num"
                                   value="<?php echo old('lot_num'); ?>">
                        </div>



                    </div><!-- /.row -->
                </div><!-- /.modal-body -->

                <div class="modal-footer border-0 px-4 pb-4 pt-2 justify-content-end">
                    <button type="button"
                            data-bs-dismiss="modal"
                            style="background: #fff; color: #374151; border: 1.5px solid #d1d5db; border-radius: 8px; padding: 0.5rem 1.4rem; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s, border-color 0.15s;"
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='#fff'">
                        Cancel
                    </button>
                    <button type="submit"
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
<!-- ============================================================= -->

<!-- Auto-open modal on validation failure -->
<?php if (session()->getFlashdata('create_item_modal_open')): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('createItemModal');
        if (el) { new bootstrap.Modal(el).show(); }
    });
</script>
<?php endif; ?>

<?php if (!empty($editItemModalOpen)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('editItemModal<?php echo htmlspecialchars($editItemModalOpen); ?>');
        if (el) { new bootstrap.Modal(el).show(); }
    });
</script>
<?php endif; ?>

<style>
    #btnAddNewItem:hover { background: #059669 !important; box-shadow: 0 4px 12px rgba(34,197,94,0.4) !important; }
</style>
