@extends('layouts.app')

@section('title', 'My Projects')

@section('content')
    <style>
        /* The definitive fix for scrollbar flickering: Force it to ALWAYS exist */
        html {
            overflow-y: scroll !important;
        }

        .app-container, .main-content {
            overflow: visible !important;
        }

        .table-wrapper {
            overflow-x: auto !important;
            padding-bottom: 40px;
        }

        /* Ensure dropdowns never trigger a height change */
        .dropdown-menu.show {
            display: flex !important;
            position: absolute !important;
            z-index: 9999 !important;
        }
        
        tr:last-child .dropdown-menu {
            top: auto !important;
            bottom: 100% !important;
            margin-bottom: 5px !important;
        }
    </style>

    <div class="flex-between" style="margin-bottom: 32px;">
        <h2>My Assigned Projects</h2>
    </div>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Client</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    <tr>
                        <td>
                            <a href="{{ route('manager.projects.show', $project->id) }}" style="color: inherit; text-decoration: none; display: block;">
                                <strong>{{ $project->project_name }}</strong>
                                @if($project->location)
                                    <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
                                        <i class="fas fa-map-marker-alt" style="font-size: 10px;"></i> {{ $project->location }}
                                    </div>
                                @endif
                            </a>
                        </td>
                        <td>{{ $project->client_name }}</td>
                        <td>{{ $project->location ?? '-' }}</td>
                        <td><span class="badge badge-{{ $project->status }}">{{ $project->status }}</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="dropdown-toggle" type="button">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('manager.projects.ledger', $project->id) }}">
                                        <i class="fas fa-file-invoice"></i> Ledger
                                    </a>
                                    @if($project->status === 'running')
                                    <a class="dropdown-item" href="{{ route('manager.expenses.create', ['project_id' => $project->id]) }}">
                                        <i class="fas fa-plus-circle"></i> Record Expense
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">No projects assigned to you.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="custom-pagination" style="margin-top: 24px;">
        {{ $projects->appends(request()->query())->links() }}
    </div>
@endsection
