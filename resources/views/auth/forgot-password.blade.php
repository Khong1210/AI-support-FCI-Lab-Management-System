<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - FCI Lab Management System</title>
    {{-- Bootstrap 5 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e8eeff 0%, #f5f7ff 60%, #eef2ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(26,86,219,0.10), 0 1.5px 6px rgba(0,0,0,0.06);
            overflow: hidden;
            border: 1px solid rgba(26,86,219,0.08);
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            background: linear-gradient(135deg, #1e2a4a 0%, #1a56db 100%);
            color: #fff;
            padding: 2rem;
            text-align: center;
        }
        .login-header .logo-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control {
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: #1a56db;
            box-shadow: 0 0 0 3px rgba(26,86,219,0.12);
        }
        .btn-login {
            background: linear-gradient(135deg, #1a56db, #1d4ed8);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-weight: 600;
            width: 100%;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26,86,219,0.25);
            color: #fff;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <div class="logo-icon">
                <i class="fas fa-unlock-alt"></i>
            </div>
            <h4 class="mb-0 fw-bold">Forgot Password</h4>
            <p class="mb-0 text-white-50 small">Verify your identity to reset</p>
        </div>

        <div class="login-body">
            @if ($errors->any())
                <div class="alert alert-danger" style="font-size: 0.85rem; padding: 0.75rem;">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('password.forgot.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" name="username" class="form-control border-start-0 ps-0" value="{{ old('username') }}" required autofocus placeholder="Enter your username">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-muted small">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control border-start-0 ps-0" value="{{ old('email') }}" required placeholder="name@example.com">
                    </div>
                </div>

                <button type="submit" class="btn btn-login mb-3">
                    <i class="fas fa-redo-alt me-2"></i> Reset Password
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-decoration-none small text-muted">
                        <i class="fas fa-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>