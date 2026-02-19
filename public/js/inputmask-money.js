// Inputmask money format for Filament v4
// Format input as 1.000.000 saat user mengetik

function attachInputMaskMoney() {
    // target inputs explicitly marked for money formatting
    document.querySelectorAll('input[data-money="1"]').forEach(function(input) {
        if (input.getAttribute('data-inputmask')) return;
        // if input rendered as number, switch to text so masking can work
        try {
            if (input.type === 'number') {
                input.type = 'text';
            }
        } catch (e) {
            // ignore
        }

        if (window.Inputmask) {
            Inputmask({
                alias: 'numeric',
                groupSeparator: '.',
                autoGroup: true,
                digits: 0,
                digitsOptional: false,
                prefix: '',
                placeholder: '',
                rightAlign: false,
                removeMaskOnSubmit: false,
                allowMinus: false,
                allowPlus: false,
            }).mask(input);
        } else {
            // fallback: simple formatting on input
            input.addEventListener('input', function () {
                let v = input.value.replace(/[^0-9]/g, '');
                if (v === '') { input.value = ''; return; }
                input.value = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            });
        }

        input.setAttribute('data-inputmask', '1');
    });
}

document.addEventListener('DOMContentLoaded', attachInputMaskMoney);
document.addEventListener('livewire:navigated', attachInputMaskMoney);
document.addEventListener('livewire:morph', attachInputMaskMoney);
