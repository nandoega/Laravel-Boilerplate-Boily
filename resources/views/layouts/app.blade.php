<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Boilerplate Admin') }}</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --sidebar-width: 250px;
        }
        body { background-color: #f8f9fa; overflow-x: hidden; }
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            background: #212529; color: white;
            padding-top: 20px;
            z-index: 1040;
            transition: transform 0.3s ease-in-out;
        }
        .sidebar a {
            color: #adb5bd; text-decoration: none;
            padding: 10px 20px; display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            color: white; background: #343a40;
            border-left: 4px solid #0d6efd;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex; flex-direction: column;
            transition: margin-left 0.3s ease-in-out;
        }
        .top-navbar {
            background: white; border-bottom: 1px solid #dee2e6;
            height: 60px; display: flex; align-items: center;
            padding: 0 20px; justify-content: space-between;
        }
        .content-body { padding: 20px; flex-grow: 1; }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .mobile-toggle {
            display: none;
            background: none; border: none; font-size: 1.5rem; color: #343a40; margin-right: 15px;
        }

        /* Mobile Responsive Handling */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 4px 0 10px rgba(0,0,0,0.2);
            }
            .main-content {
                margin-left: 0 !important;
            }
            .top-navbar {
                padding-left: 15px !important;
            }
            .mobile-toggle {
                display: block !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    @if(!request()->is('login') && !request()->is('/'))
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="text-center mb-4 px-3"><i class="bi bi-rocket-takeoff text-primary"></i> Boilerplate</h4>
        <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="/clients" class="{{ request()->is('clients*') ? 'active' : '' }}"><i class="bi bi-buildings me-2"></i> Clients</a>
        <a href="/projects" class="{{ request()->is('projects*') ? 'active' : '' }}"><i class="bi bi-briefcase me-2"></i> Projects</a>
        <a href="/tasks" class="{{ request()->is('tasks*') ? 'active' : '' }}"><i class="bi bi-list-check me-2"></i> Tasks</a>
        <a href="/teams" class="{{ request()->is('teams*') ? 'active' : '' }}"><i class="bi bi-people me-2"></i> Teams</a>
        <a href="/invoices" class="{{ request()->is('invoices*') ? 'active' : '' }}"><i class="bi bi-receipt me-2"></i> Invoices</a>
        <a href="/payments" class="{{ request()->is('payments*') ? 'active' : '' }}"><i class="bi bi-credit-card me-2"></i> Payments</a>
        <a href="/time-entries" class="{{ request()->is('time-entries*') ? 'active' : '' }}"><i class="bi bi-clock-history me-2"></i> Time Entries</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="mobile-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
                <h5 class="m-0 text-muted d-none d-sm-block">@yield('title', 'Admin Panel')</h5>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3 fw-bold" id="currentUser">Loading...</span>
                <button onclick="logout()" class="btn btn-sm btn-outline-danger"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </div>
        </div>
        
        <div class="content-body">
            @yield('content')
        </div>
    </div>
    @else
        <!-- Login Layout -->
        @yield('content')
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/api.js"></script>

    <!-- Global Alert Modal -->
    <div class="modal fade" id="globalAlertModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h6 class="modal-title fw-bold" id="globalAlertTitle">Notification</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4 bg-light">
                    <p id="globalAlertMessage" class="mb-0 text-dark fw-medium"></p>
                </div>
                <div class="modal-footer border-0 bg-light justify-content-center rounded-bottom">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill shadow-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Profile Modal -->
    <div class="modal fade" id="userProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white bg-primary border-0 rounded-top">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-lines-fill me-2"></i> User Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm border border-3 border-white" style="width: 110px; height: 110px; margin-top: -50px;">
                            <i class="bi bi-person text-secondary" style="font-size: 3.5rem;"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-1" id="profileModalName">Loading...</h4>
                    <p class="text-muted mb-2" id="profileModalEmail"></p>
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <span class="badge bg-success rounded-pill px-3 py-2 shadow-sm" id="profileModalRole"></span>
                    </div>

                    <div class="text-start bg-light p-3 rounded text-muted small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold"><i class="bi bi-telephone text-primary me-1"></i> Phone:</span>
                            <span id="profileModalPhone">N/A</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold"><i class="bi bi-calendar-check text-primary me-1"></i> Member Since:</span>
                            <span id="profileModalJoined">N/A</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light justify-content-center rounded-bottom">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill shadow-sm" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @if(!request()->is('login') && !request()->is('/'))
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }

        // Use cached profile instantly for SPA-like navigation without loading delays
        let cachedProfile = JSON.parse(localStorage.getItem('user_profile') || 'null');

        function renderProfileNav(data) {
            const userNameEl = document.getElementById('currentUser');
            userNameEl.innerHTML = `<a href="javascript:void(0)" onclick="openProfileModal()" class="text-decoration-none text-dark fw-bold"><i class="bi bi-person me-1"></i> ${data.name}</a>`;
        }
        
        // Optimistic render
        if(cachedProfile) renderProfileNav(cachedProfile);

        // Global function to show beautiful modal alert instead of window.alert
        window.showModalAlert = function(message, title = 'Notification') {
            document.getElementById('globalAlertTitle').innerText = title;
            document.getElementById('globalAlertMessage').innerText = message;
            const modal = new bootstrap.Modal(document.getElementById('globalAlertModal'));
            modal.show();
        };

        // Fetch fresh profile based on Token on every page load
        document.addEventListener('DOMContentLoaded', async () => {
            if(!api.getToken()) return;
            try {
                const res = await api.get('/auth/profile');
                if(res?.data) {
                    cachedProfile = res.data;
                    localStorage.setItem('user_profile', JSON.stringify(cachedProfile));
                    renderProfileNav(cachedProfile);
                }
            } catch (e) {
                console.error('Failed to get profile');
            }
        });

        function openProfileModal() {
            if(cachedProfile) {
                document.getElementById('profileModalName').innerText = cachedProfile.name;
                document.getElementById('profileModalEmail').innerText = cachedProfile.email;
                document.getElementById('profileModalRole').innerText = (cachedProfile.roles && cachedProfile.roles.length) ? cachedProfile.roles[0].toUpperCase() : 'USER';
                document.getElementById('profileModalPhone').innerText = cachedProfile.phone || '-';
                // formatted date
                const d = new Date(cachedProfile.created_at);
                document.getElementById('profileModalJoined').innerText = d.toLocaleDateString();
            }
            const modal = new bootstrap.Modal(document.getElementById('userProfileModal'));
            modal.show();
        }

        async function logout() {
            try {
                await api.post('/auth/logout');
            } catch (e) {} // ignore error, just clear local
            api.clearToken();
            localStorage.removeItem('user_profile');
            window.location.href = '/login';
        }
    </script>
    @endif

    @stack('scripts')
</body>
</html>
