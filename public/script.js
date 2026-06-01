let state = {
    user: null,
    currentTopicIndex: null,
    currentLessonIndex: 0,
    completedTopics: [],
    isFinalExam: false,
    voucherCode: localStorage.getItem('cssm_voucher') || null,
    courseUnlocked: false,
    hasBoughtVoucher: false
};

let topics = [];
let finalExam = [];

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
const affTypeSelect = $('su-afftype');
if (affTypeSelect) {
    affTypeSelect.addEventListener('change', function () {
        const label = $('aff-name-label');
        const input = $('su-affname');
        if (label) label.textContent = 'Organization / School Name';
        if (input) input.placeholder = 'e.g. University of the Philippines';
    });
}

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
        const affType = $('su-afftype').value;
        const affName = $('su-affname').value.trim();
        const phone   = $('su-phone').value.trim();
        const pw      = $('su-password').value;
        const conf    = $('su-confirm').value;

        if (!fname || !lname)              return showToast('First and last name are required.');
        if (!email || !email.includes('@')) return showToast('Enter a valid email address.');
        if (!bdate)                        return showToast('Birthdate is required.');
        if (!affName)                      return showToast(`${affType === 'school' ? 'School' : 'Company'} name is required.`);
        if (!phone)                        return showToast('Contact number is required.');
        if (pw.length < 6)                 return showToast('Password must be at least 6 characters.');
        if (pw !== conf)                   return showToast('Passwords do not match.');

        try {
            const data = await apiRequest('/api/auth/register', 'POST', {
                'su-fname': fname,
                'su-lname': lname,
                'su-email': email,
                'su-bdate': bdate,
                'su-afftype': affType,
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
async function loadTopicsIfNeeded() {
    if (topics.length > 0) return;

    try {
        const topicData = await apiRequest('/api/topics');
        if (topicData && topicData.success) {
            topics = topicData.topics;
        }
    } catch (e) {
        console.error("Topics catalog loading failed.", e);
    }
}

async function boot() {
    // 1. Fetch authenticated session first so guest visits do not trigger protected API errors
    try {
        const sessionData = await apiRequest('/api/auth/session');
        if (sessionData && sessionData.success && sessionData.user) {
            await loadTopicsIfNeeded();
            loginUser(sessionData.user);
        } else {
            showScreen('landing-screen');
        }
    } catch (e) {
        showScreen('landing-screen');
    }
}

// Start boot pipeline
boot();

async function loginUser(user) {
    state.user = user;
    state.courseUnlocked = user.isCourseUnlocked || false;

    await loadTopicsIfNeeded();
    
    // Fetch live progress
    try {
        const pData = await apiRequest('/api/progress');
        if (pData && pData.success) {
            state.completedTopics = pData.completedTopics;
            if (pData.lastTopicStarted) {
                state.lastTopicStarted = pData.lastTopicStarted;
            }
        }
    } catch (e) {
        state.completedTopics = [];
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
            if (data && data.success) {
                const codeEl = $('generated-code');
                if (codeEl) codeEl.textContent = data.code;
                const s1 = $('buy-step-1');
                const s2 = $('buy-step-2');
                if (s1) s1.classList.add('hidden');
                if (s2) s2.classList.remove('hidden');
                
                state.hasBoughtVoucher = true;
                localStorage.setItem('cssm_bought_voucher', 'true');
                updateVoucherButtons();
                
                showToast('Purchase successful!', 'success');
            }
        } catch (e) {}
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
                        state.isFinalExam = true;
                        const exam = await apiRequest('/api/exam/questions');
                        if (exam && exam.success) {
                            finalExam = exam.questions;
                            startQuiz(finalExam);
                            showToast('Voucher accepted! Starting exam...', 'success');
                        }
                    }
                }
            }
        } catch (e) {}
    });
}

