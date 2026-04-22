@extends('layouts.auth-app')

@section('content')
    <main class="form-signin">
        @if ($alreadyEnabled)
            <div class="text-center">
                <img class="mb-4" src="{{ asset('assets/images/icone-bm.png') }}" alt="" width="100" height="100">
                <h1 class="h4 mb-3 fw-normal">2FA já está ativo</h1>
                <p class="text-muted">Se precisar, você pode desativar e configurar novamente.</p>
                <form method="POST" action="{{ route('two-factor.disable') }}">
                    @csrf
                    <button class="w-100 btn btn-lg btn-outline-danger" type="submit">Desativar 2FA</button>
                </form>
                @include('auth.partials.docs-link')
            </div>
        @else
            <form method="POST" action="{{ route('two-factor.enable') }}">
                @csrf
                <img class="mb-4" src="{{ asset('assets/images/icone-bm.png') }}" alt="" width="100" height="100">
                <h1 class="h4 mb-3 fw-normal">Configurar 2FA</h1>
                <p class="text-muted">Use o segredo abaixo no seu autenticador.</p>

                <div class="alert alert-secondary otp-card">
                    <div><strong>Secret:</strong></div>
                    <div class="otp-value">{{ $secret }}</div>
                    @if ($otpauth)
                        <div class="mt-2 otp-raw"><strong>OTPAuth:</strong> {{ $otpauth }}</div>
                    @endif
                </div>
                @if ($otpauth)
                    <div class="text-center mb-3">
                        <img
                            src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($otpauth) }}"
                            alt="QR Code 2FA"
                            class="qr-image"
                        >
                        <div class="form-text mt-2">Leia este QR no Google Authenticator</div>
                    </div>
                @endif

                <div class="form-floating">
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="codeInput"
                        name="code" required autofocus placeholder="123456">
                    <label for="codeInput">Código 2FA</label>

                    @error('code')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">Ativar 2FA</button>
                @include('auth.partials.docs-link')
            </form>
        @endif
    </main>
@endsection
