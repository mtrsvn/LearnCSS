// ─── DATA (Inlined for reliability) ──────────────────────
const topics = [
  {
    id: 1,
    title: "CSS Introduction",
    lessons: [
      {
        title: "CSS Home & Introduction",
        videoUrl: "https://www.youtube.com/embed/1Rs2ND1ryYc",
        notes: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."
      },
      {
        title: "CSS Syntax & Selectors",
        videoUrl: "https://www.youtube.com/embed/l1mER1ZzY1Y",
        notes: "Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."
      }
    ],
    quiz: [
      {
        question: "What does CSS stand for?",
        options: ["Cascading Style Sheets", "Creative Style System", "Computer Style Sheets", "Colorful Style Sheets"],
        answer: 0
      },
      {
        question: "Which HTML tag is used to define an internal style sheet?",
        options: ["<script>", "<css>", "<style>", "<design>"],
        answer: 2
      }
    ]
  },
  {
    id: 2,
    title: "CSS Syntax Deep Dive",
    lessons: [
      {
        title: "Comments & Selectors",
        videoUrl: "https://www.youtube.com/embed/yfoY53QXEnI",
        notes: "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo."
      },
      {
        title: "Combinators & Pseudo-elements",
        videoUrl: "https://www.youtube.com/embed/mHAt-vYvFfM",
        notes: "Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet."
      }
    ],
    quiz: [
      {
        question: "How do you insert a comment in a CSS file?",
        options: ["// this is a comment", "/* this is a comment */", "' this is a comment", "// this is a comment //"],
        answer: 1
      },
      {
        question: "Which selector is used to style an element with a specific ID?",
        options: [".id", "#id", "*id", "id="],
        answer: 1
      }
    ]
  },
  {
    id: 3,
    title: "CSS Colors",
    lessons: [
      {
        title: "Colors, RGB, HEX, HSL",
        videoUrl: "https://www.youtube.com/embed/fD2Zp4baS24",
        notes: "Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae."
      }
    ],
    quiz: [
      {
        question: "Which property is used to change the background color?",
        options: ["color", "bgcolor", "background-color", "surface-color"],
        answer: 2
      },
      {
        question: "How do you write 'Hello World' in an HSL color format?",
        options: ["hsl(0, 100%, 50%)", "rgb(255, 0, 0)", "#FF0000", "red"],
        answer: 0
      }
    ]
  },
  {
    id: 4,
    title: "CSS Backgrounds",
    lessons: [
      {
        title: "Background Color & Images",
        videoUrl: "https://www.youtube.com/embed/yVIsP-O0n1M",
        notes: "At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident."
      }
    ],
    quiz: [
      {
        question: "Which property is used to set the background image of an element?",
        options: ["background-image", "image-background", "bg-image", "content-image"],
        answer: 0
      }
    ]
  },
  {
    id: 5,
    title: "CSS Borders",
    lessons: [
      {
        title: "Borders & Rounded Corners",
        videoUrl: "https://www.youtube.com/embed/n4p_nC-pTTo",
        notes: "Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est."
      }
    ],
    quiz: [
      {
        question: "Which property is used to change the border width?",
        options: ["border-width", "width-border", "thickness", "border-style"],
        answer: 0
      }
    ]
  },
  {
    id: 6,
    title: "CSS Margins & Box Model",
    lessons: [
      {
        title: "Margins & Box Model",
        videoUrl: "https://www.youtube.com/embed/nSst4-WbEzU",
        notes: "Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus."
      }
    ],
    quiz: [
      {
        question: "In the CSS box model, which one is the outermost layer?",
        options: ["Padding", "Border", "Margin", "Content"],
        answer: 2
      }
    ]
  },
  {
    id: 7,
    title: "CSS Padding & Outline",
    lessons: [
      {
        title: "Padding & Outlines",
        videoUrl: "https://www.youtube.com/embed/1Rs2ND1ryYc",
        notes: "Accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur."
      }
    ],
    quiz: [
      {
        question: "Which property is used to change the left padding of an element?",
        options: ["padding-left", "left-padding", "padding: left", "spacing-left"],
        answer: 0
      }
    ]
  },
  {
    id: 8,
    title: "CSS Text",
    lessons: [
      {
        title: "Text Formatting & Alignment",
        videoUrl: "https://www.youtube.com/embed/K8I8lSAsa6I",
        notes: "Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Lorem ipsum dolor sit amet, consectetur adipiscing."
      }
    ],
    quiz: [
      {
        question: "Which property is used to change the color of text?",
        options: ["text-color", "fgcolor", "color", "font-color"],
        answer: 2
      }
    ]
  },
  {
    id: 9,
    title: "CSS Fonts",
    lessons: [
      {
        title: "Font Families & Styles",
        videoUrl: "https://www.youtube.com/embed/hOshmK6CscA",
        notes: "Laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident."
      }
    ],
    quiz: [
      {
        question: "Which CSS property controls the text size?",
        options: ["font-style", "text-size", "font-size", "text-style"],
        answer: 2
      }
    ]
  },
  {
    id: 10,
    title: "CSS Links, Lists & Tables",
    lessons: [
      {
        title: "Links, Lists & Tables",
        videoUrl: "https://www.youtube.com/embed/cy9Hh6VvXN4",
        notes: "Similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque."
      }
    ],
    quiz: [
      {
        question: "How do you remove the underline from all hyperlinks?",
        options: ["a {text-decoration:none;}", "a {underline:none;}", "a {decoration:no-underline;}", "a {text-style:none;}"],
        answer: 0
      }
    ]
  }
];

