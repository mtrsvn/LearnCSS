@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('kicker', 'Overview')

@section('header_actions')
    <a class="btn btn-muted" href="{{ route('admin.reports.index') }}">View reports</a>
    <a class="btn btn-primary" href="{{ route('admin.notifications.index') }}">Create announcement</a>
@endsection

@section('content')
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
                <div class="progress-track"><div class="progress-fill" style="width: {{ $avgProgress }}%;"></div></div>
                <span class="muted">{{ $avgProgress }}% across active learners</span>
            </div>
            <div class="list-item">
                <strong>Final exam pass rate</strong>
                <div class="progress-track"><div class="progress-fill" style="width: {{ $finalPassRate }}%;"></div></div>
                <span class="muted">{{ $finalPassRate }}% of final exam attempts passed</span>
            </div>
            <div class="list-item">
                <strong>Certificate eligibility</strong>
                <div class="progress-track"><div class="progress-fill" style="width: {{ $closeToCertPct }}%;"></div></div>
                <span class="muted">{{ $closeToCertPct }}% of active learners are close to certification</span>
            </div>
        </div>
    </section>

    <section class="panel">
        <p class="panel-label">Recent activity</p>
        <h2 class="panel-title">System Timeline</h2>
        <p class="panel-subtitle">Latest user and admin events recorded in real-time.</p>

        <div class="list-stack">
            @forelse ($activities as $activity)
                <div class="list-item">
                    <strong>{{ $activity['title'] }}</strong>
                    <span class="muted">{{ $activity['meta'] }}</span>
                </div>
            @empty
                <div class="list-item">
                    <span class="muted">No security or learning events recorded.</span>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
