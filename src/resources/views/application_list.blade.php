@extends('layouts.app')

@section('title')
<title>申請一覧</title>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/application_list.css') }}">
@endsection

@section('header')
@if (auth('admin')->check())
<nav class="admin-header-nav">
    <ul class="admin-header-nav__list">
        <li><a href="/admin/attendance/list" class="header-nav__attendance">勤怠一覧</a></li>
        <li><a href="/admin/staff/list" class="admin-header-nav__staff">スタッフ一覧</a></li>
        <li><a href="/stamp_correction_request/list" class="header-nav__application">申請一覧</a></li>
        <li>
            <form action="/admin/logout" class="logout" method="post">
                @csrf
                <button class="admin-header-nav__item--button">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
@elseif (auth('web')->check())
<nav class="header-nav">
    <ul class="header-nav__list">
        <li><a href="/attendance" class="header-nav__attendance">勤怠</a></li>
        <li><a href="/attendance/list" class="header-nav__staff">勤怠一覧</a></li>
        <li><a href="/stamp_correction_request/list" class="header-nav__application">申請</a></li>
        <li>
            <form action="/logout" class="logout" method="post">
                @csrf
                <button class="header-nav__item--button">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
@endif
@endsection

@section('content')
<div class="list-content">
    <div class="content__title">
        申請一覧
    </div>
    <div class="page-tag">
        <a href="/stamp_correction_request/list" class="{{ $tab === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="/stamp_correction_request/list/?tab=approved" class="{{ $tab === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
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
            @foreach($changes as $change)
            <tr class="record-data">
                <td class="table__status">
                    @if($change->status === 'pending')
                    承認待ち
                    @else
                    承認済み
                    @endif
                </td>
                <td class="table__name">
                    {{ $change->user->name }}
                </td>
                <td class="table__target-date">
                    {{ optional($change->attendance)->work_date
                    ? \Carbon\Carbon::parse($change->attendance->work_date)->format('Y/m/d') : '' }}
                </td>
                <td class="table__application-reason">
                    {{ $change->remarks }}
                </td>
                <td class="table__application-date">
                    {{ $change->created_at->format('Y/m/d') }}
                </td>
                <td class="table__detail">
                    @if (auth('admin')->check())
                    <a class="detail-link" href="/stamp_correction_request/approve/{{ $change->id }}">詳細</a>
                    @elseif (auth('web')->check())
                    <a class="detail-link" href="/attendance/{{ $change->attendance->id }}">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection