// Cleave.js money format helper for Filament v4
// Load this in Filament admin panel for all amount inputs

// CDN fallback if Cleave not loaded

function loadCleaveIfNeeded() {
    if (typeof window.Cleave === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js';
        script.onload = function() {
            if (window.initCleaveMoney) {
                window.initCleaveMoney();
            } else {
                console.error('Cleave loaded but initCleaveMoney missing');
            }
        };
        script.onerror = function() {
            console.error('Gagal load Cleave.js dari CDN!');
        };
        document.head.appendChild(script);
    } else {
        // Cleave already loaded
        window.initCleaveMoney && window.initCleaveMoney();
    }
}
    // Native JS money format for Filament v4
    // Format input as 1.000.000 saat user mengetik

    function formatRupiah(angka) {
        angka = angka.replace(/[^\d]/g, '');
        if (!angka) return '';
        return angka.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function attachMoneyFormat() {
        const selectors = [
            'input[name="amount"]',
            'input[name="initial_balance"]',
            'input[name="project_value"]',
            'input[id$=".amount"]',
            'input[id$=".initial_balance"]',
            'input[id$=".project_value"]',
        ];
        document.querySelectorAll(selectors.join(',')).forEach(function(input) {
            if (input.getAttribute('data-money-format')) return;
            input.setAttribute('data-money-format', '1');
            input.addEventListener('input', function(e) {
                const cursor = input.selectionStart;
                const before = input.value;
                input.value = formatRupiah(input.value);
                // Try to keep cursor position
                let diff = input.value.length - before.length;
                input.setSelectionRange(cursor + diff, cursor + diff);
            });
            // Format on load
            input.value = formatRupiah(input.value);
        });
    }

    document.addEventListener('DOMContentLoaded', attachMoneyFormat);
    document.addEventListener('livewire:navigated', attachMoneyFormat);
    document.addEventListener('livewire:morph', attachMoneyFormat);
document.addEventListener('livewire:morph', filamentCleaveInit);
