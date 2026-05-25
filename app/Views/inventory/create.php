<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Add Inventory Item</h1>
        </div>
        <div>
            <a href="<?php echo base_url('inventory'); ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Inventory</span>
            </a>
        </div>
    </div>
</div>

<!-- Validation Error alerts if any -->
<?php if (validation_errors() || isset($error)): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4 rounded-3 fade show" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation fs-5 mt-1"></i>
            <div>
                <span class="fw-bold d-block mb-1">Please correct the errors below:</span>
                <div class="small">
                    <?php echo validation_errors('<li>', '</li>'); ?>
                    <?php if (isset($error)) echo "<li>{$error}</li>"; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Create Item Form Card -->
<div class="row fade-in-up" style="animation-delay: 0.1s;">
    <div class="col-12 col-lg-8 col-xl-6">
        <div class="standard-card">
            <div class="card-header-styled mb-4">
                <h5 class="card-title-styled">
                    <i class="fa-solid fa-plus-circle text-primary"></i>
                    <span>Item Specifications</span>
                </h5>
            </div>

            <form method="POST" action="<?php echo base_url('inventory/create'); ?>" class="row g-3">
                <!-- 1. Item Code -->
                <div class="col-12 col-sm-6">
                    <label for="item_code" class="form-label small fw-semibold text-secondary">Item Code <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="item_code" 
                           name="item_code" 
                           placeholder="e.g. LAB-004"
                           style="text-transform: uppercase;"
                           value="<?php echo set_value('item_code'); ?>"
                           required>
                    <div class="form-text small text-muted">Must be unique, alphanumeric and dashes only.</div>
                </div>

                <!-- 2. Item Name -->
                <div class="col-12 col-sm-6">
                    <label for="name" class="form-label small fw-semibold text-secondary">Item Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="name" 
                           name="name" 
                           placeholder="e.g. Blood Lancet"
                           value="<?php echo set_value('name'); ?>"
                           required>
                </div>

                <!-- 3. Department -->
                <div class="col-12 col-sm-6">
                    <label for="department" class="form-label small fw-semibold text-secondary">Department <span class="text-danger">*</span></label>
                    <select class="form-select input-custom" id="department" name="department" required>
                        <option value="">-- Select Department --</option>
                        <option value="LAB" <?php echo set_select('department', 'LAB'); ?>>LAB</option>
                        <option value="PHARMA" <?php echo set_select('department', 'PHARMA'); ?>>PHARMA</option>
                        <option value="SUPPLIES" <?php echo set_select('department', 'SUPPLIES'); ?>>SUPPLIES</option>
                        <option value="OR/DR COMPLEX" <?php echo set_select('department', 'OR/DR COMPLEX'); ?>>OR/DR COMPLEX</option>
                    </select>
                </div>

                <!-- 4. Unit -->
                <div class="col-12 col-sm-6">
                    <label for="unit" class="form-label small fw-semibold text-secondary">Unit <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="unit" 
                           name="unit" 
                           placeholder="e.g. pcs, box, pair, bottle"
                           value="<?php echo set_value('unit', 'pcs'); ?>"
                           required>
                </div>

                <!-- 5. Initial Quantity -->
                <div class="col-12 col-sm-6">
                    <label for="quantity" class="form-label small fw-semibold text-secondary">Initial Quantity <span class="text-danger">*</span></label>
                    <input type="number" 
                           class="form-control input-custom" 
                           id="quantity" 
                           name="quantity" 
                           min="0"
                           value="<?php echo set_value('quantity', '0'); ?>"
                           required>
                </div>

                <!-- 6. Min Stock Threshold -->
                <div class="col-12 col-sm-6">
                    <label for="min_stock" class="form-label small fw-semibold text-secondary">Min Stock Alert Level <span class="text-danger">*</span></label>
                    <input type="number" 
                           class="form-control input-custom" 
                           id="min_stock" 
                           name="min_stock" 
                           min="0"
                           value="<?php echo set_value('min_stock', '5'); ?>"
                           required>
                    <div class="form-text small text-muted">Warns the system when stock levels reach or fall below this.</div>
                </div>

                <!-- 7. Description -->
                <div class="col-12">
                    <label for="description" class="form-label small fw-semibold text-secondary">Description</label>
                    <textarea class="form-control input-custom" 
                              id="description" 
                              name="description" 
                              rows="3" 
                              placeholder="Add specifications, manufacturer info, or shelf notes..."><?php echo set_value('description'); ?></textarea>
                </div>

                <!-- Submission Actions -->
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        <span>Save Item</span>
                    </button>
                    <a href="<?php echo base_url('inventory'); ?>" class="btn btn-outline-secondary px-4 py-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
