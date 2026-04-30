@extends('layouts.app')

@section('title', 'Employees')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <h2 style="margin: 0;">Project Manager Management</h2>
        <button class="btn btn-primary" onclick="document.getElementById('createEmployeeForm').style.display='block'" style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i> Add Project Manager
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

    @if($errors->any())
        <div style="background: rgba(255, 76, 76, 0.2); color: var(--danger); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="createEmployeeForm" class="glass-panel animate-fade-in" style="display: {{ $errors->any() ? 'block' : 'none' }}; margin-bottom: 32px; position: relative;">
        <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="document.getElementById('createEmployeeForm').style.display='none'" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
            <i class="fas fa-times"></i>
        </button>
        <div style="margin-bottom: 24px;">
            <h3>Create New Project Manager</h3>
        </div>
        
        <form method="POST" action="{{ route('admin.employees.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Name <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name') <span style="color: var(--danger); font-size: 12px;">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    @error('phone') <span style="color: var(--danger); font-size: 12px;">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                    @error('address') <span style="color: var(--danger); font-size: 12px;">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">NID (Frontend Picture)</label>
                    <input type="file" name="nid_frontend" class="form-control" accept="image/*">
                    @error('nid_frontend') <span style="color: var(--danger); font-size: 12px;">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">NID (Backend Picture)</label>
                    <input type="file" name="nid_backend" class="form-control" accept="image/*">
                    @error('nid_backend') <span style="color: var(--danger); font-size: 12px;">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Join Date</label>
                    <input type="date" name="join_date" class="form-control" value="{{ old('join_date', date('Y-m-d')) }}">
                    @error('join_date') <span style="color: var(--danger); font-size: 12px;">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Profile Picture (Optional)</label>
                    <input type="file" name="picture" class="form-control" accept="image/*">
                    @error('picture') <span style="color: var(--danger); font-size: 12px;">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span style="color: var(--danger);">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    @error('email') <span style="color: var(--danger); font-size: 12px;">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" name="create_user" value="1" {{ old('create_user') ? 'checked' : '' }} onchange="document.getElementById('passwordField').style.display = this.checked ? 'block' : 'none'">
                        Create Project Manager Login Account
                    </label>
                </div>
                <div class="form-group" id="passwordField" style="display: {{ old('create_user') ? 'block' : 'none' }};">
                    <label class="form-label">Login Password</label>
                    <input type="password" name="password" class="form-control">
                    @error('password') <span style="color: var(--danger); font-size: 12px;">{{ $message }}</span> @enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save Project Manager</button>
        </form>
    </div>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>System Access</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employee)
                    <tr>
                        <td>{{ $employee->id }}</td>
                        <td><strong>{{ $employee->name }}</strong></td>
                        <td>{{ $employee->email ?? '-' }}</td>
                        <td>{{ $employee->phone ?? '-' }}</td>
                        <td>{{ $employee->address ?? '-' }}</td>
                        <td>
                            @if($employee->user)
                                <span class="badge badge-completed">Project Manager</span>
                            @else
                                <span class="badge badge-pending">None</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="dropdown-toggle" type="button">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="{{ route('admin.employees.financials', $employee->id) }}" class="dropdown-item">
                                        <i class="fas fa-file-invoice-dollar" style="color: var(--success);"></i> Ledger
                                    </a>
                                    <a href="{{ route('admin.employees.edit', $employee->id) }}" class="dropdown-item">
                                        <i class="fas fa-user-edit" style="color: var(--accent-blue);"></i> Edit / Reset PW
                                    </a>
                                    <div style="border-top: 1px solid var(--border-color); margin: 4px 0;"></div>
                                    <form id="delete-form-{{ $employee->id }}" action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button" class="dropdown-item text-danger" 
                                        onclick="confirmDelete('delete-form-{{ $employee->id }}', 'This will permanently delete the project manager and their login access. Continue?')">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="custom-pagination" style="margin-top: 24px;">
        {{ $employees->appends(request()->query())->links() }}
    </div>
@endsection
