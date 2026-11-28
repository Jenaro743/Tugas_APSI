@extends('layout')

@section('title', 'Login staf')

@section('content')
{{-- LOGIN STAF: menerima ID atau email, lalu memeriksa password akun. --}}
<div class="row justify-content-center py-5">
    <div class="col-md-5">
        <div class="card rounded-4 p-4">
            <span class="accent fw-bold">AREA STAF</span>
            <h1 class="fw-bold mt-2">Selamat datang.</h1>
            <p class="text-muted">Masuk menggunakan ID atau email akun.</p>

            <form method="post" action="{{ route('login.post') }}">
                @csrf
                <label class="form-label mt-3">ID atau Email Admin / Karyawan</label>
                <input class="form-control" name="staff_id" autocomplete="username" required value="{{ old('staff_id') }}">

                <label class="form-label mt-3">Password</label>
                <input class="form-control" name="password" type="password" autocomplete="current-password" required>

                <button class="btn btn-success w-100 mt-4">Masuk ke dashboard</button>
            </form>

            {{-- Data akun ini adalah data demo untuk mencoba aplikasi. --}}
            <div class="border-top border-secondary mt-4 pt-3">
                <h2 class="h6 fw-bold">Akun demo</h2>
                <div class="small text-muted">
                    <div>
                        <strong class="text-white">Admin</strong> · ID: <code>ADMIN001</code> · Email: <code>admin@rexel.local</code><br>
                        Password: <code>RexelAdmin123!</code>
                    </div>
                    <div class="mt-2">
                        <strong class="text-white">Karyawan</strong> · ID: <code>KRY001</code> · Email: <code>karyawan@rexel.local</code><br>
                        Password: <code>RexelStaff123!</code>
                    </div>
                    <p class="mt-2 mb-0">Kolom di atas menerima ID maupun email.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
