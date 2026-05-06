<?php if (!strpos($_SERVER['REQUEST_URI'], 'login.php')): ?>
        </main>
        
        <!-- Footer -->
        <footer class="footer">
            <p>© <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Todos los derechos reservados.</p>
            <p>Versión <?php echo APP_VERSION; ?></p>
        </footer>
    </div>
    </div>
</div>
<?php endif; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    <!--Excel Export-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo APP_URL; ?>/assets/js/export-excel.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/loader.js"></script>
    <?php if (!strpos($_SERVER['REQUEST_URI'], 'login.php')): ?>
    <script>
        (function () {
            const desktopToggle = document.getElementById('sidebarToggleDesktop');
            const desktopReopen = document.getElementById('sidebarReopenDesktop');
            const mobileToggle = document.getElementById('sidebarToggleMobile');
            const overlay = document.getElementById('sidebarOverlay');
            const mobileMq = window.matchMedia('(max-width: 991.98px)');
            const storageKey = 'hh-sidebar-collapsed';

            if (!desktopToggle && !mobileToggle) {
                return;
            }

            const applyDesktopPreference = () => {
                if (mobileMq.matches) {
                    document.body.classList.remove('hh-sidebar-collapsed');
                    return;
                }
                const collapsed = localStorage.getItem(storageKey) === '1';
                document.body.classList.toggle('hh-sidebar-collapsed', collapsed);
            };

            applyDesktopPreference();

            desktopToggle && desktopToggle.addEventListener('click', function () {
                if (mobileMq.matches) {
                    return;
                }
                const collapsedNow = !document.body.classList.contains('hh-sidebar-collapsed');
                document.body.classList.toggle('hh-sidebar-collapsed', collapsedNow);
                localStorage.setItem(storageKey, collapsedNow ? '1' : '0');
            });

            desktopReopen && desktopReopen.addEventListener('click', function () {
                if (mobileMq.matches) {
                    return;
                }
                document.body.classList.remove('hh-sidebar-collapsed');
                localStorage.setItem(storageKey, '0');
            });

            const closeMobileSidebar = () => document.body.classList.remove('hh-sidebar-open');

            mobileToggle && mobileToggle.addEventListener('click', function () {
                document.body.classList.toggle('hh-sidebar-open');
            });

            overlay && overlay.addEventListener('click', closeMobileSidebar);

            window.addEventListener('resize', function () {
                if (!mobileMq.matches) {
                    closeMobileSidebar();
                    applyDesktopPreference();
                } else {
                    document.body.classList.remove('hh-sidebar-collapsed');
                }
            });
        })();
    </script>
    <?php endif; ?>
    
    <?php if (isset($customJS)): ?>
    <script>
        <?php echo $customJS; ?>
    </script>
    <?php endif; ?>
</body>
</html>