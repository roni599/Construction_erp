@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <h2 style="margin: 0;">Create New Project</h2>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Projects
        </a>
    </div>

    <div class="glass-panel animate-slide-down" style="padding: 32px;">
        <form method="POST" action="{{ route('admin.projects.store') }}">
            @csrf
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Project Name</label>
                    <input type="text" name="project_name" class="form-control" required value="{{ old('project_name') }}" placeholder="Enter project name">
                </div>
                <div class="form-group">
                    <label class="form-label">Client Name</label>
                    <input type="text" name="client_name" class="form-control" required value="{{ old('client_name') }}" placeholder="Enter client name">
                </div>
                <div class="form-group">
                    <label class="form-label">Client Phone</label>
                    <input type="text" name="client_phone" class="form-control" value="{{ old('client_phone') }}" placeholder="Enter client phone">
                </div>
                <div class="form-group">
                    <label class="form-label">Client Email</label>
                    <input type="email" name="client_email" class="form-control" value="{{ old('client_email') }}" placeholder="Enter client email">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="Enter project location">
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
            <div style="margin-top: 32px; display: flex; gap: 16px;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px;">
                    <i class="fas fa-save"></i> Save Project
                </button>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-outline" style="flex: 1; padding: 12px; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
