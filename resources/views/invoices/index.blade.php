@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="glass-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold">All Invoices</h5>
        <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="bi bi-plus-lg"></i> New Invoice</button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Client / Project</th>
                    <th>Status</th>
                    <th>Total Ext</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="6" class="text-center text-muted">Loading invoices...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="dataModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Invoice Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="dataForm">
                    <input type="hidden" id="recordId">
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <select id="client_id" class="form-control" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Project (Optional)</label>
                        <select id="project_id" class="form-control"></select>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Status</label>
                            <select id="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="sent">Sent</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" id="due_date" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-4 mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" id="amount" class="form-control" step="0.01" oninput="calcTotal()" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Tax</label>
                            <input type="number" id="tax" class="form-control" step="0.01" value="0" oninput="calcTotal()">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label fw-bold">Total</label>
                            <input type="number" id="total" class="form-control bg-light" readonly>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-success me-auto" id="btnMarkPaid" onclick="markPaid()"><i class="bi bi-check-circle"></i> Mark Paid</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveData()">Save Invoice</button>
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
        
        // Load dependencies
        try {
            const [clRes, prRes] = await Promise.all([ api.get('/clients'), api.get('/projects') ]);
            
            let clOpts = '<option value="">Select Client...</option>';
            clRes.data.forEach(c => clOpts += `<option value="${c.id}">${c.name}</option>`);
            document.getElementById('client_id').innerHTML = clOpts;

            let prOpts = '<option value="">Select Project (Optional)...</option>';
            prRes.data.forEach(p => prOpts += `<option value="${p.id}">${p.name}</option>`);
            document.getElementById('project_id').innerHTML = prOpts;
        } catch(e) {}
        
        loadData();
    });

    function calcTotal() {
        const amt = parseFloat(document.getElementById('amount').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        document.getElementById('total').value = (amt + tax).toFixed(2);
    }

    async function loadData() {
        try {
            const res = await api.get('/invoices');
            let html = '';
            if(res.data.length === 0) {
                html = '<tr><td colspan="6" class="text-center text-muted">No invoices found.</td></tr>';
            } else {
                res.data.forEach(item => {
                    const statusColors = { 'draft': 'secondary', 'sent': 'primary', 'paid': 'success', 'overdue': 'danger', 'cancelled': 'dark' };
                    const badge = `<span class="badge bg-${statusColors[item.status]}">${item.status.toUpperCase()}</span>`;
                    
                    html += `<tr>
                        <td class="fw-bold text-primary">${item.invoice_number}</td>
                        <td>
                            <div>${item.client ? item.client.name : '-'}</div>
                            <small class="text-muted">${item.project ? item.project.name : 'No Project'}</small>
                        </td>
                        <td>${badge}</td>
                        <td class="fw-semibold">$${(item.total || 0).toLocaleString()}</td>
                        <td>${item.due_date || '-'}</td>
                        <td>
                            <button class="btn btn-sm btn-light text-primary border me-1" onclick='editData(${JSON.stringify(item).replace(/'/g, "&#39;")})'><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light text-danger border" onclick="deleteData(${item.id})"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('tableBody').innerHTML = html;
        } catch (e) {
            showModalAlert('Failed to load invoices: ' + e.message);
        }
    }

    function openModal() {
        document.getElementById('dataForm').reset();
        document.getElementById('recordId').value = '';
        document.getElementById('modalTitle').textContent = 'New Invoice';
        document.getElementById('btnMarkPaid').classList.add('d-none');
        dataModal.show();
    }

    function editData(item) {
        document.getElementById('recordId').value = item.id;
        document.getElementById('client_id').value = item.client_id || '';
        document.getElementById('project_id').value = item.project_id || '';
        document.getElementById('status').value = item.status || 'draft';
        document.getElementById('amount').value = item.amount || 0;
        document.getElementById('tax').value = item.tax || 0;
        document.getElementById('due_date').value = item.due_date || '';
        calcTotal();

        document.getElementById('btnMarkPaid').classList.toggle('d-none', item.status === 'paid');
        document.getElementById('modalTitle').textContent = `Edit Invoice ${item.invoice_number}`;
        dataModal.show();
    }

    async function markPaid() {
        const id = document.getElementById('recordId').value;
        if(!id) return;
        try {
            await api.post(`/invoices/${id}/mark-paid`);
            dataModal.hide();
            loadData();
        } catch (e) {
            showModalAlert('Failed: ' + e.message);
        }
    }

    async function saveData() {
        const id = document.getElementById('recordId').value;
        const payload = {
            client_id: document.getElementById('client_id').value,
            project_id: document.getElementById('project_id').value || null,
            status: document.getElementById('status').value,
            amount: parseFloat(document.getElementById('amount').value),
            tax: parseFloat(document.getElementById('tax').value),
            total: parseFloat(document.getElementById('total').value),
            due_date: document.getElementById('due_date').value,
        };

        try {
            if(id) {
                await api.put(`/invoices/${id}`, payload);
            } else {
                await api.post('/invoices', payload);
            }
            dataModal.hide();
            loadData();
        } catch (e) {
            showModalAlert('Failed to save invoice: ' + e.message);
        }
    }

    async function deleteData(id) {
        if(!confirm('Are you sure you want to delete this invoice?')) return;
        try {
            await api.delete(`/invoices/${id}`);
            loadData();
        } catch (e) {
            showModalAlert('Failed to delete: ' + e.message);
        }
    }
</script>
@endpush