// ─── Dashboard ───────────────────────────────────────────
function renderDashboard() {
    const container = $('topics-container');
    if (!container) return;
    container.innerHTML = '';

    const topicCountEl = $('dashboard-topic-count');
    if (topicCountEl) topicCountEl.textContent = String(topics.length);

    topics.forEach((topic, index) => {
        const done = state.completedTopics.includes(topic.id);
        const prevTopicId = index > 0 ? topics[index - 1].id : null;
        let unlocked = false;
        let lockMsg = '';

        if (!state.courseUnlocked) {
            unlocked = false;
            lockMsg = '<span class="topic-lock"><i data-lucide="lock"></i>Unlock courses with a voucher</span>';
        } else {
            unlocked = index === 0 || (prevTopicId && state.completedTopics.includes(prevTopicId));
            lockMsg = unlocked ? '' : '<span class="topic-lock"><i data-lucide="lock"></i>Complete the previous topic to unlock</span>';
        }

        const card = document.createElement('div');
        card.className = `topic-card ${done ? 'completed' : ''} ${unlocked ? '' : 'locked'}`.trim();
        card.innerHTML = `
            <p class="topic-num">Topic ${topic.id}</p>
            <h3>${topic.title}${done ? '<span class="topic-done-badge"><i data-lucide="check"></i></span>' : ''}</h3>
            <span>${topic.lessons.length} lesson${topic.lessons.length > 1 ? 's' : ''}</span>
            ${lockMsg}
        `;
        if (unlocked) {
            card.addEventListener('click', () => openTopic(index));
        }
        container.appendChild(card);
    });

    const pct = topics.length > 0 ? Math.round((state.completedTopics.length / topics.length) * 100) : 0;

    const summaryEl = $('dashboard-progress-summary');
    if (summaryEl) summaryEl.textContent = `${pct}%`;
    const completedEl = $('dashboard-modules-completed');
    if (completedEl) completedEl.textContent = `${state.completedTopics.length} / ${topics.length}`;

    const resumeBtn = $('resume-module-btn');
    if (resumeBtn) {
        if (!state.courseUnlocked) {
            resumeBtn.classList.add('hidden');
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
    resumeCourseBtn.addEventListener('click', () => {
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
        if (nextIndex >= 0) openTopic(nextIndex);
    });
}

function updateFinalCard() {
    const btn = $('final-exam-btn');
    if (!btn) return;
    const allDone = state.completedTopics.length === topics.length;

    const statusEl = $('final-card-status');
    const lockEl = $('final-card-lock');

    if (!allDone) {
        btn.className = 'topic-card locked final-exam-card';
        btn.onclick = null;
        if (statusEl) statusEl.textContent = 'Complete all topics to unlock this exam.';
        if (lockEl) lockEl.classList.remove('hidden');
    } else {
        btn.className = 'topic-card final-exam-card';
        btn.onclick = async () => {
            if (state.voucherCode) {
                state.isFinalExam = true;
                try {
                    const exam = await apiRequest('/api/exam/questions');
                    if (exam && exam.success) {
                        finalExam = exam.questions;
                        startQuiz(finalExam);
                    }
                } catch(e) {}
            } else {
                openModal('modal-enter-voucher');
            }
        };
        if (statusEl) statusEl.textContent = 'Ready to take the final exam.';
        if (lockEl) lockEl.classList.add('hidden');
    }
}

// ─── Lesson ──────────────────────────────────────────────
function openTopic(index) {
    state.currentTopicIndex = index;
    state.currentLessonIndex = 0;
    
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

function renderLesson() {
    const topic  = topics[state.currentTopicIndex];
    const lesson = topic.lessons[state.currentLessonIndex];

    const titleEl = $('current-topic-title');
    if (titleEl) titleEl.textContent = topic.title;

    const list = $('lesson-list');
    if (list) {
        list.innerHTML = '';
        topic.lessons.forEach((l, i) => {
            const item = document.createElement('div');
            item.className = `lesson-item ${i === state.currentLessonIndex ? 'active' : ''}`;
            item.textContent = l.title;
            item.addEventListener('click', () => { state.currentLessonIndex = i; renderLesson(); });
            list.appendChild(item);
        });
    }

    const player = $('video-player');
    if (player) player.src = lesson.videoUrl;
    
    const notesEl = $('lesson-notes-content');
    if (notesEl) notesEl.innerHTML = `<p>${lesson.notes}</p>`;
}

const backBtn = $('lesson-back-btn');
if (backBtn) backBtn.addEventListener('click', () => showScreen('dashboard-screen'));

const takeQuizBtn = $('take-quiz-btn');
if (takeQuizBtn) {
    takeQuizBtn.addEventListener('click', () => {
        state.isFinalExam = false;
        startQuiz(topics[state.currentTopicIndex].quiz);
    });
}

// ─── Quiz ─────────────────────────────────────────────────
let quizData = [], qIndex = 0, score = 0, selected = null, answersList = [];

function startQuiz(data) {
    quizData = data; qIndex = 0; score = 0; answersList = [];
    const totalQEl = $('total-q');
    if (totalQEl) totalQEl.textContent = data.length;
    renderQuestion();
    showScreen('quiz-screen');
}

function renderQuestion() {
    const q = quizData[qIndex];
    const currentQEl = $('current-q');
    if (currentQEl) currentQEl.textContent = qIndex + 1;
    const qTextEl = $('question-text');
    if (qTextEl) qTextEl.textContent = q.question;
    const progressBar = $('quiz-progress-bar');
    if (progressBar) progressBar.style.width = ((qIndex + 1) / quizData.length * 100) + '%';

    const opts = $('options-container');
    if (opts) {
        opts.innerHTML = '';
        selected = null;
        const nextBtn = $('next-q-btn');
        if (nextBtn) nextBtn.disabled = true;

        q.options.forEach((opt, i) => {
            const btn = document.createElement('button');
            btn.className = 'option-btn';
            btn.textContent = opt;
            btn.addEventListener('click', () => {
                document.querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                selected = i;
                if (nextBtn) nextBtn.disabled = false;
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

        document.querySelectorAll('.option-btn').forEach((btn, i) => {
            btn.disabled = true;
            // For regular quiz, we know answers index locally.
            // For final exam, options grading is performed securely at submit!
            if (!state.isFinalExam) {
                if (i === q.answer) btn.classList.add('correct');
                else if (i === selected) btn.classList.add('wrong');
            } else {
                if (i === selected) btn.classList.add('selected');
            }
        });

        if (!state.isFinalExam && selected === q.answer) {
            score++;
        }

        setTimeout(() => { 
            qIndex++; 
            qIndex < quizData.length ? renderQuestion() : finishQuiz(); 
        }, 1200);
    });
}

async function finishQuiz() {
    if (state.isFinalExam) {
        try {
            const data = await apiRequest('/api/exam/submit', 'POST', {
                'voucher_code': state.voucherCode,
                'answers': answersList
            });

            if (data && data.success) {
                if (data.passed) {
                    showCertificate(data.certificate);
                    showToast('Congratulations! You passed the final exam.', 'success');
                } else {
                    showToast(`You scored ${data.score}/${data.total}. A perfect score is required.`, 'error');
                    renderDashboard();
                    showScreen('dashboard-screen');
                }
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
        dateEl.innerHTML = `${certInfo.issuedAt}<br><small style="opacity: 0.8; font-size: 0.85em; font-family: monospace; display: block; margin-top: 4px;">VERIFIABLE CODE: ${certInfo.code}</small>`;
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
