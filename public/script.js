let state = {
    user: null,
    currentTopicIndex: null,
    currentLessonIndex: 0,
    completedTopics: [],
    topicProgressMap: {},
    examType: 'quiz',
    voucherCode: localStorage.getItem('cssm_voucher') || null,
    courseUnlocked: false,
    hasBoughtVoucher: false
};

let courses = [];
let topics = [];
let finalExam = [];
let currentCourseId = null;

// ─── CSRF & API Helpers ───────────────────────────────────
function getCsrfToken() {
    const name = 'XSRF-TOKEN=';
    const decodedCookie = decodeURIComponent(document.cookie);
    const ca = decodedCookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return null;
}

async function apiRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': getCsrfToken()
        }
    };

    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Something went wrong with the request.');
        }

        return result;
    } catch (error) {
        showToast(error.message);
        throw error;
    }
}

// ─── Toast System ────────────────────────────────────────
function showToast(msg, type = 'error') {
    const container = $('toast-container');
    if (!container) return console.error("Toast container not found");
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ─── Utils ───────────────────────────────────────────────
const $ = id => document.getElementById(id);

function showScreen(id) {
    document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
    const el = $(id);
    if (el) el.classList.add('active');
    window.scrollTo(0, 0);
}

// ─── Modal System ────────────────────────────────────────
const overlay = $('modal-overlay');
const MODALS = ['modal-signup','modal-login','modal-forgot','modal-buy-voucher','modal-enter-voucher'];

function openModal(id) {
    if (!overlay) return;
    overlay.classList.remove('hidden');
    MODALS.forEach(m => {
        const el = $(m);
        if (el) el.classList.add('hidden');
    });
    const target = $(id);
    if (target) target.classList.remove('hidden');
}

function closeModal() {
    if (!overlay) return;
    overlay.classList.add('hidden');
    MODALS.forEach(m => {
        const el = $(m);
        if (el) el.classList.add('hidden');
    });
}

if (overlay) {
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
}

document.querySelectorAll('[data-close]').forEach(btn => {
    btn.addEventListener('click', closeModal);
});

// ─── Password Toggles ────────────────────────────────────
document.querySelectorAll('.toggle-pw').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = $(btn.dataset.target);
        if (!input) return;
        const hide = input.type === 'password';
        input.type = hide ? 'text' : 'password';
        btn.textContent = hide ? 'Hide' : 'Show';
    });
});

// ─── Birthdate MM/DD/YYYY auto-format ────────────────────
const bdateInput = $('su-bdate');
if (bdateInput) {
    bdateInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 8);
        if (v.length >= 5)      v = v.slice(0,2) + '/' + v.slice(2,4) + '/' + v.slice(4);
        else if (v.length >= 3) v = v.slice(0,2) + '/' + v.slice(2);
        this.value = v;
    });

    bdateInput.addEventListener('blur', function () {
        const parts = this.value.split('/');
        if (this.value && parts.length === 3) {
            const [mm, dd, yyyy] = parts;
            const d = new Date(`${yyyy}-${mm}-${dd}`);
            if (isNaN(d.getTime())) {
                this.style.borderColor = 'var(--wrong)';
                showToast('Invalid birthdate format.');
            } else {
                this.style.borderColor = '';
            }
        }
    });
}

// ─── Affiliation Label Toggle ────────────────────────────

// ─── Modal Triggers ──────────────────────────────────────
const triggers = [
    { id: 'nav-login-btn', modal: 'modal-login' },
    { id: 'nav-signup-btn', modal: 'modal-signup' },
    { id: 'hero-signup-btn', modal: 'modal-signup' },
    { id: 'hero-login-btn', modal: 'modal-login' },
    { id: 'curriculum-signup-btn', modal: 'modal-signup' },
    { id: 'preview-signup-btn', modal: 'modal-signup' },
    { id: 'go-login', modal: 'modal-login', prevent: true },
    { id: 'go-signup-2', modal: 'modal-signup', prevent: true },
    { id: 'go-forgot', modal: 'modal-forgot', prevent: true },
    { id: 'go-login-2', modal: 'modal-login', prevent: true },
];

triggers.forEach(t => {
    const el = $(t.id);
    if (el) {
        el.addEventListener('click', e => {
            if (t.prevent) e.preventDefault();
            openModal(t.modal);
        });
    }
});

const goBuyVoucher = $('go-buy-voucher');
if (goBuyVoucher) goBuyVoucher.addEventListener('click', e => { e.preventDefault(); openBuyVoucherModal(); });

const heroBuyVoucher = $('buy-voucher-hero-btn');
if (heroBuyVoucher) heroBuyVoucher.addEventListener('click', () => openBuyVoucherModal());

const heroRedeemVoucher = $('redeem-voucher-hero-btn');
if (heroRedeemVoucher) heroRedeemVoucher.addEventListener('click', () => openModal('modal-enter-voucher'));

function updateVoucherButtons() {
    const buyBtn = $('buy-voucher-hero-btn');
    const redeemBtn = $('redeem-voucher-hero-btn');
    
    if (state.courseUnlocked) {
        if (buyBtn) buyBtn.classList.add('hidden');
        if (redeemBtn) redeemBtn.classList.add('hidden');
    } else {
        if (buyBtn) buyBtn.classList.remove('hidden');
        if (redeemBtn) redeemBtn.classList.remove('hidden');
    }
}

// ─── Sign Up ─────────────────────────────────────────────
const signupBtn = $('signup-btn');
if (signupBtn) {
    signupBtn.addEventListener('click', async () => {
        const fname   = $('su-fname').value.trim();
        const lname   = $('su-lname').value.trim();
        const email   = $('su-email').value.trim().toLowerCase();
        const bdate   = $('su-bdate').value;
        const affName = $('su-affname').value.trim();
        const phone   = $('su-phone').value.trim();
        const pw      = $('su-password').value;
        const conf    = $('su-confirm').value;

        if (!fname || !lname)              return showToast('First and last name are required.');
        if (!email || !email.includes('@')) return showToast('Enter a valid email address.');
        if (!bdate)                        return showToast('Birthdate is required.');
        if (!affName)                      return showToast('Organization / University name is required.');
        if (!phone)                        return showToast('Contact number is required.');
        if (pw.length < 6)                 return showToast('Password must be at least 6 characters.');
        if (pw !== conf)                   return showToast('Passwords do not match.');

        try {
            const data = await apiRequest('/api/auth/register', 'POST', {
                'su-fname': fname,
                'su-lname': lname,
                'su-email': email,
                'su-bdate': bdate,
                'su-afftype': 'student',
                'su-affname': affName,
                'su-phone': phone,
                'su-password': pw
            });

            if (data && data.success) {
                loginUser(data.user);
                showToast(data.message, 'success');
            }
        } catch (err) {}
    });
}

