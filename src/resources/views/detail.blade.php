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
        <li><a href="/attendance" class="header-nav__attendance">勤怠</a></li>
        <li><a href="/attendance/list" class="header-nav__list">勤怠一覧</a></li>
        <li><a href="" class="header-nav__application">申請</a></li>
        <li>
            <form action="/logout" class="logout" method="post">
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
                <td class="table__value">
                    <p class="table__value-name">{{ $user->name }}</p>
                </td>
            </tr>
            <tr class="table__item">
                <th class="table__title">日付</th>
                <td class="table__date">
                    <div class="flex-container">
                        <p class="date-year">{{ $year }}</p>
                        <p class="date-day">{{ $date }}</p>
                    </div>
                </td>
            </tr>
            <tr class="table__item">
                <th class="table__title">出勤・退勤</th>
                <td class="table__work-time">
                    <div class="flex-container">
                        <input class="time-in" type="time" name="clock_in" value="{{ $clockIn }}">
                        <p class="mark">~</p>
                        <input class="time-out" type="time" name="clock_out" value="{{ $clockOut }}">
                    </div>
                </td>
            </tr>
            @foreach($breaks as $index => $break)
            <tr class="table__item">
                <th class="table__title">休憩{{ $index + 1 }}</th>
                <td class="table__break-time">
                    <div class="flex-container">
                        <input class="time-in" type="time" name="break_start" value="{{ $break['start'] }}">
                        <p class="mark">~</p>
                        <input class="time-out" type="time" name="break_end" value="{{ $break['end'] }}">
                    </div>
                </td>
            </tr>
            @endforeach
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