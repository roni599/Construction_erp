@extends('layouts.app')

@section('title', 'Expense Categories')

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2>Expense Categories</h2>
        <button class="btn btn-primary" onclick="document.getElementById('createCategoryForm').style.display='block'">+ Add Category</button>
    </div>

    <div id="createCategoryForm" class="glass-panel animate-fade-in" style="display: none; margin-bottom: 32px; position: relative;">
        <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="document.getElementById('createCategoryForm').style.display='none'" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
            <i class="fas fa-times"></i>
        </button>
        <div style="margin-bottom: 24px;">
            <h3>Add New Category</h3>
        </div>
        
        <form method="POST" action="{{ route('manager.categories.store') }}">
            @csrf
            <div class="dashboard-grid">
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g., Raw Materials, Labor, Transport">
                </div>
                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <input type="text" name="description" class="form-control" placeholder="Short description of this category">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save Category</button>
        </form>
    </div>

    <!-- Filters & Record Control -->
    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('manager.categories.index') }}" style="display: grid; grid-template-columns: 2fr auto; gap: 16px; align-items: end;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Search Category</label>
                <input type="text" name="search" class="form-control" placeholder="Type category name..." value="{{ request('search') }}">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('manager.categories.index') }}" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Usage Count</th>
                    <th>Status</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>#{{ $category->id }}</td>
                        <td>
                            <div class="icon-text-pair">
                                <i class="fas fa-tag" style="color: var(--accent-yellow); font-size: 0.9rem;"></i>
                                <strong>{{ $category->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $category->description ?: '-' }}</td>
                        <td>{{ $category->expenses_count }} expenses</td>
                        <td>
                            <span class="badge {{ $category->is_active ? 'badge-running' : 'badge-hold' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px; justify-content: center;">
                                <button class="btn btn-outline" style="padding: 6px 12px; font-size: 12px;" onclick="openEditModal({{ $category->id }}, '{{ $category->name }}', '{{ $category->description }}', {{ $category->is_active ? 'true' : 'false' }})">Edit</button>
                                
                                <form id="delete-cat-{{ $category->id }}" action="{{ route('manager.categories.destroy', $category->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <button type="button" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; color: var(--danger); border-color: var(--danger);" onclick="confirmDelete('delete-cat-{{ $category->id }}', 'Deleting this category will only work if it has NO expenses linked to it.')">Delete</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="custom-pagination" style="margin-top: 24px;">
        {{ $categories->appends(request()->query())->links() }}
    </div>

    <!-- Edit Modal (Simple overlay) -->
    <div id="editCategoryModal" class="sidebar-overlay" style="display: none; align-items: center; justify-content: center; z-index: 2000;">
        <div class="glass-panel" style="width: 100%; max-width: 500px; padding: 32px; position: relative;">
            <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="closeEditModal()" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="fas fa-times"></i>
            </button>
            <div style="margin-bottom: 24px;">
                <h3>Edit Category</h3>
            </div>
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" id="edit_description" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="is_active" id="edit_status" class="form-control" style="background: rgba(0,0,0,0.8);">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Update Category</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, description, isActive) {
            const form = document.getElementById('editCategoryForm');
            form.action = `/manager/categories/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description === '-' ? '' : description;
            document.getElementById('edit_status').value = isActive ? '1' : '0';
            
            const modal = document.getElementById('editCategoryModal');
            modal.style.display = 'flex';
            modal.classList.add('active');
        }

        function closeEditModal() {
            const modal = document.getElementById('editCategoryModal');
            modal.style.display = 'none';
            modal.classList.remove('active');
        }
    </script>
@endsection