// ─── Login ───────────────────────────────────────────────
const loginBtn = $('login-btn');
if (loginBtn) {
    loginBtn.addEventListener('click', async () => {
        const emailInput = $('li-email');
        const pwInput    = $('li-password');
        if (!emailInput || !pwInput) return;

        const email = emailInput.value.trim().toLowerCase();
        const pw    = pwInput.value;

        if (!email || !email.includes('@')) return showToast('Enter a valid email address.');
        if (!pw)                            return showToast('Password is required.');

        try {
            const data = await apiRequest('/api/auth/login', 'POST', {
                'email': email,
                'password': pw
            });

            if (data && data.success) {
                loginUser(data.user);
                showToast(data.message, 'success');
            }
        } catch (err) {}
    });
}

// ─── Forgot Password ─────────────────────────────────────
const forgotBtn = $('forgot-btn');
if (forgotBtn) {
    forgotBtn.addEventListener('click', async () => {
        const email = $('fp-email').value.trim().toLowerCase();
        if (!email || !email.includes('@')) return showToast('Enter a valid email address.');

        try {
            const data = await apiRequest('/api/auth/forgot-password', 'POST', { 'email': email });
            if (data && data.success) {
                showToast(data.message, 'info');
            }
        } catch (err) {}
    });
}

// ─── Logout ──────────────────────────────────────────────
const logoutBtn = $('logout-btn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
        try {
            await apiRequest('/api/auth/logout', 'POST');
            state.user = null;
            state.completedTopics = [];
            showScreen('landing-screen');
            showToast('Logged out successfully.', 'info');
        } catch (err) {}
    });
}

// ─── Dynamic Boot initialization ──────────────────────────
async function loadCoursesIfNeeded() {
    if (courses.length > 0) return;
    try {
        const data = await apiRequest('/api/courses');
        if (data && data.success) {
            courses = data.courses;
        }
    } catch (e) { console.error(e); }
}

async function loadTopicsIfNeeded(courseId) {
    try {
        const topicData = await apiRequest(`/api/courses/${courseId}/topics`);
        if (topicData && topicData.success) {
            topics = topicData.topics;
        }
    } catch (e) {
        console.error("Topics catalog loading failed.", e);
    }
}

async function boot() {
    // Fetch public curriculum for the landing page right away
    loadPublicCurriculum();

    // 1. Fetch authenticated session first so guest visits do not trigger protected API errors
    try {
        const sessionData = await apiRequest('/api/auth/session');
        if (sessionData && sessionData.success && sessionData.user) {
            await loadCoursesIfNeeded();
            await loginUser(sessionData.user);
        } else {
            showScreen('landing-screen');
        }
    } catch (e) {
        showScreen('landing-screen');
    }
}

// Start boot pipeline
boot().then(() => {
    checkXenditReturn();
});

async function loadPublicCurriculum() {
    const container = $('dynamic-topic-roadmap');
    if (!container) return;

    try {
        const response = await fetch('/api/public/courses');
        const data = await response.json();

        if (data && data.success) {
            container.innerHTML = '';
            if (data.courses.length === 0) {
                container.innerHTML = '<p style="text-align:center; padding: 2rem; color: var(--text-muted);">Curriculum coming soon.</p>';
                return;
            }

            data.courses.forEach((course, index) => {
                const numStr = (index + 1).toString().padStart(2, '0');
                const item = document.createElement('div');
                item.className = 'roadmap-item';
                item.innerHTML = `
                    <div class="roadmap-number">${numStr}</div>
                    <div class="roadmap-content">
                        <h3>${course.title}</h3>
                        <p>${course.description || 'No description available yet.'}</p>
                    </div>
                `;
                container.appendChild(item);
            });
        }
    } catch (e) {
        container.innerHTML = '<p style="text-align:center; padding: 2rem; color: var(--wrong);">Failed to load curriculum. Please refresh.</p>';
    }
}

async function loginUser(user) {
    if (user && user.role === 'admin') {
        window.location.href = '/admin/dashboard';
        return;
    }

    state.user = user;
    state.courseUnlocked = user.isCourseUnlocked || false;
    state.hasCertificate = user.hasCertificate || false;

    await loadCoursesIfNeeded();
    
    // Fetch live progress (defaults to all progress)
    try {
        const pData = await apiRequest('/api/progress');
        if (pData && pData.success) {
            state.completedTopics = pData.completedTopics || [];
            state.topicProgressMap = pData.topicProgressMap || {};
            state.hasCertificate = pData.hasCertificate || state.hasCertificate;
            state.hasPassedMidExam = pData.hasPassedMidExam || false;
            if (pData.lastTopicStarted) {
                state.lastTopicStarted = pData.lastTopicStarted;
            }
        }
    } catch (e) {
        state.completedTopics = [];
        state.topicProgressMap = {};
    }


    const heroName = $('dashboard-hero-name');
    if (heroName) heroName.textContent = user.firstName || user.name;
    const certName = $('cert-user-name');
    if (certName) certName.textContent = user.name;

    const panelName = $('panel-name');
    if (panelName) panelName.textContent = user.name || ((user.firstName || '') + ' ' + (user.lastName || '')).trim() || 'Student';
    const panelEmail = $('panel-email');
    if (panelEmail) panelEmail.textContent = user.email || 'N/A';
    const panelOrg = $('panel-org');
    if (panelOrg) panelOrg.textContent = user.affiliationName || user.affName || user.organization || 'Not specified';
    
    updateVoucherButtons();
    closeModal();
    renderDashboard();
    fetchNotifications();
    showScreen('dashboard-screen');
}

// ─── Buy Voucher ─────────────────────────────────────────
function openBuyVoucherModal() {
    const s1 = $('buy-step-1');
    const s2 = $('buy-step-2');
    if (s1) s1.classList.remove('hidden');
    if (s2) s2.classList.add('hidden');
    openModal('modal-buy-voucher');
}

const buyConfirmBtn = $('buy-confirm-btn');
if (buyConfirmBtn) {
    buyConfirmBtn.addEventListener('click', async () => {
        try {
            const data = await apiRequest('/api/voucher/buy', 'POST');
            if (data && data.success && data.checkout_url) {
                buyConfirmBtn.textContent = 'Redirecting to Xendit...';
                window.location.href = data.checkout_url;
            } else {
                showToast(data.message || 'Failed to initiate purchase', 'error');
            }
        } catch (e) {
            showToast('Network error', 'error');
        }
    });
}

const doneBuyingBtn = $('done-buying-btn');
if (doneBuyingBtn) doneBuyingBtn.addEventListener('click', closeModal);

// ─── Enter Voucher ───────────────────────────────────────
const redeemBtn = $('redeem-voucher-btn');
if (redeemBtn) {
    redeemBtn.addEventListener('click', async () => {
        const input = $('voucher-input');
        if (!input) return;
        const code = input.value.trim();
        if (!code) return showToast('Please enter a voucher code.');

        try {
            const verify = await apiRequest('/api/voucher/verify', 'POST', { 'code': code });
            if (verify && verify.success) {
                const redeem = await apiRequest('/api/voucher/redeem', 'POST', { 'code': code });
                if (redeem && redeem.success) {
                    closeModal();
                    state.voucherCode = code;
                    localStorage.setItem('cssm_voucher', code);
                    
                    if (!state.courseUnlocked) {
                        state.courseUnlocked = true;
                        updateVoucherButtons();
                        renderDashboard();
                        showToast('Voucher accepted! You can now access the courses.', 'success');
                    } else {
                        const allDone = topics.length > 0 && state.completedTopics.length === topics.length;
                        if (!allDone || !state.hasPassedMidExam) {
                            showToast('The Final Exam can only be taken after completing all topics and the Mid Certification Exam.', 'error');
                        } else {
                            state.examType = 'final';
                            const exam = await apiRequest('/api/exam/questions?type=final');
                            if (exam && exam.success) {
                                finalExam = exam.questions;
                                startQuiz(finalExam);
                                showToast('Voucher accepted! Starting exam...', 'success');
                            }
                        }
                    }
                }
            }
        } catch (e) {}
    });
}

// ─── Dashboard ───────────────────────────────────────────
function renderDashboard() {
    const cContainer = $('courses-container');
    if (cContainer) {
        cContainer.innerHTML = '';
        courses.forEach(course => {
            const card = document.createElement('div');
            card.className = 'topic-card';
            card.style.cursor = 'pointer';
            card.innerHTML = `
                <p class="topic-num">Course</p>
                <h3>${course.title}</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;">${course.description || ''}</p>
            `;
            card.addEventListener('click', async () => {
                currentCourseId = course.id;
                const titleEl = $('course-details-title');
                if (titleEl) titleEl.textContent = course.title;
                $('dashboard-courses-head').style.display = 'none';
                cContainer.style.display = 'none';
                $('course-details-area').style.display = 'block';
                
                await loadTopicsIfNeeded(course.id);
                // Also fetch progress for this specific course
                const pData = await apiRequest('/api/progress?course_id=' + course.id);
                if (pData && pData.success) {
                    state.completedTopics = pData.completedTopics || [];
                    state.topicProgressMap = pData.topicProgressMap || {};
                }
                
                renderTopics();
            });
            cContainer.appendChild(card);
        });
        
        const backBtn = $('back-to-courses-btn');
        if (backBtn) {
            backBtn.onclick = () => {
                $('course-details-area').style.display = 'none';
                $('dashboard-courses-head').style.display = 'block';
                cContainer.style.display = 'grid';
            };
        }
    }
}

function renderTopics() {
    const container = $('topics-container');
    if (!container) return;
    container.innerHTML = '';

    const topicCountEl = $('dashboard-topic-count');
    if (topicCountEl) topicCountEl.textContent = String(topics.length);

    topics.forEach((topic, index) => {
        const done = state.completedTopics.includes(topic.id);
        const prevTopicId = index > 0 ? topics[index - 1].id : null;
        let unlocked = index === 0 || (prevTopicId && state.completedTopics.includes(prevTopicId));
        let lockMsg = '';
        
        const midIndex = Math.floor(topics.length / 2);
        
        if (index >= midIndex && !state.hasPassedMidExam && !done) {
            if (index === midIndex && prevTopicId && state.completedTopics.includes(prevTopicId)) {
                unlocked = false;
                lockMsg = '<span class="topic-lock"><i data-lucide="lock"></i>Complete Mid Exam to unlock</span>';
            } else {
                unlocked = false;
                lockMsg = '<span class="topic-lock"><i data-lucide="lock"></i>Complete the previous topic to unlock</span>';
            }
        } else {
            lockMsg = unlocked ? '' : '<span class="topic-lock"><i data-lucide="lock"></i>Complete the previous topic to unlock</span>';
        }

        if (index === midIndex && midIndex > 0) {
            const midDone = state.hasPassedMidExam;
            const midUnlocked = index === 0 || (prevTopicId && state.completedTopics.includes(prevTopicId));
            const midLockMsg = midUnlocked ? '' : '<span class="topic-lock"><i data-lucide="lock"></i>Complete previous topics to unlock Mid Exam</span>';
            
            const midCard = document.createElement('div');
            midCard.className = `topic-card ${midDone ? 'completed' : ''} ${midUnlocked ? '' : 'locked'}`.trim();
            midCard.innerHTML = `
                <p class="topic-num" style="color: ${midDone ? 'var(--correct)' : 'var(--accent)'}; font-weight: bold;">Mid Exam</p>
                <h3>Mid Certification Exam${midDone ? '<span class="topic-done-badge"><i data-lucide="check"></i></span>' : ''}</h3>
                <span style="display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.9rem;">${midDone ? 'You have passed the Mid Certification Exam.' : 'Test your knowledge on the first half!'}</span>
                ${midLockMsg}
            `;
            if (midUnlocked) {
                midCard.addEventListener('click', async () => {
                    state.examType = 'mid';
                    try {
                        const exam = await apiRequest('/api/courses/' + currentCourseId + '/exam/questions?type=mid');
                        if (exam && exam.success) {
                            startQuiz(exam.questions);
                        }
                    } catch(e) {}
                });
            }
            container.appendChild(midCard);
        }

        const card = document.createElement('div');
        card.className = `topic-card ${done ? 'completed' : ''} ${(unlocked || done) ? '' : 'locked'}`.trim();
        card.innerHTML = `
            <p class="topic-num">Topic ${topic.sort_order}</p>
            <h3>${topic.title}${done ? '<span class="topic-done-badge"><i data-lucide="check"></i></span>' : ''}</h3>
            ${(unlocked || done) ? '' : lockMsg}
        `;
        card.style.cursor = 'pointer';
        card.addEventListener('click', () => openTopic(index));
        container.appendChild(card);
    });

    const pct = topics.length > 0 ? Math.round((state.completedTopics.length / topics.length) * 100) : 0;

    const summaryEl = $('dashboard-progress-summary');
    if (summaryEl) summaryEl.textContent = `${pct}%`;
    const completedEl = $('dashboard-modules-completed');
    if (completedEl) completedEl.textContent = `${state.completedTopics.length} / ${topics.length}`;

    const resumeBtn = $('resume-module-btn');
    if (resumeBtn) {
        if (state.hasCertificate) {
            resumeBtn.textContent = 'View Your Certificate';
            resumeBtn.classList.remove('hidden');
        } else if (state.completedTopics.length === 0 && !state.lastTopicStarted) {
            resumeBtn.textContent = 'Start Your First Lesson';
            resumeBtn.classList.remove('hidden');
        } else {
            resumeBtn.textContent = 'Resume Module';
            resumeBtn.classList.remove('hidden');
        }
    }

    updateFinalCard();
    if (window.lucide) lucide.createIcons();
}

const resumeCourseBtn = $('resume-module-btn');
if (resumeCourseBtn) {
    resumeCourseBtn.addEventListener('click', async () => {
        if (state.hasCertificate) {
            try {
                const res = await apiRequest('/api/certificate');
                if (res && res.success && res.certificate) {
                    showCertificate(res.certificate);
                }
            } catch(e) {}
            return;
        }
        let nextIndex = 0;
        if (state.lastTopicStarted) {
            nextIndex = topics.findIndex(t => t.id == state.lastTopicStarted);
            if (nextIndex < 0) nextIndex = 0;
        } else {
            nextIndex = topics.findIndex((topic, index) => {
                const prevTopicId = index > 0 ? topics[index - 1].id : null;
                return index === 0 || (prevTopicId && state.completedTopics.includes(prevTopicId));
            });
        }
        if (nextIndex >= 0) {
            const midIndex = Math.floor(topics.length / 2);
            if (nextIndex >= midIndex && !state.hasPassedMidExam) {
                showToast('Please complete the Mid Exam first before continuing to the next topic.', 'warning');
            } else {
                openTopic(nextIndex);
            }
        }
    });
}

function updateFinalCard() {
    const btn = $('final-exam-btn');
    if (!btn) return;
    const allDone = topics.length > 0 && state.completedTopics.length === topics.length;

    const statusEl = $('final-card-status');
    const lockEl = $('final-card-lock');
    const h3El = btn.querySelector('h3');

    if (state.hasCertificate) {
        btn.className = 'topic-card completed final-exam-card';
        btn.style.borderColor = '';
        btn.style.boxShadow = '';
        btn.innerHTML = `
            <p class="topic-num">Final Exam</p>
            <h3 style="margin-bottom: 0.5rem; font-size: 1.25rem; color: var(--text);">Final Certification Exam<span class="topic-done-badge"><i data-lucide="check"></i></span></h3>
            <span id="final-card-status" style="display: block; margin-bottom: 1.5rem; color: var(--text-muted); font-size: 0.9rem;">You have successfully passed the final exam. Congratulations!</span>
            <div style="display: flex; gap: 0.75rem;">
                <button id="view-cert-btn" class="btn-primary" style="flex: 1; font-size: 0.9rem; padding: 0.6rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">View Certificate</button>
                <button id="retake-exam-btn" class="btn-ghost" style="flex: 1; font-size: 0.9rem; padding: 0.6rem; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; gap: 0.5rem;">Retake</button>
            </div>
        `;
        
        $('view-cert-btn').onclick = async (e) => {
            e.stopPropagation();
            try {
                const res = await apiRequest('/api/courses/' + currentCourseId + '/certificate');
                if (res && res.success && res.certificate) {
                    showCertificate(res.certificate);
                }
            } catch(e) {}
        };
        
        $('retake-exam-btn').onclick = async (e) => {
            e.stopPropagation();
            state.examType = 'final';
            try {
                const exam = await apiRequest('/api/courses/' + currentCourseId + '/exam/questions?type=final');
                if (exam && exam.success) {
                    finalExam = exam.questions;
                    startQuiz(finalExam);
                }
            } catch(e) {}
        };
        
        btn.onclick = null;
    } else if (!allDone || !state.hasPassedMidExam) {
        btn.className = 'topic-card locked final-exam-card';
        btn.style.borderColor = 'var(--border)';
        btn.style.boxShadow = 'none';
        btn.innerHTML = `
            <p class="topic-num" style="color: var(--text-muted); font-weight: bold;">Final Exam</p>
            <h3 style="color: var(--text-muted);">Final Certification Exam</h3>
            <span style="display: block; margin-bottom: 0.5rem; color: var(--text-muted); font-size: 0.9rem;">Comprehensive test covering all topics!</span>
            <span class="topic-lock"><i data-lucide="lock"></i>Complete all topics and Mid Exam to unlock</span>
        `;
        btn.onclick = null;
    } else {
        btn.className = 'topic-card final-exam-card';
        btn.style.borderColor = 'var(--accent)';
        btn.style.boxShadow = '0 4px 12px rgba(99, 102, 241, 0.15)';
        btn.innerHTML = `
            <div class="exam-card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <p class="topic-num" style="margin: 0; font-weight: 600; color: var(--accent);">Final Exam</p>
                <span class="topic-unlock" style="background: rgba(99, 102, 241, 0.1); color: var(--accent); padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; display: flex; align-items: center; gap: 0.35rem; font-weight: 600;"><i data-lucide="unlock" style="width: 14px; height: 14px;"></i> Unlocked</span>
            </div>
            <h3 style="margin-bottom: 0.5rem; font-size: 1.25rem; color: var(--text);">Final Certification Exam</h3>
            <span id="final-card-status" style="display: block; margin-bottom: 1.5rem; color: var(--text-muted); font-size: 0.9rem;">Ready to take the final exam. Good luck!</span>
            <button class="btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; font-size: 0.95rem; padding: 0.75rem;">
                <i data-lucide="play-circle" style="width: 18px; height: 18px;"></i> Start Exam
            </button>
        `;
        btn.onclick = async () => {
            if (state.courseUnlocked) {
                state.examType = 'final';
                try {
                    const exam = await apiRequest('/api/courses/' + currentCourseId + '/exam/questions?type=final');
                    if (exam && exam.success) {
                        finalExam = exam.questions;
                        startQuiz(finalExam);
                    }
                } catch(e) {}
            } else {
                openModal('modal-enter-voucher');
            }
        };
    }

    if (typeof lucide !== 'undefined') lucide.createIcons({ root: btn });
}

// ─── Lesson (Coursera-style) ──────────────────────────────────
let currentSubtopicIndex = 0;

function openTopic(index) {
    state.currentTopicIndex = index;
    currentSubtopicIndex = 0;

    if (topics[index]) {
        apiRequest('/api/progress/start', 'POST', { topic_id: topics[index].id })
            .then(res => {
                if (res && res.success && res.lastTopicStarted) {
                    state.lastTopicStarted = res.lastTopicStarted;
                }
            }).catch(() => {});
    }

    renderLesson();
    showScreen('lesson-screen');
}

function renderLesson(openIdx = 0) {
    const topic = topics[state.currentTopicIndex];
    if (!topic) return;

    // Determine current unlocked progress for this topic from backend
    let maxUnlocked = state.topicProgressMap[topic.id] || 0;
    window.currentUnlockedIdx = maxUnlocked;
    window.currentTopicStorageKey = `certApp_progress_${topic.id}`; // legacy fallback if needed

    // Set topic title in sidebar
    const titleEl = $('current-topic-title');
    if (titleEl) titleEl.textContent = topic.title;

    const navList = $('subtopics-nav-list');
    if (!navList) return;
    navList.innerHTML = '';

    const subtopics = topic.subtopics || [];
    const noSubMsg  = $('no-subtopics-msg');
    const subHeader = $('subtopic-header');
    const videoWrap = $('video-container');
    const docsWrap  = $('docs-container');

    if (subtopics.length === 0) {
        // No subtopics — show placeholder
        if (noSubMsg)  noSubMsg.style.display  = 'flex';
        if (subHeader) subHeader.style.display = 'none';
        if (videoWrap) videoWrap.style.display = 'none';
        if (docsWrap)  docsWrap.style.display  = 'none';

        // Legacy fallback: show topic-level video/docs if present
        if (topic.videoUrl || (topic.videos && topic.videos.length) || topic.documentationPath) {
            if (noSubMsg) noSubMsg.style.display = 'none';
            if (subHeader) {
                subHeader.style.display = 'flex';
                const numEl = $('subtopic-header-num');
                const ttlEl = $('subtopic-header-title');
                if (numEl) numEl.textContent = 'Topic Content';
                if (ttlEl) ttlEl.textContent = topic.title;
            }
            loadVideoForSubtopic({ videoUrl: topic.videoUrl });
            loadDocsForSubtopic({ documentationPath: topic.documentationPath, documentationFilename: topic.documentationFilename });
            
            if (videoWrap) videoWrap.style.display = 'block';
            if (docsWrap)  docsWrap.style.display  = 'flex';
        }
        return;
    }

    if (noSubMsg)  noSubMsg.style.display  = 'none';
    if (subHeader) subHeader.style.display = 'flex';

    // Build flattened items list for openFlattenedItem()
    window.currentFlattenedItems = [];
    subtopics.forEach((sub, subIndex) => {
        if (sub.documentationPath) {
            window.currentFlattenedItems.push({ sub, type: 'doc',   subIndex });
        }
        if (sub.videoUrl) {
            window.currentFlattenedItems.push({ sub, type: 'video', subIndex });
        }
        if (!sub.videoUrl && !sub.documentationPath) {
            window.currentFlattenedItems.push({ sub, type: 'none',  subIndex });
        }
    });

    // Build GROUPED sidebar: subtopic title as header, items nested under it
    subtopics.forEach((sub, subIndex) => {
        // ── Group header (non-clickable) ──
        const group = document.createElement('div');
        group.className = 'sub-group';

        const groupHeader = document.createElement('div');
        groupHeader.className = 'sub-group-header';
        groupHeader.innerHTML = `
            <span class="sub-group-num">${subIndex + 1}</span>
            <span class="sub-group-title">${sub.title}</span>
        `;
        group.appendChild(groupHeader);

        // ── Child items ──
        const childItems = [];
        if (sub.documentationPath) {
            const flatIdx = window.currentFlattenedItems.findIndex(
                f => f.subIndex === subIndex && f.type === 'doc'
            );
            childItems.push({ label: 'Reading', icon: '📄', flatIdx });
        }
        if (sub.videoUrl) {
            const flatIdx = window.currentFlattenedItems.findIndex(
                f => f.subIndex === subIndex && f.type === 'video'
            );
            childItems.push({ label: 'Video', icon: '▶', flatIdx });
        }
        if (childItems.length === 0) {
            const flatIdx = window.currentFlattenedItems.findIndex(
                f => f.subIndex === subIndex && f.type === 'none'
            );
            childItems.push({ label: 'No content', icon: '—', flatIdx });
        }

        childItems.forEach(({ label, icon, flatIdx }) => {
            const btn = document.createElement('button');
            btn.className = 'subtopic-nav-item sub-child-item';
            btn.dataset.flatIdx = flatIdx;
            
            // Check lock state
            if (flatIdx > window.currentUnlockedIdx) {
                btn.classList.add('locked');
                btn.innerHTML = `<span class="sub-child-icon" style="opacity: 0.5; margin-right: 0.3rem;"><i data-lucide="lock" style="width: 14px; height: 14px;"></i></span><span class="sub-child-label">${label}</span>`;
            } else {
                btn.innerHTML = `<span class="sub-child-label">${label}</span>`;
            }

            btn.addEventListener('click', () => {
                if (flatIdx <= window.currentUnlockedIdx) {
                    openFlattenedItem(flatIdx);
                }
            });
            group.appendChild(btn);
        });

        navList.appendChild(group);
    });

    // Open target item
    if (window.currentFlattenedItems.length > 0) {
        openFlattenedItem(openIdx);
    }
}

function openFlattenedItem(index) {
    const item = window.currentFlattenedItems[index];
    if (!item) return;

    // Update active nav item — match by data-flat-idx
    document.querySelectorAll('.sub-child-item').forEach(el => {
        el.classList.toggle('active', parseInt(el.dataset.flatIdx) === index);
    });

    // Update header
    const numEl = $('subtopic-header-num');
    const ttlEl = $('subtopic-header-title');
    if (numEl) numEl.textContent = `${item.type === 'video' ? '▶ Video' : '📄 Reading'} · Part ${item.subIndex + 1}`;
    if (ttlEl) ttlEl.textContent = item.sub.title;

    const videoContainer = $('video-container');
    const docsContainer  = $('docs-container');

    if (item.type === 'video') {
        if (videoContainer) videoContainer.style.display = 'flex';
        if (docsContainer)  docsContainer.style.display  = 'none';
        loadVideoForSubtopic(item.sub);
    } else if (item.type === 'doc') {
        if (videoContainer) videoContainer.style.display = 'none';
        if (docsContainer)  docsContainer.style.display  = 'flex';
        loadDocsForSubtopic(item.sub);
    } else {
        if (videoContainer) videoContainer.style.display = 'none';
        if (docsContainer)  docsContainer.style.display  = 'none';
    }

    // Handle "Mark as Complete & Continue" button
    const completeBar = $('mark-complete-bar');
    const completeBtn = $('mark-complete-btn');
    if (completeBar && completeBtn) {
        if (index < window.currentFlattenedItems.length - 1) {
            completeBar.style.display = 'flex';
            completeBtn.onclick = async () => {
                const nextIdx = index + 1;
                // Unlock if needed
                if (nextIdx > window.currentUnlockedIdx) {
                    window.currentUnlockedIdx = nextIdx;
                    const topic = topics[state.currentTopicIndex];
                    state.topicProgressMap[topic.id] = nextIdx;
                    
                    // Save to backend
                    try {
                        await apiRequest('/api/progress/unlock', 'POST', {
                            topic_id: topic.id,
                            max_unlocked_index: nextIdx
                        });
                    } catch (e) {
                        console.error('Failed to save progress to backend');
                    }

                    renderLesson(nextIdx); // re-render to update locks, which also opens it
                } else {
                    openFlattenedItem(nextIdx);
                }
            };
        } else {
            completeBar.style.display = 'none';
        }
    }

    if (window.lucide) lucide.createIcons();
}

function loadVideoForSubtopic(sub) {
    const player       = $('video-player');
    const videoIframeWrap = $('video-iframe-wrap');
    const unavailable  = $('video-unavailable');
    const titleLabel   = $('video-title-label');

    // Reset
    if (videoIframeWrap) videoIframeWrap.style.display = 'none';
    if (unavailable)     unavailable.style.display     = 'none';

    if (titleLabel) titleLabel.textContent = sub.title || 'Video';

    if (!sub.videoUrl) {
        if (unavailable) unavailable.style.display = 'flex';
        return;
    }

    // Parse YouTube URL to embed format
    const getEmbedUrl = (url) => {
        if (!url) return '';
        let vidId = '';
        if (url.includes('youtube.com/watch?v=')) {
            vidId = url.split('v=')[1].split('&')[0];
        } else if (url.includes('youtu.be/')) {
            vidId = url.split('youtu.be/')[1].split('?')[0];
        } else if (url.includes('youtube.com/embed/')) {
            return url;
        }
        return vidId ? `https://www.youtube.com/embed/${vidId}?rel=0` : url;
    };

    if (player) {
        player.src = getEmbedUrl(sub.videoUrl);
    }
    if (videoIframeWrap) videoIframeWrap.style.display = 'block';
}

function loadDocsForSubtopic(sub) {
    const docsBtn       = $('docs-download-btn');
    const docsIframe    = $('docs-iframe');
    const docsIframeWrap = $('docs-iframe-wrap');
    const docsImg       = $('docs-img');
    const docsImgWrap   = $('docs-img-wrap');
    const docsFallback  = $('docs-fallback');
    const filenameLabel = $('docs-filename-label');

    // Reset
    if (docsIframeWrap) docsIframeWrap.style.display = 'none';
    if (docsImgWrap)    docsImgWrap.style.display    = 'none';
    if (docsFallback)   docsFallback.style.display   = 'none';

    if (!sub.documentationPath) {
        if (docsBtn) docsBtn.style.display = 'none';
        if (docsFallback) docsFallback.style.display = 'flex';
        if (filenameLabel) filenameLabel.textContent = 'No document';
        return;
    }

    if (docsBtn) {
        docsBtn.style.display = 'inline-flex';
        docsBtn.href = sub.documentationPath;
    }
    if (filenameLabel) filenameLabel.textContent = sub.documentationFilename || 'Document';

    const path  = sub.documentationPath.toLowerCase();
    const isPdf = path.endsWith('.pdf');
    const isImg = /\.(jpeg|jpg|gif|png|webp)$/.test(path);

    if (isImg) {
        if (docsImg) docsImg.src = sub.documentationPath;
        if (docsImgWrap) docsImgWrap.style.display = 'flex';
    } else if (isPdf) {
        if (docsIframe) docsIframe.src = sub.documentationPath;
        if (docsIframeWrap) docsIframeWrap.style.display = 'block';
    } else {
        if (docsFallback) {
            docsFallback.style.display = 'flex';
            docsFallback.innerHTML = `
                <div style="width:64px;height:64px;border-radius:16px;background:rgba(255,255,255,0.05);border:1px dashed var(--border);display:flex;align-items:center;justify-content:center;color:var(--text-muted);">
                    <i data-lucide="file-archive" style="width:32px;height:32px;"></i>
                </div>
                <p style="color:var(--text);font-weight:600;">Preview not available</p>
                <p style="color:var(--text-muted);font-size:0.9rem;">Use the Download button to view this file.</p>
            `;
            if (window.lucide) lucide.createIcons({ root: docsFallback });
        }
    }
}



const backBtn = $('lesson-back-btn');
if (backBtn) backBtn.addEventListener('click', () => showScreen('dashboard-screen'));

const takeQuizBtn = $('take-quiz-btn');
if (takeQuizBtn) {
    takeQuizBtn.addEventListener('click', () => {
        state.examType = 'quiz';
        startQuiz(topics[state.currentTopicIndex].quiz);
    });
}


// ─── Quiz ─────────────────────────────────────────────────
let quizData = [], qIndex = 0, score = 0, selected = null, answersList = [];
let quizTimerInterval = null;
let quizTimerSeconds = 1200;

