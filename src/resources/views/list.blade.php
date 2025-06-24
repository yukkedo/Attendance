@extends('layouts.app')

@section('title')
<title>勤怠一覧</title>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
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
                <button class="header-nav__item--button">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
@endsection

@section('content')
<div class="list-content">
    <div class="content__title">
        勤怠一覧
    </div>
    <div class="content__date">
        <a href="{{ url('/attendance/list/' . $prevMonth) }}" class="prev-month"><img class="left-arrow" src="{{ asset('image/image_left.png') }}" alt="矢印"> 前月</a>
        <div class="date-month">
            <img class="img-calender" src="{{ asset('image/image_month.png') }}" alt="カレンダー">
            <span class="date-calender">{{ $currentDate }}</span>
        </div>
        <a href="{{ url('/attendance/list/' . $nextMonth) }}" class=" next-month">翌月 <img class="right-arrow" src="{{ asset('image/image_right.png') }}" alt="矢印"></a>
    </div>
    <div class="content__record">
        <table class="record__table">
            <tr class="record-title">
                <th class="table__title">日付</th>
                <th class="table__title">出勤</th>
                <th class="table__title">退勤</th>
                <th class="table__title">休憩</th>
                <th class="table__title">合計</th>
                <th class="table__title">詳細</th>
            </tr>
            @foreach($dailyData as $day)
            <tr class="record-data">
                <td class="table__date">{{ $day->formatted_date  }}</td>
                <td class="table__work-in">{{ $day->clock_in ? \Carbon\Carbon::parse($day->clock_in)->format('H:i') : '' }}</td>
                <td class="table__work-out">{{ $day->clock_out ? \Carbon\Carbon::parse($day->clock_out)->format('H:i') : '' }}</td>
                <td class="table__break">{{ $day->break_time ?? '' }}</td>
                <td class="table__total">{{ $day->work_time ?? '' }}</td>
                <td class="table__detail">
                    @if(isset($day->id))
                    <a class="detail-link" href="/attendance/{{ $day->id }}">詳細</a>
                    @else
                    <a class="detail-link">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection