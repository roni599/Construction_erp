@extends('layouts.app')

@section('title', 'Edit Project: ' . $project->project_name)

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2>Edit Project: {{ $project->project_name }}</h2>
        <a href="{{ route('admin.projects.show', $project->id) }}" class="btn btn-outline">&larr; Back to Details</a>
    </div>

    @if(session('success'))
        <div style="background: rgba(0, 230, 118, 0.2); color: var(--success); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="glass-panel animate-fade-in" style="margin-bottom: 32px;">
        <form method="POST" action="{{ route('admin.projects.update', $project->id) }}">
            @csrf
            @method('PUT')
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Project Name</label>
                    <input type="text" name="project_name" class="form-control" value="{{ $project->project_name }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Client Name</label>
                    <input type="text" name="client_name" class="form-control" value="{{ $project->client_name }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Client Phone</label>
                    <input type="text" name="client_phone" class="form-control" value="{{ $project->client_phone }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Client Email</label>
                    <input type="email" name="client_email" class="form-control" value="{{ $project->client_email }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="{{ $project->location }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Estimated Budget (Tk.)</label>
                    <input type="number" step="0.01" name="estimated_budget" class="form-control" value="{{ $project->estimated_budget }}" min="{{ $totalReceived }}">
                    @if($totalReceived > 0)
                        <small style="color: var(--accent-yellow);">Total Received: Tk. {{ number_format($totalReceived, 2) }} (Budget cannot be less than this)</small>
                    @endif
                    @error('estimated_budget')
                        <small style="color: var(--danger); display: block; margin-top: 4px;">{{ $message }}</small>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Assign Project Manager</label>
                    <select name="employee_id" class="form-control" style="background: rgba(0,0,0,0.8);">
                        <option value="">-- No Manager --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $project->employee_id == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $project->start_date ? $project->start_date->format('Y-m-d') : '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $project->end_date ? $project->end_date->format('Y-m-d') : '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="pending" {{ $project->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="running" {{ $project->status == 'running' ? 'selected' : '' }}>Running</option>
                        <option value="hold" {{ $project->status == 'hold' ? 'selected' : '' }}>Hold</option>
                        <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Description / Note</label>
                    <textarea name="description" class="form-control" rows="3">{{ $project->description }}</textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Project</button>
        </form>
    </div>
@endsection
