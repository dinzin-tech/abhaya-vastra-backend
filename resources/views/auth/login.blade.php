@extends('layouts.app')

@section('content')
<div class="authentication-inner">
    <div class="card__wrapper">
        <div class="authentication-top text-center mb-20">
            <a href="javascript:;" class="authentication-logo logo-black">
                <img src="{{asset('assets/images/logo/logo1.png')}}" alt="logo">
            </a>
            <a href="javascript:;" class="authentication-logo logo-white">
                <img src="{{asset('assets/images/logo/logo.png')}}" alt="logo">
            </a>
            <h4 class="mb-15">Welcome to {{config('app.name')}}</h4>
            <p class="mb-15">Please sign-in to your account and start the adventure</p>
        </div>
        <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
            
            <div class="from__input-box">
                <div class="form__input-title">
                    <label for="email">{{ __('Email Address') }}</label>
                </div>
                <div class="form__input">
                    <input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                </div>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="from__input-box">
                <div class="form__input-title d-flex justify-content-between">
                    <label for="passwordInput">{{ __('Password') }}</label>
                    <a href="{{ route('password.request') }}">
                        <small>Forgot Password?</small>
                    </a>
                </div>
                <div class="form__input">
                    <input class="form-control" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                    <div class="pass-icon" id="passwordToggle"><i
                            class="fa-sharp fa-light fa-eye-slash"></i></div>
                </div>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember-me" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember-me">
                        Remember Me
                    </label>
                </div>
            </div>
            <div class="mb-3">
                <button class="btn btn-primary w-100" type="submit">{{ __('Login') }}</button>
            </div>
        </form>
       
    </div>
</div>
@endsection
