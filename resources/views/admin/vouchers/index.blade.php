@extends('admin.layouts.app')

@section('title', 'Voucher Management')
@section('kicker', 'Payments and Access')

@section('header_actions')
    <button class="btn btn-primary" type="button">Generate voucher</button>
@endsection

@section('content')
@php
    $vouchers = [
        ['code' => 'CSSM-X8Q2-LP9A', 'status' => 'Used', 'user' => 'john@example.com', 'amount' => 'PHP 299', 'date' => 'May 19, 2026'],
        ['code' => 'CSSM-K4M1-ZZ82', 'status' => 'Unused', 'user' => 'Unassigned', 'amount' => 'PHP 299', 'date' => 'May 18, 2026'],
        ['code' => 'CSSM-A7PP-Q2MN', 'status' => 'Expired', 'user' => 'maria@example.com', 'amount' => 'PHP 299', 'date' => 'Apr 30, 2026'],
    ];
    $transactions = [
        ['receipt' => 'TXN-2026-00091', 'buyer' => 'John Reyes', 'method' => 'GCash placeholder', 'amount' => 'PHP 299', 'status' => 'Paid'],
        ['receipt' => 'TXN-2026-00092', 'buyer' => 'Maria Santos', 'method' => 'Card placeholder', 'amount' => 'PHP 299', 'status' => 'Paid'],
        ['receipt' => 'TXN-2026-00093', 'buyer' => 'Rina Cruz', 'method' => 'Manual payment', 'amount' => 'PHP 299', 'status' => 'Pending'],
    ];
@endphp

<div class="split-grid">
    <section class="panel">
        <p class="panel-label">Voucher list</p>
        <h2 class="panel-title">Exam Access Vouchers</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Code</th><th>Status</th><th>Assigned user</th><th>Amount</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($vouchers as $voucher)
                        <tr>
                            <td><strong>{{ $voucher['code'] }}</strong></td>
                            <td><span class="status {{ $voucher['status'] === 'Used' ? 'success' : ($voucher['status'] === 'Unused' ? 'info' : 'danger') }}">{{ $voucher['status'] }}</span></td>
                            <td>{{ $voucher['user'] }}</td>
                            <td>{{ $voucher['amount'] }}</td>
                            <td>{{ $voucher['date'] }}</td>
                            <td><div class="actions"><button class="btn btn-muted" type="button">Assign</button><button class="btn btn-warning" type="button">Expire</button></div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <aside class="panel">
        <p class="panel-label">Generate or assign</p>
        <h2 class="panel-title">Voucher Form</h2>
        <div class="form-grid">
            <div class="field full"><label>Voucher prefix</label><input value="CSSM"></div>
            <div class="field"><label>Quantity</label><input type="number" value="1"></div>
            <div class="field"><label>Amount</label><input value="299"></div>
            <div class="field full"><label>Assign to user email</label><input placeholder="user@example.com"></div>
            <div class="field full"><label>Expiration date</label><input type="date"></div>
        </div>
        <div class="actions" style="margin-top: 14px;">
            <button class="btn btn-primary" type="button">Generate</button>
            <button class="btn btn-muted" type="button">Assign existing</button>
        </div>
    </aside>
</div>

<section class="panel" style="margin-top: 18px;">
    <p class="panel-label">Transactions</p>
    <h2 class="panel-title">Payment Records</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Receipt</th><th>Buyer</th><th>Method</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($transactions as $txn)
                    <tr><td>{{ $txn['receipt'] }}</td><td>{{ $txn['buyer'] }}</td><td>{{ $txn['method'] }}</td><td>{{ $txn['amount'] }}</td><td><span class="status {{ $txn['status'] === 'Paid' ? 'success' : 'warning' }}">{{ $txn['status'] }}</span></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
