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
        <li><a href="" class="header-nav__attendance">勤怠</a></li>
        <li><a href="" class="header-nav__list">勤怠一覧</a></li>
        <li><a href="" class="header-nav__application">申請</a></li>
        <li>
            <form action="" class="logout" method="">
                @csrf
                <a class="header-nav__item--button">ログアウト</a>
            </form>
        </li>
    </ul>
</nav>
@endsection

@section('content')
<div class="content">
    <div class="stamp-item">

        <p class="item__status">勤務外</p>
        <!-- <p class="item__status">出勤中</p>
        <p class="item__status">休憩中</p>
        <p class="item__status">退勤済</p> -->

        <p class="item__date">2025年5月5日(木)</p>
        <p class="item__time">08:00</p>

        <!-- 出勤ボタン -->
        <button class="item__button">出勤</button>
        <!-- 休憩入り時ボタン -->
        <!-- <div class="stamp-button">
            <button class="item__button">退勤</button>
            <button class="item__button--break">休憩入</button>
        </div> -->
        <!-- 休憩終わり時ボタン -->
        <!-- <button class="item__button--break">休憩戻</button> -->
        <!-- 退勤ボタン -->
        <!-- <p class="end-message">お疲れ様でした。</p> -->

    </div>
</div>
@endsection