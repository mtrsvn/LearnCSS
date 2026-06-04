@extends('admin.layouts.app')

@section('title', 'Topics and Lessons')
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
                <thead><tr><th style="width: 30px;"></th><th>Order</th><th>Topic</th><th>Status</th><th>Video</th><th>Actions</th></tr></thead>
                <tbody id="topicsTableBody">
                    @forelse ($topics as $topic)
                        <tr data-id="{{ $topic->id }}">
                            <td style="vertical-align: middle;"><i data-lucide="grip-vertical" class="drag-handle" style="cursor: grab; color: var(--text-muted); width: 18px; opacity: 0.5;"></i></td>
                            <td class="sort-order-cell" style="vertical-align: middle;">{{ $topic->sort_order }}</td>
                            <td style="vertical-align: middle;"><strong>{{ $topic->title }}</strong></td>
                            <td style="vertical-align: middle;">
                                @if($topic->status === 'approved')
                                    <span class="status success">Approved</span>
                                @else
                                    <span class="status warning">Pending</span>
                                @endif
                            </td>
                            <td style="vertical-align: middle;">
                                @if ($topic->video_url)
                                    <span class="muted"><code>{{ $topic->video_url }}</code></span>
                                @else
                                    <span class="muted">No video configured</span>
                                @endif
                            </td>
                            <td style="vertical-align: middle;">
                                <div class="actions" style="display: flex; gap: 0.5rem; justify-content: flex-start;">
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
                                                data-doc="{{ $topic->documentation_filename }}"
                                                data-video="{{ $topic->video_url }}"
                                                data-videos="{{ json_encode($topic->videos ?? []) }}"
                                                {{ ($isInstructor && $topic->status === 'pending') ? 'disabled' : '' }}>
                                            Edit Topic
                                        </button>
                                    @endif
                                    <form action="{{ route('admin.content.topics.destroy', $topic->id) }}" method="POST" onsubmit="return confirmDelete(event, 'Are you sure you want to delete this topic and all its lessons/quizzes?');" style="margin:0;">
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
    <form action="{{ route('admin.content.topics.store') }}" method="POST" enctype="multipart/form-data" class="admin-modal-content">
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
                        <label for="add_description">Topic Description</label>
                        <textarea id="add_description" name="description" required placeholder="Brief description of the topic..." style="width: 100%; min-height: 80px; padding: 0.8rem; border-radius: 8px; border: 1.5px solid var(--border); background: rgba(255,255,255,0.02); color: var(--text); font-family: inherit; resize: vertical;"></textarea>
                    </div>
                    <div class="field full">
                        <label>Video Embed URLs (Optional)</label>
                        <div id="add_topic_videos_container" style="display: flex; flex-direction: column; gap: 10px;">
                            <input type="text" name="videos[]" placeholder="e.g. https://www.youtube.com/embed/dQw4w9WgXcQ">
                        </div>
                        <button type="button" class="btn-ghost btn-sm" onclick="addVideoInput('add_topic_videos_container')" style="margin-top: 10px; width: fit-content;">+ Add another video</button>
                    </div>
                    <div class="field full" style="border: 1px dashed var(--border); padding: 1rem; border-radius: 8px;">
                        <label for="add_topic_documentation">Topic Documentation File (Optional)</label>
                        <p class="muted" style="margin-top: 0; font-size: 0.8rem; margin-bottom: 0.5rem;">Upload a PDF, DOC, ZIP, or Image (PNG/JPG) to be available to students in the Documentation tab for this topic.</p>
                        <input type="file" id="add_topic_documentation" name="documentation" accept=".pdf,.doc,.docx,.zip,.txt,image/*" style="background: rgba(255,255,255,0.02); color: var(--text); padding: 0.3rem; border-radius: 8px; width: 100%; border: 1.5px solid var(--border); cursor: pointer; font-family: inherit; font-size: 0.85rem; height: 2.8rem; box-sizing: border-box;">
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
                        <input type="text" id="edit_title" name="title" required placeholder="e.g. CSS Basics">
                    </div>
                    <div class="field">
                        <label for="edit_description">Topic Description</label>
                        <textarea id="edit_description" name="description" required placeholder="Brief description of the topic..." style="width: 100%; min-height: 80px; padding: 0.8rem; border-radius: 8px; border: 1.5px solid var(--border); background: rgba(255,255,255,0.02); color: var(--text); font-family: inherit; resize: vertical;"></textarea>
                    </div>
                    <div class="field full">
                        <label>Video Embed URLs (Optional)</label>
                        <div id="edit_topic_videos_container" style="display: flex; flex-direction: column; gap: 10px;">
                            <input type="text" name="videos[]" placeholder="e.g. https://www.youtube.com/embed/dQw4w9WgXcQ">
                        </div>
                        <button type="button" class="btn-ghost btn-sm" onclick="addVideoInput('edit_topic_videos_container')" style="margin-top: 10px; width: fit-content;">+ Add another video</button>
                    </div>
                    <div class="field full" style="border: 1px dashed var(--border); padding: 1rem; border-radius: 8px;">
                        <label for="edit_topic_documentation">Topic Documentation File (Optional)</label>
                        <p class="muted" style="margin-top: 0; font-size: 0.8rem; margin-bottom: 0.5rem;">Upload a PDF, DOC, ZIP, or Image (PNG/JPG) to be available to students in the Documentation tab for this topic.</p>
                        <input type="file" id="edit_topic_documentation" name="documentation" accept=".pdf,.doc,.docx,.zip,.txt,image/*" style="background: rgba(255,255,255,0.02); color: var(--text); padding: 0.3rem; border-radius: 8px; width: 100%; border: 1.5px solid var(--border); cursor: pointer; font-family: inherit; font-size: 0.85rem; height: 2.8rem; box-sizing: border-box;">
                        
                        <div id="current_topic_documentation_info" style="display: none; margin-top: 0.5rem; font-size: 0.85rem; padding: 0.5rem; background: rgba(16, 185, 129, 0.1); border-radius: 6px; border: 1px solid rgba(16, 185, 129, 0.2); align-items: center; justify-content: space-between;">
                            <span><strong style="color: var(--correct);">Current File:</strong> <span id="current_topic_doc_filename"></span></span>
                            <label style="display: inline-flex; align-items: center; gap: 0.3rem; margin: 0; font-size: 0.8rem; color: var(--wrong); cursor: pointer;">
                                <input type="checkbox" name="remove_documentation" value="1" style="width: auto; margin: 0;"> Remove file
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="btn-ghost" onclick="closeModal('editTopicModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
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

    function openAddTopicModal() {
        const container = document.getElementById('add_topic_videos_container');
        if (container) container.innerHTML = createVideoRow('');
        openModal('addTopicModal');
    }

    function createVideoRow(value) {
        return `<div style="display: flex; align-items: center; gap: 8px;">
            <input type="text" name="videos[]" value="${value}" placeholder="e.g. https://www.youtube.com/watch?v=..." style="flex: 1;">
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: var(--wrong); cursor: pointer; font-size: 1.2rem; padding: 0.4rem 0.6rem; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.background='none'" title="Remove video">&times;</button>
        </div>`;
    }

    function addVideoInput(containerId) {
        const container = document.getElementById(containerId);
        container.insertAdjacentHTML('beforeend', createVideoRow(''));
    }

    // Topic Edit Bindings
    document.querySelectorAll('.edit-topic-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const title = btn.dataset.title;
            const description = btn.dataset.description;
            const docFile = btn.dataset.doc;
            const videoUrl = btn.dataset.video;
            const videos = JSON.parse(btn.dataset.videos || '[]');

            document.getElementById('edit_title').value = title;
            document.getElementById('edit_description').value = description || '';
            document.getElementById('editTopicForm').action = `/admin/content/topics/${id}`;
            
            const container = document.getElementById('edit_topic_videos_container');
            if (container) {
                container.innerHTML = '';
                if (videos.length === 0 && !videoUrl) {
                    container.innerHTML = createVideoRow('');
                } else {
                    if (videoUrl) {
                        container.innerHTML += createVideoRow(videoUrl);
                    }
                    videos.forEach(v => {
                        container.innerHTML += createVideoRow(v);
                    });
                }
            }
            
            if (docFile) {
                document.getElementById('current_topic_documentation_info').style.display = 'flex';
                document.getElementById('current_topic_doc_filename').textContent = docFile;
            } else {
                document.getElementById('current_topic_documentation_info').style.display = 'none';
            }
            
            openModal('editTopicModal');
        });
    });



    // SortableJS initialization
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = '{{ csrf_token() }}';

        // Topics Sortable
        const topicsTbody = document.getElementById('topicsTableBody');
        if (topicsTbody) {
            new Sortable(topicsTbody, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: function (evt) {
                    const orderedIds = Array.from(topicsTbody.children).map(tr => tr.dataset.id);
                    
                    // Update UI order text immediately
                    Array.from(topicsTbody.children).forEach((tr, index) => {
                        tr.querySelector('.sort-order-cell').textContent = index + 1;
                    });

                    fetch('{{ route("admin.content.topics.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ ordered_ids: orderedIds })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            console.log('Topics reordered successfully');
                        }
                    });
                }
            });
        }


    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
@endsection
