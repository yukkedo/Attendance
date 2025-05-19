@extends('layouts.app')

@section('title')
<title>勤怠登録</title>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/registration.css') }}">
@endsection

@section('header')
<nav class="header-nav">
    <ul class="header-nav__list">
        <li><a href="/attendance" class="header-nav__attendance">勤怠</a></li>
        <li><a href="" class="header-nav__list">勤怠一覧</a></li>
        <li><a href="" class="header-nav__application">申請</a></li>
        <li>
            <form action="/logout" class="logout" method="post">
                @csrf
                <button class="header-nav__item--button">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
@endsection

@section('content')
<div class="content">
    <div class="stamp-item">
        <p class="item__status">{{ $status }}</p>

        <p class="item__date">{{ $now->format('Y年m月d日') }}</p>
        <p class="item__time">{{ $now->format('H:i') }}</p>

        <!-- 出勤ボタン -->
        @if($clockIn)
        <form action="/attendance/clockIn" method="post">
            @csrf
            <button class="item__button" type="submit">出勤</button>
        </form>
        @endif
        <!-- 休憩入り時ボタン -->
        <div class="stamp-button">
            @if($clockOut)
            <form action="/attendance/clockOut" method="post">
                @csrf
                <button class="item__button">退勤</button>
            </form>
            @endif
            @if($breakStart)
            <form action="/attendance/breakStart" method="post">
                @csrf
                <button class="item__button--break">休憩入</button>
            </form>
            @endif
        </div>

        <!-- 休憩終わり時ボタン -->
        @if($breakEnd)
        <form action="/attendance/breakEnd" method="post">
            @csrf
            <button class="item__button--break">休憩戻</button>
        </form>
        @endif

        <!-- 退勤ボタン -->
        @if($clockOutMessage)
        <p class="end-message">お疲れ様でした。</p>
        @endif

    </div>
</div>
@endsection