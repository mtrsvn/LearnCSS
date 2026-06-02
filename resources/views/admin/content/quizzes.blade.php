@extends('admin.layouts.app')

@section('title', 'Quizzes and Final Exam')
@section('kicker', 'Assessment Management')

@section('header_actions')
    <button class="btn btn-primary" type="button" onclick="openAddQuestionModal()">Add question</button>
@endsection

@section('content')
@php
    $isAdmin = Auth::user()->is_admin || trim(strtolower(Auth::user()->role)) === 'admin';
    $isInstructor = trim(strtolower(Auth::user()->role)) === 'instructor' && !Auth::user()->is_admin;
@endphp
<div class="tabs">
    <a class="tab" href="{{ route('admin.content.index') }}">Overview</a>
    <a class="tab" href="{{ route('admin.content.topics') }}">Topics and lessons</a>
    <a class="tab active" href="{{ route('admin.content.quizzes') }}">Quizzes and final exam</a>
</div>

<div class="split-grid">
    <section class="panel">
        <p class="panel-label">Assessment sets</p>
        <h2 class="panel-title">Assessments</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Assessment Set</th><th>Questions count</th><th>Pass rule</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($topics as $topic)
                        <tr>
                            <td><strong>{{ $topic->title }} Quiz</strong></td>
                            <td>{{ $topic->quizQuestions()->count() }} questions</td>
                            <td>Completion marks topic done</td>
                            <td><span class="status success">Published</span></td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>Final Certification Exam</strong></td>
                        <td>{{ \App\Models\QuizQuestion::whereNull('topic_id')->count() }} questions</td>
                        <td>Perfect score required (100%)</td>
                        <td><span class="status success">Published</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <aside class="panel">
        <p class="panel-label">Curriculum parameters</p>
        <h2 class="panel-title">Assessment Scope</h2>
        <p class="panel-subtitle">Current standards configured in database:</p>
        <div class="list-stack">
            <div class="list-item">
                <strong>Topic Quiz Rules</strong>
                <span class="muted">Learners must complete the quiz under each topic. Correct answers register the topic as completed in their dashboard.</span>
            </div>
            <div class="list-item">
                <strong>Final Certification Exam</strong>
                <span class="muted">Requires 100% correct answers (5/5). Success generates a cryptographically verifiable certification serial.</span>
            </div>
        </div>
    </aside>
</div>

<section class="panel" style="margin-top: 18px;">
    <p class="panel-label">Question bank</p>
    <h2 class="panel-title">Active Database Questions</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Correct Option Answer</th>
                    <th>Scope</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quizzes as $question)
                    <tr>
                        <td style="vertical-align: middle;"><strong>{{ $question->question }}</strong></td>
                        <td style="vertical-align: middle;">
                            @php
                                $opts = $question->options;
                                $ansIdx = $question->answer;
                                $ansText = isset($opts[$ansIdx]) ? $opts[$ansIdx] : 'Option ' . $ansIdx;
                            @endphp
                            <code>{{ $ansText }}</code>
                        </td>
                        <td style="vertical-align: middle;">
                            @if ($question->topic)
                                <span class="status info">{{ $question->topic->title }}</span>
                            @else
                                <span class="status warning">Final Exam</span>
                            @endif
                        </td>
                        <td style="vertical-align: middle;">
                            @if($question->status === 'approved')
                                <span class="status success">Approved</span>
                            @else
                                <span class="status warning">Pending</span>
                            @endif
                        </td>
                        <td style="vertical-align: middle;">
                            <div class="dropdown">
                                <button class="dropdown-trigger" type="button" onclick="toggleDropdown(this, event)">&#8942;</button>
                                <div class="dropdown-menu">
                                    @if($question->status === 'pending' && $isAdmin)
                                    <form action="{{ route('admin.content.quizzes.approve', $question->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button class="dropdown-item" type="submit" style="color: var(--correct);">Approve</button>
                                    </form>
                                    @endif
                                    
                                    @if(!($isAdmin && $question->status === 'pending'))
                                        <button class="dropdown-item edit-question-btn" type="button"
                                                data-id="{{ $question->id }}"
                                                data-question="{{ $question->question }}"
                                                data-topic-id="{{ $question->topic_id ?: '' }}"
                                                data-options="{{ json_encode($question->options) }}"
                                                data-answer="{{ $question->answer }}"
                                                {{ ($isInstructor && $question->status === 'pending') ? 'disabled' : '' }}
                                                style="{{ ($isInstructor && $question->status === 'pending') ? 'opacity: 0.5; cursor: not-allowed;' : '' }}">
                                            Edit Question
                                        </button>
                                        <hr class="dropdown-divider">
                                    @endif
                                    <form action="{{ route('admin.content.quizzes.destroy', $question->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this question?');" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="dropdown-item danger" type="submit">Delete Question</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted">No assessment questions configured.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<!-- ================= ADD / EDIT QUESTION MODAL ================= -->
