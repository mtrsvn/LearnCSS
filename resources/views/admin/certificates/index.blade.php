@extends('admin.layouts.app')

@section('title', 'Certificate Management')
@section('kicker', 'Credentials')

@section('content')
@php
    $certificates = [
        ['code' => 'LC-CERT-2026-0388', 'user' => 'Maria Santos', 'email' => 'maria@example.com', 'issued' => 'May 19, 2026', 'status' => 'Verified'],
        ['code' => 'LC-CERT-2026-0387', 'user' => 'Paolo Dela Cruz', 'email' => 'paolo@example.com', 'issued' => 'May 18, 2026', 'status' => 'Verified'],
        ['code' => 'LC-CERT-2026-0386', 'user' => 'Anne Lim', 'email' => 'anne@example.com', 'issued' => 'May 17, 2026', 'status' => 'Revoked'],
    ];
@endphp

<div class="stat-grid">
    <article class="metric-card"><p class="metric-label">Issued certificates</p><p class="metric-value">388</p><p class="metric-note">All time</p></article>
    <article class="metric-card"><p class="metric-label">Verified</p><p class="metric-value">382</p><p class="metric-note">Publicly valid</p></article>
    <article class="metric-card"><p class="metric-label">Revoked</p><p class="metric-value">6</p><p class="metric-note">Admin disabled</p></article>
    <article class="metric-card"><p class="metric-label">Pending review</p><p class="metric-value">12</p><p class="metric-note">Eligible users waiting</p></article>
</div>

<section class="panel" style="margin-top: 18px;">
    <div class="toolbar">
        <div>
            <p class="panel-label">Issued list</p>
            <h2 class="panel-title">Certificates</h2>
        </div>
        <div class="toolbar-group">
            <input type="search" placeholder="Search code or user" style="width: 240px;">
            <button class="btn btn-muted" type="button">Export certificates</button>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Certificate code</th><th>User</th><th>Email</th><th>Issue date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($certificates as $certificate)
                    <tr>
                        <td><strong>{{ $certificate['code'] }}</strong></td>
                        <td>{{ $certificate['user'] }}</td>
                        <td>{{ $certificate['email'] }}</td>
                        <td>{{ $certificate['issued'] }}</td>
                        <td><span class="status {{ $certificate['status'] === 'Verified' ? 'success' : 'danger' }}">{{ $certificate['status'] }}</span></td>
                        <td><div class="actions"><button class="btn btn-primary" type="button">Verify</button><button class="btn btn-danger" type="button">Revoke</button></div></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
