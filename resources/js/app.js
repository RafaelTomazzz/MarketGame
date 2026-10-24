import 'bootstrap/dist/js/bootstrap.bundle.min.js';

const applyDigitMask = (input, maxDigits, format) => {
    input.addEventListener('input', () => {
        const cursorPosition = input.selectionStart ?? input.value.length;
        const digitsBeforeCursor = input.value.slice(0, cursorPosition).replace(/\D/g, '').length;
        const digits = input.value.replace(/\D/g, '').slice(0, maxDigits);

        input.value = format(digits);

        let nextCursor = 0;
        let countedDigits = 0;

        while (nextCursor < input.value.length && countedDigits < digitsBeforeCursor) {
            if (/\d/.test(input.value[nextCursor])) {
                countedDigits++;
            }

            nextCursor++;
        }

        input.setSelectionRange(nextCursor, nextCursor);
    });
};

const cpfInput = document.querySelector('#cpf');
if (cpfInput instanceof HTMLInputElement) {
    applyDigitMask(cpfInput, 11, (digits) => {
        let value = digits.slice(0, 3);
        if (digits.length > 3) value += `.${digits.slice(3, 6)}`;
        if (digits.length > 6) value += `.${digits.slice(6, 9)}`;
        if (digits.length > 9) value += `-${digits.slice(9, 11)}`;

        return value;
    });
}

const phoneInput = document.querySelector('#telefone');
if (phoneInput instanceof HTMLInputElement) {
    applyDigitMask(phoneInput, 11, (digits) => {
        if (digits.length === 0) return '';

        const ddd = digits.slice(0, 2);
        const number = digits.slice(2);
        const firstPartLength = digits.length > 10 ? 5 : 4;

        let value = `(${ddd}`;
        if (digits.length > 2) value += `) ${number.slice(0, firstPartLength)}`;
        if (number.length > firstPartLength) value += `-${number.slice(firstPartLength)}`;

        return value;
    });
}

const cepInput = document.querySelector('#cep');
if (cepInput instanceof HTMLInputElement) {
    applyDigitMask(cepInput, 8, (digits) => {
        if (digits.length <= 5) return digits;

        return `${digits.slice(0, 5)}-${digits.slice(5)}`;
    });
}

const ufInput = document.querySelector('#uf');
if (ufInput instanceof HTMLInputElement) {
    ufInput.addEventListener('input', () => {
        ufInput.value = ufInput.value.replace(/[^a-z]/gi, '').toUpperCase().slice(0, 2);
    });
}
