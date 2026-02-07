@extends('layouts.auth-app')

@section('content')

<main class="form-signin">
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <img class="mb-4" src="{{ asset('assets/images/icone-bm.png') }}" alt="" width="100" height="100">
        <h1 class="h3 mb-3 fw-normal">Please sign in</h1>

        <div class="form-floating">
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="floatingInput" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="name@example.com">
            <label for="floatingInput">Email address</label>

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-floating">
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="floatingPassword" name="password" required autocomplete="current-password" placeholder="Password">
            <label for="floatingPassword">Password</label>

            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="checkbox mb-3">
            <label>
                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}> Remember me
            </label>
        </div>
        
        <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
        @if (Route::has('password.request'))
            <a class="btn btn-link" href="{{ route('password.request') }}">
                {{ __('Forgot Your Password?') }}
            </a>
        @endif
        <p class="mt-5 mb-3 text-muted">&copy; 2017–2021</p>
    </form>
</main>
@endsection
