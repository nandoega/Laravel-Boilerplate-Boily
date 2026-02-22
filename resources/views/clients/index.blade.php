@extends('layouts.app')

@section('title', 'Clients Management')

@section('content')
<div class="glass-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold">All Clients</h5>
        <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="bi bi-plus-lg"></i> Add Client</button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="clientTableBody">
                <tr><td colspan="5" class="text-center text-muted">Loading clients...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="clientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="clientForm">
                    <input type="hidden" id="clientId">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" id="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" id="company" class="form-control">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="isActive" checked>
                        <label class="form-check-label">Is Active</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveClient()">Save Client</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let clientModal;

    document.addEventListener('DOMContentLoaded', () => {
        clientModal = new bootstrap.Modal(document.getElementById('clientModal'));
        loadClients();
    });

    async function loadClients() {
        try {
            const res = await api.get('/clients');
            let html = '';
            if(res.data.length === 0) {
                html = '<tr><td colspan="5" class="text-center text-muted">No clients found.</td></tr>';
            } else {
                res.data.forEach(c => {
                    const badge = c.is_active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-secondary">Inactive</span>';
                        
                    html += `<tr>
                        <td class="fw-semibold">${c.name}</td>
                        <td>${c.company || '-'}</td>
                        <td>${c.email || '-'}</td>
                        <td>${badge}</td>
                        <td>
                            <button class="btn btn-sm btn-light text-primary border me-1" onclick='editClient(${JSON.stringify(c).replace(/'/g, "&#39;")})'><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light text-danger border" onclick="deleteClient(${c.id})"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('clientTableBody').innerHTML = html;
        } catch (e) {
            showModalAlert('Failed to load clients: ' + e.message);
        }
    }

    function openModal() {
        document.getElementById('clientForm').reset();
        document.getElementById('clientId').value = '';
        document.getElementById('modalTitle').textContent = 'Add Client';
        clientModal.show();
    }

    function editClient(client) {
        document.getElementById('clientId').value = client.id;
        document.getElementById('name').value = client.name;
        document.getElementById('email').value = client.email || '';
        document.getElementById('company').value = client.company || '';
        document.getElementById('isActive').checked = client.is_active;
        document.getElementById('modalTitle').textContent = 'Edit Client';
        clientModal.show();
    }

    async function saveClient() {
        const id = document.getElementById('clientId').value;
        const payload = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            company: document.getElementById('company').value,
            is_active: document.getElementById('isActive').checked
        };

        try {
            if(id) {
                await api.put(`/clients/${id}`, payload);
            } else {
                await api.post('/clients', payload);
            }
            clientModal.hide();
            loadClients();
        } catch (e) {
            showModalAlert('Failed to save client: ' + e.message);
        }
    }

    async function deleteClient(id) {
        if(!confirm('Are you sure you want to delete this client?')) return;
        try {
            await api.delete(`/clients/${id}`);
            loadClients();
        } catch (e) {
            showModalAlert('Failed to delete: ' + e.message);
        }
    }
</script>
@endpush
