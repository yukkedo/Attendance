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
        <li><a href="/stamp_correction_request/list" class="header-nav__application">申請</a></li>
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
        <form action="/attendance/{{$attendance->id}}" method="post">
            @csrf
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
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
                            <input class="time-in {{ $isPending ? 'plain-input' : '' }}" type="time" name="new_clock_in" value="{{ $clockIn }}" @if($isPending) readonly @endif>
                            <p class="mark">~</p>
                            <input class="time-out {{ $isPending ? 'plain-input' : '' }}" type="time" name="new_clock_out" value="{{ $clockOut }}" @if($isPending) readonly @endif>
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
                            <input class="time-in {{ $isPending ? 'plain-input' : '' }}" type="time" name="breaks[{{ $index }}][start]" value="{{ $break['start'] }}" @if($isPending) readonly @endif>
                            <p class="mark">~</p>
                            <input class="time-out {{ $isPending ? 'plain-input' : '' }}" type="time" name="breaks[{{ $index }}][end]" value="{{ $break['end'] }}" @if($isPending) readonly @endif>
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
                        <textarea class="textarea {{ $isPending ? 'plain-input' : '' }}" name="remarks" id="" @if($isPending) readonly @endif>{{ $remarks }}</textarea>
                        @error('remarks')
                        <span class="error-message">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
            </table>
            <div class="button">
                @if($isPending)
                <p class="pending-message">*承認待ちのため修正はできません。</p>
                @else
                <button class="fixes-button">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection