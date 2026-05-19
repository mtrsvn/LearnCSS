@extends('admin.layouts.app')

@section('title', 'Notifications and Announcements')
@section('kicker', 'Communication')

@section('content')
@php
    $history = [
        ['title' => 'Final exam voucher reminder', 'audience' => 'Eligible users', 'sent_by' => 'Admin', 'date' => 'May 19, 2026 11:00 AM', 'status' => 'Sent'],
        ['title' => 'New CSS topic notes available', 'audience' => 'All users', 'sent_by' => 'Admin', 'date' => 'May 18, 2026 02:35 PM', 'status' => 'Sent'],
        ['title' => 'Scheduled maintenance', 'audience' => 'All users', 'sent_by' => 'Admin', 'date' => 'May 17, 2026 08:00 AM', 'status' => 'Draft'],
    ];
@endphp

<div class="split-grid">
    <section class="panel">
        <p class="panel-label">Create announcement</p>
        <h2 class="panel-title">Message Composer</h2>
        <p class="panel-subtitle">Placeholder form for future database-backed notifications.</p>
        <div class="form-grid">
            <div class="field"><label>Title</label><input placeholder="Announcement title"></div>
            <div class="field"><label>Audience</label><select><option>All users</option><option>Selected users</option><option>Eligible for final exam</option><option>Certificate holders</option></select></div>
            <div class="field full"><label>Selected user emails</label><input placeholder="maria@example.com, john@example.com"></div>
            <div class="field full"><label>Message</label><textarea placeholder="Write the announcement or notification message here."></textarea></div>
            <div class="field"><label>Delivery channel</label><select><option>In-app notification</option><option>Email placeholder</option><option>Both</option></select></div>
            <div class="field"><label>Schedule</label><input type="datetime-local"></div>
        </div>
        <div class="actions" style="margin-top: 14px;"><button class="btn btn-primary" type="button">Send notification</button><button class="btn btn-muted" type="button">Save draft</button></div>
    </section>

    <aside class="panel">
        <p class="panel-label">Targeting examples</p>
        <h2 class="panel-title">Audience Segments</h2>
        <div class="list-stack">
            <div class="list-item"><strong>All users</strong><span class="muted">Every registered learner.</span></div>
            <div class="list-item"><strong>Active learners</strong><span class="muted">Users with recent lesson or quiz activity.</span></div>
            <div class="list-item"><strong>Final exam eligible</strong><span class="muted">Users with all topics complete.</span></div>
            <div class="list-item"><strong>Voucher buyers</strong><span class="muted">Users with unused or used vouchers.</span></div>
        </div>
    </aside>
</div>

<section class="panel" style="margin-top: 18px;">
    <p class="panel-label">History</p>
    <h2 class="panel-title">Notification History</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Title</th><th>Audience</th><th>Admin</th><th>Date/time</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($history as $row)
                    <tr>
                        <td><strong>{{ $row['title'] }}</strong></td>
                        <td>{{ $row['audience'] }}</td>
                        <td>{{ $row['sent_by'] }}</td>
                        <td>{{ $row['date'] }}</td>
                        <td><span class="status {{ $row['status'] === 'Sent' ? 'success' : 'warning' }}">{{ $row['status'] }}</span></td>
                        <td><button class="btn btn-muted" type="button">View</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
