@extends('layouts.app')

@section('title', 'Record New Expense')

@section('content')
    <div class="flex-between" style="margin-bottom: 20px;">
        <div>
            <h2>Record Project Expense</h2>
            <p style="color: var(--text-secondary);">Select a project and category to record a new expenditure</p>
        </div>
        <a href="{{ route('admin.projects.all_expenses') }}" class="btn btn-outline">&larr; Back to Expenses</a>
    </div>

    <div class="glass-panel" style="max-width: 1200px; margin: 0 auto; padding: 24px 32px;">
        <form method="POST" action="{{ route('admin.projects.expenses.storeGlobal') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Select Project <span style="color: var(--danger);">*</span></label>
                    <select name="project_id" id="project_id" class="form-control select2" required style="background: rgba(0,0,0,0.8);">
                        <option value="">-- Select Project --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ (isset($selectedProject) && $selectedProject->id == $p->id) || old('project_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->project_name }} ({{ $p->client_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Expense Category <span style="color: var(--danger);">*</span></label>
                    <select name="expense_category_id" class="form-control select2" required style="background: rgba(0,0,0,0.8);">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Amount (Tk.) <span style="color: var(--danger);">*</span></label>
                    <input type="number" step="any" name="amount" class="form-control" required placeholder="Enter amount" value="{{ old('amount') }}" onwheel="this.blur()">
                    <div id="balance-info" style="margin-top: 8px;">
                        @if($summary)
                            <p style="font-size: 13px; color: var(--text-secondary);">
                                PM Hand Cash: <span style="color: var(--success); font-weight: 600;">Tk. {{ number_format($summary['manager_cash_balance'], 2) }}</span>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Expense Date <span style="color: var(--danger);">*</span></label>
                    <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Description (Optional)</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Describe the expense details...">{{ old('description') }}</textarea>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Upload Receipt (Optional)</label>
                <div style="border: 2px dashed var(--border-color); border-radius: 12px; padding: 16px; text-align: center; background: rgba(255,255,255,0.02); transition: border-color 0.3s;" onmouseover="this.style.borderColor='var(--accent-yellow)'" onmouseout="this.style.borderColor='var(--border-color)'">
                    <input type="file" name="bill_image" class="form-control" accept="image/*" style="border: none; background: transparent;">
                    <p style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">Accepted formats: JPG, PNG, WEBP (Max 5MB)</p>
                </div>
            </div>

            <div style="display: flex; gap: 16px;">
                <button type="submit" class="btn btn-primary" style="flex: 2; padding: 14px; font-weight: 600;">
                    <i class="fas fa-check-circle"></i> Save Transaction
                </button>
                <a href="{{ route('admin.projects.all_expenses') }}" class="btn btn-outline" style="flex: 1; padding: 14px;">Cancel</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Select2 Glassmorphism Styling */
        .select2-container--default .select2-selection--single {
            background: rgba(0, 0, 0, 0.8) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 8px !important;
            height: 45px !important;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--text-primary) !important;
            padding-left: 12px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 43px !important;
        }
        .select2-dropdown {
            background: #1a1a1a !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 8px !important;
            color: var(--text-primary) !important;
            backdrop-filter: blur(10px);
        }
        .select2-search__field {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-primary) !important;
            border-radius: 4px !important;
        }
        .select2-results__option--highlighted[aria-selected], 
        .select2-results__option:hover {
            background-color: #E6C200 !important;
            color: white !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: rgba(230, 194, 0, 0.3) !important;
            color: white !important;
        }
    </style>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
                placeholder: 'Click to search...',
                allowClear: true
            });

            document.getElementById('project_id').addEventListener('change', function() {
                const projectId = this.value;
                const balanceInfo = document.getElementById('balance-info');
                
                if (!projectId) {
                    balanceInfo.innerHTML = '';
                    return;
                }

                balanceInfo.innerHTML = '<p style="font-size: 12px; color: var(--accent-yellow);"><i class="fas fa-spinner fa-spin"></i> Loading project stats...</p>';
                
                // Redirect to refresh balance info
                window.location.href = "{{ route('admin.projects.expenses.createGlobal') }}?project_id=" + projectId;
            });
        });
    </script>
@endsection
