@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="glass-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold">Payments Ledger</h5>
        <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="bi bi-plus-lg"></i> Record Payment</button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Date Paid</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="7" class="text-center text-muted">Loading payments...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="dataModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="dataForm">
                    <input type="hidden" id="recordId">
                    <div class="mb-3">
                        <label class="form-label">Invoice</label>
                        <select id="invoice_id" class="form-control" required></select>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" id="amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Date Paid</label>
                            <input type="date" id="paid_at" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Method</label>
                            <input type="text" id="method" class="form-control" placeholder="e.g. Bank Transfer">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Reference</label>
                            <input type="text" id="reference" class="form-control" placeholder="TX-12345">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-warning me-auto" id="btnRefund" onclick="refundPayment()"><i class="bi bi-arrow-counterclockwise"></i> Refund</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveData()">Save Payment</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let dataModal;

    document.addEventListener('DOMContentLoaded', async () => {
        dataModal = new bootstrap.Modal(document.getElementById('dataModal'));
        
        // Load invoices for dropdown
        try {
            const invRes = await api.get('/invoices');
            let opts = '<option value="">Select Invoice...</option>';
            invRes.data.forEach(i => opts += `<option value="${i.id}">${i.invoice_number}</option>`);
            document.getElementById('invoice_id').innerHTML = opts;
        } catch(e) {}
        
        loadData();
    });

    async function loadData() {
        try {
            const res = await api.get('/payments');
            let html = '';
            if(res.data.length === 0) {
                html = '<tr><td colspan="7" class="text-center text-muted">No payments recorded.</td></tr>';
            } else {
                res.data.forEach(item => {
                    const status = item.is_refunded ? '<span class="badge bg-danger">Refunded</span>' : '<span class="badge bg-success">Settled</span>';
                    
                    html += `<tr>
                        <td class="fw-semibold text-primary">${item.invoice ? item.invoice.invoice_number : 'Unknown'}</td>
                        <td>${item.method || '-'}</td>
                        <td class="font-monospace small">${item.reference || '-'}</td>
                        <td class="fw-bold">$${(item.amount || 0).toLocaleString()}</td>
                        <td>${item.paid_at ? item.paid_at.split('T')[0] : '-'}</td>
                        <td>${status}</td>
                        <td>
                            <button class="btn btn-sm btn-light text-primary border" onclick='editData(${JSON.stringify(item).replace(/'/g, "&#39;")})'><i class="bi bi-pencil"></i></button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('tableBody').innerHTML = html;
        } catch (e) {
            showModalAlert('Failed to load payments: ' + e.message);
        }
    }

    function openModal() {
        document.getElementById('dataForm').reset();
        document.getElementById('recordId').value = '';
        document.getElementById('modalTitle').textContent = 'Record Payment';
        document.getElementById('btnRefund').classList.add('d-none');
        dataModal.show();
    }

    function editData(item) {
        document.getElementById('recordId').value = item.id;
        document.getElementById('invoice_id').value = item.invoice_id || '';
        document.getElementById('amount').value = item.amount || 0;
        document.getElementById('paid_at').value = item.paid_at ? item.paid_at.split('T')[0] : '';
        document.getElementById('method').value = item.method || '';
        document.getElementById('reference').value = item.reference || '';

        document.getElementById('btnRefund').classList.toggle('d-none', item.is_refunded);
        document.getElementById('modalTitle').textContent = `Edit Payment`;
        dataModal.show();
    }

    async function refundPayment() {
        const id = document.getElementById('recordId').value;
        const reason = prompt('Please enter refund reason:');
        if(!reason) return;
        
        try {
            await api.post(`/payments/${id}/refund`, { reason });
            dataModal.hide();
            loadData();
        } catch (e) {
            showModalAlert('Failed: ' + e.message);
        }
    }

    async function saveData() {
        const id = document.getElementById('recordId').value;
        const payload = {
            invoice_id: document.getElementById('invoice_id').value,
            amount: parseFloat(document.getElementById('amount').value),
            paid_at: document.getElementById('paid_at').value || null,
            method: document.getElementById('method').value,
            reference: document.getElementById('reference').value,
        };

        try {
            if(id) {
                await api.put(`/payments/${id}`, payload);
            } else {
                await api.post('/payments', payload);
            }
            dataModal.hide();
            loadData();
        } catch (e) {
            showModalAlert('Failed to save payment: ' + e.message);
        }
    }
</script>
@endpush
