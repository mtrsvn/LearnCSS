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
<div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
    <a href="{{ route('admin.content.index') }}" class="btn-ghost" style="padding: 0.5rem; color: var(--text-muted); text-decoration: none;"><i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Back to Courses</a>
    <h2 style="margin:0; font-family: 'Outfit', sans-serif;">{{ $course->title }}</h2>
</div>

<div class="tabs">
    <a class="tab" href="{{ route('admin.content.topics', $course->id) }}">Topics and subtopics</a>
    <a class="tab active" href="{{ route('admin.content.quizzes', $course->id) }}">Quizzes and final exam</a>
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
                </tbody>
            </table>
        </div>
    </section>

</div>

<section class="panel" style="margin-top: 18px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <div>
            <p class="panel-label">Question bank</p>
            <h2 class="panel-title" style="margin-bottom: 0;">Active Database Questions</h2>
        </div>
        <form action="{{ route('admin.content.quizzes', $course->id) }}" method="GET" style="display: flex; gap: 0.5rem; margin: 0;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search questions..." style="padding: 0.5rem 1rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--text); min-width: 250px;">
            <button type="submit" class="btn-primary" style="padding: 0.5rem 1rem;">Search</button>
        </form>
    </div>
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
                        <td style="vertical-align: middle; max-width: 350px; white-space: normal; line-height: 1.5; font-size: 0.95rem;"><strong>{{ $question->question }}</strong></td>
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
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                @if($question->status === 'pending' && $isAdmin)
                                <form action="{{ route('admin.content.quizzes.approve', $question->id) }}" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="btn-ghost" style="padding: 0.35rem 0.6rem; font-size: 0.75rem; color: var(--correct); border-color: var(--correct);">Approve</button>
                                </form>
                                @endif
                                
                                @if(!($isAdmin && $question->status === 'pending'))
                                    <button type="button" class="btn-ghost edit-question-btn" 
                                            data-id="{{ $question->id }}"
                                            data-question="{{ $question->question }}"
                                            data-topic-id="{{ $question->topic_id ?: '' }}"
                                            data-options="{{ json_encode($question->options) }}"
                                            data-answer="{{ $question->answer }}"
                                            {{ ($isInstructor && $question->status === 'pending') ? 'disabled' : '' }}
                                            style="padding: 0.35rem 0.6rem; font-size: 0.75rem; {{ ($isInstructor && $question->status === 'pending') ? 'opacity: 0.5; cursor: not-allowed;' : '' }}">
                                        Edit
                                    </button>
                                @endif
                                <form action="{{ route('admin.content.quizzes.destroy', $question->id) }}" method="POST" onsubmit="return confirmDelete(event, 'Are you sure you want to delete this question?');" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-ghost" style="padding: 0.35rem 0.6rem; font-size: 0.75rem; color: var(--wrong); border-color: var(--wrong);">Delete</button>
                                </form>
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
    <div style="margin-top: 1.5rem;">
        {{ $quizzes->appends(['search' => request('search')])->links('pagination::bootstrap-4') }}
    </div>
</section>

<!-- ================= ADD / EDIT QUESTION MODAL ================= -->
<div id="questionModal" class="admin-modal">
    <form id="questionForm" method="POST" action="{{ route('admin.content.quizzes.store') }}" class="admin-modal-content">
        @csrf
        <input type="hidden" id="question_method" name="_method" value="POST">
        
        <div class="admin-modal-header">
            <h3 class="admin-modal-title" id="modalTitle">Add New Question</h3>
            <button type="button" class="admin-modal-close" onclick="closeModal('questionModal')">&times;</button>
        </div>
        
        <div class="admin-modal-body">
            <div class="form-grid">
                <div class="field full">
                    <label for="q_scope">Assessment Scope / Topic</label>
                    <select id="q_scope" name="topic_id" required>
                        <option value="" disabled selected>-- Select a Topic --</option>
                        @foreach ($topics as $topic)
                            <option value="{{ $topic->id }}">{{ $topic->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field full">
                    <label for="q_text">Question Text</label>
                    <textarea id="q_text" name="question" required placeholder="e.g. Which CSS property is used to change the text color?" style="width: 100%; min-height: 80px; padding: 0.8rem; border-radius: 8px; border: 1.5px solid var(--border); background: rgba(255,255,255,0.02); color: var(--text); font-family: inherit; resize: vertical;"></textarea>
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
        
        <div class="admin-modal-footer">
            <button type="button" class="btn-ghost" onclick="closeModal('questionModal')">Cancel</button>
            <button type="submit" id="saveQuestionBtn" class="btn-primary">Create Question</button>
        </div>
    </form>
</div>

<script>
    // Modal Helpers
    function openModal(id) {
        document.getElementById(id).classList.add('open');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }

    function resetQuestionForm() {
        document.getElementById('modalTitle').textContent = 'Add New Question';
        document.getElementById('q_scope').value = '';
        document.getElementById('q_text').value = '';
        document.getElementById('q_opt0').value = '';
        document.getElementById('q_opt1').value = '';
        document.getElementById('q_opt2').value = '';
        document.getElementById('q_opt3').value = '';
        document.getElementById('q_answer').value = '0';
        
        document.getElementById('questionForm').action = "{{ route('admin.content.quizzes.store', $course->id) }}";
        document.getElementById('question_method').value = 'POST';
        document.getElementById('saveQuestionBtn').textContent = 'Create Question';
    }

    function openAddQuestionModal() {
        resetQuestionForm();
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
            
            document.getElementById('questionForm').action = `/admin/content/courses/{{ $course->id }}/quizzes/${id}`;
            document.getElementById('question_method').value = 'POST'; // POST method with Route override
            document.getElementById('saveQuestionBtn').textContent = 'Save Question Changes';
            
            openModal('questionModal');
        });
    });
</script>
@endsection
