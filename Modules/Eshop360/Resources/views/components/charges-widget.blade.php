<div id="charges-realtime-widget" class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h5 class="card-title mb-0">{{ __('Charges en temps reel') }}</h5>
            <span class="badge bg-danger-transparent text-danger" id="charges-pulse">
                <i class="ti ti-activity me-1"></i>Live
            </span>
        </div>

        {{-- Main counter --}}
        <div class="text-center py-3">
            <div id="charges-counter" class="display-6 fw-bold text-danger">0 {{ $eshopCurrency ?? 'FCFA' }}</div>
            <small class="text-muted">{{ __('Accumule ce mois') }}</small>
        </div>

        {{-- Per-second rate --}}
        <div class="text-center mb-3">
            <span class="badge bg-secondary" id="charges-rate">-- {{ $eshopCurrency ?? 'FCFA' }}/s</span>
        </div>

        {{-- Breakdown by category --}}
        <div id="charges-breakdown" class="mt-3">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Categorie') }}</th>
                        <th class="text-end">{{ __('Mensuel') }}</th>
                        <th class="text-end">{{ __('Accumule') }}</th>
                    </tr>
                </thead>
                <tbody id="charges-breakdown-body">
                    <tr>
                        <td colspan="3" class="text-center text-muted">{{ __('Chargement...') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function() {
    let chargesData = null;
    const counterEl = document.getElementById('charges-counter');
    const rateEl = document.getElementById('charges-rate');
    const breakdownBody = document.getElementById('charges-breakdown-body');
    const fmt = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XAF', minimumFractionDigits: 0 });
    const fmtDecimal = new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 4, maximumFractionDigits: 4 });

    function updateChargesDisplay() {
        if (!chargesData) return;

        // Increment accumulated by cost_per_second
        chargesData.accumulated_since_month_start += chargesData.cost_per_second;
        counterEl.textContent = fmt.format(chargesData.accumulated_since_month_start);

        // Also increment each category breakdown
        if (chargesData.breakdown) {
            Object.keys(chargesData.breakdown).forEach(function(cat) {
                chargesData.breakdown[cat].accumulated += chargesData.breakdown[cat].cost_per_second;
            });
            renderBreakdown();
        }
    }

    function renderBreakdown() {
        if (!chargesData || !chargesData.breakdown) return;

        // Clear existing rows safely
        while (breakdownBody.firstChild) {
            breakdownBody.removeChild(breakdownBody.firstChild);
        }

        var categories = Object.keys(chargesData.breakdown);
        if (categories.length === 0) {
            var emptyRow = document.createElement('tr');
            var emptyCell = document.createElement('td');
            emptyCell.setAttribute('colspan', '3');
            emptyCell.className = 'text-center text-muted';
            emptyCell.textContent = 'Aucune charge active';
            emptyRow.appendChild(emptyCell);
            breakdownBody.appendChild(emptyRow);
            return;
        }

        categories.forEach(function(cat) {
            var item = chargesData.breakdown[cat];
            var row = document.createElement('tr');

            var catCell = document.createElement('td');
            catCell.textContent = cat.charAt(0).toUpperCase() + cat.slice(1);
            row.appendChild(catCell);

            var monthlyCell = document.createElement('td');
            monthlyCell.className = 'text-end';
            monthlyCell.textContent = fmt.format(item.monthly_total);
            row.appendChild(monthlyCell);

            var accCell = document.createElement('td');
            accCell.className = 'text-end text-danger';
            accCell.textContent = fmt.format(item.accumulated);
            row.appendChild(accCell);

            breakdownBody.appendChild(row);
        });
    }

    function fetchChargesData() {
        fetch('__BLADE_BLOCK_11__')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                chargesData = data;
                rateEl.textContent = fmtDecimal.format(data.cost_per_second) + ' {{ $eshopCurrency ?? "FCFA" }}/s';
                renderBreakdown();
            })
            .catch(function(err) {
                console.warn('Failed to fetch charges data', err);
            });
    }

    // Initial fetch
    fetchChargesData();

    // Update counter every second
    setInterval(updateChargesDisplay, 1000);

    // Refresh data from server every 60 seconds
    setInterval(fetchChargesData, 60000);
})();
</script>