const finalExam = [
  {
    question: "What is the correct CSS syntax?",
    options: ["body {color: black;}", "{body;color:black;}", "body:color=black;", "{body:color=black;}"],
    answer: 0
  },
  {
    question: "How do you select an element with id 'demo'?",
    options: [".demo", "#demo", "*demo", "demo"],
    answer: 1
  },
  {
    question: "How do you select elements with class name 'test'?",
    options: ["*test", "#test", ".test", "test"],
    answer: 2
  },
  {
    question: "How do you display hyperlinks without an underline?",
    options: ["a {decoration:no-underline;}", "a {text-decoration:none;}", "a {text-decoration:no-underline;}", "a {underline:none;}"],
    answer: 1
  },
  {
    question: "Which property is used to change the left margin of an element?",
    options: ["margin-left", "padding-left", "indent", "left-margin"],
    answer: 0
  }
];

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

// ─── Storage Helpers ──────────────────────────────────────
const USERS_KEY    = 'cssm_users';
const SESSION_KEY  = 'cssm_session';
const VOUCHERS_KEY = 'cssm_vouchers';

const getUsers     = ()      => JSON.parse(localStorage.getItem(USERS_KEY)    || '{}');
const saveUsers    = u       => localStorage.setItem(USERS_KEY, JSON.stringify(u));
const getSession   = ()      => JSON.parse(localStorage.getItem(SESSION_KEY)  || 'null');
const saveSession  = u       => localStorage.setItem(SESSION_KEY, JSON.stringify(u));
const clearSession = ()      => localStorage.removeItem(SESSION_KEY);
const getProgress  = email   => JSON.parse(localStorage.getItem(`cssm_p_${email}`) || '[]');
const saveProgress = (e, d)  => localStorage.setItem(`cssm_p_${e}`, JSON.stringify(d));

function getVouchers() { return JSON.parse(localStorage.getItem(VOUCHERS_KEY) || '{}'); }
function saveVouchers(v) { localStorage.setItem(VOUCHERS_KEY, JSON.stringify(v)); }

function generateVoucherCode() {
    const seg = () => Math.random().toString(36).substring(2, 6).toUpperCase();
    return `CSSM-${seg()}-${seg()}`;
}

function createVoucher() {
    const code = generateVoucherCode();
    const vouchers = getVouchers();
    vouchers[code] = { used: false, usedBy: null };
    saveVouchers(vouchers);
    return code;
}

function isValidVoucher(code) {
    const vouchers = getVouchers();
    const v = vouchers[code.toUpperCase()];
    return v && !v.used;
}

function redeemVoucher(code, email) {
    const vouchers = getVouchers();
    vouchers[code.toUpperCase()].used = true;
    vouchers[code.toUpperCase()].usedBy = email;
    saveVouchers(vouchers);
}

// ─── App State ───────────────────────────────────────────
let state = {
    user: null,
    currentTopicIndex: null,
    currentLessonIndex: 0,
    completedTopics: [],
    isFinalExam: false
};

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
        const isSchool = this.value === 'school';
        const label = $('aff-name-label');
        const input = $('su-affname');
        if (label) label.textContent = isSchool ? 'University / School Name' : 'Company / Organization Name';
        if (input) input.placeholder = isSchool ? 'e.g. University of the Philippines' : 'e.g. Acme Corporation';
    });
}

// ─── Modal Triggers ──────────────────────────────────────
const triggers = [
    { id: 'nav-login-btn', modal: 'modal-login' },
    { id: 'nav-signup-btn', modal: 'modal-signup' },
    { id: 'hero-signup-btn', modal: 'modal-signup' },
    { id: 'hero-login-btn', modal: 'modal-login' },
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

const navBuyVoucher = $('buy-voucher-nav-btn');
if (navBuyVoucher) navBuyVoucher.addEventListener('click', () => openBuyVoucherModal());

// ─── Sign Up ─────────────────────────────────────────────
const signupBtn = $('signup-btn');
if (signupBtn) {
    signupBtn.addEventListener('click', () => {
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

        const users = getUsers();
        if (users[email])                  return showToast('An account with this email already exists.');

        const user = {
            name: `${fname} ${lname}`,
            firstName: fname, lastName: lname,
            email, bdate, affType, affName, phone
        };
        users[email] = { ...user, password: pw };
        saveUsers(users);
        saveSession(user);
        loginUser(user);
        showToast('Account created successfully!', 'success');
    });
}

// ─── Login ───────────────────────────────────────────────
const loginBtn = $('login-btn');
if (loginBtn) {
    loginBtn.addEventListener('click', () => {
        const emailInput = $('li-email');
        const pwInput    = $('li-password');
        if (!emailInput || !pwInput) return;

        const email = emailInput.value.trim().toLowerCase();
        const pw    = pwInput.value;

        if (!email || !email.includes('@')) return showToast('Enter a valid email address.');
        if (!pw)                            return showToast('Password is required.');

        const users   = getUsers();
        let user = users[email];

        if (!user) {
            user = {
                firstName: email.split('@')[0],
                name: email.split('@')[0],
                email: email,
                isGuest: true
            };
            users[email] = { ...user, password: pw };
            saveUsers(users);
        } else {
            if (user.password !== pw) return showToast('Incorrect password.');
        }

        const { password: _, ...sessionUser } = user;
        saveSession(sessionUser);
        loginUser(sessionUser);
        showToast(`Welcome, ${sessionUser.firstName}!`, 'success');
    });
}

// ─── Forgot Password ─────────────────────────────────────
const forgotBtn = $('forgot-btn');
if (forgotBtn) {
    forgotBtn.addEventListener('click', () => {
        const email = $('fp-email').value.trim().toLowerCase();
        if (!email || !email.includes('@')) return showToast('Enter a valid email address.');

        const users = getUsers();
        if (!users[email]) return showToast('No account found with this email.');

        showToast(`Your password is: "${users[email].password}"`, 'info');
    });
}

// ─── Logout ──────────────────────────────────────────────
const logoutBtn = $('logout-btn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
        clearSession();
        state.user = null;
        state.completedTopics = [];
        showScreen('landing-screen');
        showToast('Logged out successfully.', 'info');
    });
}

// ─── Auto-login ──────────────────────────────────────────
(function boot() {
    const session = getSession();
    if (session) loginUser(session);
})();

function loginUser(user) {
    state.user = user;
    state.completedTopics = getProgress(user.email);
    const dispName = $('display-name');
    if (dispName) dispName.textContent = user.firstName || user.name;
    const certName = $('cert-user-name');
    if (certName) certName.textContent = user.name;
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
    buyConfirmBtn.addEventListener('click', () => {
        const code = createVoucher();
        const codeEl = $('generated-code');
        if (codeEl) codeEl.textContent = code;
        const s1 = $('buy-step-1');
        const s2 = $('buy-step-2');
        if (s1) s1.classList.add('hidden');
        if (s2) s2.classList.remove('hidden');
        showToast('Purchase successful!', 'success');
    });
}

