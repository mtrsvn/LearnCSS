@extends('admin.layouts.app')

@section('title', 'User Management')
@section('kicker', 'Accounts')

@section('content')
<style>
    .role-form { display: flex; align-items: center; gap: 0.5rem; margin: 0; }
    .role-select { background: rgba(255,255,255,0.05); border: 1.5px solid var(--border); color: var(--text); padding: 0.3rem 0.5rem; border-radius: 8px; font-size: 0.8rem; outline: none; }
    body.light-mode .role-select { background: rgba(0,0,0,0.05); }
    .actions { display: flex; gap: 0.5rem; justify-content: flex-end; }
</style>

<div class="stat-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
    <article class="metric-card">
        <p class="metric-label">All users</p>
        <p class="metric-value">{{ number_format($stats['total']) }}</p>
        <p class="metric-note">Total platform accounts</p>
    </article>
    <article class="metric-card">
        <p class="metric-label">Active users</p>
        <p class="metric-value" style="background: var(--correct); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ number_format($stats['active']) }}</p>
        <p class="metric-note">Currently active</p>
    </article>
    <article class="metric-card">
        <p class="metric-label">Inactive users</p>
        <p class="metric-value" style="background: var(--text-muted); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ number_format($stats['inactive']) }}</p>
        <p class="metric-note">Dormant or disabled</p>
    </article>
</div>

<section class="panel">
    <div class="toolbar" style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 1rem;">
        <div>
            <p class="panel-label">Directory</p>
            <h2 class="panel-title">All Users</h2>
        </div>
        <form action="{{ route('admin.users.index') }}" method="GET" class="toolbar-group" style="gap: 0.5rem; display: flex;">
            <input type="search" name="search" placeholder="Search name, email..." value="{{ request('search') }}" style="background: rgba(255,255,255,0.02); border: 1.5px solid var(--border); color: var(--text); padding: 0.55rem 1rem; border-radius: 8px; font-family: inherit; font-size: 0.85rem; outline: none; width: 220px;">
            @if(request()->anyFilled(['search']))
                <a href="{{ route('admin.users.index') }}" class="btn-ghost" style="padding: 0.55rem 1rem; font-size: 0.85rem; display: inline-flex; align-items: center; justify-content: center;">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Affiliation</th>
                    <th>Role</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td style="font-weight: 600;">{{ $user['name'] }}</td>
                        <td style="color: var(--text-muted);">{{ $user['email'] }}</td>
                        <td>{{ $user['affiliation'] }}</td>
                        <td>
                            <form action="{{ route('admin.users.role', $user['id']) }}" method="POST" class="role-form">
                                @csrf
                                <select name="role" class="role-select">
                                    <option value="student" {{ strtolower($user['role']) === 'student' ? 'selected' : '' }} style="color: #000;">Student</option>
                                    <option value="instructor" {{ strtolower($user['role']) === 'instructor' ? 'selected' : '' }} style="color: #000;">Instructor</option>
                                    <option value="admin" {{ strtolower($user['role']) === 'admin' ? 'selected' : '' }} style="color: #000;">Admin</option>
                                </select>
                                <button type="submit" class="btn-ghost" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">Save</button>
                            </form>
                        </td>
                        <td style="text-align: right;">
                            <div class="actions">
                                <a class="btn-ghost" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" href="{{ route('admin.users.show', $user['id']) }}">View</a>
                                <form action="{{ route('admin.users.toggle', $user['id']) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button class="btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; {{ $user['status'] === 'Active' ? 'background: rgba(239,68,68,0.1); color: var(--wrong); border: 1px solid rgba(239,68,68,0.2);' : 'background: rgba(16,185,129,0.1); color: var(--correct); border: 1px solid rgba(16,185,129,0.2);' }}" type="submit">
                                        {{ $user['status'] === 'Inactive' ? 'Activate' : 'Disable' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            No users found matching your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection