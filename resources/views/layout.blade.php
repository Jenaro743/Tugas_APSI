<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Rexel Games')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body,body *{font-family:Inter,system-ui;color:#fff}
        body{background:#0b0f1a}
        .navbar,.card,.table,.modal-content{background:#131a29!important;color:#eef1f7!important;border-color:#263149!important}
        .navbar-brand{font-weight:800;color:#9cff57!important;letter-spacing:.08em}
        .text-muted,.text-white-50{color:#fff!important;opacity:.78}
        .hero{background:radial-gradient(ellipse at 80% 20%,#2a4e42,transparent 45%),linear-gradient(135deg,#16243b,#101522);border-radius:24px;padding:64px 48px}
        .accent{color:#9cff57}
        .btn-success{background:#9cff57;border-color:#9cff57;color:#101522;font-weight:700}
        .btn-outline-light{border-color:#3a4860}
        .form-control,.form-select{background:#0e1421;color:#eef1f7;border-color:#303b51}
        .form-control:focus,.form-select:focus{background:#0e1421;color:white}
        .table{--bs-table-color:#eef1f7;--bs-table-bg:#131a29;--bs-table-border-color:#263149}
        .badge-soft{background:#1d342d;color:#9cff57}
        .product-img{height:170px;object-fit:cover;background:#1d2535}
        .footer{color:#7e8ba2;border-top:1px solid #263149;padding:24px 0;margin-top:70px}
        .pill{border:1px solid #34415a;border-radius:100px;padding:8px 15px;color:#bac6d8}
        .pagination .page-link{padding:.25rem .55rem;font-size:.875rem;background:#131a29;color:#bac6d8;border-color:#263149}
        .pagination .page-link:hover{background:#1d342d;color:#9cff57}
        .pagination .page-item.active .page-link{background:#9cff57;border-color:#9cff57;color:#101522}
        .pagination .page-item.disabled .page-link{background:#101522;color:#667085;border-color:#263149}
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg border-bottom border-secondary-subtle">
        <div class="container py-2">
            <a class="navbar-brand" href="{{ route('home') }}">REXEL<span class="text-white"> GAMES</span></a>
            <div class="ms-auto d-flex gap-2 align-items-center">
                <a class="btn btn-sm btn-outline-light" href="{{ route('home') }}">Katalog</a>
                @auth
                    <a class="btn btn-sm btn-outline-light" href="{{ route('dashboard') }}">Dashboard</a>
                    <form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-success">Keluar</button></form>
                @else
                    @if(session('guest'))
                        <a class="btn btn-sm btn-outline-light" href="{{ route('dashboard') }}">Keranjang ({{ array_sum(session('cart', [])) }})</a>
                        <form method="post" action="{{ route('guest.exit') }}">@csrf<button class="btn btn-sm btn-outline-light">Akhiri sesi</button></form>
                    @else
                        <a class="btn btn-sm btn-outline-light" href="{{ route('login') }}">Login staf</a>
                        <form method="post" action="{{ route('guest') }}">@csrf<button class="btn btn-sm btn-success">Masuk sebagai tamu</button></form>
                    @endif
                @endauth
            </div>
        </div>
    </nav>
    <main class="container py-4">
        @if(session('ok'))<div class="alert alert-success">{{ session('ok') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
    <footer class="container footer d-flex justify-content-between">
        <span>© {{ date('Y') }} Rexel Games</span>
        <span>Game pilihan, pengalaman terbaik.</span>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>