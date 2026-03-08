    </div> <!-- End container-fluid -->
    <footer>
        <div class="container text-center pt-4">
            &copy; <?php echo date('Y'); ?> KIU Support - Kampala International University Online Complaint Management System
        </div>
    </footer>
    </div> <!-- End #main-content -->

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle for Mobile Devices
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        if(sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }
    </script>
</body>
</html>
