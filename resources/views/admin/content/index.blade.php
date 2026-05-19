@extends('admin.layouts.app')

@section('title', 'Content Management')
@section('kicker', 'Lessons and Exams')

@section('content')
<div class="tabs">
    <a class="tab active" href="{{ route('admin.content.index') }}">Overview</a>
    <a class="tab" href="{{ route('admin.content.topics') }}">Topics and lessons</a>
    <a class="tab" href="{{ route('admin.content.quizzes') }}">Quizzes and final exam</a>
</div>

<div class="page-grid three">
    <article class="panel">
        <p class="panel-label">Topics</p>
        <h2 class="panel-title">10 CSS Topics</h2>
        <p class="panel-subtitle">Manage topic titles, ordering, status, and lesson count.</p>
        <a class="btn btn-primary" href="{{ route('admin.content.topics') }}">Manage topics</a>
    </article>
    <article class="panel">
        <p class="panel-label">Lessons</p>
        <h2 class="panel-title">Video and Notes</h2>
        <p class="panel-subtitle">Update YouTube embeds, lesson labels, and study notes.</p>
        <a class="btn btn-primary" href="{{ route('admin.content.topics') }}">Edit lessons</a>
    </article>
    <article class="panel">
        <p class="panel-label">Assessments</p>
        <h2 class="panel-title">Quizzes and Exam</h2>
        <p class="panel-subtitle">Manage questions, choices, answers, and exam rules.</p>
        <a class="btn btn-primary" href="{{ route('admin.content.quizzes') }}">Manage quizzes</a>
    </article>
</div>

<section class="panel" style="margin-top: 18px;">
    <p class="panel-label">Suggested backend split</p>
    <h2 class="panel-title">Content Workflow</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Module</th><th>Admin action</th><th>Future database table</th><th>User-side effect</th></tr></thead>
            <tbody>
                <tr><td>Topics</td><td>Create, sort, publish, archive</td><td>topics</td><td>Dashboard topic cards</td></tr>
                <tr><td>Lessons</td><td>Edit video link and notes</td><td>lessons</td><td>Lesson screen video and notes</td></tr>
                <tr><td>Quizzes</td><td>Manage questions and answers</td><td>quiz_questions</td><td>Topic quiz flow</td></tr>
                <tr><td>Final exam</td><td>Set final question pool and pass rule</td><td>exam_questions</td><td>Certification exam</td></tr>
            </tbody>
        </table>
    </div>
</section>
@endsection
