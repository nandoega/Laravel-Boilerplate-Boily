@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row g-4" id="statsContainer">
    <!-- Stats Cards Will Render Here -->
</div>

<div class="row mt-4">
    <div class="col-md-6 mb-4">
        <div class="glass-card p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow text-success"></i> Cash Flow (Revenue vs Expenses)</h5>
            <div id="cashFlowList" class="text-muted">Loading chart data...</div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="glass-card p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-bar-chart-steps text-info"></i> Project Profitability</h5>
            <ul id="profitList" class="list-group list-group-flush">
                <li class="list-group-item text-muted border-0 px-0">Loading projects...</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function loadDashboard() {
        try {
            // Fetch Dashboard Summary
            const dashRes = await api.get('/reports/dashboard');
            const data = dashRes.data;

            const cards = `
                <div class="col-md-3">
                    <div class="glass-card p-4 border-primary border-start border-4 border-top-0 border-end-0 border-bottom-0">
                        <p class="text-muted mb-1 fw-semibold text-uppercase" style="font-size:12px;">Active Projects</p>
                        <h2 class="fw-bold mb-0">${data.active_projects || 0}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card p-4 border-warning border-start border-4 border-top-0 border-end-0 border-bottom-0">
                        <p class="text-muted mb-1 fw-semibold text-uppercase" style="font-size:12px;">Pending Invoices</p>
                        <h2 class="fw-bold mb-0">${data.pending_invoices || 0}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card p-4 border-success border-start border-4 border-top-0 border-end-0 border-bottom-0">
                        <p class="text-muted mb-1 fw-semibold text-uppercase" style="font-size:12px;">Total Revenue</p>
                        <h2 class="fw-bold mb-0">$${(data.total_revenue || 0).toLocaleString()}</h2>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card p-4 border-info border-start border-4 border-top-0 border-end-0 border-bottom-0">
                        <p class="text-muted mb-1 fw-semibold text-uppercase" style="font-size:12px;">Active Members</p>
                        <h2 class="fw-bold mb-0">${data.active_users || 0}</h2>
                    </div>
                </div>
            `;
            document.getElementById('statsContainer').innerHTML = cards;

            // CashFlow Data is extracted from dashboard stats
            document.getElementById('cashFlowList').innerHTML = `
                <div class="d-flex justify-content-between mb-2"><span>Total Invoiced</span><span class="fw-bold text-dark">$${(data.total_invoiced || 0).toLocaleString()}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Total Paid</span><span class="fw-bold text-success">$${(data.total_collected || 0).toLocaleString()}</span></div>
                <hr>
                <div class="d-flex justify-content-between"><span>Outstanding Balance</span><span class="fw-bold text-danger">$${((data.total_invoiced || 0) - (data.total_collected || 0)).toLocaleString()}</span></div>
            `;

            // Fetch Profitability
            const profRes = await api.get('/reports/project-profitability');
            let plHtml = '';
            profRes.data.forEach(p => {
                plHtml += `<li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 bg-transparent">
                    <div><strong>${p.project}</strong><br><small class="text-muted">${p.client}</small></div>
                    <div class="text-end">
                        <span class="badge bg-${p.profit_margin > 30 ? 'success' : 'warning'} rounded-pill">${p.profit_margin}% Margin</span><br>
                        <small class="fw-bold">$${p.profit}</small>
                    </div>
                </li>`;
            });
            document.getElementById('profitList').innerHTML = plHtml || '<li class="list-group-item text-muted border-0 bg-transparent px-0">No data available</li>';

        } catch (error) {
            console.error(error);
            document.getElementById('statsContainer').innerHTML = `<div class="alert alert-danger w-100">Failed to load dashboard data.</div>`;
        }
    }

    document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
@endpush
