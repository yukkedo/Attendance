@extends('layouts.app')

@section('title')
<title>勤怠詳細(管理者)</title>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/detail.css') }}">
@endsection

@section('header')
<nav class="header-nav">
    <ul class="header-nav__list">
        <li><a href="/admin/attendance/list" class="header-nav__attendance">勤怠一覧</a></li>
        <li><a href="/admin/staff/list" class="header-nav__staff">スタッフ一覧</a></li>
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
        <form action="/attendance/{{ $attendance->id }}" method="post">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
            <table class="table">
                <tr class="table__item">
                    <th class="table__title">名前</th>
                    <td class="table__value">{{ $user->name}}</td>
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
                            <input class="time-in" type="time" name="new_clock_in" value="{{ $clockIn }}">
                            <p class="mark">~</p>
                            <input class="time-out" type="time" name="new_clock_out" value="{{ $clockOut }}">
                        </div>
                        @error('new_clock_in')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                        @error('new_clock_out')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
                @foreach($breaks as $index => $break)
                <tr class="table__item">
                    <th class="table__title">休憩{{ $index + 1 }}</th>
                    <td class="table__break-time">
                        <div class="flex-container">
                            <input type="hidden" name="work_break_id[]" value="{{ $break['id'] ?? '' }}">
                            <input class="time-in" type="time" name="breaks[{{ $index }}][start]" value="{{ $break['start'] }}">
                            <p class="mark">~</p>
                            <input class="time-out" type="time" name="breaks[{{ $index }}][end]" value="{{ $break['end'] }}">
                        </div>
                        @error("breaks.$index")
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
                @endforeach
                <tr class="table__item">
                    <th class="table__title">備考</th>
                    <td class="table__remarks">
                        <textarea class="textarea" name="remarks">{{ $remarks }}</textarea>
                        @error('remarks')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
            </table>
            <div class="button">
                <button class="fixes-button">修正</button>
            </div>
        </form>
    </div>
</div>
@endsection