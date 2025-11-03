</main>
    
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-2">&copy; <?= date('Y') ?> FitLife Winnipeg Fitness Center. All rights reserved.</p>
            <p class="mb-0">
                <i class="fas fa-map-marker-alt"></i> Downtown & St. Vital Locations | 
                <i class="fas fa-phone"></i> (204) 555-FITLIFE | 
                <i class="fas fa-envelope"></i> info@fitlifewinnipeg.com
            </p>
        </div>
    </footer>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= isset($js_path) ? $js_path : '../assets/js/main.js' ?>"></script>
</body>
</html>