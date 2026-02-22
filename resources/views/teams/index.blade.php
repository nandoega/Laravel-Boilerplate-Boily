@extends('layouts.app')

@section('title', 'Teams')

@section('content')
<div class="glass-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold">Team Management</h5>
        <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="bi bi-plus-lg"></i> New Team</button>
    </div>

    <div class="row" id="teamsContainer">
        <div class="col-12 text-center text-muted py-5">Loading teams...</div>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="dataModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Team Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="dataForm">
                    <input type="hidden" id="recordId">
                    <div class="mb-3">
                        <label class="form-label">Team Name</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea id="description" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveData()">Save Team</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let dataModal;

    document.addEventListener('DOMContentLoaded', () => {
        dataModal = new bootstrap.Modal(document.getElementById('dataModal'));
        loadData();
    });

    async function loadData() {
        try {
            const res = await api.get('/teams');
            let html = '';
            if(res.data.length === 0) {
                html = '<div class="col-12 text-center text-muted">No teams found.</div>';
            } else {
                res.data.forEach(item => {
                    const count = item.members ? item.members.length : 0;
                    html += `
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-sm h-100 border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold">${item.name}</h5>
                                    <p class="card-text text-muted small">${item.description || 'No description provided.'}</p>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="badge bg-secondary rounded-pill px-3 py-2"><i class="bi bi-people me-1"></i> ${count} Members</div>
                                        <small class="ms-2 text-muted">Owner: ${item.owner ? item.owner.name : 'System'}</small>
                                    </div>
                                    <div class="d-flex gap-2 mt-auto">
                                        <button class="btn btn-sm btn-outline-primary w-100" onclick='editData(${JSON.stringify(item).replace(/'/g, "&#39;")})'>Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteData(${item.id})"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
            document.getElementById('teamsContainer').innerHTML = html;
        } catch (e) {
            showModalAlert('Failed to load teams: ' + e.message);
        }
    }

    function openModal() {
        document.getElementById('dataForm').reset();
        document.getElementById('recordId').value = '';
        document.getElementById('modalTitle').textContent = 'New Team';
        dataModal.show();
    }

    function editData(item) {
        document.getElementById('recordId').value = item.id;
        document.getElementById('name').value = item.name;
        document.getElementById('description').value = item.description || '';
        document.getElementById('modalTitle').textContent = 'Edit Team';
        dataModal.show();
    }

    async function saveData() {
        const id = document.getElementById('recordId').value;
        const payload = {
            name: document.getElementById('name').value,
            description: document.getElementById('description').value,
            owner_id: 1 // Simulated auth user id for now
        };

        try {
            if(id) {
                await api.put(`/teams/${id}`, payload);
            } else {
                await api.post('/teams', payload);
            }
            dataModal.hide();
            loadData();
        } catch (e) {
            showModalAlert('Failed to save team: ' + e.message);
        }
    }

    async function deleteData(id) {
        if(!confirm('Are you sure you want to delete this team?')) return;
        try {
            await api.delete(`/teams/${id}`);
            loadData();
        } catch (e) {
            showModalAlert('Failed to delete: ' + e.message);
        }
    }
</script>
@endpush
