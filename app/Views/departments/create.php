<!-- Page Title Section -->
<div class="page-title-section fade-in-up">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="page-title mb-1">Add Hospital Department</h1>
            <p class="text-secondary mb-0">Register a new medical, administrative, or clinical unit</p>
        </div>
        <div>
            <a href="<?php echo base_url('departments'); ?>" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Directory</span>
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

<!-- Create Department Form Card -->
<div class="row fade-in-up" style="animation-delay: 0.1s;">
    <div class="col-12 col-lg-8 col-xl-6">
        <div class="standard-card">
            <div class="card-header-styled mb-4">
                <h5 class="card-title-styled">
                    <i class="fa-solid fa-hospital-user text-primary"></i>
                    <span>Department Specifications</span>
                </h5>
            </div>

            <form method="POST" action="<?php echo base_url('departments/create'); ?>" class="row g-3">
                <!-- 1. Department Code -->
                <div class="col-12 col-sm-6">
                    <label for="code" class="form-label small fw-semibold text-secondary">Department Code <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="code" 
                           name="code" 
                           placeholder="e.g. OPD, LAB"
                           style="text-transform: uppercase;"
                           value="<?php echo set_value('code'); ?>"
                           required>
                    <div class="form-text small text-muted">Must be unique, alphanumeric and dashes only.</div>
                </div>

                <!-- 2. Department Name -->
                <div class="col-12 col-sm-6">
                    <label for="name" class="form-label small fw-semibold text-secondary">Department Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control input-custom" 
                           id="name" 
                           name="name" 
                           placeholder="e.g. Outpatient Department"
                           value="<?php echo set_value('name'); ?>"
                           required>
                </div>

                <!-- 3. Description -->
                <div class="col-12">
                    <label for="description" class="form-label small fw-semibold text-secondary">Description</label>
                    <textarea class="form-control input-custom" 
                              id="description" 
                              name="description" 
                              rows="3" 
                              placeholder="Add brief details about the department services..."><?php echo set_value('description'); ?></textarea>
                </div>

                <!-- Submission Actions -->
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-save"></i>
                        <span>Save Department</span>
                    </button>
                    <a href="<?php echo base_url('departments'); ?>" class="btn btn-outline-secondary px-4 py-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