<div id="questionModal" class="modal">
    <div class="modal-content" style="max-width: 650px;">
        <form id="questionForm" method="POST" action="{{ route('admin.content.quizzes.store') }}">
            @csrf
            <input type="hidden" id="question_method" name="_method" value="POST">
            
            <div class="modal-header">
                <h3 id="modalTitle" class="modal-title">Add New Question</h3>
                <button type="button" class="modal-close" onclick="closeModal('questionModal')">&times;</button>
            </div>
            
            <div class="modal-body">
                <div class="form-grid">
                    <div class="field full">
                        <label for="q_scope">Assessment Scope / Topic</label>
                        <select id="q_scope" name="topic_id" required>
                            <option value="">-- Final Certification Exam --</option>
                            @foreach ($topics as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->title }} Quiz</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field full">
                        <label for="q_text">Question Text</label>
                        <textarea id="q_text" name="question" required placeholder="e.g. Which CSS property is used to change the text color?"></textarea>
                    </div>

                    <div class="field">
                        <label for="q_opt0">Option 1 (Index 0)</label>
                        <input type="text" id="q_opt0" name="options[]" required placeholder="e.g. color">
                    </div>
                    <div class="field">
                        <label for="q_opt1">Option 2 (Index 1)</label>
                        <input type="text" id="q_opt1" name="options[]" required placeholder="e.g. text-color">
                    </div>
                    <div class="field">
                        <label for="q_opt2">Option 3 (Index 2)</label>
                        <input type="text" id="q_opt2" name="options[]" required placeholder="e.g. font-color">
                    </div>
                    <div class="field">
                        <label for="q_opt3">Option 4 (Index 3)</label>
                        <input type="text" id="q_opt3" name="options[]" required placeholder="e.g. background-color">
                    </div>

                    <div class="field full">
                        <label for="q_answer">Correct Answer Option</label>
                        <select id="q_answer" name="answer" required>
                            <option value="0">Option 1 (Index 0)</option>
                            <option value="1">Option 2 (Index 1)</option>
                            <option value="2">Option 3 (Index 2)</option>
                            <option value="3">Option 4 (Index 3)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-muted" onclick="closeModal('questionModal')">Cancel</button>
                <button type="submit" id="saveQuestionBtn" class="btn btn-primary">Create Question</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.add('open');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    function openAddQuestionModal() {
        document.getElementById('modalTitle').textContent = 'Add New Question';
        document.getElementById('q_scope').value = '';
        document.getElementById('q_text').value = '';
        document.getElementById('q_opt0').value = '';
        document.getElementById('q_opt1').value = '';
        document.getElementById('q_opt2').value = '';
        document.getElementById('q_opt3').value = '';
        document.getElementById('q_answer').value = '0';
        
        document.getElementById('questionForm').action = "{{ route('admin.content.quizzes.store') }}";
        document.getElementById('question_method').value = 'POST';
        document.getElementById('saveQuestionBtn').textContent = 'Create Question';
        
        openModal('questionModal');
    }

    document.querySelectorAll('.edit-question-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const question = btn.dataset.question;
            const topicId = btn.dataset.topicId;
            const options = JSON.parse(btn.dataset.options);
            const answer = btn.dataset.answer;

            document.getElementById('modalTitle').textContent = 'Edit Question';
            document.getElementById('q_scope').value = topicId;
            document.getElementById('q_text').value = question;
            
            // Populate options
            if (options && options.length >= 4) {
                document.getElementById('q_opt0').value = options[0];
                document.getElementById('q_opt1').value = options[1];
                document.getElementById('q_opt2').value = options[2];
                document.getElementById('q_opt3').value = options[3];
            } else {
                // Fallback in case of dynamic length or empty options
                document.getElementById('q_opt0').value = options[0] || '';
                document.getElementById('q_opt1').value = options[1] || '';
                document.getElementById('q_opt2').value = options[2] || '';
                document.getElementById('q_opt3').value = options[3] || '';
            }
            
            document.getElementById('q_answer').value = answer;
            
            document.getElementById('questionForm').action = `/admin/content/quizzes/${id}`;
            document.getElementById('question_method').value = 'POST'; // POST method with Route override
            document.getElementById('saveQuestionBtn').textContent = 'Save Question Changes';
            
            openModal('questionModal');
        });
    });
</script>
@endsection
