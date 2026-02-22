@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="glass-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold">All Projects</h5>
        <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="bi bi-plus-lg"></i> New Project</button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Project Name</th>
                    <th>Client</th>
                    <th>Status</th>
                    <th>Budget</th>
                    <th>Timeline</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="6" class="text-center text-muted">Loading projects...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="dataModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Project Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="dataForm">
                    <input type="hidden" id="recordId">
                    <div class="mb-3">
                        <label class="form-label">Project Name</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client</label>
                        <select id="client_id" class="form-select" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="status" class="form-select">
                            <option value="planning">Planning</option>
                            <option value="active">Active</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" id="start_date" class="form-control">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" id="end_date" class="form-control">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveData()">Save Project</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let dataModal;
    let authUserIds = 1; // Simplification, should come from profile context

    document.addEventListener('DOMContentLoaded', async () => {
        dataModal = new bootstrap.Modal(document.getElementById('dataModal'));
        
        // Load clients for dropdown
        try {
            const clRes = await api.get('/clients');
            let opts = '<option value="">Select Client...</option>';
            clRes.data.forEach(c => opts += `<option value="${c.id}">${c.name}</option>`);
            document.getElementById('client_id').innerHTML = opts;
        } catch(e) {}
        
        loadData();
    });

    async function loadData() {
        try {
            const res = await api.get('/projects');
            let html = '';
            if(res.data.length === 0) {
                html = '<tr><td colspan="6" class="text-center text-muted">No projects found.</td></tr>';
            } else {
                res.data.forEach(item => {
                    const statusColors = { 'planning': 'secondary', 'active': 'primary', 'on_hold': 'warning', 'completed': 'success' };
                    const badge = `<span class="badge bg-${statusColors[item.status]}">${item.status.replace('_', ' ').toUpperCase()}</span>`;
                    
                    html += `<tr>
                        <td class="fw-semibold">${item.name}</td>
                        <td>${item.client ? item.client.name : '-'}</td>
                        <td>${badge}</td>
                        <td>$${(item.budget || 0).toLocaleString()}</td>
                        <td><small class="text-muted">${item.start_date || '?'} to ${item.end_date || '?'}</small></td>
                        <td>
                            <button class="btn btn-sm btn-light text-primary border me-1" onclick='editData(${JSON.stringify(item).replace(/'/g, "&#39;")})'><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light text-danger border" onclick="deleteData(${item.id})"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('tableBody').innerHTML = html;
        } catch (e) {
            showModalAlert('Failed to load projects: ' + e.message);
        }
    }

    function openModal() {
        document.getElementById('dataForm').reset();
        document.getElementById('recordId').value = '';
        document.getElementById('modalTitle').textContent = 'New Project';
        dataModal.show();
    }

    function editData(item) {
        document.getElementById('recordId').value = item.id;
        document.getElementById('name').value = item.name;
        document.getElementById('client_id').value = item.client ? item.client.id : '';
        document.getElementById('status').value = item.status;
        document.getElementById('start_date').value = item.start_date || '';
        document.getElementById('end_date').value = item.end_date || '';
        document.getElementById('modalTitle').textContent = 'Edit Project';
        dataModal.show();
    }

    async function saveData() {
        const id = document.getElementById('recordId').value;
        const payload = {
            name: document.getElementById('name').value,
            client_id: document.getElementById('client_id').value,
            status: document.getElementById('status').value,
            start_date: document.getElementById('start_date').value,
            end_date: document.getElementById('end_date').value,
            owner_id: 1 // fallback dummy owner
        };

        try {
            if(id) {
                await api.put(`/projects/${id}`, payload);
            } else {
                await api.post('/projects', payload);
            }
            dataModal.hide();
            loadData();
        } catch (e) {
            showModalAlert('Failed to save project: ' + e.message);
        }
    }

    async function deleteData(id) {
        if(!confirm('Are you sure you want to delete this project?')) return;
        try {
            await api.delete(`/projects/${id}`);
            loadData();
        } catch (e) {
            showModalAlert('Failed to delete: ' + e.message);
        }
    }
</script>
@endpush
