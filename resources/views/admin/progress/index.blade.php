@extends('admin.layouts.app')

@section('title', 'Progress and Records')
@section('kicker', 'Learning Records')

@section('content')
@php
    $records = [
        ['user' => 'Maria Santos', 'topics' => '10/10', 'quiz_avg' => '96%', 'exam_attempts' => 1, 'final_result' => 'Passed', 'eligible' => 'Yes'],
        ['user' => 'John Reyes', 'topics' => '8/10', 'quiz_avg' => '88%', 'exam_attempts' => 0, 'final_result' => 'Not taken', 'eligible' => 'Soon'],
        ['user' => 'Rina Cruz', 'topics' => '4/10', 'quiz_avg' => '75%', 'exam_attempts' => 0, 'final_result' => 'Not taken', 'eligible' => 'No'],
        ['user' => 'Alex Tan', 'topics' => '2/10', 'quiz_avg' => '65%', 'exam_attempts' => 0, 'final_result' => 'Not taken', 'eligible' => 'No'],
    ];
@endphp

<div class="stat-grid">
    <article class="metric-card"><p class="metric-label">Completed topics</p><p class="metric-value">8,960</p><p class="metric-note">Total topic completions</p></article>
    <article class="metric-card"><p class="metric-label">Average quiz score</p><p class="metric-value">84%</p><p class="metric-note">Across all topic quizzes</p></article>
    <article class="metric-card"><p class="metric-label">Exam attempts</p><p class="metric-value">470</p><p class="metric-note">Final certification attempts</p></article>
    <article class="metric-card"><p class="metric-label">Eligible users</p><p class="metric-value">143</p><p class="metric-note">Ready for certificate review</p></article>
</div>

<section class="panel" style="margin-top: 18px;">
    <div class="toolbar">
        <div>
            <p class="panel-label">Records</p>
            <h2 class="panel-title">User Progress Table</h2>
        </div>
        <div class="toolbar-group">
            <input type="search" placeholder="Search user" style="width: 220px;">
            <select style="width: 180px;"><option>All certificate states</option><option>Eligible</option><option>Not eligible</option></select>
            <button class="btn btn-muted" type="button">Export records</button>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>User</th><th>Completed topics</th><th>Quiz average</th><th>Final exam attempts</th><th>Pass/fail status</th><th>Certificate eligibility</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($records as $record)
                    <tr>
                        <td><strong>{{ $record['user'] }}</strong></td>
                        <td>{{ $record['topics'] }}</td>
                        <td>{{ $record['quiz_avg'] }}</td>
                        <td>{{ $record['exam_attempts'] }}</td>
                        <td><span class="status {{ $record['final_result'] === 'Passed' ? 'success' : 'neutral' }}">{{ $record['final_result'] }}</span></td>
                        <td><span class="status {{ $record['eligible'] === 'Yes' ? 'success' : ($record['eligible'] === 'Soon' ? 'warning' : 'neutral') }}">{{ $record['eligible'] }}</span></td>
                        <td><button class="btn btn-muted" type="button">View history</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
