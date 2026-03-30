<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول</title>

    {{-- Bootstrap 5 RTL --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">

    {{-- Google Fonts: Tajawal --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: "Tajawal", sans-serif;
            background: #f6f8fb;
        }

        .login-card {
            max-width: 480px;
            margin: 8vh auto 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-card">
            <div class="text-center mb-4">
                <h3 class="fw-bold mb-2">عيادة بيتس كورنر البيطرية</h3>
                <div class="text-muted">تسجيل الدخول</div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    {{-- تم الإضافة: عرض رسائل الأخطاء والتحقق --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-bold mb-1">حدثت أخطاء</div>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input id="email" name="email" type="email" class="form-control"
                                value="{{ old('email') }}" required autocomplete="email" autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <input id="password" name="password" type="password" class="form-control" required
                                autocomplete="current-password">
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                تذكرني
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            دخول
                        </button>
                    </form>
                </div>
            </div>

            <div class="text-center text-muted mt-3 small">
                جميع الحقوق محفوظة &copy; {{ date('Y') }}
            </div>
        </div>
    </div>

    {{-- Bootstrap 5 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

