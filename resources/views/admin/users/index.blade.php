@extends('admin.layouts.app')

@section('title', 'User Management')
@section('kicker', 'Accounts')

@section('header_actions')
    <button class="btn btn-muted" type="button">Export users</button>
    <button class="btn btn-primary" type="button">Add user placeholder</button>
@endsection

@section('content')
@php
    $users = [
        ['id' => 1, 'name' => 'Maria Santos', 'email' => 'maria@example.com', 'status' => 'Active', 'affiliation' => 'University of Manila', 'progress' => 100, 'quizzes' => 12, 'exams' => 1, 'certificate' => 'Issued'],
        ['id' => 2, 'name' => 'John Reyes', 'email' => 'john@example.com', 'status' => 'Active', 'affiliation' => 'Acme Corporation', 'progress' => 80, 'quizzes' => 9, 'exams' => 0, 'certificate' => 'Eligible soon'],
        ['id' => 3, 'name' => 'Rina Cruz', 'email' => 'rina@example.com', 'status' => 'Pending', 'affiliation' => 'State College', 'progress' => 40, 'quizzes' => 4, 'exams' => 0, 'certificate' => 'Not eligible'],
        ['id' => 4, 'name' => 'Alex Tan', 'email' => 'alex@example.com', 'status' => 'Inactive', 'affiliation' => 'Design Studio PH', 'progress' => 15, 'quizzes' => 2, 'exams' => 0, 'certificate' => 'Not eligible'],
    ];
@endphp

<div class="stat-grid">
    <article class="metric-card"><p class="metric-label">Registered users</p><p class="metric-value">1,248</p><p class="metric-note">All learner accounts</p></article>
    <article class="metric-card"><p class="metric-label">Active</p><p class="metric-value">1,012</p><p class="metric-note">Can access lessons</p></article>
    <article class="metric-card"><p class="metric-label">Pending review</p><p class="metric-value">36</p><p class="metric-note">Need verification UI</p></article>
    <article class="metric-card"><p class="metric-label">Inactive</p><p class="metric-value">200</p><p class="metric-note">Dormant or disabled</p></article>
</div>

<section class="panel" style="margin-top: 18px;">
    <div class="toolbar">
        <div>
            <p class="panel-label">Directory</p>
            <h2 class="panel-title">Learner Accounts</h2>
        </div>
        <form action="{{ route('admin.users.index') }}" method="GET" class="toolbar-group">
            <input type="search" name="search" placeholder="Search name, email, school..." style="width: 240px;" value="{{ request('search') }}">
            <select name="status" style="width: 160px;" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <select name="affiliation" style="width: 180px;" onchange="this.form.submit()">
                <option value="">All affiliations</option>
                <option value="school" {{ request('affiliation') === 'school' ? 'selected' : '' }}>University / School</option>
                <option value="company" {{ request('affiliation') === 'company' ? 'selected' : '' }}>Company / Organization</option>
            </select>
            @if(request()->anyFilled(['search', 'status', 'affiliation']))
                <a href="{{ route('admin.users.index') }}" class="btn btn-muted" style="min-height: 38px;">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Affiliation</th>
                    <th>Progress</th>
                    <th>Quiz attempts</th>
                    <th>Final exams</th>
                    <th>Certificate</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>
                            <strong>{{ $user['name'] }}</strong><br>
                            <span class="muted">{{ $user['email'] }}</span>
                        </td>
                        <td>{{ $user['affiliation'] }}</td>
                        <td>
                            <strong>{{ $user['progress'] }}%</strong>
                            <div class="progress-track"><div class="progress-fill" style="width: {{ $user['progress'] }}%;"></div></div>
                        </td>
                        <td>{{ $user['quizzes'] }}</td>
                        <td>{{ $user['exams'] }}</td>
                        <td>{{ $user['certificate'] }}</td>
                        <td>
                            <span class="status {{ $user['status'] === 'Active' ? 'success' : ($user['status'] === 'Pending' ? 'warning' : 'neutral') }}">{{ $user['status'] }}</span>
                        </td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-primary" href="{{ route('admin.users.show', $user['id']) }}">View</a>
                                <form action="{{ route('admin.users.toggle', $user['id']) }}" method="POST" style="margin: 0; display: inline;">
                                    @csrf
                                    <button class="btn {{ $user['status'] === 'Inactive' ? 'btn-primary' : 'btn-warning' }}" type="submit">
                                        {{ $user['status'] === 'Inactive' ? 'Activate' : 'Deactivate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
