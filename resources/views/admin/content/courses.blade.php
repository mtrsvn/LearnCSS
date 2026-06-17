@extends('admin.layouts.app')

@section('title', 'Courses')
@section('kicker', 'Content Management')

@section('header_actions')
    <button class="btn btn-primary" type="button" onclick="openAddCourseModal()">Add Course</button>
@endsection

@section('content')
<style>
    .course-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .course-info h3 { margin: 0 0 0.5rem 0; }
    .course-info p { color: var(--text-muted); margin: 0; font-size: 0.9rem; }
</style>

<div class="tabs">
    <a class="tab" href="{{ route('admin.content.index') }}">Overview</a>
    <a class="tab active" href="{{ route('admin.content.courses') }}">Courses</a>
    <a class="tab" href="{{ route('admin.content.topics') }}">Topics and subtopics</a>
    <a class="tab" href="{{ route('admin.content.quizzes') }}">Quizzes and final exam</a>
</div>

<div class="split-grid">
    <section class="panel">
        <div class="toolbar">
            <div>
                <p class="panel-label">Courses</p>
                <h2 class="panel-title">Available Courses</h2>
            </div>
        </div>

        @if(session('success'))
            <div style="margin: 0 0 1rem; padding: 0.75rem 1rem; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.25); border-radius: 8px; color: #10b981; font-size: 0.875rem; font-weight: 500;">
                ✓ {{ session('success') }}
            </div>
        @endif

        <div id="coursesList">
            @forelse ($courses as $course)
                <div class="course-card">
                    <div class="course-info">
                        <h3>{{ $course->title }}</h3>
                        <p>{{ $course->description }}</p>
                    </div>
                    <div class="course-actions">
                        <button class="btn-ghost" type="button" onclick="openEditCourseModal({{ $course->id }}, '{{ addslashes($course->title) }}', '{{ addslashes($course->description) }}')">Edit</button>
                        <form action="{{ route('admin.content.courses.destroy', $course->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this course?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn-ghost" style="color: var(--wrong);">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p>No courses found. Create one to get started.</p>
            @endforelse
        </div>
    </section>
</div>

<!-- ADD COURSE MODAL -->
<div id="addCourseModal" class="admin-modal">
    <form action="{{ route('admin.content.courses.store') }}" method="POST" class="admin-modal-content">
        @csrf
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Add New Course</h3>
            <button type="button" class="admin-modal-close" onclick="closeModal('addCourseModal')">&times;</button>
        </div>
        <div class="admin-modal-body">
            <div class="field">
                <label>Course Title</label>
                <input type="text" name="title" required style="width:100%; padding: 0.8rem; border-radius: 8px; background: rgba(255,255,255,0.02); border: 1.5px solid var(--border); color: var(--text);">
            </div>
            <div class="field" style="margin-top: 1rem;">
                <label>Description</label>
                <textarea name="description" style="width:100%; padding: 0.8rem; border-radius: 8px; background: rgba(255,255,255,0.02); border: 1.5px solid var(--border); color: var(--text);"></textarea>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn-ghost" onclick="closeModal('addCourseModal')">Cancel</button>
            <button type="submit" class="btn-primary">Create Course</button>
        </div>
    </form>
</div>

<!-- EDIT COURSE MODAL -->
<div id="editCourseModal" class="admin-modal">
    <form id="editCourseForm" method="POST" class="admin-modal-content">
        @csrf
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Edit Course</h3>
            <button type="button" class="admin-modal-close" onclick="closeModal('editCourseModal')">&times;</button>
        </div>
        <div class="admin-modal-body">
            <div class="field">
                <label>Course Title</label>
                <input type="text" id="edit_course_title" name="title" required style="width:100%; padding: 0.8rem; border-radius: 8px; background: rgba(255,255,255,0.02); border: 1.5px solid var(--border); color: var(--text);">
            </div>
            <div class="field" style="margin-top: 1rem;">
                <label>Description</label>
                <textarea id="edit_course_description" name="description" style="width:100%; padding: 0.8rem; border-radius: 8px; background: rgba(255,255,255,0.02); border: 1.5px solid var(--border); color: var(--text);"></textarea>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn-ghost" onclick="closeModal('editCourseModal')">Cancel</button>
            <button type="submit" class="btn-primary">Save</button>
        </div>
    </form>
</div>

<script>
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    
    function openAddCourseModal() { openModal('addCourseModal'); }
    function openEditCourseModal(id, title, desc) {
        document.getElementById('edit_course_title').value = title;
        document.getElementById('edit_course_description').value = desc;
        document.getElementById('editCourseForm').action = '/admin/content/courses/' + id;
        openModal('editCourseModal');
    }
</script>
@endsection
