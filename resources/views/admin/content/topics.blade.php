@extends('admin.layouts.app')

@section('title', 'Topics and Lessons')
@section('kicker', 'Content Management')

@section('header_actions')
    <a class="btn btn-muted" href="{{ route('admin.content.index') }}">Content overview</a>
    <button class="btn btn-primary" type="button" onclick="openAddTopicModal()">Add topic</button>
@endsection

@section('content')
<style>
    input[type="file"] {
        display: flex;
        align-items: center;
    }
    input[type="file"]::file-selector-button {
        background: var(--surface-solid);
        border: 1px solid var(--border);
        color: var(--text);
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        cursor: pointer;
        margin: 0;
        margin-right: 1rem;
        font-family: inherit;
        transition: all 0.2s;
        vertical-align: middle;
    }
    input[type="file"]::file-selector-button:hover {
        background: rgba(255,255,255,0.1);
    }
    body.light-mode input[type="file"]::file-selector-button {
        background: rgba(0,0,0,0.05);
    }
    body.light-mode input[type="file"]::file-selector-button:hover {
        background: rgba(0,0,0,0.1);
    }
</style>
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
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Order</th><th>Topic</th><th>Lessons</th><th>Primary video</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse ($topics as $topic)
                        <tr>
                            <td style="vertical-align: middle;">{{ $topic->sort_order }}</td>
                            <td style="vertical-align: middle;"><strong>{{ $topic->title }}</strong></td>
                            <td style="vertical-align: middle;">{{ $topic->lessons->count() }} lessons</td>
                            <td style="vertical-align: middle;">
                                @if ($topic->lessons->first())
                                    <span class="muted"><code>{{ $topic->lessons->first()->video_url }}</code></span>
                                @else
                                    <span class="muted">No video configured</span>
                                @endif
                            </td>
                            <td style="vertical-align: middle;">
                                <div class="actions" style="display: flex; gap: 0.5rem; justify-content: flex-start;">
                                    <button class="btn-ghost edit-topic-btn" type="button" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;"
                                            data-id="{{ $topic->id }}" 
                                            data-title="{{ $topic->title }}" 
                                            data-order="{{ $topic->sort_order }}">
                                        Edit Topic
                                    </button>
                                    <button class="btn-primary edit-lessons-btn" type="button" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;"
                                            data-id="{{ $topic->id }}"
                                            data-title="{{ $topic->title }}"
                                            data-lessons="{{ json_encode($topic->lessons) }}">
                                        Manage Lessons
                                    </button>
                                    <form action="{{ route('admin.content.topics.destroy', $topic->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this topic and all its lessons/quizzes?');" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-ghost" type="submit" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; color: var(--wrong);">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted">No topics found in database.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <aside class="panel">
        <p class="panel-label">Topic details</p>
        <h2 class="panel-title">Curriculum Scope</h2>
        <p class="panel-subtitle">Review content structures.</p>
        <div class="list-stack">
            <div class="list-item">
                <strong>Standard CSS Chapters</strong>
                <span class="muted">Manage and restructure CSS chapters dynamically. Changes propagate instantly to active student dashboards.</span>
            </div>
            <div class="list-item">
                <strong>Lessons management</strong>
                <span class="muted">Click on the "Lessons" button next to a topic to add, update, or remove tutorials, notes, and video configurations.</span>
            </div>
        </div>
    </aside>
</div>

<!-- ================= MODALS ================= -->

<!-- ADD TOPIC MODAL -->
<div id="addTopicModal" class="admin-modal">
    <div class="admin-modal-content">
        <form action="{{ route('admin.content.topics.store') }}" method="POST">
            @csrf
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Add New Topic</h3>
                <button type="button" class="admin-modal-close" onclick="closeModal('addTopicModal')">&times;</button>
            </div>
            <div class="admin-modal-body">
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="field">
                        <label for="add_title">Topic Title</label>
                        <input type="text" id="add_title" name="title" required placeholder="e.g. CSS Grid Layout">
                    </div>
                    <div class="field">
                        <label for="add_sort_order">Sort Order</label>
                        <input type="number" id="add_sort_order" name="sort_order" required value="{{ count($topics) + 1 }}">
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-ghost" onclick="closeModal('addTopicModal')">Cancel</button>
                <button type="submit" class="btn-primary">Create Topic</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT TOPIC MODAL -->
