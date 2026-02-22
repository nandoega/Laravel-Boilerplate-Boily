@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="glass-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold">All Tasks</h5>
        <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="bi bi-plus-lg"></i> New Task</button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Task Title</th>
                    <th>Project</th>
                    <th>Assignee</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Due Date</th>
                    <th>Hrs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="8" class="text-center text-muted">Loading tasks...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="dataModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Task Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="dataForm">
                    <input type="hidden" id="recordId">
                    <div class="mb-3">
                        <label class="form-label">Task Title</label>
                        <input type="text" id="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Project</label>
                        <select id="project_id" class="form-select" required></select>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Status</label>
                            <select id="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Priority</label>
                            <select id="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" id="due_date" class="form-control">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Est. Hours</label>
                            <input type="number" id="estimated_hours" class="form-control" step="0.5">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveData()">Save Task</button>
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
        
        // Load projects for dropdown
        try {
            const prRes = await api.get('/projects');
            let opts = '<option value="">Select Project...</option>';
            prRes.data.forEach(p => opts += `<option value="${p.id}">${p.name}</option>`);
            document.getElementById('project_id').innerHTML = opts;
        } catch(e) {}
        
        loadData();
    });

    async function loadData() {
        try {
            const res = await api.get('/tasks');
            let html = '';
            if(res.data.length === 0) {
                html = '<tr><td colspan="8" class="text-center text-muted">No tasks found.</td></tr>';
            } else {
                res.data.forEach(item => {
                    const statusColors = { 'pending': 'secondary', 'in_progress': 'primary', 'completed': 'success', 'cancelled': 'danger' };
                    const priorityColors = { 'low': 'secondary', 'medium': 'info', 'high': 'warning', 'urgent': 'danger' };
                    
                    const statBadge = `<span class="badge bg-${statusColors[item.status]}">${item.status.replace('_', ' ').toUpperCase()}</span>`;
                    const prioBadge = `<span class="badge bg-${priorityColors[item.priority]}">${item.priority.toUpperCase()}</span>`;
                    
                    html += `<tr>
                        <td class="fw-semibold">${item.title}</td>
                        <td>${item.project ? item.project.name : '-'}</td>
                        <td>${item.assignee ? item.assignee.name : '<span class="text-muted fst-italic">Unassigned</span>'}</td>
                        <td class="dropdown">
                            <button class="btn btn-sm p-0 m-0 border-0" data-bs-toggle="dropdown" aria-expanded="false">${statBadge}</button>
                            <ul class="dropdown-menu shadow-sm">
                                <li><a class="dropdown-item py-1" href="#" onclick="updateStatus(${item.id}, 'pending')">Pending</a></li>
                                <li><a class="dropdown-item py-1" href="#" onclick="updateStatus(${item.id}, 'in_progress')">In Progress</a></li>
                                <li><a class="dropdown-item py-1" href="#" onclick="updateStatus(${item.id}, 'completed')">Completed</a></li>
                            </ul>
                        </td>
                        <td>${prioBadge}</td>
                        <td>${item.due_date || '-'}</td>
                        <td>${item.estimated_hours || 0}h</td>
                        <td>
                            <button class="btn btn-sm btn-light text-primary border me-1" onclick='editData(${JSON.stringify(item).replace(/'/g, "&#39;")})'><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light text-danger border" onclick="deleteData(${item.id})"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('tableBody').innerHTML = html;
        } catch (e) {
            showModalAlert('Failed to load tasks: ' + e.message);
        }
    }

    async function updateStatus(id, newStatus) {
        try {
            await api.patch(`/tasks/${id}/status`, { status: newStatus });
            loadData();
        } catch (e) {
            showModalAlert('Failed to update status');
        }
    }

    function openModal() {
        document.getElementById('dataForm').reset();
        document.getElementById('recordId').value = '';
        document.getElementById('modalTitle').textContent = 'New Task';
        dataModal.show();
    }

    function editData(item) {
        document.getElementById('recordId').value = item.id;
        document.getElementById('title').value = item.title;
        document.getElementById('project_id').value = item.project_id;
        document.getElementById('status').value = item.status || 'pending';
        document.getElementById('priority').value = item.priority || 'medium';
        document.getElementById('due_date').value = item.due_date || '';
        document.getElementById('estimated_hours').value = item.estimated_hours || '';
        document.getElementById('modalTitle').textContent = 'Edit Task';
        dataModal.show();
    }

    async function saveData() {
        const id = document.getElementById('recordId').value;
        const payload = {
            title: document.getElementById('title').value,
            project_id: document.getElementById('project_id').value,
            status: document.getElementById('status').value,
            priority: document.getElementById('priority').value,
            due_date: document.getElementById('due_date').value,
            estimated_hours: document.getElementById('estimated_hours').value,
        };

        try {
            if(id) {
                await api.put(`/tasks/${id}`, payload);
            } else {
                await api.post('/tasks', payload);
            }
            dataModal.hide();
            loadData();
        } catch (e) {
            showModalAlert('Failed to save task: ' + e.message);
        }
    }

    async function deleteData(id) {
        if(!confirm('Are you sure you want to delete this task?')) return;
        try {
            await api.delete(`/tasks/${id}`);
            loadData();
        } catch (e) {
            showModalAlert('Failed to delete: ' + e.message);
        }
    }
</script>
@endpush
