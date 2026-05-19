@extends('admin.layouts.app')

@section('title', 'Reports')
@section('kicker', 'Analytics')

@section('header_actions')
    <button class="btn btn-muted" type="button">Export CSV</button>
    <button class="btn btn-muted" type="button">Export PDF</button>
@endsection

@section('content')
<div class="page-grid three">
    <article class="panel">
        <p class="panel-label">User completion</p>
        <h2 class="panel-title">Completion Report</h2>
        <p class="panel-subtitle">Track users by topic completion and certificate eligibility.</p>
        <button class="btn btn-primary" type="button">Generate report</button>
    </article>
    <article class="panel">
        <p class="panel-label">Exam pass/fail</p>
        <h2 class="panel-title">Exam Report</h2>
        <p class="panel-subtitle">Review exam attempts, failures, retakes, and pass rate.</p>
        <button class="btn btn-primary" type="button">Generate report</button>
    </article>
    <article class="panel">
        <p class="panel-label">Voucher sales</p>
        <h2 class="panel-title">Sales Report</h2>
        <p class="panel-subtitle">Summarize voucher purchases, redemptions, and revenue.</p>
        <button class="btn btn-primary" type="button">Generate report</button>
    </article>
</div>

<section class="panel" style="margin-top: 18px;">
    <div class="toolbar">
        <div>
            <p class="panel-label">Filters</p>
            <h2 class="panel-title">Report Builder</h2>
        </div>
        <div class="toolbar-group">
            <input type="date">
            <input type="date">
            <select style="width: 200px;"><option>All reports</option><option>User completion</option><option>Exam pass/fail</option><option>Voucher sales</option><option>Certificates</option></select>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Report</th><th>Sample metric</th><th>Last generated</th><th>Export</th></tr></thead>
            <tbody>
                <tr><td>User completion report</td><td>388 certified users</td><td>May 19, 2026 10:30 AM</td><td><div class="actions"><button class="btn btn-muted" type="button">CSV</button><button class="btn btn-muted" type="button">PDF</button></div></td></tr>
                <tr><td>Exam pass/fail report</td><td>64% pass rate</td><td>May 19, 2026 09:45 AM</td><td><div class="actions"><button class="btn btn-muted" type="button">CSV</button><button class="btn btn-muted" type="button">PDF</button></div></td></tr>
                <tr><td>Voucher sales report</td><td>PHP 186,875 revenue</td><td>May 18, 2026 05:10 PM</td><td><div class="actions"><button class="btn btn-muted" type="button">CSV</button><button class="btn btn-muted" type="button">PDF</button></div></td></tr>
                <tr><td>Certificate report</td><td>382 verified certificates</td><td>May 18, 2026 04:20 PM</td><td><div class="actions"><button class="btn btn-muted" type="button">CSV</button><button class="btn btn-muted" type="button">PDF</button></div></td></tr>
            </tbody>
        </table>
    </div>
</section>
@endsection
