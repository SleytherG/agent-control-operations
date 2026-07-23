document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.data-table thead th[scope="col"][data-sort]').forEach(function(th) {
        th.addEventListener('click', function() {
            var table = th.closest('table');
            var tbody = table.querySelector('tbody');
            var index = parseInt(th.getAttribute('data-sort'));
            var rows = Array.from(tbody.querySelectorAll('tr'));
            var isAsc = th.classList.contains('sort-asc');

            // Clear indicators
            table.querySelectorAll('.sort-indicator').forEach(function(i) { i.className = 'sort-indicator'; });
            th.querySelector('.sort-indicator').className = 'sort-indicator' + (isAsc ? ' sort-indicator--desc' : ' sort-indicator--asc');
            th.classList.toggle('sort-asc');

            rows.sort(function(a, b) {
                var aVal = a.children[index].textContent.trim();
                var bVal = b.children[index].textContent.trim();
                var aNum = parseFloat(aVal.replace(/[^0-9.-]/g, ''));
                var bNum = parseFloat(bVal.replace(/[^0-9.-]/g, ''));
                if (!isNaN(aNum) && !isNaN(bNum)) return isAsc ? bNum - aNum : aNum - bNum;
                return isAsc ? bVal.localeCompare(aVal) : aVal.localeCompare(bVal);
            });

            rows.forEach(function(row) { tbody.appendChild(row); });
        });
    });
});
