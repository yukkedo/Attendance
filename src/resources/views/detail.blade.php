@extends('layouts.app')

@section('title')
<title>勤怠詳細</title>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
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
<div class="list-content">
    <div class="content__title">
        勤怠詳細
    </div>
    <div class="content__detail">
        <table class="table">
            <tr class="table__item">
                <th class="table__title">名前</th>
                <td class="table__value">田中 太郎</td>
            </tr>
            <tr class="table__item">
                <th class="table__title">日付</th>
                <td class="table__date">
                    <div class="flex-container">
                        <p class="date-year">2025年</p>
                        <p class="date-day">6月1日</p>
                    </div>
                </td>
            </tr>
            <tr class="table__item">
                <th class="table__title">出勤・退勤</th>
                <td class="table__work-time">
                    <div class="flex-container">
                        <input class="time-in" type="time">
                        <p class="mark">~</p>
                        <input class="time-out" type="time">
                    </div>
                </td>
            </tr>
            <tr class="table__item">
                <th class="table__title">休憩</th>
                <td class="table__break-time">
                    <div class="flex-container">
                        <input class="time-in" type="time">
                        <p class="mark">~</p>
                        <input class="time-out" type="time">
                    </div>
                </td>
            </tr>
            <tr class="table__item">
                <th class="table__title">備考</th>
                <td class="table__remarks">
                    <textarea class="textarea" name="" id=""></textarea>
                </td>
            </tr>
        </table>
        <div class="button">
            <button class="fixes-button">修正</button>
        </div>
    </div>
</div>
@endsection