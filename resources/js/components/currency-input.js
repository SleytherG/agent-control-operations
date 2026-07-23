document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.currency-input').forEach(function(input) {
        input.addEventListener('blur', function() {
            var val = parseFloat(this.value.replace(/[^0-9.-]/g, ''));
            if (!isNaN(val)) {
                this.value = val.toFixed(2);
            }
        });

        input.addEventListener('input', function() {
            var cleaned = this.value.replace(/[^0-9.]/g, '');
            var parts = cleaned.split('.');
            if (parts.length > 2) cleaned = parts[0] + '.' + parts.slice(1).join('');
            this.value = cleaned;
        });
    });
});
