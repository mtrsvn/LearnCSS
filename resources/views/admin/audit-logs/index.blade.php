@extends('admin.layouts.app')

@section('title', 'Audit Logs')
@section('kicker', 'Security Trail')

@section('content')
@php
    $logs = [
        ['action' => 'Updated lesson video URL', 'admin' => 'Admin User', 'module' => 'Content', 'target' => 'CSS Introduction', 'date' => 'May 19, 2026 11:20 AM', 'ip' => '127.0.0.1'],
        ['action' => 'Generated voucher', 'admin' => 'Admin User', 'module' => 'Vouchers', 'target' => 'CSSM-K4M1-ZZ82', 'date' => 'May 19, 2026 10:50 AM', 'ip' => '127.0.0.1'],
        ['action' => 'Revoked certificate', 'admin' => 'Admin User', 'module' => 'Certificates', 'target' => 'LC-CERT-2026-0386', 'date' => 'May 18, 2026 04:18 PM', 'ip' => '127.0.0.1'],
        ['action' => 'Deactivated user', 'admin' => 'Admin User', 'module' => 'Users', 'target' => 'alex@example.com', 'date' => 'May 18, 2026 03:40 PM', 'ip' => '127.0.0.1'],
    ];
@endphp

<section class="panel">
    <div class="toolbar">
        <div>
            <p class="panel-label">Admin activity</p>
            <h2 class="panel-title">Action Log</h2>
        </div>
        <div class="toolbar-group">
            <input type="search" placeholder="Search logs" style="width: 220px;">
            <select style="width: 160px;"><option>All modules</option><option>Users</option><option>Content</option><option>Vouchers</option><option>Certificates</option></select>
            <button class="btn btn-muted" type="button">Export logs</button>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Action</th><th>Admin name</th><th>Module</th><th>Affected record</th><th>Date/time</th><th>IP</th></tr></thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td><strong>{{ $log['action'] }}</strong></td>
                        <td>{{ $log['admin'] }}</td>
                        <td>{{ $log['module'] }}</td>
                        <td>{{ $log['target'] }}</td>
                        <td>{{ $log['date'] }}</td>
                        <td>{{ $log['ip'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
