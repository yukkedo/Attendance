@extends('layouts.app')

@section('title')
<title>申請一覧</title>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/application_list.css') }}">
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
        申請一覧
    </div>
    <div class="page-tag">
        <a href="">承認待ち</a>
        <a href="">承認済み</a>
    </div>
    <div class="content__record">
        <table class="record__table">
            <tr class="record-title">
                <th class="table__title">状態</th>
                <th class="table__title">名前</th>
                <th class="table__title">対象日時</th>
                <th class="table__title">申請理由</th>
                <th class="table__title">申請日時</th>
                <th class="table__title">詳細</th>
            </tr>
            <tr class="record-data">
                <td class="table__status">承認待ち</td>
                <td class="table__name">田中太郎</td>
                <td class="table__target-date">2025/06/01</td>
                <td class="table__application-reason">遅延のため</td>
                <td class="table__application-date">2025/06/01</td>
                <td class="table__detail">
                    <a class="detail-link" href="">詳細</a>
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection