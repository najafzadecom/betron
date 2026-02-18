/* ------------------------------------------------------------------------------
 *
 *  # Custom JS code
 *
 *  Place here all your custom js. Make sure it's loaded after app.js
 *
 * ---------------------------------------------------------------------------- */

// Common List Page Functions
class ListPageManager {
    constructor() {
        this.init();
    }

    init() {
        this.initializeAdvancedFilters();
        this.bindEvents();
        this.initializeDeleteConfirmation();
    }

    // Initialize advanced filters visibility based on filled inputs
    initializeAdvancedFilters() {
        document.addEventListener('DOMContentLoaded', () => {
            // Check if any advanced filters are filled and show advanced filters if needed
            // This will be configured per page via data attributes or specific input lists
            const advancedFiltersContainer = document.getElementById('advancedFilters');
            if (!advancedFiltersContainer) return;

            const advancedInputs = advancedFiltersContainer.querySelectorAll('input, select');
            let hasAdvancedFilters = false;

            advancedInputs.forEach(input => {
                if (input.value && input.value !== '') {
                    hasAdvancedFilters = true;
                }
            });

            if (hasAdvancedFilters) {
                advancedFiltersContainer.style.display = 'block';
            }
        });
    }

    bindEvents() {
        // Auto-submit on select change for better UX
        document.addEventListener('change', (e) => {
            if (e.target.tagName === 'SELECT' && e.target.closest('#searchForm')) {
                document.getElementById('searchForm').submit();
            }
        });

        // Handle sorting clicks to preserve form filters
        document.addEventListener('click', (e) => {
            const sortLink = e.target.closest('th a[href*="sort="]');
            if (sortLink) {
                e.preventDefault();

                // Get current form data
                const formData = new FormData(document.getElementById('searchForm'));
                const params = new URLSearchParams(formData);

                // Extract sort parameters from the clicked link
                const url = new URL(sortLink.href);
                const sort = url.searchParams.get('sort');
                const direction = url.searchParams.get('direction');

                // Add sort parameters to existing form params
                params.set('sort', sort);
                params.set('direction', direction);

                // Navigate to the new URL with all parameters
                window.location.href = window.location.pathname + '?' + params.toString();
            }
        });
    }

    // Initialize delete confirmation functionality
    initializeDeleteConfirmation() {
        document.addEventListener('click', (e) => {
            const deleteButton = e.target.closest('[data-delete-url]');
            if (deleteButton) {
                e.preventDefault();
                const deleteUrl = deleteButton.getAttribute('data-delete-url');
                const itemName = deleteButton.getAttribute('data-item-name') || 'bu elementi';

                this.showDeleteConfirmation(deleteUrl, itemName);
            }
        });
    }

    // Show delete confirmation modal using SweetAlert2
    showDeleteConfirmation(deleteUrl, itemName) {
        // Initialize SweetAlert2
        const swalInit = Swal.mixin({
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-light',
                denyButton: 'btn btn-light'
            }
        });

        // Get translations from window object or use defaults
        const translations = window.deleteTranslations || {
            title: 'Are you sure?',
            textTemplate: 'Are you sure you want to delete :item? This action cannot be undone.',
            confirmButtonText: 'Yes, delete!',
            cancelButtonText: 'No, cancel',
            deletingTitle: 'Deleting...',
            deletingText: 'Please wait',
            successTitle: 'Successfully deleted!',
            successText: 'Element successfully deleted',
            errorTitle: 'Error!',
            errorText: 'An error occurred while deleting',
            okButton: 'OK'
        };

        // Replace :item placeholder with actual item name
        const deleteText = translations.textTemplate.replace(':item', itemName);

        swalInit.fire({
            title: translations.title,
            text: deleteText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: translations.confirmButtonText,
            cancelButtonText: translations.cancelButtonText,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                swalInit.fire({
                    title: translations.deletingTitle,
                    text: translations.deletingText,
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send AJAX request instead of form submit
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                const formData = new FormData();
                formData.append('_token', csrfToken ? csrfToken.getAttribute('content') : '');
                formData.append('_method', 'DELETE');
                formData.append('confirmed', '1');

                fetch(deleteUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    return response.json().then(data => {
                        if (response.ok) {
                        // Success - show success message and reload page
                            swalInit.fire({
                                title: translations.successTitle,
                                text: data.message || translations.successText,
                                icon: 'success',
                                confirmButtonText: translations.okButton
                            }).then(() => {
                                // Reload the page to update the list
                                window.location.reload();
                            });
                        } else {
                            // Error - show error message
                            swalInit.fire({
                                title: translations.errorTitle,
                                text: data.message || translations.errorText,
                                icon: 'error',
                                confirmButtonText: translations.okButton
                            });
                        }
                    });
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    swalInit.fire({
                        title: translations.errorTitle,
                        text: translations.errorText,
                        icon: 'error',
                        confirmButtonText: translations.okButton
                    });
                });
            }
        });
    }
}

// Common utility functions for list pages
// toggleAdvancedFilters function removed - filters are now always visible

function clearFilters() {
    // Clear all form inputs
    document.getElementById('searchForm').reset();

    // Submit form to remove URL parameters
    window.location.href = window.location.pathname;
}

function changeLimit(limit) {
    // Get current form data
    const formData = new FormData(document.getElementById('searchForm'));
    const params = new URLSearchParams(formData);

    // Add limit parameter
    params.set('limit', limit);

    // Navigate to the new URL with all parameters
    window.location.href = window.location.pathname + '?' + params.toString();
}

// Currency symbols utility
const currencySymbols = {
    'TRY': '₺',
    'USD': '$',
    'EUR': '€',
    'GBP': '£'
};

