@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <h2 style="margin: 0;">Projects Management</h2>
        <button class="btn btn-primary" onclick="toggleForm()" style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i> Create Project
        </button>
    </div>

    @if(session('success'))
        <div style="background: rgba(0, 230, 118, 0.2); color: var(--success); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: rgba(255, 76, 76, 0.2); color: var(--danger); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            {{ session('error') }}
        </div>
    @endif

    <!-- Create Project Modal -->
    <div id="createProjectModal" class="sidebar-overlay" style="display: {{ $errors->any() ? 'flex' : 'none' }}; align-items: flex-start; justify-content: flex-start; z-index: 2000;">
        <div class="glass-panel animate-slide-down" style="width: 100%; max-width: 100%; margin: 0; padding: 32px; position: relative; border-radius: 0; border-bottom: 1px solid var(--border-color);">
            <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="toggleForm()" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="fas fa-times"></i>
            </button>
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0;">Create New Project</h3>
            </div>
            
            <form method="POST" action="{{ route('admin.projects.store') }}">
                @csrf
                <div class="dashboard-grid">
                    <div class="form-group">
                        <label class="form-label">Project Name</label>
                        <input type="text" name="project_name" class="form-control" required value="{{ old('project_name') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Client Name</label>
                        <input type="text" name="client_name" class="form-control" required value="{{ old('client_name') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Client Phone</label>
                        <input type="text" name="client_phone" class="form-control" value="{{ old('client_phone') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Client Email</label>
                        <input type="email" name="client_email" class="form-control" value="{{ old('client_email') }}">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location') }}">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Assign Project Manager</label>
                        <select name="employee_id" class="form-control" style="background: rgba(0,0,0,0.8);">
                            <option value="">-- Select Manager --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Initial Status</label>
                        <select name="status" class="form-control" required style="background: rgba(0,0,0,0.8);">
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="running" {{ old('status', 'running') == 'running' ? 'selected' : '' }}>Running</option>
                            <option value="hold" {{ old('status') == 'hold' ? 'selected' : '' }}>Hold</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px;">Save Project</button>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('admin.projects.index') }}" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
            <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                <label class="form-label">Project Name</label>
                <select name="project_id" class="form-control" style="background: rgba(0,0,0,0.8);">
                    <option value="">All Projects</option>
                    @foreach($allProjects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                <label class="form-label">Client Name</label>
                <input type="text" name="client_name" class="form-control" placeholder="Search client name..." value="{{ request('client_name') }}">
            </div>
            <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                <label class="form-label">Client Phone</label>
                <input type="text" name="client_phone" class="form-control" placeholder="Search phone..." value="{{ request('client_phone') }}">
            </div>
            <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                <label class="form-label">Status</label>
                <select name="status" class="form-control" style="background: rgba(0,0,0,0.8);">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>Running</option>
                    <option value="hold" {{ request('status') == 'hold' ? 'selected' : '' }}>Hold</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-search"></i> Search</button>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-outline" style="white-space: nowrap;">Clear</a>
            </div>
        </form>
    </div>

    <!-- Projects Table -->
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Client Name</th>
                    <th>Client Phone</th>
                    <th>Client Email</th>
                    <th>Budget</th>
                    <th>Manager</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    <tr>
                        <td>
                            <a href="{{ route('admin.projects.show', $project->id) }}" style="color: inherit; text-decoration: none; display: block;">
                                <strong>{{ $project->project_name }}</strong>
                                @if($project->location)
                                    <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
                                        <i class="fas fa-map-marker-alt" style="font-size: 10px;"></i> {{ $project->location }}
                                    </div>
                                @endif
                            </a>
                        </td>
                        <td>{{ $project->client_name }}</td>
                        <td>{{ $project->client_phone ?? '-' }}</td>
                        <td>{{ $project->client_email ?? '-' }}</td>
                        <td>Tk. {{ number_format($project->estimated_budget, 2) }}</td>
                        <td style="font-size: 14px;">
                            {{ $project->manager->name ?? 'Unassigned' }}
                            @if(isset($project->manager->phone))
                                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">{{ $project->manager->phone }}</div>
                            @endif
                        </td>
                        <td><span class="badge badge-{{ $project->status }}">{{ ucfirst($project->status) }}</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="dropdown-toggle" type="button">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.projects.show', $project->id) }}">
                                        <i class="fas fa-tasks"></i> Manage
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.projects.edit', $project->id) }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form id="delete-project-{{ $project->id }}" action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button class="dropdown-item text-danger" type="button" onclick="confirmDelete('delete-project-{{ $project->id }}', 'This project and its settings will be removed!')">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 24px;">
                            No projects found matching the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    <div class="custom-pagination" style="margin-top: 24px;">
        {{ $projects->appends(request()->query())->links() }}
    </div>

    <script>
        function toggleForm() {
            const modal = document.getElementById('createProjectModal');
            if (modal.style.display === 'none' || modal.style.display === '') {
                modal.style.display = 'flex';
                setTimeout(() => modal.classList.add('active'), 10);
            } else {
                modal.classList.remove('active');
                setTimeout(() => modal.style.display = 'none', 300);
            }
        }
    </script>
@endsection
