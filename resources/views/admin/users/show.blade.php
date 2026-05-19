@extends('admin.layouts.app')

@section('title', 'User Profile')
@section('kicker', 'User Details')

@section('header_actions')
    <a class="btn btn-muted" href="{{ route('admin.users.index') }}">Back to users</a>
    <button class="btn btn-primary" type="button">Save changes</button>
@endsection

@section('content')
@php
    $sampleUsers = [
        1 => ['name' => 'Maria Santos', 'email' => 'maria@example.com', 'phone' => '+63 917 000 1144', 'birthdate' => '03/18/1999', 'affType' => 'University / School', 'affName' => 'University of Manila', 'status' => 'Active', 'progress' => 100],
        2 => ['name' => 'John Reyes', 'email' => 'john@example.com', 'phone' => '+63 918 221 9988', 'birthdate' => '07/09/1997', 'affType' => 'Company / Organization', 'affName' => 'Acme Corporation', 'status' => 'Active', 'progress' => 80],
        3 => ['name' => 'Rina Cruz', 'email' => 'rina@example.com', 'phone' => '+63 915 343 7710', 'birthdate' => '11/26/2000', 'affType' => 'University / School', 'affName' => 'State College', 'status' => 'Pending', 'progress' => 40],
    ];
    $id = (int) ($userId ?? request()->route('user', 1));
    $user = $sampleUsers[$id] ?? $sampleUsers[1];
    $topics = [
        ['title' => 'CSS Introduction', 'status' => 'Complete', 'score' => '2/2'],
        ['title' => 'CSS Syntax Deep Dive', 'status' => 'Complete', 'score' => '2/2'],
        ['title' => 'CSS Colors', 'status' => 'Complete', 'score' => '2/2'],
        ['title' => 'CSS Backgrounds', 'status' => 'In progress', 'score' => 'Pending'],
        ['title' => 'CSS Borders', 'status' => 'Locked', 'score' => 'Pending'],
    ];
    $history = [
        ['type' => 'Topic quiz', 'name' => 'CSS Introduction', 'score' => '2/2', 'result' => 'Passed', 'date' => 'May 18, 2026 09:12 AM'],
        ['type' => 'Topic quiz', 'name' => 'CSS Syntax Deep Dive', 'score' => '2/2', 'result' => 'Passed', 'date' => 'May 18, 2026 10:04 AM'],
        ['type' => 'Final exam', 'name' => 'Certification Exam', 'score' => '5/5', 'result' => 'Passed', 'date' => 'May 19, 2026 08:20 AM'],
    ];
@endphp

<div class="split-grid">
    <section class="panel">
        <p class="panel-label">Profile</p>
        <h2 class="panel-title">{{ $user['name'] }}</h2>
        <p class="panel-subtitle">View and update learner account information.</p>

        <div class="form-grid">
            <div class="field">
                <label>Full name</label>
                <input type="text" value="{{ $user['name'] }}">
            </div>
            <div class="field">
                <label>Email address</label>
                <input type="email" value="{{ $user['email'] }}">
            </div>
            <div class="field">
                <label>Phone number</label>
                <input type="text" value="{{ $user['phone'] }}">
            </div>
            <div class="field">
                <label>Birthdate</label>
                <input type="text" value="{{ $user['birthdate'] }}">
            </div>
            <div class="field">
                <label>Affiliation type</label>
                <select>
                    <option selected>{{ $user['affType'] }}</option>
                    <option>University / School</option>
                    <option>Company / Organization</option>
                </select>
            </div>
            <div class="field">
                <label>Affiliation name</label>
                <input type="text" value="{{ $user['affName'] }}">
            </div>
        </div>
    </section>

    <aside class="panel">
        <p class="panel-label">Account controls</p>
        <h2 class="panel-title">Status and Access</h2>
        <p class="panel-subtitle">Placeholder controls for future account moderation.</p>

        <div class="list-stack">
            <div class="list-item">
                <strong>Current status</strong>
                <span class="status {{ $user['status'] === 'Active' ? 'success' : 'warning' }}">{{ $user['status'] }}</span>
            </div>
            <div class="list-item">
                <strong>Learning progress</strong>
                <div class="progress-track"><div class="progress-fill" style="width: {{ $user['progress'] }}%;"></div></div>
                <span class="muted">{{ $user['progress'] }}% complete</span>
            </div>
            <button class="btn btn-warning" type="button">Deactivate account</button>
            <button class="btn btn-muted" type="button">Send password reset</button>
        </div>
    </aside>
</div>

<div class="page-grid two" style="margin-top: 18px;">
    <section class="panel">
        <p class="panel-label">Progress</p>
        <h2 class="panel-title">Topic Completion</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Topic</th><th>Status</th><th>Quiz score</th></tr></thead>
                <tbody>
                    @foreach ($topics as $topic)
                        <tr>
                            <td>{{ $topic['title'] }}</td>
                            <td><span class="status {{ $topic['status'] === 'Complete' ? 'success' : ($topic['status'] === 'In progress' ? 'info' : 'neutral') }}">{{ $topic['status'] }}</span></td>
                            <td>{{ $topic['score'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <p class="panel-label">History</p>
        <h2 class="panel-title">Quiz and Exam Attempts</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Type</th><th>Name</th><th>Score</th><th>Result</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach ($history as $row)
                        <tr>
                            <td>{{ $row['type'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['score'] }}</td>
                            <td><span class="status success">{{ $row['result'] }}</span></td>
                            <td>{{ $row['date'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