// Format amount with currency symbol
function formatAmountWithCurrency(amount, currency) {
    const symbol = currencySymbols[currency] || '';
    return `${symbol} ${parseFloat(amount).toFixed(2)}`;
}

// Modal handling for show details
function initializeShowModal() {
    const showModal = document.getElementById('show_modal');
    if (!showModal) return;

    showModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const url = button.getAttribute('data-url');

        fetch(url)
            .then(response => response.json())
            .then(responseData => {
                const data = responseData.item;

                // Populate modal fields based on data attributes
                Object.keys(data).forEach(key => {
                    const element = document.getElementById(key.replace(/_/g, '-'));
                    if (element) {
                        if (key.endsWith('_html') || key === 'status_html' || key === 'coloredName' || key === 'coloredRoleNames') {
                            element.innerHTML = data[key] ?? '-';
                        } else if (key === 'roles' && Array.isArray(data[key])) {
                            element.innerText = data[key].map(role => role.name).join(', ') || '-';
                        } else if (key === 'created_at' || key === 'updated_at') {
                            element.innerText = data[key] ? new Date(data[key]).toLocaleString() : '-';
                        } else if (key === 'image' && data[key]) {
                            // Special handling for image fields - show as image if URL exists
                            const imageUrl = data[key];
                            if (imageUrl && imageUrl !== '-' && imageUrl.trim() !== '') {
                                element.innerHTML = `<img src="${imageUrl}" alt="Image" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">`;
                            } else {
                                element.innerText = '-';
                            }
                        } else if (key === 'image_url' && data[key]) {
                            // Special handling for image_url fields - show as image if URL exists
                            const imageUrl = data[key];
                            if (imageUrl && imageUrl !== '-' && imageUrl.trim() !== '') {
                                element.innerHTML = `<img src="${imageUrl}" alt="Bank Logo" class="img-thumbnail" style="max-width: 120px; max-height: 120px;">`;
                            } else {
                                element.innerText = '-';
                            }
                        } else {
                            element.innerHTML = data[key] ?? '-';
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    });
}

// Password toggle functionality
function togglePasswordVisibility(inputId = 'password', iconId = 'togglePassword') {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = document.getElementById(iconId);
    const toggleButton = toggleIcon?.closest('[data-bs-toggle="tooltip"]');

    if (passwordInput && toggleIcon) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('ph-eye');
            toggleIcon.classList.add('ph-eye-slash');

            // Update tooltip text
            if (toggleButton) {
                const hideText = toggleButton.dataset.hideText || 'Hide password';
                toggleButton.setAttribute('data-bs-original-title', hideText);
                toggleButton.setAttribute('title', hideText);
            }
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('ph-eye-slash');
            toggleIcon.classList.add('ph-eye');

            // Update tooltip text
            if (toggleButton) {
                const showText = toggleButton.dataset.showText || 'Show password';
                toggleButton.setAttribute('data-bs-original-title', showText);
                toggleButton.setAttribute('title', showText);
            }
        }
    }
}

// Specific toggle functions for different forms
function togglePassword() {
    togglePasswordVisibility('password', 'togglePassword');
}

function togglePasswordConfirmation() {
    togglePasswordVisibility('password_confirmation', 'togglePasswordConfirmation');
}

/* ------------------------------------------------------------------------------
 *
 *  #  Toggle Tooltips
 *
 *  Initialize and manage toggle tooltips
 *
 * ---------------------------------------------------------------------------- */

// Setup module
const Tooltips = function () {

    // Initialize all password toggle tooltips
    const _initializeTooltips = function() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    };

    // Return objects assigned to module
    return {
        init: function() {
            _initializeTooltips();
        }
    }
}();

// Initialize daterange pickers
function initializeDateRangePickers() {
    if (typeof $ === 'undefined' || typeof $.fn.daterangepicker === 'undefined') {
        return;
    }

    $('.daterange-picker').each(function() {
        const $this = $(this);
        const fieldName = $this.attr('name');
        let startName, endName;

        // Determine start and end field names based on field name
        if (fieldName === 'creation_date_range') {
            startName = 'created_from';
            endName = 'created_to';
        } else if (fieldName === 'update_date_range') {
            startName = 'updated_from';
            endName = 'updated_to';
        } else if (fieldName === 'date_range') {
            startName = 'date_from';
            endName = 'date_to';
        } else if (fieldName === 'accepted_date_range') {
            startName = 'accepted_from';
            endName = 'accepted_to';
        }

        else {
            // Try to infer from data attributes
            startName = $this.data('start-name') || 'date_from';
            endName = $this.data('end-name') || 'date_to';
        }

        // Get translation strings from window object or use defaults
        const translations = window.daterangeTranslations || {
            cancelLabel: 'Clear',
            applyLabel: 'Apply',
            fromLabel: 'From',
            toLabel: 'To',
            customRangeLabel: 'Custom',
            format: 'YYYY-MM-DD',
            daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
            monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        };

        $this.daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: translations.cancelLabel,
                applyLabel: translations.applyLabel,
                fromLabel: translations.fromLabel,
                toLabel: translations.toLabel,
                customRangeLabel: translations.customRangeLabel,
                format: translations.format,
                daysOfWeek: translations.daysOfWeek,
                monthNames: translations.monthNames,
                firstDay: 1
            },
            ranges: translations.ranges
        });

        $this.on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $('input[name="' + startName + '"]').val(picker.startDate.format('YYYY-MM-DD'));
            $('input[name="' + endName + '"]').val(picker.endDate.format('YYYY-MM-DD'));
        });

        $this.on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('input[name="' + startName + '"]').val('');
            $('input[name="' + endName + '"]').val('');
        });
    });
}

// Initialize the list page manager
document.addEventListener('DOMContentLoaded', () => {
    new ListPageManager();
    initializeShowModal();
    initializeDateRangePickers();
    Tooltips.init();
});
