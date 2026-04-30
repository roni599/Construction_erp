@extends('layouts.app')

@section('title', 'Edit Project Manager: ' . $employee->name)

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2>Edit Project Manager: {{ $employee->name }}</h2>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline">&larr; Back to Project Managers</a>
    </div>

    @if(session('success'))
        <div style="background: rgba(0, 230, 118, 0.2); color: var(--success); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="dashboard-grid">
        <div class="glass-panel" style="grid-column: span 1;">
            <h3>Update Details</h3>
            <form method="POST" action="{{ route('admin.employees.update', $employee->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                @if($employee->picture)
                    <div style="margin-bottom: 24px; text-align: center;">
                        <img src="{{ $employee->picture }}" alt="Profile Picture" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent-yellow);">
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $employee->name }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $employee->email }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ $employee->phone }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ $employee->address }}">
                </div>
                <div class="form-group" style="display: flex; gap: 16px;">
                    <div style="flex: 1;">
                        <label class="form-label">NID (Frontend Picture)</label>
                        <input type="file" name="nid_frontend" class="form-control" accept="image/*">
                        @if($employee->nid_frontend)
                            <a href="{{ $employee->nid_frontend }}" target="_blank" style="display: block; margin-top: 8px; font-size: 14px; color: var(--accent-blue);">View Current NID Frontend</a>
                        @endif
                    </div>
                    <div style="flex: 1;">
                        <label class="form-label">NID (Backend Picture)</label>
                        <input type="file" name="nid_backend" class="form-control" accept="image/*">
                        @if($employee->nid_backend)
                            <a href="{{ $employee->nid_backend }}" target="_blank" style="display: block; margin-top: 8px; font-size: 14px; color: var(--accent-blue);">View Current NID Backend</a>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Join Date</label>
                    <input type="date" name="join_date" class="form-control" value="{{ $employee->join_date ? $employee->join_date->format('Y-m-d') : '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Profile Picture (Leave blank to keep current)</label>
                    <input type="file" name="picture" class="form-control" accept="image/*">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="1" {{ $employee->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$employee->is_active ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Update Project Manager</button>
            </form>
        </div>

        <div class="glass-panel" style="grid-column: span 1;">
            <h3>Password Reset</h3>
            @if($employee->user)
                <p style="color: var(--text-secondary); margin-bottom: 16px;">This project manager has a login account. You can reset their password here.</p>
                <form method="POST" action="{{ route('admin.employees.update', $employee->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter new password" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-danger" style="width: 100%;">Reset Password</button>
                </form>
            @else
                <p style="color: var(--text-secondary); margin-bottom: 16px;">This project manager does not have a system login account. If you want to grant them access, you can create an account for them.</p>
                <form method="POST" action="{{ route('admin.employees.update', $employee->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="create_user" value="1">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter new password" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Create PM Login Account</button>
                </form>
            @endif
        </div>
    </div>
@endsection
