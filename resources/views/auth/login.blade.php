<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            height: 100vh;
        }
        .card {
            border: none;
            border-radius: 1rem;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #6a11cb;
        }
        .btn-primary {
            background-color: #6a11cb;
            border: none;
        }
        .btn-primary:hover {
            background-color: #5a0fb3;
        }
        .btn-outline-primary {
            border-color: #4267B2;
            color: #4267B2;
        }
        .btn-outline-danger {
            border-color: #DB4437;
            color: #DB4437;
        }
    </style>
</head>
<body>
<div class="container h-100 d-flex justify-content-center align-items-center">
    <div class="card shadow-lg p-4 w-100" style="max-width: 400px;">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="logo.png" alt="Logo" class="mb-3" style="max-width: 80px;">
                <h3 class="card-title">Welcome Back</h3>
                <p class="text-muted">Sign in to continue</p>
            </div>

            {{-- Display global login error --}}
            @if ($errors->has('email'))
                <div class="alert alert-danger">
                    {{ $errors->first('email') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mb-3 form-floating">
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="name@example.com" required>
                    <label for="email">Email address</label>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-floating">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Password" required>
                    <label for="password">Password</label>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
            </form>

            <hr>
        </div>
    </div>
</div>
<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
