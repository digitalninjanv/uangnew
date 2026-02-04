// Main JavaScript

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function () {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navbarMenu = document.getElementById('navbarMenu');
    const mobileFab = document.getElementById('mobileFab');
    const mobileActionSheet = document.getElementById('mobileActionSheet');
    const mobileActionOverlay = document.getElementById('mobileActionOverlay');
    const mobileActionClose = document.querySelector('.mobile-action-close');
    const mobileActionLinks = document.querySelectorAll('.mobile-action-item');

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function () {
            navbarMenu.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function (e) {
        if (navbarMenu && navbarMenu.classList.contains('active')) {
            if (!e.target.closest('.navbar-menu') && !e.target.closest('.mobile-menu-toggle')) {
                navbarMenu.classList.remove('active');
            }
        }
    });

    const toggleActionSheet = (isOpen) => {
        if (!mobileActionSheet || !mobileActionOverlay) {
            return;
        }

        if (isOpen) {
            mobileActionSheet.classList.add('active');
            mobileActionOverlay.classList.add('active');
            document.body.classList.add('no-scroll');
        } else {
            mobileActionSheet.classList.remove('active');
            mobileActionOverlay.classList.remove('active');
            document.body.classList.remove('no-scroll');
        }
    };

    if (mobileFab) {
        mobileFab.addEventListener('click', function () {
            toggleActionSheet(true);
        });
    }

    if (mobileActionClose) {
        mobileActionClose.addEventListener('click', function () {
            toggleActionSheet(false);
        });
    }

    if (mobileActionOverlay) {
        mobileActionOverlay.addEventListener('click', function () {
            toggleActionSheet(false);
        });
    }

    mobileActionLinks.forEach(link => {
        link.addEventListener('click', function () {
            toggleActionSheet(false);
        });
    });

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Modal helper functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal when clicking outside or pressing ESC
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal')) {
        closeModal(e.target.id);
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
        const activeModal = document.querySelector('.modal.active');
        if (activeModal) {
            closeModal(activeModal.id);
        }
    }
});

// AJAX helper function
async function fetchAPI(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Request failed');
        }

        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Show notification (deprecated - use toast system instead)
function showNotification(message, type = 'success') {
    // Use new toast system if available
    if (typeof showToast === 'function') {
        showToast(message, type);
        return;
    }

    // Fallback to old alert system
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
    `;

    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alert, container.firstChild);

        setTimeout(() => {
            alert.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => alert.remove(), 300);
        }, 3000);
    }
}

// Format currency for display
function formatCurrency(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Format number with thousand separators (for input)
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Parse formatted number back to plain number
function parseFormattedNumber(str) {
    return parseFloat(str.replace(/[^0-9,-]/g, '').replace(',', '.')) || 0;
}

// Currency Input Formatter
function initCurrencyInput(input) {
    // Check if already initialized to prevent duplicate event listeners
    if (input.dataset.currencyInit === 'true') {
        // Just update the value if needed
        const hiddenInput = input.nextElementSibling;
        if (hiddenInput && hiddenInput.classList.contains('currency-value')) {
            if (input.value && input.value !== '0') {
                const numValue = parseFloat(input.value) || 0;
                input.value = formatNumber(numValue);
                hiddenInput.value = numValue;
            }
        }
        return;
    }

    // Create hidden input to store actual value
    let hiddenInput = input.nextElementSibling;
    if (!hiddenInput || !hiddenInput.classList.contains('currency-value')) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = input.name || input.getAttribute('name') || 'balance';
        hiddenInput.classList.add('currency-value');
        input.parentNode.insertBefore(hiddenInput, input.nextSibling);
        input.removeAttribute('name'); // Remove name from visible input
    }

    // Set initial value if exists
    if (input.value && input.value !== '0') {
        const numValue = parseFloat(input.value) || 0;
        input.value = formatNumber(numValue);
        hiddenInput.value = numValue;
    } else {
        input.value = '';
        hiddenInput.value = '0';
    }

    // Format on input
    input.addEventListener('input', function (e) {
        let value = e.target.value;

        // Remove all non-digit characters
        value = value.replace(/\D/g, '');

        if (value === '' || value === '0') {
            e.target.value = '';
            hiddenInput.value = '0';
            return;
        }

        // Convert to number and format
        const numValue = parseInt(value);
        e.target.value = formatNumber(numValue);
        hiddenInput.value = numValue;
    });

    // Add visual indicator
    if (!input.dataset.currencyInit) {
        input.dataset.currencyInit = 'true';
        input.style.fontFamily = "'Monaco', 'Courier New', monospace";
        input.style.fontWeight = '600';

        // Add Rp prefix as placeholder if empty
        const originalPlaceholder = input.placeholder;
        if (!originalPlaceholder.includes('Rp')) {
            input.placeholder = 'Rp 0';
        }
    }

    // Format on blur (cleanup)
    input.addEventListener('blur', function (e) {
        if (e.target.value === '' || e.target.value === '0') {
            e.target.value = '';
            hiddenInput.value = '0';
        }
    });

    // Handle form submission
    const form = input.closest('form');
    if (form && !form.dataset.currencyFormInit) {
        form.dataset.currencyFormInit = 'true';
        form.addEventListener('submit', function (e) {
            // Make sure all hidden inputs have correct values
            const currencyInputs = form.querySelectorAll('input[data-currency]');
            currencyInputs.forEach(inp => {
                const hidden = inp.nextElementSibling;
                if (hidden && hidden.classList.contains('currency-value')) {
                    const value = inp.value.replace(/\D/g, '');
                    hidden.value = value || '0';
                }
            });
        });
    }
}

// Auto-initialize all currency inputs
document.addEventListener('DOMContentLoaded', function () {
    const currencyInputs = document.querySelectorAll('input[data-currency]');
    currencyInputs.forEach(input => {
        initCurrencyInput(input);
    });
});

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
}

// CSS for fadeOut animation
if (!document.querySelector('#dynamicStyles')) {
    const style = document.createElement('style');
    style.id = 'dynamicStyles';
    style.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
    `;
    document.head.appendChild(style);
}
