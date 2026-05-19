@extends('admin.layouts.app')

@section('title', 'Topics and Lessons')
@section('kicker', 'Content Management')

@section('header_actions')
    <a class="btn btn-muted" href="{{ route('admin.content.index') }}">Content overview</a>
    <button class="btn btn-primary" type="button">Add topic</button>
@endsection

@section('content')
@php
    $topics = [
        ['id' => 1, 'title' => 'CSS Introduction', 'lessons' => 2, 'video' => 'https://www.youtube.com/embed/1Rs2ND1ryYc', 'status' => 'Published'],
        ['id' => 2, 'title' => 'CSS Syntax Deep Dive', 'lessons' => 2, 'video' => 'https://www.youtube.com/embed/yfoY53QXEnI', 'status' => 'Published'],
        ['id' => 3, 'title' => 'CSS Colors', 'lessons' => 1, 'video' => 'https://www.youtube.com/embed/fD2Zp4baS24', 'status' => 'Published'],
        ['id' => 4, 'title' => 'CSS Backgrounds', 'lessons' => 1, 'video' => 'https://www.youtube.com/embed/yVIsP-O0n1M', 'status' => 'Draft review'],
    ];
@endphp

<div class="tabs">
    <a class="tab" href="{{ route('admin.content.index') }}">Overview</a>
    <a class="tab active" href="{{ route('admin.content.topics') }}">Topics and lessons</a>
    <a class="tab" href="{{ route('admin.content.quizzes') }}">Quizzes and final exam</a>
</div>

<div class="split-grid">
    <section class="panel">
        <div class="toolbar">
            <div>
                <p class="panel-label">Topics</p>
                <h2 class="panel-title">Lesson Catalog</h2>
            </div>
            <input type="search" placeholder="Search topic" style="width: 220px;">
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Order</th><th>Topic</th><th>Lessons</th><th>Primary video</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($topics as $topic)
                        <tr>
                            <td>{{ $topic['id'] }}</td>
                            <td><strong>{{ $topic['title'] }}</strong></td>
                            <td>{{ $topic['lessons'] }}</td>
                            <td><span class="muted">{{ $topic['video'] }}</span></td>
                            <td><span class="status {{ $topic['status'] === 'Published' ? 'success' : 'warning' }}">{{ $topic['status'] }}</span></td>
                            <td><div class="actions"><button class="btn btn-muted" type="button">Edit</button><button class="btn btn-warning" type="button">Archive</button></div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <aside class="panel">
        <p class="panel-label">Edit lesson placeholder</p>
        <h2 class="panel-title">Lesson Details</h2>
        <p class="panel-subtitle">Use this UI later with a real lesson record.</p>
        <div class="form-grid">
            <div class="field full"><label>Topic title</label><input type="text" value="CSS Introduction"></div>
            <div class="field full"><label>Lesson title</label><input type="text" value="CSS Home and Introduction"></div>
            <div class="field full"><label>YouTube embed link</label><input type="url" value="https://www.youtube.com/embed/1Rs2ND1ryYc"></div>
            <div class="field full"><label>Study notes</label><textarea>Lorem ipsum lesson notes placeholder from the current user-side prototype.</textarea></div>
            <div class="field"><label>Status</label><select><option>Published</option><option>Draft</option><option>Archived</option></select></div>
            <div class="field"><label>Sort order</label><input type="number" value="1"></div>
        </div>
        <div class="actions" style="margin-top: 14px;">
            <button class="btn btn-primary" type="button">Save lesson</button>
            <button class="btn btn-muted" type="button">Preview</button>
        </div>
    </aside>
</div>
@endsection