<div id="editTopicModal" class="admin-modal">
    <div class="admin-modal-content">
        <form id="editTopicForm" method="POST">
            @csrf
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Edit Topic</h3>
                <button type="button" class="admin-modal-close" onclick="closeModal('editTopicModal')">&times;</button>
            </div>
            <div class="admin-modal-body">
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="field">
                        <label for="edit_title">Topic Title</label>
                        <input type="text" id="edit_title" name="title" required placeholder="e.g. CSS Basics">
                    </div>
                    <div class="field">
                        <label for="edit_sort_order">Sort Order</label>
                        <input type="number" id="edit_sort_order" name="sort_order" required>
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-ghost" onclick="closeModal('editTopicModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- MANAGE LESSONS MODAL -->
<div id="manageLessonsModal" class="admin-modal" style="z-index: 1001;">
    <div class="admin-modal-content" style="max-width: 800px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Manage Lessons: <span id="lessonsModalTopicTitle"></span></h3>
            <button type="button" class="admin-modal-close" onclick="closeModal('manageLessonsModal')">&times;</button>
        </div>
        <div class="admin-modal-body">
            <!-- Lessons List -->
            <h4 style="margin: 0 0 10px 0;">Current Lessons</h4>
            <div class="table-wrap" style="margin-bottom: 20px;">
                <table class="data-table" style="min-width: 100%;">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Lesson Title</th>
                            <th>Video URL</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="lessonsTableBody">
                        <!-- Loaded dynamically -->
                    </tbody>
                </table>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--line); margin: 20px 0;">

            <!-- Add/Edit Lesson Form -->
            <h4 id="lessonFormTitle" style="margin: 0 0 15px 0;">Add New Lesson</h4>
            
            <form id="lessonForm" method="POST" action="{{ route('admin.content.lessons.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="lesson_topic_id" name="topic_id">
                <input type="hidden" id="lesson_method" name="_method" value="POST">
                
                <div class="form-grid">
                    <div class="field">
                        <label for="lesson_title">Lesson Title</label>
                        <input type="text" id="lesson_title" name="title" required placeholder="e.g. Intro to Box Model">
                    </div>
                    <div class="field">
                        <label for="lesson_sort_order">Sort Order</label>
                        <input type="number" id="lesson_sort_order" name="sort_order" required value="1">
                    </div>
                    <div class="field full">
                        <label for="lesson_video_url">Video Embed URL</label>
                        <input type="text" id="lesson_video_url" name="video_url" required placeholder="e.g. https://www.youtube.com/embed/dQw4w9WgXcQ">
                    </div>
                    <div class="field full" style="border: 1px dashed var(--border); padding: 1rem; border-radius: 8px;">
                        <label for="lesson_documentation">Lesson Documentation File (Optional)</label>
                        <p class="muted" style="margin-top: 0; font-size: 0.8rem; margin-bottom: 0.5rem;">Upload a PDF, DOC, or ZIP file to be available to students in the Documentation tab.</p>
                        <input type="file" id="lesson_documentation" name="documentation" accept=".pdf,.doc,.docx,.zip,.txt" style="background: rgba(255,255,255,0.02); color: var(--text); padding: 0.3rem; border-radius: 8px; width: 100%; border: 1.5px solid var(--border); cursor: pointer; font-family: inherit; font-size: 0.85rem; height: 2.8rem; box-sizing: border-box;">
                        
                        <div id="current_documentation_info" style="display: none; margin-top: 0.5rem; font-size: 0.85rem; padding: 0.5rem; background: rgba(16, 185, 129, 0.1); border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.2); align-items: center; justify-content: space-between;">
                            <span><strong style="color: var(--correct);">Current File:</strong> <span id="current_doc_filename"></span></span>
                            <label style="display: inline-flex; align-items: center; gap: 0.3rem; margin: 0; font-size: 0.8rem; color: var(--wrong); cursor: pointer;">
                                <input type="checkbox" name="remove_documentation" value="1" style="width: auto; margin: 0;"> Remove file
                            </label>
                        </div>
                    </div>
                    <div class="field full">
                        <label for="lesson_notes">Interactive Study Notes</label>
                        <textarea id="lesson_notes" name="notes" placeholder="Write markdown or detailed HTML/plain notes here..." style="width: 100%; min-height: 120px; padding: 0.8rem; border-radius: 8px; border: 1.5px solid var(--border); background: rgba(255,255,255,0.02); color: var(--text); font-family: inherit; resize: vertical; outline: none; font-size: 0.85rem; line-height: 1.5;"></textarea>
                    </div>
                </div>
                
                <div style="margin-top: 15px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" id="cancelLessonEditBtn" class="btn-ghost" style="display: none;" onclick="resetLessonForm()">Cancel Edit</button>
                    <button type="submit" id="saveLessonBtn" class="btn-primary">Add Lesson</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Modal Helpers
    function openModal(id) {
        document.getElementById(id).classList.add('open');
    }
    
    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
        if (id === 'manageLessonsModal') {
            resetLessonForm();
        }
    }

    function openAddTopicModal() {
        openModal('addTopicModal');
    }

    // Topic Edit Bindings
    document.querySelectorAll('.edit-topic-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const title = btn.dataset.title;
            const order = btn.dataset.order;

            document.getElementById('edit_title').value = title;
            document.getElementById('edit_sort_order').value = order;
            document.getElementById('editTopicForm').action = `/admin/content/topics/${id}`;
            
            openModal('editTopicModal');
        });
    });

    // Lessons Edit Bindings
    document.querySelectorAll('.edit-lessons-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const topicId = btn.dataset.id;
            const topicTitle = btn.dataset.title;
            const lessons = JSON.parse(btn.dataset.lessons);

            document.getElementById('lessonsModalTopicTitle').textContent = topicTitle;
            document.getElementById('lesson_topic_id').value = topicId;

            const tbody = document.getElementById('lessonsTableBody');
            tbody.innerHTML = '';

            if (lessons.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="muted" style="text-align:center;">No lessons added to this topic yet. Use the form below to add one!</td></tr>';
            } else {
                lessons.forEach(l => {
                    const row = document.createElement('tr');
                    
                    const cellOrder = document.createElement('td');
                    cellOrder.textContent = l.sort_order;
                    
                    const cellTitle = document.createElement('td');
                    cellTitle.innerHTML = `<strong>${l.title}</strong>`;
                    
                    const cellVideo = document.createElement('td');
                    cellVideo.innerHTML = `<code style="font-size: 11px;">${l.video_url}</code>`;
                    
                    const cellActions = document.createElement('td');
                    cellActions.className = 'actions';
                    cellActions.style.display = 'flex';
                    cellActions.style.gap = '0.4rem';
                    cellActions.style.alignItems = 'center';
                    
                    const editBtn = document.createElement('button');
                    editBtn.className = 'btn-ghost';
                    editBtn.type = 'button';
                    editBtn.style.padding = '0.3rem 0.7rem';
                    editBtn.style.fontSize = '0.8rem';
                    editBtn.style.fontWeight = '500';
                    editBtn.style.border = '1px solid var(--border)';
                    editBtn.style.background = 'var(--surface-solid)';
                    editBtn.style.borderRadius = '6px';
                    editBtn.textContent = 'Edit';
                    editBtn.addEventListener('click', () => {
                        // Enter Edit Lesson Mode
                        document.getElementById('lessonFormTitle').textContent = `Edit Lesson: ${l.title}`;
                        document.getElementById('lesson_title').value = l.title;
                        document.getElementById('lesson_sort_order').value = l.sort_order;
                        document.getElementById('lesson_video_url').value = l.video_url;
                        document.getElementById('lesson_notes').value = l.notes || '';
                        
                        if (l.documentation_filename) {
                            document.getElementById('current_documentation_info').style.display = 'flex';
                            document.getElementById('current_doc_filename').textContent = l.documentation_filename;
                        } else {
                            document.getElementById('current_documentation_info').style.display = 'none';
                        }
                        
                        document.getElementById('lessonForm').action = `/admin/content/lessons/${l.id}`;
                        document.getElementById('lesson_method').value = 'POST'; // We can post to Laravel route
                        
                        document.getElementById('saveLessonBtn').textContent = 'Save Lesson Changes';
                        document.getElementById('cancelLessonEditBtn').style.display = 'inline-flex';
                    });

                    const deleteForm = document.createElement('form');
                    deleteForm.action = `/admin/content/lessons/${l.id}`;
                    deleteForm.method = 'POST';
                    deleteForm.style.display = 'inline-block';
                    deleteForm.style.margin = '0';
                    deleteForm.onsubmit = () => confirm('Are you sure you want to delete this lesson?');
                    deleteForm.innerHTML = `
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button class="btn btn-danger" type="submit" style="padding: 0.3rem 0.7rem; font-size: 0.8rem; font-weight: 500; min-height: auto; border-radius: 6px; background: var(--wrong); color: #fff; border: 1px solid var(--wrong); cursor: pointer; transition: all 0.2s;">Delete</button>
                    `;
                    
                    cellActions.appendChild(editBtn);
                    cellActions.appendChild(deleteForm);
                    
                    row.appendChild(cellOrder);
                    row.appendChild(cellTitle);
                    row.appendChild(cellVideo);
                    row.appendChild(cellActions);
                    
                    tbody.appendChild(row);
                });
            }

            openModal('manageLessonsModal');
        });
    });

    function resetLessonForm() {
        document.getElementById('lessonFormTitle').textContent = 'Add New Lesson';
        document.getElementById('lesson_title').value = '';
        document.getElementById('lesson_sort_order').value = '1';
        document.getElementById('lesson_video_url').value = '';
        document.getElementById('lesson_documentation').value = '';
        document.getElementById('current_documentation_info').style.display = 'none';
        document.getElementById('lesson_notes').value = '';
        
        document.getElementById('lessonForm').action = "{{ route('admin.content.lessons.store') }}";
        document.getElementById('lesson_method').value = 'POST';
        
        document.getElementById('saveLessonBtn').textContent = 'Add Lesson';
        document.getElementById('cancelLessonEditBtn').style.display = 'none';
    }
</script>
@endsection
