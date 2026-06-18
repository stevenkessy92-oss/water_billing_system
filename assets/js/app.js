document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
            if (content) content.classList.toggle('active');
        });
    }

    // Auto-calculate bill preview on reading form
    const prevReading = document.getElementById('previous_reading');
    const currReading = document.getElementById('current_reading');
    const priceUnit = document.getElementById('price_per_unit');
    const consumptionDisplay = document.getElementById('consumption_display');
    const billDisplay = document.getElementById('bill_display');

    function updateBillPreview() {
        if (!prevReading || !currReading || !consumptionDisplay || !billDisplay) return;
        const prev = parseFloat(prevReading.value) || 0;
        const curr = parseFloat(currReading.value) || 0;
        const price = parseFloat(priceUnit?.value) || 0;
        const consumption = Math.max(0, curr - prev);
        const bill = consumption * price;
        consumptionDisplay.textContent = consumption.toFixed(2);
        billDisplay.textContent = bill.toLocaleString('sw-TZ', { minimumFractionDigits: 0 });
    }

    if (prevReading) prevReading.addEventListener('input', updateBillPreview);
    if (currReading) currReading.addEventListener('input', updateBillPreview);
    if (priceUnit) priceUnit.addEventListener('input', updateBillPreview);

    // Confirm delete
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
});
