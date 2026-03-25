@extends('layouts.auth-app')

@section('content')
    <main class="form-signin">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <img class="mb-4" src="{{ asset('assets/images/icone-bm.png') }}" alt="" width="100" height="100">
            <h1 class="h3 mb-3 fw-normal">Please sign in</h1>

            <div class="form-floating">
                <input type="text" class="form-control @error('login') is-invalid @enderror" id="floatingInput"
                    name="login" value="{{ old('login') }}" required autocomplete="username" autofocus
                    placeholder="username ou email">
                <label for="floatingInput">Username ou email</label>

                @error('login')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-floating">
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="floatingPassword"
                    name="password" required autocomplete="current-password" placeholder="Password">
                <label for="floatingPassword">Password</label>

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="checkbox mb-3">
                <label>
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}> Remember
                    me
                </label>
            </div>

            <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
            @if (!config('ad.enabled') && Route::has('password.request'))
                <a class="btn btn-link" href="{{ route('password.request') }}">
                    {{ __('Forgot Your Password?') }}
                </a>
            @endif
            @if (config('ad.enabled'))
                <p class="mt-3 text-muted" style="color: #ba9a69 !important;">
                    Autenticação via Active Directory
                </p>
            @endif
            <p class="mt-5 mb-3 text-muted" style="color: #ba9a69 !important;">&copy; Banco de Moçambique
                {{ date('Y') }}</p>
        </form>
    </main>
@endsection
