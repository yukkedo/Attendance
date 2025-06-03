@extends('layouts.app')

@section('title')
<title>スタッフ一覧(管理者)</title>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_list.css') }}">
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
        スタッフ一覧
    </div>
    <div class="content__record">
        <table class="record__table">
            <tr class="record-title">
                <th class="table__title">名前</th>
                <th class="table__title">メールアドレス</th>
                <th class="table__title">月次勤怠</th>
            </tr>
            @foreach($users as $user)
            <tr class="record-data">
                <td class="table__date">{{ $user->name }}</td>
                <td class="table__email">{{ $user->email }}</td>
                <td class="table__detail">
                    <a class="detail-link" href="{{ url('/admin/attendance/staff/' . $user->id . '/' . now()->format('Y-m')) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection