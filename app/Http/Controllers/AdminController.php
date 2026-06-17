<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Topic;
use App\Models\Subtopic;
use App\Models\Course;

use App\Models\QuizQuestion;
use App\Models\UserProgress;
use App\Models\QuizAttempt;
use App\Models\Voucher;
use App\Models\Certificate;
use App\Models\Announcement;
use App\Models\AuditLog;
use Carbon\Carbon;

class AdminController extends Controller
{
    // ─── AUTHENTICATION ──────────────────────────────────────────
    public function showLogin()
    {
        if (Auth::check() && (Auth::user()->is_admin || in_array(trim(strtolower(Auth::user()->role)), ['admin', 'instructor']))) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->is_admin || in_array(trim(strtolower($user->role)), ['admin', 'instructor'])) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'Admin Login',
                    'description' => ucfirst($user->role ?: 'Administrator') . ' logged into the dashboard.',
                    'ip_address' => $request->ip()
                ]);
                return redirect()->intended(route('admin.dashboard'));
            } else {
                Auth::logout();
                return back()->withErrors(['email' => 'Unauthorized access. You are not an administrator or instructor.']);
            }
        }

        return back()->withErrors(['email' => 'Invalid email or password.']);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Admin Logout',
                'description' => 'Administrator logged out.',
                'ip_address' => $request->ip()
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    // ─── DASHBOARD OVERVIEW ──────────────────────────────────────
    public function dashboard()
    {
        // Include everyone in the user counts as requested
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $newThisMonth = User::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        
        $completedTopics = UserProgress::count();
        $quizAttempts = QuizAttempt::whereNotNull('topic_id')->count();
        $finalExamAttempts = QuizAttempt::whereNull('topic_id')->count();
        $certificatesIssued = Certificate::count();
        $vouchersSold = Voucher::count();
        $revenue = Voucher::sum('price');

        // Snapshot stats
        $stats = [
            ['label' => 'Total users', 'value' => number_format($totalUsers), 'note' => '+' . number_format($newThisMonth) . ' this month'],
            ['label' => 'Active users', 'value' => number_format($activeUsers), 'note' => ($totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0) . '% active rate'],
            ['label' => 'Completed topics', 'value' => number_format($completedTopics), 'note' => 'Across all learners'],
            ['label' => 'Quiz attempts', 'value' => number_format($quizAttempts), 'note' => 'Topic quiz submissions'],
            ['label' => 'Final exam attempts', 'value' => number_format($finalExamAttempts), 'note' => QuizAttempt::whereNull('topic_id')->where('passed', false)->count() . ' pending retakes'],
            ['label' => 'Certificates issued', 'value' => number_format($certificatesIssued), 'note' => 'Verified completions']
        ];

        if (Auth::user()->is_admin || trim(strtolower(Auth::user()->role)) === 'admin') {
            $stats[] = ['label' => 'Vouchers sold', 'value' => number_format($vouchersSold), 'note' => Voucher::where('used', true)->count() . ' already used'];
            $stats[] = ['label' => 'Voucher revenue', 'value' => 'PHP ' . number_format($revenue, 2), 'note' => 'Lifetime earnings'];
        }

        // Recent Users for Dashboard
        $recentUsersQuery = User::query();
        if (trim(strtolower(Auth::user()->role)) === 'instructor' && !Auth::user()->is_admin) {
            $recentUsersQuery->where(function($q) {
                $q->where('role', 'student')->orWhereNull('role');
            });
        }
        $recentUsers = $recentUsersQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'dashboard_page');

        return view('admin.dashboard', compact('stats', 'recentUsers'));
    }

    // ─── USER MANAGEMENT ─────────────────────────────────────────
    public function users(Request $request)
    {
        $query = User::query();

        if (trim(strtolower(Auth::user()->role)) === 'instructor' && !Auth::user()->is_admin) {
            $query->where(function($q) {
                $q->where('role', 'student')->orWhereNull('role');
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('affiliation_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'Active') {
                $query->where('is_active', true);
            } elseif ($status === 'Inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('affiliation')) {
            $query->where('affiliation_type', $request->input('affiliation'));
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        
        $totalTopicsCount = Topic::count() ?: 1;

        $users->getCollection()->transform(function ($u) use ($totalTopicsCount) {
            $completed = UserProgress::where('user_id', $u->id)->count();
            $progressPct = round(($completed / $totalTopicsCount) * 100);
            
            $quizzesCount = QuizAttempt::where('user_id', $u->id)->whereNotNull('topic_id')->count();
            $examsCount = QuizAttempt::where('user_id', $u->id)->whereNull('topic_id')->count();
            
            $cert = Certificate::where('user_id', $u->id)->first();
            $certificateStatus = $cert ? 'Issued (' . $cert->code . ')' : ($progressPct >= 100 ? 'Eligible' : 'Not eligible');

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->is_admin ? 'Admin' : ucfirst($u->role ?? 'Student'),
                'affiliation' => $u->affiliation_name ?: 'N/A',
                'progress' => $progressPct,
                'quizzes' => $quizzesCount,
                'exams' => $examsCount,
                'certificate' => $certificateStatus,
                'status' => $u->is_active ? 'Active' : 'Inactive',
            ];
        });

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count()
        ];

        return view('admin.users.index', ['users' => $users, 'stats' => $stats]);
    }

    public function showUser($id)
    {
        $user = User::findOrFail($id);
        
        $totalTopicsCount = Topic::count() ?: 1;
        $completed = UserProgress::where('user_id', $user->id)->count();
        $progressPct = round(($completed / $totalTopicsCount) * 100);
        
        $quizzesCount = QuizAttempt::where('user_id', $user->id)->whereNotNull('topic_id')->count();
        $examsCount = QuizAttempt::where('user_id', $user->id)->whereNull('topic_id')->count();
        
        $cert = Certificate::where('user_id', $user->id)->first();
        $certificateStatus = $cert ? 'Issued (' . $cert->code . ')' : ($progressPct >= 100 ? 'Eligible' : 'Not eligible');

        $userStats = [
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone ?: 'N/A',
            'birthdate' => $user->birthdate ?: 'N/A',
            'affiliation_type' => $user->affiliation_type ?: 'N/A',
            'affiliation' => $user->affiliation_name ?: 'N/A',
            'progress' => $progressPct,
            'quizzes' => $quizzesCount,
            'exams' => $examsCount,
            'certificate' => $certificateStatus,
            'status' => $user->is_active ? 'Active' : 'Inactive',
            'joined_at' => $user->created_at->format('M d, Y')
        ];

        // Topic completion details
        $topics = Topic::orderBy('sort_order')->get();
        $topicsProgress = $topics->map(function ($topic) use ($user) {
            $done = UserProgress::where('user_id', $user->id)->where('topic_id', $topic->id)->exists();
            $bestAttempt = QuizAttempt::where('user_id', $user->id)->where('topic_id', $topic->id)->orderBy('score', 'desc')->first();
            
            return [
                'id' => $topic->id,
                'title' => $topic->title,
                'completed' => $done ? 'Completed' : 'Locked/In-progress',
                'score' => $bestAttempt ? $bestAttempt->score . '/' . $bestAttempt->total : 'No attempt'
            ];
        });

        // Exam logs
        $examAttempts = QuizAttempt::where('user_id', $user->id)->whereNull('topic_id')->orderBy('created_at', 'desc')->get();
        $examLogs = $examAttempts->map(function ($attempt) {
            return [
                'date' => $attempt->created_at->format('M d, Y h:i A'),
                'score' => $attempt->score . '/' . $attempt->total,
                'status' => $attempt->passed ? 'Passed' : 'Failed'
            ];
        });

        // Voucher log
        $vouchers = Voucher::where('used_by', $user->id)->get();
        $voucherLogs = $vouchers->map(function ($v) {
            return [
                'code' => $v->code,
                'price' => '₱' . number_format($v->price, 2),
                'status' => $v->used ? 'Redeemed' : 'Active (Unused)',
                'date' => $v->created_at->format('M d, Y')
            ];
        });

        return view('admin.users.show', compact('userStats', 'topicsProgress', 'examLogs', 'voucherLogs'));
    }

    public function toggleUserStatus($id, Request $request)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $user->is_active ? 'Activate User' : 'Deactivate User',
            'description' => ($user->is_active ? 'Activated' : 'Deactivated') . ' learner account: ' . $user->email,
            'ip_address' => $request->ip()
        ]);

        return back();
    }

    public function updateUserRole($id, Request $request)
    {
        if (!Auth::user()->is_admin && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'role' => 'required|in:admin,instructor,student'
        ]);

        $user = User::findOrFail($id);
        $newRole = $request->input('role');
        
        $user->is_admin = ($newRole === 'admin');
        $user->role = $newRole;
        $user->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Role Update',
            'description' => "Changed role of {$user->email} to {$newRole}",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "User role successfully updated to " . ucfirst($newRole) . ".");
    }

    // ─── CONTENT MANAGEMENT ──────────────────────────────────────
    public function content()
    {
        $courses = Course::with(['topics'])->orderBy('created_at', 'desc')->get();
        $topics = \App\Models\Topic::all();
        return view('admin.content.index', compact('courses', 'topics'));
    }

    public function contentCourses()
    {
        $courses = Course::orderBy('created_at', 'desc')->get();
        return view('admin.content.courses', compact('courses'));
    }

    // ─── COURSE CRUD ──────────────────────────────────────────────
    public function storeCourse(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail_url' => 'nullable|string'
        ]);

        $course = Course::create([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'thumbnail_url' => $request->input('thumbnail_url'),
            'is_published' => true
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Create Course',
            'description' => 'Created course: ' . $course->title,
            'ip_address'  => $request->ip()
        ]);

        return back()->with('success', 'Course created successfully.');
    }

    public function updateCourse(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail_url' => 'nullable|string'
        ]);

        $course->update([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'thumbnail_url' => $request->input('thumbnail_url')
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Update Course',
            'description' => 'Updated course: ' . $course->title,
            'ip_address'  => $request->ip()
        ]);

        return back()->with('success', 'Course updated successfully.');
    }

    public function destroyCourse(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $title = $course->title;
        $course->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Delete Course',
            'description' => 'Deleted course: ' . $title,
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', 'Course deleted successfully.');
    }

    public function contentTopics(Request $request, $course_id)
    {
        $course = Course::findOrFail($course_id);
        $topics = Topic::with(['subtopics', 'course'])
                    ->where('course_id', $course->id)
                    ->orderBy('sort_order')
                    ->get();
        return view('admin.content.topics', compact('topics', 'course'));
    }

    public function contentQuizzes(Request $request, $course_id)
    {
        $course = Course::findOrFail($course_id);
        $query = QuizQuestion::with('topic')->where('course_id', $course->id);
        
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('question', 'like', "%{$search}%");
        }
        
        $quizzes = $query->paginate(5);
        $topics = Topic::where('course_id', $course->id)->orderBy('sort_order')->get();
        return view('admin.content.quizzes', compact('quizzes', 'topics', 'course'));
    }

    // ─── TOPIC CRUD ──────────────────────────────────────────────
    public function reorderTopics(Request $request, $course_id)
    {
        $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'integer|exists:topics,id'
        ]);

        foreach ($request->ordered_ids as $index => $id) {
            Topic::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }



    public function storeTopic(Request $request, $course_id)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course = Course::findOrFail($course_id);

        $status       = (Auth::user()->is_admin || Auth::user()->role === 'admin') ? 'approved' : 'pending';
        $maxSortOrder = Topic::where('course_id', $course->id)->max('sort_order') ?? 0;

        $topic = Topic::create([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'course_id'   => $course->id,
            'sort_order'  => $maxSortOrder + 1,
            'status'      => $status,
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Create Topic',
            'description' => 'Created topic: ' . $topic->title,
            'ip_address'  => $request->ip()
        ]);

        return back()->with('success', 'Topic created successfully.');
    }

    public function updateTopic(Request $request, $course_id, $id)
    {
        $topic = Topic::findOrFail($id);
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $topic->update([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Update Topic',
            'description' => 'Updated topic: ' . $topic->title,
            'ip_address'  => $request->ip()
        ]);

        return back()->with('success', 'Topic updated successfully.');
    }

    public function destroyTopic(Request $request, $course_id, $id)
    {
        $topic = Topic::findOrFail($id);
        $title = $topic->title;
        $topic->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Delete Topic',
            'description' => 'Deleted topic: ' . $title,
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', 'Topic deleted successfully.');
    }


    // ─── SUBTOPIC CRUD ───────────────────────────────────────────
    public function storeSubtopic(Request $request, $course_id)
    {
        $request->validate([
            'topic_id'    => 'required|exists:topics,id',
            'title'       => 'required|string|max:255',
            'video_url'   => 'nullable|string|max:500',
            'sort_order'  => 'nullable|integer',
        ]);

        $status = (Auth::user()->is_admin || Auth::user()->role === 'admin') ? 'approved' : 'pending';
        $maxOrder = Subtopic::where('topic_id', $request->input('topic_id'))->max('sort_order') ?? 0;

        $data = [
            'topic_id'   => $request->input('topic_id'),
            'title'      => $request->input('title'),
            'video_url'  => $request->input('video_url'),
            'sort_order' => $request->input('sort_order', $maxOrder + 1),
            'status'     => $status,
        ];

        if ($request->hasFile('documentation')) {
            $file     = $request->file('documentation');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs('documentation', $filename, 'public');
            $data['documentation_path']     = '/storage/' . $path;
            $data['documentation_filename'] = $file->getClientOriginalName();
        }

        $sub = Subtopic::create($data);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Create Subtopic',
            'description' => 'Created subtopic: ' . $sub->title . ' (Topic ID ' . $sub->topic_id . ')',
            'ip_address'  => $request->ip()
        ]);

        return back()->with('success', 'Subtopic created successfully.');
    }

    public function updateSubtopic(Request $request, $course_id, $id)
    {
        $sub = Subtopic::findOrFail($id);
        $request->validate([
            'title'      => 'required|string|max:255',
            'video_url'  => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        $data = [
            'title'      => $request->input('title'),
            'video_url'  => $request->input('video_url'),
            'sort_order' => $request->input('sort_order', $sub->sort_order),
        ];

        if ($request->hasFile('documentation')) {
            $file     = $request->file('documentation');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs('documentation', $filename, 'public');
            $data['documentation_path']     = '/storage/' . $path;
            $data['documentation_filename'] = $file->getClientOriginalName();
        } elseif ($request->input('remove_documentation') === '1') {
            $data['documentation_path']     = null;
            $data['documentation_filename'] = null;
        }

        $sub->update($data);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Update Subtopic',
            'description' => 'Updated subtopic: ' . $sub->title,
            'ip_address'  => $request->ip()
        ]);

        return back()->with('success', 'Subtopic updated successfully.');
    }

    public function destroySubtopic(Request $request, $course_id, $id)
    {
        $sub   = Subtopic::findOrFail($id);
        $title = $sub->title;
        $sub->delete();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Delete Subtopic',
            'description' => 'Deleted subtopic: ' . $title,
            'ip_address'  => $request->ip()
        ]);

        return back()->with('success', 'Subtopic deleted successfully.');
    }

    public function approveSubtopic(Request $request, $course_id, $id)
    {
        if (!Auth::user()->is_admin && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        $sub = Subtopic::findOrFail($id);
        $sub->update(['status' => 'approved']);
        return back()->with('success', 'Subtopic approved successfully.');
    }


    // ─── QUIZ CRUD ───────────────────────────────────────────────
    public function storeQuiz(Request $request, $course_id)
    {
        $request->validate([
            'topic_id' => 'nullable|exists:topics,id',
            'question' => 'required|string',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
            'answer' => 'required|integer|min:0',
        ]);

        $status = (Auth::user()->is_admin || Auth::user()->role === 'admin') ? 'approved' : 'pending';

        $quiz = QuizQuestion::create([
            'topic_id'  => $request->input('topic_id') ?: null,
            'course_id' => $course_id,
            'question'  => $request->input('question'),
            'options'   => $request->input('options'),
            'answer'    => $request->input('answer'),
            'status'    => $status,
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Create Quiz Question',
            'description' => 'Created ' . ($quiz->topic_id ? 'quiz question' : 'final exam question') . ': ' . substr($quiz->question, 0, 50),
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', 'Question created successfully.');
    }

    public function updateQuiz(Request $request, $course_id, $id)
    {
        $quiz = QuizQuestion::findOrFail($id);
        $request->validate([
            'topic_id' => 'nullable|exists:topics,id',
            'question' => 'required|string',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
            'answer' => 'required|integer|min:0',
        ]);

        $quiz->update([
            'topic_id'  => $request->input('topic_id') ?: null,
            'course_id' => $course_id,
            'question'  => $request->input('question'),
            'options'   => $request->input('options'),
            'answer'    => $request->input('answer'),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Update Quiz Question',
            'description' => 'Updated question ID: ' . $quiz->id,
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', 'Question updated successfully.');
    }

    public function destroyQuiz(Request $request, $course_id, $id)
    {
        $quiz = QuizQuestion::findOrFail($id);
        $desc = substr($quiz->question, 0, 50);
        $quiz->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Delete Quiz Question',
            'description' => 'Deleted question: ' . $desc,
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', 'Question deleted successfully.');
    }

    // ─── APPROVAL METHODS ────────────────────────────────────────
    public function approveTopic($course_id, $id)
    {
        if (!Auth::user()->is_admin && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        $topic = Topic::findOrFail($id);
        $topic->update(['status' => 'approved']);
        return back()->with('success', 'Topic approved successfully.');
    }



    public function approveQuiz($course_id, $id)
    {
        if (!Auth::user()->is_admin && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        $quiz = QuizQuestion::findOrFail($id);
        $quiz->update(['status' => 'approved']);
        return back()->with('success', 'Question approved successfully.');
    }

    // ─── PROGRESS OVERVIEW ───────────────────────────────────────
    public function progress()
    {
        $attempts = QuizAttempt::with(['user', 'topic'])->orderBy('created_at', 'desc')->get();
        return view('admin.progress.index', compact('attempts'));
    }

    // ─── VOUCHER MANAGEMENT ──────────────────────────────────────
    public function vouchers()
    {
        $user = Auth::user();
        if ($user) {
            $user->last_vouchers_viewed_at = now();
            $user->save();
        }
        
        $vouchers = Voucher::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function generateVouchers(Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100'
        ]);

        $quantity = $request->input('quantity');
        
        for ($i = 0; $i < $quantity; $i++) {
            $seg = function() {
                return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
            };
            $code = "CSSM-" . $seg() . "-" . $seg();

            Voucher::create([
                'code' => $code,
                'price' => 299.00,
                'used' => false
            ]);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Generate Vouchers',
            'description' => 'Generated ' . $quantity . ' prepaid voucher codes.',
            'ip_address' => $request->ip()
        ]);

        return back();
    }

    // ─── CERTIFICATE RECORD ──────────────────────────────────────
    public function certificates()
    {
        $user = Auth::user();
        if ($user) {
            $user->last_certificates_viewed_at = now();
            $user->save();
        }
        
        $certificates = Certificate::with('user')->orderBy('issued_at', 'desc')->get();
        return view('admin.certificates.index', compact('certificates'));
    }

    // ─── REPORTS ─────────────────────────────────────────────────
    public function reports()
    {
        // Calculate some aggregate metrics
        $totalSales = Voucher::count();
        $totalRevenue = Voucher::sum('price');
        $passedExams = QuizAttempt::whereNull('topic_id')->where('passed', true)->count();
        $failedExams = QuizAttempt::whereNull('topic_id')->where('passed', false)->count();
        $totalExams = $passedExams + $failedExams;

        $completedStudents = Certificate::count();
        $startedStudents = User::where('is_admin', false)->count();

        return view('admin.reports.index', compact(
            'totalSales', 'totalRevenue', 'passedExams', 'failedExams', 'totalExams', 'completedStudents', 'startedStudents'
        ));
    }

    // ─── ANNOUNCEMENTS ───────────────────────────────────────────
    public function announcements()
    {
        $announcements = Announcement::with('creator')->orderBy('created_at', 'desc')->get();
        return view('admin.notifications.index', compact('announcements'));
    }

    public function createAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string'
        ]);

        Announcement::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'created_by' => Auth::id()
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Announcement Created',
            'description' => 'Created global notice: ' . $request->input('title'),
            'ip_address' => $request->ip()
        ]);

        return back();
    }

    // ─── AUDIT SECURITY LOGS ─────────────────────────────────────
    public function auditLogs()
    {
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.audit-logs.index', compact('logs'));
    }

    // ─── SETTINGS ────────────────────────────────────────────────
    public function settings()
    {
        return view('admin.settings.index');
    }
}
