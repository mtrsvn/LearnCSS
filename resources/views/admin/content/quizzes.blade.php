@extends('admin.layouts.app')

@section('title', 'Quizzes and Final Exam')
@section('kicker', 'Assessment Management')

@section('header_actions')
    <a class="btn btn-muted" href="{{ route('admin.content.index') }}">Content overview</a>
    <button class="btn btn-primary" type="button">Add question</button>
@endsection

@section('content')
@php
    $quizSets = [
        ['name' => 'CSS Introduction Quiz', 'questions' => 2, 'pass_rule' => 'Completion marks topic done', 'status' => 'Published'],
        ['name' => 'CSS Colors Quiz', 'questions' => 2, 'pass_rule' => 'Completion marks topic done', 'status' => 'Published'],
        ['name' => 'Final Certification Exam', 'questions' => 5, 'pass_rule' => 'Perfect score required', 'status' => 'Published'],
    ];
    $questions = [
        ['question' => 'What does CSS stand for?', 'answer' => 'Cascading Style Sheets', 'type' => 'Topic quiz'],
        ['question' => 'Which property changes background color?', 'answer' => 'background-color', 'type' => 'Topic quiz'],
        ['question' => 'What is the correct CSS syntax?', 'answer' => 'body {color: black;}', 'type' => 'Final exam'],
    ];
@endphp

<div class="tabs">
    <a class="tab" href="{{ route('admin.content.index') }}">Overview</a>
    <a class="tab" href="{{ route('admin.content.topics') }}">Topics and lessons</a>
    <a class="tab active" href="{{ route('admin.content.quizzes') }}">Quizzes and final exam</a>
</div>

<div class="page-grid two">
    <section class="panel">
        <p class="panel-label">Quiz sets</p>
        <h2 class="panel-title">Assessments</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Assessment</th><th>Questions</th><th>Pass rule</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($quizSets as $set)
                        <tr>
                            <td><strong>{{ $set['name'] }}</strong></td>
                            <td>{{ $set['questions'] }}</td>
                            <td>{{ $set['pass_rule'] }}</td>
                            <td><span class="status success">{{ $set['status'] }}</span></td>
                            <td><button class="btn btn-muted" type="button">Manage</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <p class="panel-label">Question editor</p>
        <h2 class="panel-title">Question Details</h2>
        <div class="form-grid">
            <div class="field full"><label>Question</label><textarea>What does CSS stand for?</textarea></div>
            <div class="field"><label>Choice A</label><input value="Cascading Style Sheets"></div>
            <div class="field"><label>Choice B</label><input value="Creative Style System"></div>
            <div class="field"><label>Choice C</label><input value="Computer Style Sheets"></div>
            <div class="field"><label>Choice D</label><input value="Colorful Style Sheets"></div>
            <div class="field"><label>Correct answer</label><select><option>Choice A</option><option>Choice B</option><option>Choice C</option><option>Choice D</option></select></div>
            <div class="field"><label>Assessment type</label><select><option>Topic quiz</option><option>Final exam</option></select></div>
        </div>
        <div class="actions" style="margin-top: 14px;"><button class="btn btn-primary" type="button">Save question</button><button class="btn btn-muted" type="button">Duplicate</button></div>
    </section>
</div>

<section class="panel" style="margin-top: 18px;">
    <p class="panel-label">Question bank</p>
    <h2 class="panel-title">Sample Questions</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Question</th><th>Correct answer</th><th>Type</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($questions as $question)
                    <tr><td>{{ $question['question'] }}</td><td>{{ $question['answer'] }}</td><td>{{ $question['type'] }}</td><td><button class="btn btn-muted" type="button">Edit</button></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
