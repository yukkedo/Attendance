@extends('layouts.app')

@section('title')
<title>勤怠一覧</title>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_attendance.css') }}">
@endsection

@section('header')
<nav class="header-nav">
    <ul class="header-nav__list">
        <li><a href="" class="header-nav__attendance">勤怠一覧</a></li>
        <li><a href="" class="header-nav__list">スタッフ一覧</a></li>
        <li><a href="" class="header-nav__application">申請一覧</a></li>
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
        nameさんの勤怠
    </div>
    <div class="content__date">
        <a href="" class="prev-month"><img class="left-arrow" src="{{ asset('image/image_left.png') }}" alt="矢印"> 前月</a>
        <div class="date-month">
            <img class="img-calender" src="{{ asset('image/image_month.png') }}" alt="カレンダー">
            <span class="date-calender">2025/06</span>
        </div>
        <a href="" class="next-month">翌月 <img class="right-arrow" src="{{ asset('image/image_right.png') }}" alt="矢印"></a>
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
            <tr class="record-data">
                <td class="table__date">06/01(木)</td>
                <td class="table__work-in">09:00</td>
                <td class="table__work-out">18:00</td>
                <td class="table__break">1:00</td>
                <td class="table__total">8:00</td>
                <td class="table__detail">
                    <a class="detail-link" href="">詳細</a>
                </td>
            </tr>
        </table>
    </div>
    <div class="button">
        <button class="csv-button">CSV出力</button>
    </div>
</div>
@endsection