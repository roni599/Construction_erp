@extends('layouts.app')

@section('title', 'Admin Profile Settings')

@section('content')
    <div class="flex-between" style="margin-bottom: 32px; animation: fadeIn 0.5s ease;">
        <div>
            <h2 style="margin: 0; font-size: 24px; letter-spacing: -0.5px;">Admin Profile</h2>
            <p style="color: var(--text-secondary); margin: 5px 0 0 0;">Manage your core business identity and personal contact details.</p>
        </div>
    </div>

    <div style="max-width: 1200px; margin: 0 auto; animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);">
        @if($errors->any())
            <div class="glass-panel" style="margin-bottom: 24px; border-color: var(--danger); background: rgba(255, 82, 82, 0.05); padding: 15px;">
                <ul style="margin: 0; color: var(--danger); font-size: 14px; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="profile-layout">
            
            <!-- Left: Profile Overview Card -->
            <div class="glass-panel profile-card" style="padding: 80px 40px; text-align: center; height: 100%; display: flex; flex-direction: column; justify-content: center;">
                <div style="position: relative; display: inline-block; margin-bottom: 30px;">
                    <div style="width: 200px; height: 200px; border-radius: 50%; overflow: hidden; border: 4px solid var(--accent-blue); box-shadow: 0 0 25px rgba(0, 123, 255, 0.25); background: rgba(255,255,255,0.03); display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                        @if($user->image)
                            <img src="{{ $user->image }}" id="preview-img" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <i class="fas fa-user-shield" id="preview-icon" style="font-size: 90px; color: var(--accent-blue); opacity: 0.8;"></i>
                        @endif
                    </div>
                    <label for="image-upload" style="position: absolute; bottom: 8px; right: 25px; width: 50px; height: 50px; background: var(--accent-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid #1a1a2e; color: white; transition: all 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>

                <h3 style="margin: 0; font-size: 26px; color: var(--text-primary); font-weight: 700;">{{ $user->name }}</h3>
                <div style="display: inline-block; padding: 6px 16px; background: linear-gradient(135deg, var(--accent-blue), #0056b3); color: white; border-radius: 20px; font-size: 13px; font-weight: 700; margin: 15px auto 0; text-transform: uppercase; letter-spacing: 1.5px; box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);">
                    Admin Owner
                </div>

                <div style="margin-top: 32px; padding-top: 32px; border-top: 1px solid var(--border-color); display: grid; gap: 20px; text-align: left;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: var(--accent-yellow);">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-secondary);">Email Address</div>
                            <div style="font-size: 14px;">{{ $user->email }}</div>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: var(--success);">
                            <i class="fas fa-building"></i>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: var(--text-secondary);">Business</div>
                            <div style="font-size: 14px;">{{ $user->business_name ?? 'Not Set' }}</div>
                        </div>
                    </div>
                </div>
            </div>

                <input type="file" id="image-upload" name="image" style="display: none;" onchange="previewImage(this)">

                <div style="height: 100%;">
                    
                    <!-- Combined Profile Information Card -->
                    <div class="glass-panel" style="padding: 25px; height: 100%; display: flex; flex-direction: column;">
                        <!-- Section Header: Account Info -->
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                            <i class="fas fa-user-shield" style="font-size: 16px; color: var(--accent-blue);"></i>
                            <h4 style="margin: 0; font-size: 15px;">Profile & Business Identity</h4>
                        </div>
                        
                        <div class="settings-grid-2" style="margin-bottom: 15px;">
                            <div class="form-group">
                                <label class="form-label" style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px; display: block;">Full Name</label>
                                <div style="position: relative;">
                                    <i class="fas fa-user" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 12px;"></i>
                                    <input type="text" name="name" class="form-control" style="padding-left: 35px; height: 38px; font-size: 13px;" value="{{ old('name', $user->name) }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px; display: block;">Business Name</label>
                                <div style="position: relative;">
                                    <i class="fas fa-briefcase" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 12px;"></i>
                                    <input type="text" name="business_name" class="form-control" style="padding-left: 35px; height: 38px; font-size: 13px;" value="{{ old('business_name', $user->business_name) }}" placeholder="e.g. Acme Construction">
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <div class="form-group">
                                <label class="form-label" style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px; display: block;">Business Logo</label>
                                <div style="display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.02); padding: 10px; border-radius: 8px; border: 1px dashed var(--border-color);">
                                    <div style="width: 50px; height: 50px; border-radius: 6px; background: rgba(255,255,255,0.03); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--border-color);">
                                        @if($user->business_logo)
                                            <img src="{{ $user->business_logo }}" id="logo-preview-img" style="width: 100%; height: 100%; object-fit: contain;">
                                        @else
                                            <i class="fas fa-image" id="logo-preview-icon" style="font-size: 18px; color: var(--text-secondary);"></i>
                                        @endif
                                    </div>
                                    <div style="flex: 1;">
                                        <label for="logo-upload" class="btn btn-outline" style="padding: 4px 10px; font-size: 10px; cursor: pointer;">
                                            <i class="fas fa-upload"></i> Upload Logo
                                        </label>
                                        <input type="file" id="logo-upload" name="business_logo" style="display: none;" onchange="previewLogo(this)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Header: Contact & Location -->
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; padding-top: 15px; border-top: 1px solid var(--border-color);">
                            <i class="fas fa-map-marked-alt" style="font-size: 16px; color: var(--accent-yellow);"></i>
                            <h4 style="margin: 0; font-size: 15px;">Contact & Location Details</h4>
                        </div>
                        
                        <div style="display: grid; gap: 12px; margin-bottom: 15px;">
                            <div class="form-group">
                                <label class="form-label" style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px; display: block;">Phone Number</label>
                                <div style="position: relative;">
                                    <i class="fas fa-phone" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 12px;"></i>
                                    <input type="text" name="phone" class="form-control" style="padding-left: 35px; height: 38px; font-size: 13px;" value="{{ old('phone', $user->phone) }}" placeholder="+880 1XXX-XXXXXX">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-size: 11px; color: var(--text-secondary); margin-bottom: 4px; display: block;">Business Address</label>
                                <div style="position: relative;">
                                    <i class="fas fa-map-marker-alt" style="position: absolute; left: 14px; top: 10px; color: var(--text-secondary); font-size: 12px;"></i>
                                    <textarea name="address" class="form-control" style="padding-left: 35px; font-size: 13px;" rows="2" placeholder="Enter full business address">{{ old('address', $user->address) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div style="padding-top: 32px; border-top: 1px solid var(--border-color); text-align: right; margin-top: auto;">
                            <button type="submit" class="btn btn-primary" style="padding: 14px 40px; font-weight: 600; font-size: 15px; border-radius: 12px; box-shadow: 0 10px 20px rgba(255, 215, 0, 0.2); width: 100%;">
                                <i class="fas fa-save"></i> Save All Profile & Business Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Security Section -->
        <div class="glass-panel animate-slide-down" style="margin-top: 32px; padding: 32px; animation-delay: 0.2s;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255, 76, 76, 0.1); display: flex; align-items: center; justify-content: center; color: var(--danger);">
                    <i class="fas fa-lock"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 20px;">Security & Password</h3>
                    <p style="color: var(--text-secondary); margin: 2px 0 0 0; font-size: 13px;">Update your account password to maintain security.</p>
                </div>
            </div>

            <form action="{{ route('admin.profile.password') }}" method="POST">
                @csrf
                <div class="security-grid">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div style="position: relative;">
                            <i class="fas fa-shield-alt" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 13px;"></i>
                            <input type="password" name="password" class="form-control" style="padding-left: 40px;" required placeholder="••••••••">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <div style="position: relative;">
                            <i class="fas fa-check-circle" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 13px;"></i>
                            <input type="password" name="password_confirmation" class="form-control" style="padding-left: 40px;" required placeholder="••••••••">
                        </div>
                    </div>
                </div>
                <div style="margin-top: 24px; text-align: right;">
                    <button type="submit" class="btn btn-outline" style="border-color: var(--danger); color: var(--danger); padding: 10px 30px;" onmouseover="this.style.background='rgba(255, 76, 76, 0.1)'" onmouseout="this.style.background='transparent'">
                        <i class="fas fa-sync-alt"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .form-control:focus {
            border-color: var(--accent-blue) !important;
            box-shadow: 0 0 15px rgba(0, 123, 255, 0.1) !important;
        }

        /* Responsive Layouts */
        .profile-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 32px;
        }

        .settings-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .security-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        @media (max-width: 992px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }
            .profile-card {
                padding: 40px 20px !important;
            }
        }

        @media (max-width: 576px) {
            .settings-grid-2, .security-grid {
                grid-template-columns: 1fr;
            }
            .flex-between {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
        }
    </style>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('preview-img');
                    const icon = document.getElementById('preview-icon');
                    
                    if (img) {
                        img.src = e.target.result;
                    } else if (icon) {
                        const newImg = document.createElement('img');
                        newImg.id = 'preview-img';
                        newImg.src = e.target.result;
                        newImg.style.width = '100%';
                        newImg.style.height = '100%';
                        newImg.style.objectFit = 'cover';
                        icon.parentElement.appendChild(newImg);
                        icon.remove();
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        function previewLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('logo-preview-img');
                    const icon = document.getElementById('logo-preview-icon');
                    
                    if (img) {
                        img.src = e.target.result;
                    } else if (icon) {
                        const newImg = document.createElement('img');
                        newImg.id = 'logo-preview-img';
                        newImg.src = e.target.result;
                        newImg.style.width = '100%';
                        newImg.style.height = '100%';
                        newImg.style.objectFit = 'contain';
                        icon.parentElement.appendChild(newImg);
                        icon.remove();
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
