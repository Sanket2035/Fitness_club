</div>
    <!-- Main Content Container End -->

    <!-- Footer Start -->
    <div class="footer container-fluid mt-5 py-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <h4 class="text-primary mb-4">Get In Touch</h4>
                    <p><i class="fa fa-map-marker-alt me-2"></i>123 Fitness Street, Gym City, GC 12345</p>
                    <p><i class="fa fa-phone-alt me-2"></i>+123 456 7890</p>
                    <p><i class="fa fa-envelope me-2"></i>info@fitnessclub.com</p>
                    <div class="d-flex justify-content-start mt-4">
                        <a class="btn btn-outline-light rounded-circle me-2" href="#"><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-outline-light rounded-circle me-2" href="#"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-outline-light rounded-circle me-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a class="btn btn-outline-light rounded-circle" href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h4 class="text-primary mb-4">Quick Links</h4>
                    <div class="d-flex flex-column justify-content-start">
                        <a class="text-light mb-2" href="index.php"><i class="fa fa-angle-right me-2"></i>Home</a>
                        <a class="text-light mb-2" href="about.php"><i class="fa fa-angle-right me-2"></i>About Us</a>
                        <a class="text-light mb-2" href="admin/classes.php"><i class="fa fa-angle-right me-2"></i>Our Classes</a>
                        <a class="text-light mb-2" href="admin/trainers.php"><i class="fa fa-angle-right me-2"></i>Our Trainers</a>
                        <a class="text-light" href="contact.php"><i class="fa fa-angle-right me-2"></i>Contact Us</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h4 class="text-primary mb-4">Opening Hours</h4>
                    <h5 class="text-light">Monday - Friday</h5>
                    <p>8:00 AM - 9:00 PM</p>
                    <h5 class="text-light">Saturday - Sunday</h5>
                    <p>8:00 AM - 8:00 PM</p>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h4 class="text-primary mb-4">Newsletter</h4>
                    <form action="">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="newsletterEmail" placeholder="name@example.com">
                            <label for="newsletterEmail">Email address</label>
                        </div>
                        <button class="btn btn-primary w-100">Subscribe Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid bg-dark text-light border-top py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    &copy; <?php echo date('Y'); ?> <a class="text-primary" href="#"><?php echo SITE_NAME; ?></a>. All Rights Reserved.
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item"><a href="#">Privacy Policy</a></li>
                        <li class="list-inline-item"><span class="text-light">|</span></li>
                        <li class="list-inline-item"><a href="#">Terms & Conditions</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary back-to-top"><i class="fa fa-angle-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/wow.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Initialize WOW.js for animations
        new WOW().init();

        // Initialize DataTables
        $(document).ready(function() {
            $('.datatable').DataTable();

            // Back to top button
            $(window).scroll(function() {
                if ($(this).scrollTop() > 100) {
                    $('.back-to-top').fadeIn('slow');
                } else {
                    $('.back-to-top').fadeOut('slow');
                }
            });
            $('.back-to-top').click(function() {
                $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
                return false;
            });

            // Auto-hide alerts after 5 seconds
            $('.alert').delay(5000).fadeOut(500);

            // Enable tooltips everywhere
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
</body>
</html>