const doneBuyingBtn = $('done-buying-btn');
if (doneBuyingBtn) doneBuyingBtn.addEventListener('click', closeModal);

// ─── Enter Voucher ───────────────────────────────────────
const redeemBtn = $('redeem-voucher-btn');
if (redeemBtn) {
    redeemBtn.addEventListener('click', () => {
        const input = $('voucher-input');
        if (!input) return;
        const code = input.value.trim();
        if (!code) return showToast('Please enter a voucher code.');

        // Simulation: Accept any non-empty code for frontend-only demo
        closeModal();
        state.isFinalExam = true;
        startQuiz(finalExam);
        showToast('Voucher accepted! Starting exam...', 'success');
    });
}

// ─── Dashboard ───────────────────────────────────────────
function renderDashboard() {
    const container = $('topics-container');
    if (!container) return;
    container.innerHTML = '';

    topics.forEach((topic, index) => {
        const done = state.completedTopics.includes(topic.id);
        const card = document.createElement('div');
        card.className = `topic-card ${done ? 'completed' : ''}`;
        card.innerHTML = `
            <p class="topic-num">Topic ${topic.id}</p>
            <h3>${topic.title}${done ? '<span class="topic-done-badge">✓</span>' : ''}</h3>
            <span>${topic.lessons.length} lesson${topic.lessons.length > 1 ? 's' : ''}</span>
        `;
        card.addEventListener('click', () => openTopic(index));
        container.appendChild(card);
    });

    const pct = Math.round((state.completedTopics.length / topics.length) * 100);
    const pctEl = $('progress-percent');
    if (pctEl) pctEl.textContent = `${pct}%`;

    updateFinalCard();
}

function updateFinalCard() {
    const btn = $('final-exam-btn');
    if (!btn) return;
    const allDone = state.completedTopics.length === topics.length;

    btn.className = 'final-card needs-voucher';
    btn.onclick = () => openModal('modal-enter-voucher');

    const statusEl = $('final-card-status');
    const badgeEl = $('final-badge-icon');

    if (!allDone) {
        if (statusEl) statusEl.textContent = 'Unlock the exam by entering your voucher code.';
        if (badgeEl) badgeEl.innerHTML = '<i data-lucide="ticket"></i>';
    } else {
        if (statusEl) statusEl.textContent = 'Enter your voucher code to begin the exam.';
        if (badgeEl) badgeEl.innerHTML = '<i data-lucide="ticket"></i>';
    }
    if (window.lucide) lucide.createIcons();
}

// ─── Lesson ──────────────────────────────────────────────
function openTopic(index) {
    state.currentTopicIndex = index;
    state.currentLessonIndex = 0;
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
let quizData = [], qIndex = 0, score = 0, selected = null;

function startQuiz(data) {
    quizData = data; qIndex = 0; score = 0;
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
        document.querySelectorAll('.option-btn').forEach((btn, i) => {
            btn.disabled = true;
            if (i === q.answer) btn.classList.add('correct');
            else if (i === selected) btn.classList.add('wrong');
        });
        if (selected === q.answer) score++;
        setTimeout(() => { qIndex++; qIndex < quizData.length ? renderQuestion() : finishQuiz(); }, 1200);
    });
}

function finishQuiz() {
    if (state.isFinalExam) {
        if (score === quizData.length) {
            showCertificate();
            showToast('Congratulations! You passed the final exam.', 'success');
        } else {
            showToast(`You scored ${score}/${quizData.length}. A perfect score is required.`, 'error');
            renderDashboard();
            showScreen('dashboard-screen');
        }
    } else {
        const topicId = topics[state.currentTopicIndex].id;
        if (!state.completedTopics.includes(topicId)) {
            state.completedTopics.push(topicId);
            saveProgress(state.user.email, state.completedTopics);
        }
        showToast(`Quiz complete! Score: ${score}/${quizData.length}`, 'success');
        renderDashboard();
        showScreen('dashboard-screen');
    }
}

// ─── Certificate ──────────────────────────────────────────
function showCertificate() {
    const dateEl = $('current-date');
    if (dateEl) dateEl.textContent = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    const userCertName = $('cert-user-name');
    if (userCertName) userCertName.textContent = state.user.name;
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
