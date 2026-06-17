@extends('admin.layouts.app')

@section('title', 'Topics and Subtopics')
@section('kicker', 'Content Management')

@section('header_actions')
    <button class="btn btn-primary" type="button" onclick="openAddTopicModal()">Add topic</button>
@endsection

@section('content')
@php
    $isAdmin = Auth::user()->is_admin || trim(strtolower(Auth::user()->role)) === 'admin';
    $isInstructor = trim(strtolower(Auth::user()->role)) === 'instructor' && !Auth::user()->is_admin;
@endphp
<style>
    input[type="file"] { display: flex; align-items: center; }
    input[type="file"]::file-selector-button {
        background: var(--surface-solid); border: 1px solid var(--border); color: var(--text);
        padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; margin: 0; margin-right: 1rem;
        font-family: inherit; transition: all 0.2s; vertical-align: middle;
    }
    input[type="file"]::file-selector-button:hover { background: rgba(255,255,255,0.1); }
    body.light-mode input[type="file"]::file-selector-button { background: rgba(0,0,0,0.05); }
    body.light-mode input[type="file"]::file-selector-button:hover { background: rgba(0,0,0,0.1); }

    /* Modern Topic Cards Layout */
    .topic-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .topic-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .topic-card:hover {
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .topic-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        cursor: pointer;
        background: rgba(255, 255, 255, 0.01);
    }
    .topic-header:hover {
        background: rgba(255, 255, 255, 0.03);
    }
    .topic-drag-handle {
        color: var(--text-muted);
        cursor: grab;
        opacity: 0.5;
        transition: opacity 0.2s;
    }
    .topic-drag-handle:hover {
        opacity: 1;
    }
    .topic-info {
        flex: 1;
    }
    .topic-info h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .topic-info p {
        margin: 0.25rem 0 0;
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    .topic-meta {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .topic-stats {
        text-align: right;
    }
    .topic-stats strong {
        display: block;
        font-size: 1.1rem;
        color: var(--accent);
        line-height: 1;
    }
    .topic-stats span {
        font-size: 0.75rem;
        color: var(--text-muted);
    }
    .topic-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    /* Subtopics Panel (Accordion Content) */
    .subtopics-panel {
        display: none;
        border-top: 1px solid var(--border);
        background: rgba(0, 0, 0, 0.1);
    }
    .subtopics-panel.open {
        display: block;
    }
    .subtopics-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem 1.25rem;
        background: rgba(99, 102, 241, 0.04);
        border-bottom: 1px solid var(--border);
    }
    .subtopics-panel-header h4 { margin: 0; font-size: 0.9rem; font-weight: 600; color: var(--accent); }
    .subtopic-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid var(--border);
        transition: background 0.15s;
    }
    .subtopic-row:last-child { border-bottom: none; }
    .subtopic-row:hover { background: rgba(255, 255, 255, 0.02); }
    .subtopic-info strong { display: block; font-size: 0.95rem; color: var(--text); margin-bottom: 0.25rem; }
    .subtopic-badges { display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap; }
    .badge-video { background: rgba(16,185,129,0.12); color: #10b981; padding: 0.2rem 0.55rem; border-radius: 99px; font-size: 0.7rem; font-weight: 600; }
    .badge-pdf   { background: rgba(99,102,241,0.12); color: var(--accent); padding: 0.2rem 0.55rem; border-radius: 99px; font-size: 0.7rem; font-weight: 600; }
    .badge-pending { background: rgba(245,158,11,0.12); color: #f59e0b; padding: 0.2rem 0.55rem; border-radius: 99px; font-size: 0.7rem; font-weight: 600; }
    .subtopic-actions { display: flex; gap: 0.35rem; }
</style>

<div class="tabs">
    <a class="tab" href="{{ route('admin.content.index') }}">Overview</a>
    <a class="tab active" href="{{ route('admin.content.topics') }}">Topics and subtopics</a>
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

        @if(session('success'))
            <div style="margin: 0 0 1rem; padding: 0.75rem 1rem; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.25); border-radius: 8px; color: #10b981; font-size: 0.875rem; font-weight: 500;">
                ✓ {{ session('success') }}
            </div>
        @endif

        <div class="topic-list" id="topicsList">
            @forelse ($topics as $topic)
                <div class="topic-card" data-id="{{ $topic->id }}">
                    <!-- Topic Header (Always Visible) -->
                    <div class="topic-header" onclick="toggleSubtopicsPanel({{ $topic->id }})">
                        <div class="topic-drag-handle" onclick="event.stopPropagation()">
                            <i data-lucide="grip-vertical"></i>
                        </div>
                        <div class="sort-order-cell" style="font-weight: 600; color: var(--text-muted); width: 20px;">
                            {{ $topic->sort_order }}
                        </div>
                        <div class="topic-info">
                            <h3>
                                {{ $topic->title }}
                                @if($topic->status === 'approved')
                                    <span class="status success" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">Approved</span>
                                @else
                                    <span class="status warning" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">Pending</span>
                                @endif
                            </h3>
                            @if($topic->description)
                                <p>{{ Str::limit($topic->description, 80) }}</p>
                            @endif
                        </div>
                        
                        <div class="topic-meta">
                            <div class="topic-stats">
                                <strong>{{ $topic->subtopics->count() }}</strong>
                                <span>Subtopic{{ $topic->subtopics->count() !== 1 ? 's' : '' }}</span>
                            </div>
                            
                            <div class="topic-actions" onclick="event.stopPropagation()">
                                @if($topic->status === 'pending' && $isAdmin)
                                    <form action="{{ route('admin.content.topics.approve', $topic->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button class="btn-primary" type="submit" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; background: var(--correct); border-color: var(--correct);">Approve</button>
                                    </form>
                                @endif
                                
                                @if(!($isAdmin && $topic->status === 'pending'))
                                    <button class="btn-ghost edit-topic-btn" type="button" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; {{ ($isInstructor && $topic->status === 'pending') ? 'opacity: 0.5; cursor: not-allowed;' : '' }}"
                                        data-id="{{ $topic->id }}"
                                        data-title="{{ $topic->title }}"
                                        data-description="{{ $topic->description }}"
                                        {{ ($isInstructor && $topic->status === 'pending') ? 'disabled' : '' }}>
                                        Edit
                                    </button>
                                @endif
                                <form action="{{ route('admin.content.topics.destroy', $topic->id) }}" method="POST" onsubmit="return confirmDelete(event, 'Delete this topic and all its subtopics/quizzes?');" style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-ghost" type="submit" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; color: var(--wrong);">Delete</button>
                                </form>
                                <i data-lucide="chevron-down" style="color: var(--text-muted); margin-left: 0.5rem; transition: transform 0.2s;" id="chevron-{{ $topic->id }}"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Subtopics Panel (Collapsible) -->
                    <div class="subtopics-panel" id="subtopics-panel-{{ $topic->id }}">
                        <div class="subtopics-panel-header">
                            <h4>Subtopics ({{ $topic->subtopics->count() }})</h4>
                            <button type="button" class="btn-primary btn-sm" onclick="openAddSubtopicModal({{ $topic->id }}, '{{ addslashes($topic->title) }}')" style="padding: 0.3rem 0.7rem; font-size: 0.78rem;">+ Add Subtopic</button>
                        </div>

                        @forelse ($topic->subtopics as $sub)
                            <div class="subtopic-row">
                                <div class="subtopic-info">
                                    <strong>{{ $sub->sort_order }}. {{ $sub->title }}</strong>
                                    <div class="subtopic-badges" style="margin-top: 0.3rem;">
                                        @if($sub->video_url)
                                            <span class="badge-video">Video</span>
                                        @endif
                                        @if($sub->documentation_path)
                                            <span class="badge-pdf">{{ $sub->documentation_filename }}</span>
                                        @endif
                                        @if($sub->status === 'pending')
                                            <span class="badge-pending">Pending</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="subtopic-actions">
                                    @if($sub->status === 'pending' && $isAdmin)
                                        <form action="{{ route('admin.content.subtopics.approve', $sub->id) }}" method="POST" style="margin:0;">
                                            @csrf
                                            <button class="btn-ghost" type="submit" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; background: rgba(16,185,129,0.1); color: #10b981; border-color: rgba(16,185,129,0.3);">Approve</button>
                                        </form>
                                    @endif
                                    <button type="button" class="btn-ghost edit-subtopic-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                        data-id="{{ $sub->id }}"
                                        data-title="{{ $sub->title }}"
                                        data-video="{{ $sub->video_url }}"
                                        data-doc="{{ $sub->documentation_filename }}"
                                        data-sort="{{ $sub->sort_order }}">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.content.subtopics.destroy', $sub->id) }}" method="POST" onsubmit="return confirmDelete(event, 'Delete this subtopic?');" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-ghost" type="submit" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; color: var(--wrong);">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div style="padding: 1.25rem 1rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                                No subtopics yet. Click "+ Add Subtopic" to get started.
                            </div>
                        @endforelse
                    </div>
                </div>
            @empty
                <div style="padding: 3rem; text-align: center; color: var(--text-muted); background: var(--surface); border-radius: 12px; border: 1px dashed var(--border);">
                    No topics found. Create your first topic!
                </div>
            @endforelse
        </div>
    </section>

    <aside class="panel">
        <p class="panel-label">Course structure</p>
        <h2 class="panel-title">Coursera-Style Layout</h2>
        <p class="panel-subtitle" style="line-height: 1.6;">Each <strong>Topic</strong> contains multiple <strong>Subtopics</strong>. Each subtopic has its own video and PDF/slides document — just like Coursera.</p>
        <div class="list-stack">
            <div class="list-item">
                <strong>Topics</strong>
                <span class="muted">Top-level chapters (e.g., "CSS Box Model"). Contains subtopics, and has one quiz.</span>
            </div>
            <div class="list-item">
                <strong>Subtopics</strong>
                <span class="muted">Individual lessons inside a topic. Each has its own video and optional PDF/slides.</span>
            </div>
            <div class="list-item">
                <strong>Video + PDF per Subtopic</strong>
                <span class="muted">Students can switch between the video and the document slides for each subtopic.</span>
            </div>
        </div>
    </aside>
</div>

<!-- ================= MODALS ================= -->

<!-- ADD TOPIC MODAL -->
<div id="addTopicModal" class="admin-modal">
    <form action="{{ route('admin.content.topics.store') }}" method="POST" enctype="multipart/form-data" class="admin-modal-content">
        @csrf
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Add New Topic</h3>
            <button type="button" class="admin-modal-close" onclick="closeModal('addTopicModal')">&times;</button>
        </div>
        <div class="admin-modal-body">
            <div class="form-grid" style="grid-template-columns: 1fr;">
                <div class="field">
                    <label for="add_course_id">Course</label>
                    <select id="add_course_id" name="course_id" required style="width: 100%; padding: 0.8rem; border-radius: 8px; border: 1.5px solid var(--border); background: rgba(255,255,255,0.02); color: var(--text);">
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" style="color: black;">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label for="add_title">Topic Title</label>
                    <input type="text" id="add_title" name="title" required placeholder="e.g. CSS Box Model">
                </div>
                <div class="field">
                    <label for="add_description">Topic Description</label>
                    <textarea id="add_description" name="description" required placeholder="Brief overview shown on the dashboard..." style="width: 100%; min-height: 80px; padding: 0.8rem; border-radius: 8px; border: 1.5px solid var(--border); background: rgba(255,255,255,0.02); color: var(--text); font-family: inherit; resize: vertical;"></textarea>
                </div>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn-ghost" onclick="closeModal('addTopicModal')">Cancel</button>
            <button type="submit" class="btn-primary">Create Topic</button>
        </div>
    </form>
</div>

<!-- EDIT TOPIC MODAL -->
<div id="editTopicModal" class="admin-modal">
    <form id="editTopicForm" method="POST" enctype="multipart/form-data" class="admin-modal-content">
        @csrf
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Edit Topic</h3>
            <button type="button" class="admin-modal-close" onclick="closeModal('editTopicModal')">&times;</button>
        </div>
        <div class="admin-modal-body">
            <div class="form-grid" style="grid-template-columns: 1fr;">
                <div class="field">
                    <label for="edit_title">Topic Title</label>
                    <input type="text" id="edit_title" name="title" required placeholder="e.g. CSS Box Model">
                </div>
                <div class="field">
                    <label for="edit_description">Topic Description</label>
                    <textarea id="edit_description" name="description" required placeholder="Brief overview..." style="width: 100%; min-height: 80px; padding: 0.8rem; border-radius: 8px; border: 1.5px solid var(--border); background: rgba(255,255,255,0.02); color: var(--text); font-family: inherit; resize: vertical;"></textarea>
                </div>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn-ghost" onclick="closeModal('editTopicModal')">Cancel</button>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<!-- ADD SUBTOPIC MODAL -->
<div id="addSubtopicModal" class="admin-modal">
    <form id="addSubtopicForm" action="{{ route('admin.content.subtopics.store') }}" method="POST" enctype="multipart/form-data" class="admin-modal-content">
        @csrf
        <input type="hidden" id="add_sub_topic_id" name="topic_id">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Add Subtopic</h3>
            <button type="button" class="admin-modal-close" onclick="closeModal('addSubtopicModal')">&times;</button>
        </div>
        <div class="admin-modal-body">
            <p class="muted" id="add_sub_topic_label" style="margin-bottom: 1rem; font-size: 0.85rem;"></p>
            <div class="form-grid" style="grid-template-columns: 1fr;">
                <div class="field">
                    <label for="add_sub_title">Subtopic Title</label>
                    <input type="text" id="add_sub_title" name="title" required placeholder="e.g. Introduction to the Box Model">
                </div>
                <div class="field">
                    <label for="add_sub_video">Video URL (YouTube)</label>
                    <input type="text" id="add_sub_video" name="video_url" placeholder="e.g. https://www.youtube.com/watch?v=...">
                    <p class="muted" style="font-size: 0.78rem; margin-top: 0.3rem;">Paste a YouTube watch or embed URL.</p>
                </div>
                <div class="field" style="border: 1px dashed var(--border); padding: 1rem; border-radius: 8px;">
                    <label for="add_sub_doc">PDF / Slides (Optional)</label>
                    <p class="muted" style="margin-top: 0; font-size: 0.8rem; margin-bottom: 0.5rem;">Upload a PDF or presentation file for this subtopic.</p>
                    <input type="file" id="add_sub_doc" name="documentation" accept=".pdf,.ppt,.pptx,.doc,.docx,image/*" style="background: rgba(255,255,255,0.02); color: var(--text); padding: 0.3rem; border-radius: 8px; width: 100%; border: 1.5px solid var(--border); cursor: pointer; font-family: inherit; font-size: 0.85rem; height: 2.8rem; box-sizing: border-box;">
                </div>
                <div class="field">
                    <label for="add_sub_sort">Order (optional)</label>
                    <input type="number" id="add_sub_sort" name="sort_order" min="1" placeholder="Auto-assigned if empty" style="max-width: 150px;">
                </div>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn-ghost" onclick="closeModal('addSubtopicModal')">Cancel</button>
            <button type="submit" class="btn-primary">Add Subtopic</button>
        </div>
    </form>
</div>

<!-- EDIT SUBTOPIC MODAL -->
<div id="editSubtopicModal" class="admin-modal">
    <form id="editSubtopicForm" method="POST" enctype="multipart/form-data" class="admin-modal-content">
        @csrf
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Edit Subtopic</h3>
            <button type="button" class="admin-modal-close" onclick="closeModal('editSubtopicModal')">&times;</button>
        </div>
        <div class="admin-modal-body">
            <div class="form-grid" style="grid-template-columns: 1fr;">
                <div class="field">
                    <label for="edit_sub_title">Subtopic Title</label>
                    <input type="text" id="edit_sub_title" name="title" required>
                </div>
                <div class="field">
                    <label for="edit_sub_video">Video URL (YouTube)</label>
                    <input type="text" id="edit_sub_video" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
                </div>
                <div class="field" style="border: 1px dashed var(--border); padding: 1rem; border-radius: 8px;">
                    <label for="edit_sub_doc">Replace PDF / Slides (Optional)</label>
                    <p class="muted" style="margin-top: 0; font-size: 0.8rem; margin-bottom: 0.5rem;">Upload a new file to replace the current one.</p>
                    <input type="file" id="edit_sub_doc" name="documentation" accept=".pdf,.ppt,.pptx,.doc,.docx,image/*" style="background: rgba(255,255,255,0.02); color: var(--text); padding: 0.3rem; border-radius: 8px; width: 100%; border: 1.5px solid var(--border); cursor: pointer; font-family: inherit; font-size: 0.85rem; height: 2.8rem; box-sizing: border-box;">
                    <div id="current_sub_doc_info" style="display: none; margin-top: 0.5rem; font-size: 0.85rem; padding: 0.5rem; background: rgba(16,185,129,0.1); border-radius: 6px; border: 1px solid rgba(16,185,129,0.2); align-items: center; justify-content: space-between;">
                        <span><strong style="color: var(--correct);">Current File:</strong> <span id="current_sub_doc_name"></span></span>
                        <label style="display: inline-flex; align-items: center; gap: 0.3rem; margin: 0; font-size: 0.8rem; color: var(--wrong); cursor: pointer;">
                            <input type="checkbox" name="remove_documentation" value="1" style="width: auto; margin: 0;"> Remove file
                        </label>
                    </div>
                </div>
                <div class="field">
                    <label for="edit_sub_sort">Order</label>
                    <input type="number" id="edit_sub_sort" name="sort_order" min="1" style="max-width: 150px;">
                </div>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn-ghost" onclick="closeModal('editSubtopicModal')">Cancel</button>
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>
</div>


<script>
    // ─── Modal Helpers ───────────────────────────────────────────
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    // ─── Topic Panels Toggle ─────────────────────────────────────
    function toggleSubtopicsPanel(topicId) {
        const panel = document.getElementById('subtopics-panel-' + topicId);
        if (panel) {
            panel.classList.toggle('open');
            const chevron = document.getElementById('chevron-' + topicId);
            if (chevron) {
                chevron.style.transform = panel.classList.contains('open') ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }
    }

    // ─── Add Topic Modal ─────────────────────────────────────────
    function openAddTopicModal() { openModal('addTopicModal'); }

    // ─── Edit Topic Bindings ─────────────────────────────────────
    document.querySelectorAll('.edit-topic-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id          = btn.dataset.id;
            const title       = btn.dataset.title;
            const description = btn.dataset.description;

            document.getElementById('edit_title').value       = title;
            document.getElementById('edit_description').value = description || '';
            document.getElementById('editTopicForm').action   = `/admin/content/topics/${id}`;
            openModal('editTopicModal');
        });
    });

    // ─── Add Subtopic Modal ──────────────────────────────────────
    function openAddSubtopicModal(topicId, topicTitle) {
        document.getElementById('add_sub_topic_id').value    = topicId;
        document.getElementById('add_sub_topic_label').textContent = 'Topic: ' + topicTitle;
        document.getElementById('add_sub_title').value       = '';
        document.getElementById('add_sub_video').value       = '';
        document.getElementById('add_sub_sort').value        = '';
        openModal('addSubtopicModal');
    }

    // ─── Edit Subtopic Bindings ──────────────────────────────────
    document.querySelectorAll('.edit-subtopic-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id    = btn.dataset.id;
            const title = btn.dataset.title;
            const video = btn.dataset.video;
            const doc   = btn.dataset.doc;
            const sort  = btn.dataset.sort;

            document.getElementById('edit_sub_title').value  = title;
            document.getElementById('edit_sub_video').value  = video || '';
            document.getElementById('edit_sub_sort').value   = sort || '';
            document.getElementById('editSubtopicForm').action = `/admin/content/subtopics/${id}`;

            const docInfo = document.getElementById('current_sub_doc_info');
            if (doc) {
                docInfo.style.display = 'flex';
                document.getElementById('current_sub_doc_name').textContent = doc;
            } else {
                docInfo.style.display = 'none';
            }
            openModal('editSubtopicModal');
        });
    });

    // ─── Confirm Delete Helper ───────────────────────────────────
    function confirmDelete(e, msg) {
        if (!confirm(msg || 'Are you sure?')) {
            e.preventDefault();
            return false;
        }
        return true;
    }

    // ─── SortableJS for Topics ───────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = '{{ csrf_token() }}';
        const topicsList = document.getElementById('topicsList');
        if (topicsList) {
            new Sortable(topicsList, {
                handle: '.topic-drag-handle',
                animation: 150,
                onEnd: function (evt) {
                    const orderedIds = Array.from(topicsList.children).map(card => card.dataset.id);
                    Array.from(topicsList.children).forEach((card, index) => {
                        const orderCell = card.querySelector('.sort-order-cell');
                        if (orderCell) orderCell.textContent = index + 1;
                    });
                    fetch('{{ route("admin.content.topics.reorder") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ ordered_ids: orderedIds })
                    });
                }
            });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
@endsection
