// Inputmask money format for Filament v4
// Format input as 1.000.000 saat user mengetik

function attachInputMaskMoney() {
    const selectors = [
        'input[name="amount"]',
        'input[name="initial_balance"]',
        'input[name="project_value"]',
        'input[id$=".amount"]',
        'input[id$=".initial_balance"]',
        'input[id$=".project_value"]',
    ];
    selectors.forEach(function(selector) {
        document.querySelectorAll(selector).forEach(function(input) {
            if (input.getAttribute('data-inputmask')) return;
            Inputmask({
                alias: 'numeric',
                groupSeparator: '.',
                autoGroup: true,
                digits: 0,
                digitsOptional: false,
                prefix: '',
                placeholder: '',
                rightAlign: false,
                removeMaskOnSubmit: true,
                allowMinus: false,
                allowPlus: false,
            }).mask(input);
            input.setAttribute('data-inputmask', '1');
        });
    });
}

document.addEventListener('DOMContentLoaded', attachInputMaskMoney);
document.addEventListener('livewire:navigated', attachInputMaskMoney);
document.addEventListener('livewire:morph', attachInputMaskMoney);
