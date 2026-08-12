<?php if (session()->get('logged_in')): ?>
            </main>
        </div>
    </div>
    <footer class="text-center text-muted py-3 small border-top" style="font-size: 0.85rem;">
		&copy; Copyright 2026 <strong>Biñan City Hospital Inventory Management System</strong>
    </footer>
<?php endif; ?>

<!-- External Scripts (Local) -->
<script src="<?php echo base_url('assets/vendor/jquery/jquery-3.7.0.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>

<!-- Chart.js and DataTables Core + Buttons Extension JS -->
<script src="<?php echo base_url('assets/vendor/chartjs/chart.umd.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/datatables/js/jquery.dataTables.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/datatables/js/dataTables.bootstrap5.min.js'); ?>"></script>

<!-- DataTables Buttons Extensions for CSV and Print -->
<script src="<?php echo base_url('assets/vendor/datatables/js/dataTables.buttons.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/datatables/js/buttons.bootstrap5.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/datatables/js/buttons.html5.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendor/datatables/js/buttons.print.min.js'); ?>"></script>

<!-- Custom JS Application File -->
<script src="<?php echo base_url('assets/js/app.js?v=' . filemtime(FCPATH . 'assets/js/app.js')); ?>"></script>

</body>
</html>
