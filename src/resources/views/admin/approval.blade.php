@extends('layouts.app')

@section('title')
<title>勤怠詳細(管理者)</title>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/approval.css') }}">
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
        勤怠詳細
    </div>
    <div class="content__detail">
        <table class="table">
            <tr class="table__item">
                <th class="table__title">名前</th>
                <td class="table__value">{{ $attendanceChange->user->name }}</td>
            </tr>
            <tr class="table__item">
                <th class="table__title">日付</th>
                <td class="table__date">
                    <div class="flex-container">
                        <p class="date-year">{{ $date->format('Y年') }}</p>
                        <p class="date-day">{{ $date->format('n月j日') }}</p>
                    </div>
                </td>
            </tr>
            <tr class="table__item">
                <th class="table__title">出勤・退勤</th>
                <td class="table__work-time">
                    <div class="flex-container">
                        <p class="time-in">{{ \Carbon\Carbon::parse($attendanceChange->new_clock_in)->format('H:i') }}</p>
                        <p class="mark">~</p>
                        <p class="time-out">{{ \Carbon\Carbon::parse($attendanceChange->new_clock_out)->format('H:i') }}</p>
                    </div>
                </td>
            </tr>
            @foreach ($attendanceChange->workBreakChanges as $break)
            <tr class="table__item">
                <th class="table__title">休憩</th>
                <td class="table__break-time">
                    <div class="flex-container">
                        <p class="time-in">{{ $break->new_break_start ? \Carbon\Carbon::parse($break->new_break_start)->format('H:i') : '' }}</p>
                        <p class="mark">~</p>
                        <p class="time-out">{{ $break->new_break_start ? \Carbon\Carbon::parse($break->new_break_end)->format('H:i') : '' }}</p>
                    </div>
                </td>
            </tr>
            @endforeach
            <tr class="table__item">
                <th class="table__title">休憩{{ count($attendanceChange->workBreakChanges) + 1 }}</th>
                <td class="table__break-time">
                    <div class="flex-container">
                        <p class="time-in"></p>
                        <p class="mark"></p>
                        <p class="time-out"></p>
                    </div>
                </td>
            </tr>
            <tr class="table__item">
                <th class="table__title">備考</th>
                <td class="table__remarks">
                    {{ $attendanceChange->remarks }}
                </td>
            </tr>
        </table>
        <div class="button">
            @if ($attendanceChange->status === 'pending')
            <form action="/stamp_correction_request/approve/{{ $attendanceChange->id }}" method="post">
                @csrf
                <button class="fixes-button-pre-approval">承認</button>
            </form>
            @elseif ( $attendanceChange->status === 'approved')
            <button class="fixes-button-approved">承認済み</button>
            @endif
        </div>
    </div>
</div>
@endsection