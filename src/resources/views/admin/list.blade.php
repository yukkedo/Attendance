@extends('layouts.app')

@section('title')
<title>勤怠一覧(管理者)</title>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/list.css') }}">
@endsection

@section('header')
<nav class="header-nav">
    <ul class="header-nav__list">
        <li><a href="/admin/attendance/list" class="header-nav__attendance">勤怠一覧</a></li>
        <li><a href="/admin/staff/list" class="header-nav__list">スタッフ一覧</a></li>
        <li><a href="/stamp_correction_request/list" class="header-nav__application">申請一覧</a></li>
        <li>
            <form action="/admin/logout" class="logout" method="post">
                @csrf
                <button class="header-nav__item--button">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
@endsection

@section('content')
<div class="list-content">
    <div class="content__title">
        {{ $targetDate->format('Y年n月j日') }}の勤怠
    </div>
    <div class="content__date">
        <a href="{{ url('/admin/attendance/list/' . $prevDate) }}" class="prev-month"><img class="left-arrow" src="{{ asset('image/image_left.png') }}" alt="矢印"> 前日</a>
        <div class="date-month">
            <img class="img-calender" src="{{ asset('image/image_month.png') }}" alt="カレンダー">
            <span class="date-calender">{{ $targetDate->format('Y/m/d') }}</span>
        </div>
        <a href="{{ url('/admin/attendance/list/' . $nextDate) }}" class="next-month">翌日 <img class="right-arrow" src="{{ asset('image/image_right.png') }}" alt="矢印"></a>
    </div>
    <div class="content__record">
        <table class="record__table">
            <tr class="record-title">
                <th class="table__title">名前</th>
                <th class="table__title">出勤</th>
                <th class="table__title">退勤</th>
                <th class="table__title">休憩</th>
                <th class="table__title">合計</th>
                <th class="table__title">詳細</th>
            </tr>
            @foreach($attendances as $attendance)
            <tr class="record-data">
                <td class="table__date">{{ $attendance->user->name }}</td>
                <td class="table__work-in">{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                <td class="table__work-out">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                <td class="table__break">{{ $attendance->break_time ?? '' }}</td>
                <td class="table__total">{{ $attendance->work_time ?? '' }}</td>
                <td class="table__detail">
                    <a class="detail-link" href="/attendance/{{ $attendance->id }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection