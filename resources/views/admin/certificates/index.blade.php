@extends('admin.layouts.app')

@section('title', 'Certificate Management')
@section('kicker', 'Credentials')

@section('content')
<div class="stat-grid">
    <article class="metric-card">
        <p class="metric-label">Issued certificates</p>
        <p class="metric-value">{{ $certificates->count() }}</p>
        <p class="metric-note">All time</p>
    </article>
    <article class="metric-card">
        <p class="metric-label">Verified</p>
        <p class="metric-value">{{ $certificates->count() }}</p>
        <p class="metric-note">Publicly valid</p>
    </article>
    <article class="metric-card">
        <p class="metric-label">Revoked</p>
        <p class="metric-value">0</p>
        <p class="metric-note">Admin disabled</p>
    </article>
    <article class="metric-card">
        <p class="metric-label">Pending exam</p>
        <p class="metric-value">{{ \App\Models\User::where('is_admin', false)->count() - $certificates->count() }}</p>
        <p class="metric-note">Registered learners</p>
    </article>
</div>

<section class="panel" style="margin-top: 18px;">
    <div class="toolbar">
        <div>
            <p class="panel-label">Issued list</p>
            <h2 class="panel-title">Certificates</h2>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Certificate code</th><th>User</th><th>Email</th><th>Issue date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse ($certificates as $certificate)
                    <tr>
                        <td><strong>{{ $certificate->code }}</strong></td>
                        <td>{{ $certificate->user->name ?? 'N/A' }}</td>
                        <td>{{ $certificate->user->email ?? 'N/A' }}</td>
                        <td>{{ $certificate->issued_at ? \Carbon\Carbon::parse($certificate->issued_at)->format('M d, Y h:i A') : 'N/A' }}</td>
                        <td><span class="status success">Verified</span></td>
                        <td>
                            <div class="actions">
                                <a href="/" target="_blank" class="btn btn-primary">Verify Link</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">No certificates have been issued yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
