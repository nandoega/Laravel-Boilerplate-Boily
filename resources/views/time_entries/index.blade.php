@extends('layouts.app')

@section('title', 'Time Entries')

@section('content')
<div class="glass-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold">Timesheet Logger</h5>
        <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="bi bi-plus-lg"></i> Log Time</button>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Task</th>
                    <th>User</th>
                    <th>Hours</th>
                    <th>Billable</th>
                    <th>Rate</th>
                    <th>Total Calc</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="8" class="text-center text-muted">Loading timesheets...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="dataModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Log Time Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="dataForm">
                    <input type="hidden" id="recordId">
                    <div class="mb-3">
                        <label class="form-label">Task Reference</label>
                        <select id="task_id" class="form-control" required></select>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" id="date" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Hours</label>
                            <input type="number" id="hours" class="form-control" step="0.5" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Work Description</label>
                        <textarea id="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-6 mb-3">
                            <div class="form-check pt-4">
                                <input type="checkbox" class="form-check-input" id="is_billable" checked>
                                <label class="form-check-label">Is Billable</label>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Hourly Rate</label>
                            <input type="number" id="hourly_rate" class="form-control" step="0.01">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveData()">Save Log</button>
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
        
        // Load tasks for dropdown
        try {
            const tkRes = await api.get('/tasks');
            let opts = '<option value="">Select Task...</option>';
            tkRes.data.forEach(t => opts += `<option value="${t.id}">${t.title}</option>`);
            document.getElementById('task_id').innerHTML = opts;
        } catch(e) {}
        
        // Default date today
        document.getElementById('date').valueAsDate = new Date();
        loadData();
    });

    async function loadData() {
        try {
            const res = await api.get('/time-entries');
            let html = '';
            if(res.data.length === 0) {
                html = '<tr><td colspan="8" class="text-center text-muted">No time logs recorded.</td></tr>';
            } else {
                res.data.forEach(item => {
                    const billable = item.is_billable ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-dash-circle text-muted"></i>';
                    
                    html += `<tr>
                        <td>${item.date || '-'}</td>
                        <td class="fw-semibold">${item.task ? item.task.title : 'Deleted Task'}</td>
                        <td>${item.user ? item.user.name : '-'}</td>
                        <td class="fw-bold text-primary">${item.hours}h</td>
                        <td>${billable}</td>
                        <td>$${(item.hourly_rate || 0).toLocaleString()}</td>
                        <td class="fw-bold text-success">$${(item.billable_amount || 0).toLocaleString()}</td>
                        <td>
                            <button class="btn btn-sm btn-light text-primary border me-1" onclick='editData(${JSON.stringify(item).replace(/'/g, "&#39;")})'><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-light text-danger border" onclick="deleteData(${item.id})"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('tableBody').innerHTML = html;
        } catch (e) {
            showModalAlert('Failed to load logs: ' + e.message);
        }
    }

    function openModal() {
        document.getElementById('dataForm').reset();
        document.getElementById('date').valueAsDate = new Date();
        document.getElementById('recordId').value = '';
        document.getElementById('modalTitle').textContent = 'Log Time';
        dataModal.show();
    }

    function editData(item) {
        document.getElementById('recordId').value = item.id;
        document.getElementById('task_id').value = item.task_id || '';
        document.getElementById('date').value = item.date || '';
        document.getElementById('hours').value = item.hours || 0;
        document.getElementById('description').value = item.description || '';
        document.getElementById('is_billable').checked = item.is_billable;
        document.getElementById('hourly_rate').value = item.hourly_rate || '';

        document.getElementById('modalTitle').textContent = `Edit Time Log`;
        dataModal.show();
    }

    async function saveData() {
        const id = document.getElementById('recordId').value;
        const payload = {
            task_id: document.getElementById('task_id').value,
            user_id: 1, // Simulated Auth user
            date: document.getElementById('date').value,
            hours: parseFloat(document.getElementById('hours').value),
            description: document.getElementById('description').value,
            is_billable: document.getElementById('is_billable').checked,
            hourly_rate: parseFloat(document.getElementById('hourly_rate').value) || 0,
        };

        try {
            if(id) {
                await api.put(`/time-entries/${id}`, payload);
            } else {
                await api.post('/time-entries', payload);
            }
            dataModal.hide();
            loadData();
        } catch (e) {
            showModalAlert('Failed to save log: ' + e.message);
        }
    }

    async function deleteData(id) {
        if(!confirm('Are you sure you want to delete this log?')) return;
        try {
            await api.delete(`/time-entries/${id}`);
            loadData();
        } catch (e) {
            showModalAlert('Failed to delete: ' + e.message);
        }
    }
</script>
@endpush
