@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('kicker', 'Overview')

@section('header_actions')
    <a class="btn btn-muted" href="{{ route('admin.reports.index') }}">View reports</a>
    <a class="btn btn-primary" href="{{ route('admin.notifications.index') }}">Create announcement</a>
@endsection

@section('content')
@php
    $stats = [
        ['label' => 'Total users', 'value' => '1,248', 'note' => '+42 this month'],
        ['label' => 'Active users', 'value' => '1,012', 'note' => '81% active rate'],
        ['label' => 'Completed topics', 'value' => '8,960', 'note' => 'Across all learners'],
        ['label' => 'Quiz attempts', 'value' => '18,420', 'note' => 'Topic quiz submissions'],
        ['label' => 'Final exam attempts', 'value' => '470', 'note' => '128 pending retakes'],
        ['label' => 'Certificates issued', 'value' => '388', 'note' => 'Verified completions'],
        ['label' => 'Vouchers sold', 'value' => '625', 'note' => '512 already used'],
        ['label' => 'Voucher revenue', 'value' => 'PHP 186,875', 'note' => 'Static sample amount'],
    ];

    $activities = [
        ['title' => 'Maria Santos passed the final exam', 'meta' => 'Certificate LC-CERT-2026-0388 issued 12 minutes ago'],
        ['title' => 'Voucher CSSM-X8Q2-LP9A was redeemed', 'meta' => 'Used by john@example.com 28 minutes ago'],
        ['title' => 'Topic quiz completed', 'meta' => 'CSS Colors quiz scored 2/2 by alex@example.com'],
        ['title' => 'New user registration', 'meta' => 'Rina Cruz joined from University / School'],
        ['title' => 'Admin content update placeholder', 'meta' => 'Lesson video link ready for review'],
    ];
@endphp

<div class="notice">
    Current backend has no app-specific admin tables yet. These widgets use static sample data so you can build the admin experience first without touching the user-side prototype.
</div>

<div class="stat-grid">
    @foreach ($stats as $stat)
        <article class="metric-card">
            <p class="metric-label">{{ $stat['label'] }}</p>
            <p class="metric-value">{{ $stat['value'] }}</p>
            <p class="metric-note">{{ $stat['note'] }}</p>
        </article>
    @endforeach
</div>

<div class="page-grid two" style="margin-top: 18px;">
    <section class="panel">
        <p class="panel-label">Learning health</p>
        <h2 class="panel-title">Completion Snapshot</h2>
        <p class="panel-subtitle">High-level status for the user learning journey.</p>

        <div class="list-stack">
            <div class="list-item">
                <strong>Average topic completion</strong>
                <div class="progress-track"><div class="progress-fill" style="width: 72%;"></div></div>
                <span class="muted">72% across active learners</span>
            </div>
            <div class="list-item">
                <strong>Final exam pass rate</strong>
                <div class="progress-track"><div class="progress-fill" style="width: 64%;"></div></div>
                <span class="muted">64% of final exam attempts passed</span>
            </div>
            <div class="list-item">
                <strong>Certificate eligibility</strong>
                <div class="progress-track"><div class="progress-fill" style="width: 48%;"></div></div>
                <span class="muted">48% of active learners are close to certification</span>
            </div>
        </div>
    </section>

    <section class="panel">
        <p class="panel-label">Recent activity</p>
        <h2 class="panel-title">System Timeline</h2>
        <p class="panel-subtitle">Latest user and admin events to review.</p>

        <div class="list-stack">
            @foreach ($activities as $activity)
                <div class="list-item">
                    <strong>{{ $activity['title'] }}</strong>
                    <span class="muted">{{ $activity['meta'] }}</span>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
