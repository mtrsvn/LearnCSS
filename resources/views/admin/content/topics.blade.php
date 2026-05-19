@extends('admin.layouts.app')

@section('title', 'Topics and Lessons')
@section('kicker', 'Content Management')

@section('header_actions')
    <a class="btn btn-muted" href="{{ route('admin.content.index') }}">Content overview</a>
    <button class="btn btn-primary" type="button" onclick="openAddTopicModal()">Add topic</button>
@endsection

@section('content')
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
                                <div class="dropdown">
                                    <button class="dropdown-trigger" type="button" onclick="toggleDropdown(this, event)">&#8942;</button>
                                    <div class="dropdown-menu">
                                        <button class="dropdown-item edit-topic-btn" type="button" 
                                                data-id="{{ $topic->id }}" 
                                                data-title="{{ $topic->title }}" 
                                                data-order="{{ $topic->sort_order }}">
                                            Edit Topic
                                        </button>
                                        <button class="dropdown-item edit-lessons-btn" type="button"
                                                data-id="{{ $topic->id }}"
                                                data-title="{{ $topic->title }}"
                                                data-lessons="{{ json_encode($topic->lessons) }}">
                                            Manage Lessons
                                        </button>
                                        <hr class="dropdown-divider">
                                        <form action="{{ route('admin.content.topics.destroy', $topic->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this topic and all its lessons/quizzes?');" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="dropdown-item danger" type="submit">Delete Topic</button>
                                        </form>
                                    </div>
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
<div id="addTopicModal" class="modal">
    <div class="modal-content">
        <form action="{{ route('admin.content.topics.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title">Add New Topic</h3>
                <button type="button" class="modal-close" onclick="closeModal('addTopicModal')">&times;</button>
            </div>
            <div class="modal-body">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-muted" onclick="closeModal('addTopicModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Topic</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT TOPIC MODAL -->
<div id="editTopicModal" class="modal">
    <div class="modal-content">
        <form id="editTopicForm" method="POST">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title">Edit Topic</h3>
                <button type="button" class="modal-close" onclick="closeModal('editTopicModal')">&times;</button>
            </div>
            <div class="modal-body">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-muted" onclick="closeModal('editTopicModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- MANAGE LESSONS MODAL -->
<div id="manageLessonsModal" class="modal" style="z-index: 1001;">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="modal-title">Manage Lessons: <span id="lessonsModalTopicTitle"></span></h3>
            <button type="button" class="modal-close" onclick="closeModal('manageLessonsModal')">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Lessons List -->
            <h4 style="margin: 0 0 10px 0;">Current Lessons</h4>
            <div class="table-wrap" style="margin-bottom: 20px; max-height: 250px; overflow-y: auto;">
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
            
            <form id="lessonForm" method="POST" action="{{ route('admin.content.lessons.store') }}">
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
                    <div class="field full">
                        <label for="lesson_notes">Interactive Study Notes</label>
                        <textarea id="lesson_notes" name="notes" placeholder="Write markdown or detailed HTML/plain notes here..."></textarea>
                    </div>
                </div>
                
                <div style="margin-top: 15px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" id="cancelLessonEditBtn" class="btn btn-muted" style="display: none;" onclick="resetLessonForm()">Cancel Edit</button>
                    <button type="submit" id="saveLessonBtn" class="btn btn-primary">Add Lesson</button>
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
                    
                    const editBtn = document.createElement('button');
                    editBtn.className = 'btn btn-muted';
                    editBtn.type = 'button';
                    editBtn.style.padding = '3px 6px';
                    editBtn.style.fontSize = '11px';
                    editBtn.style.minHeight = 'auto';
                    editBtn.textContent = 'Edit';
                    editBtn.addEventListener('click', () => {
                        // Enter Edit Lesson Mode
                        document.getElementById('lessonFormTitle').textContent = `Edit Lesson: ${l.title}`;
                        document.getElementById('lesson_title').value = l.title;
                        document.getElementById('lesson_sort_order').value = l.sort_order;
                        document.getElementById('lesson_video_url').value = l.video_url;
                        document.getElementById('lesson_notes').value = l.notes || '';
                        
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
                        <button class="btn btn-danger" type="submit" style="padding: 3px 6px; font-size: 11px; min-height: auto;">Delete</button>
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
        document.getElementById('lesson_notes').value = '';
        
        document.getElementById('lessonForm').action = "{{ route('admin.content.lessons.store') }}";
        document.getElementById('lesson_method').value = 'POST';
        
        document.getElementById('saveLessonBtn').textContent = 'Add Lesson';
        document.getElementById('cancelLessonEditBtn').style.display = 'none';
    }
</script>
@endsection
