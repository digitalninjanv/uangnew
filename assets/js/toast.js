// Toast Notification System

let toastContainer = null;
let toastIdCounter = 0;

// Initialize toast container
function initToastContainer() {
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    return toastContainer;
}

/**
 * Show a toast notification
 * @param {string} message - The message to display
 * @param {string} type - Type of toast: 'success', 'error', 'info', 'warning'
 * @param {number} duration - Duration in milliseconds (0 for no auto-dismiss)
 * @returns {HTMLElement} The toast element
 */
function showToast(message, type = 'info', duration = 5000) {
    const container = initToastContainer();
    const toastId = ++toastIdCounter;

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.dataset.toastId = toastId;

    // Icon based on type
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    };

    const titles = {
        success: 'Berhasil!',
        error: 'Terjadi Kesalahan',
        info: 'Informasi',
        warning: 'Peringatan'
    };

    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${icons[type] || icons.info}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${titles[type] || titles.info}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" aria-label="Close notification">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Add close button handler
    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => {
        dismissToast(toast);
    });

    // Add to container
    container.appendChild(toast);

    // Auto-dismiss if duration is set
    if (duration > 0) {
        setTimeout(() => {
            dismissToast(toast);
        }, duration);
    }

    return toast;
}

/**
 * Dismiss a toast
 * @param {HTMLElement} toast - The toast element to dismiss
 */
function dismissToast(toast) {
    if (!toast || !toast.parentElement) return;

    toast.classList.add('removing');

    setTimeout(() => {
        if (toast.parentElement) {
            toast.parentElement.removeChild(toast);
        }

        // Remove container if empty
        if (toastContainer && toastContainer.children.length === 0) {
            toastContainer.remove();
            toastContainer = null;
        }
    }, 300); // Match animation duration
}

/**
 * Dismiss all toasts
 */
function dismissAllToasts() {
    if (!toastContainer) return;

    const toasts = toastContainer.querySelectorAll('.toast');
    toasts.forEach(toast => dismissToast(toast));
}

// Convenience functions
function showSuccess(message, duration = 5000) {
    return showToast(message, 'success', duration);
}

function showError(message, duration = 7000) {
    return showToast(message, 'error', duration);
}

function showInfo(message, duration = 5000) {
    return showToast(message, 'info', duration);
}

function showWarning(message, duration = 6000) {
    return showToast(message, 'warning', duration);
}

// Export functions to global scope
window.showToast = showToast;
window.showSuccess = showSuccess;
window.showError = showError;
window.showInfo = showInfo;
window.showWarning = showWarning;
window.dismissToast = dismissToast;
window.dismissAllToasts = dismissAllToasts;
