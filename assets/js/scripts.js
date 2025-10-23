// Bilet Satın Alma Platformu - JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Page load animasyonu
    document.body.classList.add('fade-in');
    
    // Form validasyonları
    initFormValidations();
    
    // Koltuk seçimi
    initSeatSelection();
    
    // Search autocomplete
    initSearchAutocomplete();
    
    // Loading states
    initLoadingStates();
    
    // Tooltips
    initTooltips();
    
    // Auto-hide alerts
    initAutoHideAlerts();
});

/**
 * Form validasyonlarını başlat
 */
function initFormValidations() {
    // Bootstrap form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Real-time validation
    const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateInput(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateInput(this);
            }
        });
    });
}

/**
 * Input validasyonu
 */
function validateInput(input) {
    const isValid = input.checkValidity();
    
    input.classList.remove('is-valid', 'is-invalid');
    input.classList.add(isValid ? 'is-valid' : 'is-invalid');
    
    // Custom validation messages
    if (!isValid) {
        showValidationMessage(input);
    }
}

/**
 * Validation mesajını göster
 */
function showValidationMessage(input) {
    const feedback = input.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        if (input.type === 'email' && input.validity.typeMismatch) {
            feedback.textContent = 'Geçerli bir e-posta adresi girin.';
        } else if (input.validity.valueMissing) {
            feedback.textContent = 'Bu alan gereklidir.';
        } else if (input.validity.tooShort) {
            feedback.textContent = `En az ${input.minLength} karakter olmalıdır.`;
        } else if (input.validity.patternMismatch) {
            feedback.textContent = 'Geçersiz format.';
        }
    }
}

/**
 * Koltuk seçimini başlat
 */
function initSeatSelection() {
    let selectedSeats = [];
    const maxSeats = 4;
    
    // Koltuk tıklama olaylarını dinle
    const seats = document.querySelectorAll('.seat.available');
    
    seats.forEach(seat => {
        seat.addEventListener('click', function() {
            handleSeatClick(this, selectedSeats, maxSeats);
        });
    });
}

/**
 * Koltuk tıklama işlemini yönet
 */
function handleSeatClick(seatElement, selectedSeats, maxSeats) {
    const seatNumber = seatElement.getAttribute('data-seat');
    
    if (seatElement.classList.contains('selected')) {
        // Koltuk zaten seçili, çıkar
        seatElement.classList.remove('selected');
        const index = selectedSeats.indexOf(seatNumber);
        if (index > -1) {
            selectedSeats.splice(index, 1);
        }
    } else {
        // Yeni koltuk seç
        if (selectedSeats.length >= maxSeats) {
            showToast('En fazla ' + maxSeats + ' koltuk seçebilirsiniz', 'warning');
            return;
        }
        
        seatElement.classList.add('selected');
        selectedSeats.push(seatNumber);
    }
    
    updateSeatDisplay(selectedSeats);
}

/**
 * Koltuk gösterimini güncelle
 */
function updateSeatDisplay(selectedSeats) {
    // Seçili koltukları özet kısmında göster
    const selectedSeatDisplay = document.getElementById('selected_seat_display');
    const selectedSeatInput = document.getElementById('selected_seats_input');
    
    if (selectedSeats.length === 0) {
        if (selectedSeatDisplay) {
            selectedSeatDisplay.innerHTML = '<span class="text-muted">Koltuk seçin</span>';
        }
        if (selectedSeatInput) {
            selectedSeatInput.value = '';
        }
    } else {
        if (selectedSeatDisplay) {
            selectedSeatDisplay.innerHTML = selectedSeats.join(', ');
        }
        if (selectedSeatInput) {
            selectedSeatInput.value = selectedSeats.join(',');
        }
    }
    
    // Toplam fiyatı güncelle
    updateTotalPrice(selectedSeats.length);
}

/**
 * Toplam fiyatı güncelle
 */
function updateTotalPrice(seatCount) {
    const priceElement = document.querySelector('.ticket-price');
    const totalElement = document.getElementById('total_price_display');
    
    if (priceElement && totalElement) {
        const unitPrice = parseFloat(priceElement.getAttribute('data-price') || priceElement.textContent.replace('₺', '').replace(',', '.'));
        const totalPrice = unitPrice * seatCount;
        
        totalElement.textContent = totalPrice.toFixed(2) + '₺';
        
        // Hidden input'a da yaz
        const totalInput = document.getElementById('total_price_input');
        if (totalInput) {
            totalInput.value = totalPrice.toFixed(2);
        }
    }
}

/**
 * Toast mesajı göster
 */
function showToast(message, type = 'info') {
    // Basit toast implementasyonu
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

/**
 * Search autocomplete başlat
 */
function initSearchAutocomplete() {
    const cityInputs = document.querySelectorAll('#departure_city, #arrival_city');
    
    cityInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Basit autocomplete implementasyonu
            // Gerçek projede daha gelişmiş bir çözüm kullanılabilir
        });
    });
}

/**
 * Loading states başlat
 */
function initLoadingStates() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                const originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<span class="loading"></span> İşleniyor...';
                submitButton.disabled = true;
                
                // 30 saniye sonra geri döndür (timeout)
                setTimeout(() => {
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                }, 30000);
            }
        });
    });
}

/**
 * Tooltips başlat
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Auto-hide alerts başlat
 */
function initAutoHideAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.classList.contains('show')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000); // 5 saniye sonra gizle
    });
}

/**
 * Toplam fiyatı hesapla
 */
function calculateTotal() {
    const basePrice = parseFloat(document.getElementById('base_price')?.value || 0);
    const discountAmount = parseFloat(document.getElementById('discount_amount_value')?.value || 0);
    const total = basePrice - discountAmount;
    
    const totalDisplay = document.getElementById('total_price');
    if (totalDisplay) {
        totalDisplay.textContent = formatPrice(total);
    }
}

/**
 * Fiyat formatla
 */
function formatPrice(amount) {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY'
    }).format(amount);
}

/**
 * Notification göster
 */
function showNotification(message, type = 'info') {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}-circle"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const container = document.querySelector('main.container');
    if (container) {
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert && alert.classList.contains('show')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
}

/**
 * Confirm dialog
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Panoya kopyalandı!', 'success');
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('Panoya kopyalandı!', 'success');
    }
}

/**
 * Print page
 */
function printPage() {
    window.print();
}

/**
 * Toggle password visibility
 */
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.querySelector(`[onclick="togglePasswordVisibility('${inputId}')"] i`);
    
    if (input && icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

// Global functions
window.confirmAction = confirmAction;
window.copyToClipboard = copyToClipboard;
window.printPage = printPage;
window.togglePasswordVisibility = togglePasswordVisibility;