function formatTime(sec) {
    const m = Math.floor(sec / 60).toString().padStart(2, '0');
    const s = (sec % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
}

function startQuiz(data) {
    quizData = data; qIndex = 0; score = 0; answersList = [];
    const totalQEl = $('total-q');
    if (totalQEl) totalQEl.textContent = data.length;
    
    clearInterval(quizTimerInterval);
    const timerEl = $('quiz-timer');
    if (state.examType === 'final' || state.examType === 'mid') {
        quizTimerSeconds = state.examType === 'final' ? 2400 : 1200; // 40 mins for final, 20 mins for mid
        timerEl.innerHTML = `<i data-lucide="timer" style="width: 16px; height: 16px; margin-right: 6px; vertical-align: text-bottom;"></i> <span id="quiz-timer-text" style="font-weight: bold; font-family: monospace; font-size: 1.1rem;">${formatTime(quizTimerSeconds)}</span>`;
        timerEl.style.display = 'inline-block';
        timerEl.style.padding = '0.4rem 0.8rem';
        timerEl.style.backgroundColor = 'rgba(239, 68, 68, 0.1)';
        timerEl.style.color = 'var(--wrong, #ef4444)';
        timerEl.style.borderRadius = '8px';
        timerEl.style.border = '1px solid rgba(239, 68, 68, 0.2)';
        timerEl.classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons({ root: timerEl });
        
        quizTimerInterval = setInterval(() => {
            quizTimerSeconds--;
            const timerText = $('quiz-timer-text');
            if (timerText) timerText.textContent = formatTime(quizTimerSeconds);
            if (quizTimerSeconds <= 0) {
                clearInterval(quizTimerInterval);
                showToast('Time is up! Submitting exam...', 'info');
                // Auto submit with empty answers for remaining
                while (answersList.length < quizData.length) {
                    answersList.push(null);
                }
                finishQuiz();
            }
        }, 1000);
    } else {
        if (timerEl) timerEl.classList.add('hidden');
    }
    
    renderQuestion();
    showScreen('quiz-screen');
}

function renderQuestion() {
    const q = quizData[qIndex];
    const currentQEl = $('current-q');
    if (currentQEl) currentQEl.textContent = qIndex + 1;
    const qTextEl = $('question-text');
    if (qTextEl) {
        qTextEl.textContent = q.question;
        qTextEl.style.fontSize = '1.25rem';
        qTextEl.style.fontWeight = '600';
        qTextEl.style.marginBottom = '1.5rem';
        qTextEl.style.color = 'var(--text)';
    }
    const progressBar = $('quiz-progress-bar');
    if (progressBar) progressBar.style.width = ((qIndex + 1) / quizData.length * 100) + '%';

    const opts = $('options-container');
    if (opts) {
        opts.innerHTML = '';
        selected = null;
        const nextBtn = $('next-q-btn');
        if (nextBtn) {
            nextBtn.disabled = true;
            nextBtn.style.opacity = '0.5';
            nextBtn.style.cursor = 'not-allowed';
        }

        q.options.forEach((opt, i) => {
            const btn = document.createElement('button');
            btn.className = 'quiz-option';
            
            const letter = String.fromCharCode(65 + i);
            btn.innerHTML = `
                <span class="opt-letter">${letter}</span>
                <span class="opt-text">${opt}</span>
            `;
            
            btn.addEventListener('click', () => {
                document.querySelectorAll('.quiz-option').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                selected = i;
                if (nextBtn) {
                    nextBtn.disabled = false;
                    nextBtn.style.opacity = '1';
                    nextBtn.style.cursor = 'pointer';
                }
            });
            opts.appendChild(btn);
        });
    }
}

const nextQBtn = $('next-q-btn');
if (nextQBtn) {
    nextQBtn.addEventListener('click', () => {
        const q = quizData[qIndex];
        answersList.push(selected);

        document.querySelectorAll('.quiz-option').forEach((btn, i) => {
            btn.disabled = true;
            btn.style.cursor = 'default';
            const letterEl = btn.querySelector('.opt-letter');
            
            // For regular quiz, we know answers index locally.
            // For final exam or mid exam, options grading is performed securely at submit!
            if (state.examType === 'quiz') {
                if (i === q.answer) {
                    btn.classList.add('correct');
                    btn.style.borderColor = 'var(--success, #10b981)';
                    btn.style.backgroundColor = 'rgba(16, 185, 129, 0.05)';
                    if (letterEl) {
                        letterEl.style.background = 'var(--success, #10b981)';
                        letterEl.style.color = '#fff';
                        letterEl.style.borderColor = 'var(--success, #10b981)';
                        letterEl.innerHTML = `<i data-lucide="check" style="width: 16px; height: 16px;"></i>`;
                        if (window.lucide) lucide.createIcons({ root: letterEl });
                    }
                }
                else if (i === selected) {
                    btn.classList.add('wrong');
                    btn.style.borderColor = 'var(--wrong, #ef4444)';
                    btn.style.backgroundColor = 'rgba(239, 68, 68, 0.05)';
                    if (letterEl) {
                        letterEl.style.background = 'var(--wrong, #ef4444)';
                        letterEl.style.color = '#fff';
                        letterEl.style.borderColor = 'var(--wrong, #ef4444)';
                        letterEl.innerHTML = `<i data-lucide="x" style="width: 16px; height: 16px;"></i>`;
                        if (window.lucide) lucide.createIcons({ root: letterEl });
                    }
                } else {
                    btn.style.opacity = '0.5';
                }
            } else {
                if (i === selected) btn.classList.add('selected');
                else btn.style.opacity = '0.5';
            }
        });

        if (state.examType === 'quiz' && selected === q.answer) {
            score++;
        }

        setTimeout(() => { 
            qIndex++; 
            qIndex < quizData.length ? renderQuestion() : finishQuiz(); 
        }, 1200);
    });
}

async function finishQuiz() {
    clearInterval(quizTimerInterval);
    
    if (state.examType === 'final' || state.examType === 'mid') {
        try {
            const data = await apiRequest('/api/exam/submit', 'POST', {
                'voucher_code': state.voucherCode || '',
                'answers': answersList
            });

            if (data && data.success) {
                if (data.passed) {
                    if (state.examType === 'mid') {
                        showToast('Congratulations! You passed the Mid Exam.', 'success');
                    } else {
                        if (state.hasCertificate) {
                            showToast('Congratulations! You passed the final exam again!', 'success');
                        } else {
                            showToast('Congratulations! You passed the final exam.', 'success');
                            state.hasCertificate = true;
                        }
                        showCertificate(data.certificate);
                    }
                } else {
                    showToast(`You scored ${data.score}/${data.total}. You did not pass.`, 'error');
                }
                renderDashboard();
                showScreen('dashboard-screen');
            }
        } catch (e) {}
    } else {
        const topicId = topics[state.currentTopicIndex].id;
        const total = quizData.length;
        const passed = (score === total);

        try {
            const data = await apiRequest('/api/quiz/attempt', 'POST', {
                'topic_id': topicId,
                'score': score,
                'total': total,
                'passed': passed
            });

            if (data && data.success) {
                state.completedTopics = data.completedTopics;
                showToast(data.message, passed ? 'success' : 'info');
            }
        } catch (e) {}

        renderDashboard();
        showScreen('dashboard-screen');
    }
}

// ─── Certificate ──────────────────────────────────────────
function showCertificate(certInfo) {
    const dateEl = $('current-date');
    if (dateEl) {
        const d = new Date(certInfo.issuedAt);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        dateEl.textContent = d.toLocaleDateString('en-US', options);
    }
    const credEl = $('cert-credential-id');
    const liCredEl = $('li-cred-id-modal');
    if (credEl) {
        credEl.textContent = certInfo.code;
    }
    if (liCredEl) {
        liCredEl.textContent = certInfo.code;
    }
    const userCertName = $('cert-user-name');
    if (userCertName) userCertName.textContent = certInfo.userName;
    showScreen('certificate-screen');
}
// ─── Scroll Reveal Animations ─────────────────────────────
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            revealObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.15 });

document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

// ─── Animated Counters ───────────────────────────────────
let countersStarted = false;
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !countersStarted) {
            countersStarted = true;
            document.querySelectorAll('.stat-number[data-count]').forEach(el => {
                const target = parseInt(el.dataset.count);
                let current = 0;
                const step = Math.max(1, Math.floor(target / 40));
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) { current = target; clearInterval(timer); }
                    el.textContent = current + (target > 1 ? '+' : '');
                }, 30);
            });
        }
    });
}, { threshold: 0.3 });

const statsSection = document.querySelector('.stats-section');
if (statsSection) counterObserver.observe(statsSection);

// ─── Theme Toggle ─────────────────────────────────────────
(function initTheme() {
    const saved = localStorage.getItem('cssm_theme');
    if (saved === 'dark') {
        applyTheme('dark');
    } else {
        applyTheme('light');
    }
})();

function applyTheme(mode) {
    const iconEls = [$('theme-icon'), $('landing-theme-icon')];
    if (mode === 'light') {
        document.body.classList.add('light-mode');
        localStorage.setItem('cssm_theme', 'light');
        iconEls.forEach(iconEl => {
            if (iconEl) iconEl.setAttribute('data-lucide', 'sun');
        });
        if (window.lucide) lucide.createIcons();
    } else {
        document.body.classList.remove('light-mode');
        localStorage.setItem('cssm_theme', 'dark');
        iconEls.forEach(iconEl => {
            if (iconEl) iconEl.setAttribute('data-lucide', 'moon');
        });
        if (window.lucide) lucide.createIcons();
    }
}

const themeToggleBtns = [$('theme-toggle-btn'), $('landing-theme-toggle')];
themeToggleBtns.forEach(btn => {
    if (btn) {
        btn.addEventListener('click', () => {
            const isLight = document.body.classList.contains('light-mode');
            applyTheme(isLight ? 'dark' : 'light');
        });
    }
});

// ─── Mobile Menu Toggle ──────────────────────────────────
const landingMenuBtn = $('landing-menu-btn');
const landingNavActions = $('landing-nav-actions');
if (landingMenuBtn && landingNavActions) {
    landingMenuBtn.addEventListener('click', () => {
        const isOpen = landingNavActions.classList.toggle('show');
        landingMenuBtn.classList.toggle('active', isOpen);
        landingMenuBtn.setAttribute('aria-expanded', String(isOpen));
    });
}

const dashboardMenuBtn = $('dashboard-menu-btn');
const dashboardNavActions = $('dashboard-nav-actions');
if (dashboardMenuBtn && dashboardNavActions) {
    dashboardMenuBtn.addEventListener('click', () => {
        dashboardNavActions.classList.toggle('show');
    });
}

