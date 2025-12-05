/**
 * FitLife Winnipeg CMS - Main JavaScript
 */

// Auto-hide alerts after 5 seconds
$(document).ready(function() {
    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Confirm delete actions
    $('.btn-danger[href*="delete"]').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Smooth scroll anchor links
    $('a[href^="#"]').on('click', function(e) {
        var target = $(this.getAttribute('href'));
        if(target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 1000);
        }
    });
    
    // Add active class to current nav item
    var currentPath = window.location.pathname.split('/').pop();
    $('.navbar-nav a[href="' + currentPath + '"]').addClass('active');
    
    // Image preview for file uploads
    $('input[type="file"]').on('change', function() {
        var input = this;
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = $(input).closest('form').find('.image-preview');
                if (preview.length) {
                    preview.attr('src', e.target.result).show();
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    });
    
    // Form validation feedback
    $('form').on('submit', function() {
        var isValid = true;
        $(this).find('input[required], textarea[required], select[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            alert('Please fill in all required fields.');
            return false;
        }
    });
    
    // Remove invalid class on input
    $('input, textarea, select').on('input change', function() {
        $(this).removeClass('is-invalid');
    });
    
    // Bootstrap tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
    
    // Bootstrap popover initialization
    $('[data-toggle="popover"]').popover();
    
    // Print functionality
    $('.btn-print').on('click', function(e) {
        e.preventDefault();
        window.print();
    });
    
    console.log('FitLife CMS JavaScript Loaded');
});