@extends('layouts.app')

@section('title')
<title>メール認証</title>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify_email.css') }}">
@endsection

@section('content')
<div class="verified">
    <h3 class="email-msg">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </h3>
    <a class="verified-button" href="http://localhost:8025">
        認証はこちらから
    </a>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="email-resend" type="submit">
            認証メールを再送する
        </button>
    </form>
</div>
@endsection