// Check for successful Xendit return
function checkXenditReturn() {
    const params = new URLSearchParams(window.location.search);
    if (params.has('voucher_success')) {
        const code = params.get('voucher_success');
        
        // Show success modal
        const codeEl = $('generated-code');
        if (codeEl) codeEl.textContent = code;
        const s1 = $('buy-step-1');
        const s2 = $('buy-step-2');
        if (s1) s1.classList.add('hidden');
        if (s2) s2.classList.remove('hidden');
        
        state.hasBoughtVoucher = true;
        localStorage.setItem('cssm_bought_voucher', 'true');
        updateVoucherButtons();
        
        openModal('modal-buy-voucher');
        showToast('Payment successful!', 'success');
        fetchNotifications();
        
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

// ─── Notification UI & API Logic ─────────────────────────────
async function fetchNotifications() {
    if (!state.user) return;
    try {
        const data = await apiRequest('/api/notifications');
        if (data && data.success) {
            renderNotifications(data.notifications);
        }
    } catch (e) {
        console.error("Failed to fetch notifications:", e);
    }
}

function renderNotifications(notifs) {
    const list = $('notif-list');
    const badge = $('notif-badge');
    if (!list) return;

    list.innerHTML = '';
    const unreadCount = notifs.filter(n => !n.is_read).length;

    if (badge) {
        if (unreadCount > 0) {
            badge.textContent = unreadCount;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    if (notifs.length === 0) {
        list.innerHTML = '<p class="notif-empty">No notifications yet</p>';
        return;
    }

    notifs.forEach(n => {
        const item = document.createElement('div');
        item.className = `notif-item ${n.is_read ? '' : 'unread'}`;
        
        const date = new Date(n.created_at);
        const timeStr = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        item.innerHTML = `
            <div class="notif-item-title">${n.title}</div>
            <div class="notif-item-message">${n.message}</div>
            <div class="notif-item-time">${timeStr}</div>
        `;

        if (!n.is_read) {
            item.addEventListener('click', async () => {
                try {
                    await apiRequest(`/api/notifications/${n.id}/read`, 'POST');
                    n.is_read = true;
                    item.classList.remove('unread');
                    const newUnread = notifs.filter(x => !x.is_read).length;
                    if (badge) {
                        if (newUnread > 0) {
                            badge.textContent = newUnread;
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                } catch (e) {}
            });
        }
        list.appendChild(item);
    });
}

// Attach Event Listeners for Notifications
const notifBtn = $('notif-btn');
const notifDropdown = $('notif-dropdown');
const notifClearAll = $('notif-clear-all');

if (notifBtn && notifDropdown) {
    notifBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notifDropdown.classList.toggle('hidden');
        if (!notifDropdown.classList.contains('hidden')) {
            fetchNotifications();
        }
    });

    document.addEventListener('click', (e) => {
        if (notifDropdown && !notifDropdown.contains(e.target) && e.target !== notifBtn && !notifBtn.contains(e.target)) {
            notifDropdown.classList.add('hidden');
        }
    });
}

if (notifClearAll) {
    notifClearAll.addEventListener('click', async (e) => {
        e.stopPropagation();
        try {
            await apiRequest('/api/notifications/read-all', 'POST');
            fetchNotifications();
        } catch (e) {}
    });
}

// ─── Certificate Actions ──────────────────────────────────
function copyCertId() {
    const credEl = document.getElementById('cert-credential-id');
    if (credEl && credEl.textContent) {
        navigator.clipboard.writeText(credEl.textContent).then(() => {
            showToast('Certificate ID copied to clipboard!', 'success');
        }).catch(err => {
            showToast('Failed to copy ID', 'error');
        });
    }
}

function downloadCertificate() {
    const certNode = document.getElementById('certificate');
    if (!certNode) {
        alert("Error: Certificate not found on page.");
        return;
    }
    
    try {
        const originalShadow = certNode.style.boxShadow;
        const originalTransform = certNode.style.transform;
        const originalAspectRatio = certNode.style.aspectRatio;
        
        const rect = certNode.getBoundingClientRect();
        const w = Math.round(rect.width) || 520;
        const h = Math.round(rect.height) || 402;
        
        certNode.style.width = w + 'px';
        certNode.style.height = h + 'px';
        certNode.style.aspectRatio = 'auto';
        certNode.style.boxShadow = 'none';
        certNode.style.transform = 'none';
        
        html2canvas(certNode, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            width: w,
            height: h
        }).then(canvas => {
            certNode.style.boxShadow = originalShadow;
            certNode.style.transform = originalTransform;
            certNode.style.aspectRatio = originalAspectRatio;
            certNode.style.width = '';
            certNode.style.height = '';
            
            const userName = (state.user && state.user.name) ? state.user.name.replace(/[^a-zA-Z0-9]/g, '_') : 'Learner';
            const fileName = `StudySync_Certificate_${userName}.png`;
            
            const link = document.createElement('a');
            link.download = fileName;
            link.href = canvas.toDataURL('image/png', 1.0);
            document.body.appendChild(link);
            link.click();
            setTimeout(() => document.body.removeChild(link), 100);
            
        }).catch(err => {
            certNode.style.boxShadow = originalShadow;
            certNode.style.transform = originalTransform;
            certNode.style.aspectRatio = originalAspectRatio;
            certNode.style.width = '';
            certNode.style.height = '';
            alert("Error rendering image. Please try another browser. Details: " + err);
        });
    } catch (e) {
        alert("Fatal error setting up download: " + e);
    }
}

function shareOnLinkedIn() {
    const text = encodeURIComponent("I just successfully completed all courses and passed the final exam in the CSS Tutorial at StudySync! Check out my new certification. #CSS #WebDevelopment #StudySync");
    const linkedInUrl = `https://www.linkedin.com/feed/?shareActive=true&text=${text}`;
    
    window.open(linkedInUrl, '_blank', 'noopener,noreferrer');
}

// Global ESC handler to close modals
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (typeof closeModal === 'function') closeModal();
        if (typeof hideCertificate === 'function') hideCertificate();
        
        const videoOverlay = $('video-modal-overlay');
        if (videoOverlay && !videoOverlay.classList.contains('hidden')) {
            videoOverlay.classList.add('hidden');
            const vp = $('video-player');
            if (vp) vp.pause();
        }
        
        const docOverlay = $('doc-modal-overlay');
        if (docOverlay && !docOverlay.classList.contains('hidden')) {
            docOverlay.classList.add('hidden');
        }
    }
});

// "No CSS" gimmick link
const noCssBtn = $('hero-no-css-btn');
if (noCssBtn) {
    noCssBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const styles = document.querySelectorAll('link[rel="stylesheet"], style');
        
        // Disable all CSS
        styles.forEach(el => el.disabled = true);
        
        // Create an "Exit" button with hardcoded inline styles so it looks like the landing page button
        const exitBtn = document.createElement('button');
        exitBtn.textContent = 'Bring CSS Back';
        exitBtn.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif, system-ui;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            z-index: 99999;
        `;
        
        exitBtn.addEventListener('click', () => {
            styles.forEach(el => el.disabled = false);
            exitBtn.remove();
        });
        
        document.body.appendChild(exitBtn);
    });
}
