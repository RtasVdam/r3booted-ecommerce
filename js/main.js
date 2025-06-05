// Main JavaScript file for R3Booted E-commerce

document.addEventListener('DOMContentLoaded', function() {
    // Initialize page
    initializePage();
    
    // Form validations
    initializeFormValidations();
    
    // Smooth scrolling for anchor links
    initializeSmoothScrolling();
    
    // Mobile menu toggle
    initializeMobileMenu();
});

// Page initialization
function initializePage() {
    // Add loading animation
    document.body.classList.add('page-loaded');
    
    // Initialize tooltips if any
    initializeTooltips();
    
    // Auto-hide notifications
    autoHideNotifications();
    
    // Initialize quantity selectors
    initializeQuantitySelectors();
}

// Form validations
function initializeFormValidations() {
    // Real-time email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', validateEmail);
    });
    
    // Password confirmation validation
    const confirmPasswordInputs = document.querySelectorAll('input[name="confirm_password"]');
    confirmPasswordInputs.forEach(input => {
        input.addEventListener('input', validatePasswordConfirmation);
    });
    
    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', formatPhoneNumber);
    });
    
    // Form submission validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

// Email validation
function validateEmail(event) {
    const email = event.target.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const isValid = emailRegex.test(email);
    
    if (email && !isValid) {
        showFieldError(event.target, 'Please enter a valid email address');
    } else {
        clearFieldError(event.target);
    }
    
    return isValid;
}

// Password confirmation validation
function validatePasswordConfirmation(event) {
    const confirmPassword = event.target.value;
    const passwordField = document.querySelector('input[name="password"], input[name="register_password"]');
    
    if (passwordField && confirmPassword !== passwordField.value) {
        showFieldError(event.target, 'Passwords do not match');
        return false;
    } else {
        clearFieldError(event.target);
        return true;
    }
}

// Phone number formatting
function formatPhoneNumber(event) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length >= 10) {
        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
    }
    event.target.value = value;
}

// Form validation
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    // Email validation
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !validateEmail({target: field})) {
            isValid = false;
        }
    });
    
    // Password confirmation
    const confirmPasswordField = form.querySelector('input[name="confirm_password"]');
    if (confirmPasswordField && confirmPasswordField.value) {
        if (!validatePasswordConfirmation({target: confirmPasswordField})) {
            isValid = false;
        }
    }
    
    return isValid;
}

// Show field error
function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '14px';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = message;
    
    field.style.borderColor = '#dc3545';
    field.parentNode.appendChild(errorDiv);
}

// Clear field error
function clearFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
    field.style.borderColor = '';
}

// Smooth scrolling
function initializeSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            const target = document.querySelector(href);
            
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Mobile menu toggle
function initializeMobileMenu() {
    // Add mobile menu button if needed
    const navbar = document.querySelector('.navbar');
    const navLinks = document.querySelector('.nav-links');
    
    if (window.innerWidth <= 768) {
        // Create mobile menu button
        const menuButton = document.createElement('button');
        menuButton.className = 'mobile-menu-button';
        menuButton.innerHTML = '☰';
        menuButton.style.cssText = `
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #333;
        `;
        
        // Insert menu button
        const navContent = document.querySelector('.nav-content');
        navContent.insertBefore(menuButton, navLinks);
        
        // Toggle menu
        menuButton.addEventListener('click', function() {
            navLinks.classList.toggle('mobile-menu-open');
        });
        
        // Show button on mobile
        if (window.innerWidth <= 768) {
            menuButton.style.display = 'block';
        }
    }
}

// Tooltips
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(event) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = event.target.getAttribute('data-tooltip');
    tooltip.style.cssText = `
        position: absolute;
        background: #333;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
        z-index: 1000;
        white-space: nowrap;
        pointer-events: none;
    `;
    
    document.body.appendChild(tooltip);
    
    // Position tooltip
    const rect = event.target.getBoundingClientRect();
    tooltip.style.left = rect.left + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
    
    event.target.tooltipElement = tooltip;
}

function hideTooltip(event) {
    if (event.target.tooltipElement) {
        event.target.tooltipElement.remove();
        delete event.target.tooltipElement;
    }
}

// Notifications
function autoHideNotifications() {
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        if (notification.classList.contains('show')) {
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        }
    });
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Auto hide
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Quantity selectors
function initializeQuantitySelectors() {
    const quantitySelectors = document.querySelectorAll('.quantity-selector select');
    quantitySelectors.forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            if (form && form.querySelector('input[name="action"][value="add"]')) {
                // Auto-submit add to cart forms when quantity changes
                // This is optional - remove if you prefer manual submission
            }
        });
    });
}

// Cart functions
function updateCartCount() {
    // This function would typically make an AJAX call to get updated cart count
    // For now, it's handled by page refresh in PHP
}

// Search functionality
function initializeSearch() {
    const searchInput = document.querySelector('#search-input');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });
    }
}

function performSearch(query) {
    if (query.length < 2) return;
    
    // This would typically make an AJAX call to search products
    console.log('Searching for:', query);
}

// Product filtering
function filterProducts(category) {
    // Redirect to products page with category filter
    window.location.href = `products.php?category=${category}`;
}

// Image lazy loading
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

// Loading states
function showLoading(element) {
    element.disabled = true;
    element.innerHTML = '<span class="loading-spinner"></span> Loading...';
}

function hideLoading(element, originalText) {
    element.disabled = false;
    element.innerHTML = originalText;
}

// Local storage helpers (for client-side data)
function setLocalStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch (e) {
        console.warn('Could not save to localStorage:', e);
    }
}

function getLocalStorage(key) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : null;
    } catch (e) {
        console.warn('Could not read from localStorage:', e);
        return null;
    }
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function formatCurrency(amount) {
    return 'R' + parseFloat(amount).toLocaleString('en-ZA', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDate(date) {
    return new Intl.DateTimeFormat('en-ZA').format(new Date(date));
}

// Initialize additional features on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeLazyLoading();
    initializeSearch();
});

// Handle page visibility changes
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Page is hidden
        console.log('Page hidden');
    } else {
        // Page is visible
        console.log('Page visible');
        // Could refresh cart count or check for updates
    }
});

// Export functions for global use
window.R3Booted = {
    showNotification,
    formatCurrency,
    formatDate,
    filterProducts,
    updateCartCount
};