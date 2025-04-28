function updateTransferPreview(accounts, currencies, currencyRates) {
    const fromSelect = document.querySelector('select[name="fromAccountID"]');
    const amountInput = document.querySelector('input[name="amount"]');
    const previewDiv = document.getElementById('transfer-preview');

    const amount = parseFloat(amountInput.value);
    const fromCurrency = fromSelect.options[fromSelect.selectedIndex]?.dataset?.currency;

    if (!amount || !fromCurrency) {
        previewDiv.innerText = "";
        return;
    }

    previewDiv.innerText = `You will send ${currencies[fromCurrency]}${amount.toFixed(2)}. Final amount received may vary after conversion.`;
}

function setupTransferPreview(accounts, currencies, currencyRates) {
    document.querySelector('select[name="fromAccountID"]').addEventListener('change', () => updateTransferPreview(accounts, currencies, currencyRates));
    document.querySelector('input[name="toAccountID"]').addEventListener('input', () => updateTransferPreview(accounts, currencies, currencyRates));
    document.querySelector('input[name="amount"]').addEventListener('input', () => updateTransferPreview(accounts, currencies, currencyRates));
}