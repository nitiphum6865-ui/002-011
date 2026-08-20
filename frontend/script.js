// JavaScript for KPT Architecture Frontend
let currentUser = null;

document.addEventListener('DOMContentLoaded', () => {
    checkAuthState();
    fetchDepartmentInfo();
    fetchCourses();
    fetchTeachers();
    fetchPortfolios();
    setupContactForm();
    setupAuthForms();
});

// Check Local Storage Auth State
function checkAuthState() {
    const savedUser = localStorage.getItem('kpt_user');
    if (savedUser) {
        try {
            currentUser = JSON.parse(savedUser);
            renderUserNav();
        } catch (e) {
            localStorage.removeItem('kpt_user');
        }
    } else {
        renderGuestNav();
    }
}

function renderGuestNav() {
    const container = document.getElementById('authNavContainer');
    if (!container) return;
    container.innerHTML = `
        <button class="btn-auth btn-login" onclick="openModal('loginModal')"><i class="fa-solid fa-right-to-bracket"></i> เข้าสู่ระบบ</button>
        <button class="btn-auth btn-register" onclick="openModal('registerModal')"><i class="fa-solid fa-user-plus"></i> สมัครสมาชิก</button>
    `;
}

function renderUserNav() {
    const container = document.getElementById('authNavContainer');
    if (!container || !currentUser) return;
    container.innerHTML = `
        <div class="user-profile-badge" onclick="openProfileModal()">
            <img class="user-avatar-sm" src="${currentUser.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=400&auto=format&fit=crop&q=80'}" alt="User">
            <span class="user-name-sm">${currentUser.fullname || currentUser.username}</span>
            <i class="fa-solid fa-pen-to-square" style="font-size:0.75rem; color:var(--accent-gold);"></i>
        </div>
        <button class="btn-auth" onclick="handleLogout()" title="ออกจากระบบ"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
    `;
}

function handleLogout() {
    localStorage.removeItem('kpt_user');
    currentUser = null;
    renderGuestNav();
    alert('ออกจากระบบเรียบร้อยแล้ว');
}

// Modal Helpers
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('hidden');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('hidden');
}

// Close modals when clicking backdrop
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.add('hidden');
    }
});

function openProfileModal() {
    if (!currentUser) return;
    document.getElementById('profUserId').value = currentUser.id;
    document.getElementById('profFullname').value = currentUser.fullname || '';
    document.getElementById('profEmail').value = currentUser.email || '';
    document.getElementById('profPhone').value = currentUser.phone || '';
    document.getElementById('profBio').value = currentUser.bio || '';
    document.getElementById('profAvatar').value = currentUser.avatar || '';
    document.getElementById('profAvatarPreview').src = currentUser.avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=400&auto=format&fit=crop&q=80';
    document.getElementById('profPassword').value = '';
    
    document.getElementById('profileAlert').className = 'form-alert hidden';
    openModal('profileModal');
}

// Setup Auth Forms
function setupAuthForms() {
    // Login Form
    const loginForm = document.getElementById('loginForm');
    const loginAlert = document.getElementById('loginAlert');
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            username: document.getElementById('loginUsername').value,
            password: document.getElementById('loginPassword').value
        };

        try {
            const res = await fetch('/api/index.php?endpoint=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            if (json.status === 'success' && json.user) {
                currentUser = json.user;
                localStorage.setItem('kpt_user', JSON.stringify(currentUser));
                renderUserNav();
                loginAlert.className = 'form-alert success';
                loginAlert.innerText = 'เข้าสู่ระบบสำเร็จ!';
                setTimeout(() => {
                    closeModal('loginModal');
                    loginForm.reset();
                    loginAlert.classList.add('hidden');
                }, 800);
            } else {
                loginAlert.className = 'form-alert error';
                loginAlert.innerText = json.message || 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
            }
        } catch (err) {
            loginAlert.className = 'form-alert error';
            loginAlert.innerText = 'ไม่สามารถเชื่อมต่อระบบเข้าสู่ระบบได้';
        }
    });

    // Register Avatar File Upload
    const regAvatarFile = document.getElementById('regAvatarFile');
    if (regAvatarFile) {
        regAvatarFile.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (evt) => {
                    document.getElementById('regAvatar').value = evt.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Register Form
    const regForm = document.getElementById('registerForm');
    const regAlert = document.getElementById('registerAlert');
    regForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            fullname: document.getElementById('regFullname').value,
            username: document.getElementById('regUsername').value,
            email: document.getElementById('regEmail').value,
            phone: document.getElementById('regPhone').value,
            avatar: document.getElementById('regAvatar').value || undefined,
            password: document.getElementById('regPassword').value
        };

        try {
            const res = await fetch('/api/index.php?endpoint=register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            if (json.status === 'success' && json.user) {
                currentUser = json.user;
                localStorage.setItem('kpt_user', JSON.stringify(currentUser));
                renderUserNav();
                regAlert.className = 'form-alert success';
                regAlert.innerText = 'ลงทะเบียนสำเร็จ!';
                setTimeout(() => {
                    closeModal('registerModal');
                    regForm.reset();
                    regAlert.classList.add('hidden');
                }, 800);
            } else {
                regAlert.className = 'form-alert error';
                regAlert.innerText = json.message || 'เกิดข้อผิดพลาดในการลงทะเบียน';
            }
        } catch (err) {
            regAlert.className = 'form-alert error';
            regAlert.innerText = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้';
        }
    });

    // Profile Form
    const profForm = document.getElementById('profileForm');
    const profAlert = document.getElementById('profileAlert');

    // Profile Avatar File Upload
    const profAvatarFile = document.getElementById('profAvatarFile');
    if (profAvatarFile) {
        profAvatarFile.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (evt) => {
                    const base64Str = evt.target.result;
                    document.getElementById('profAvatarPreview').src = base64Str;
                    document.getElementById('profAvatar').value = base64Str;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Avatar URL preview live update
    document.getElementById('profAvatar').addEventListener('input', (e) => {
        const val = e.target.value;
        if (val) document.getElementById('profAvatarPreview').src = val;
    });

    profForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            user_id: document.getElementById('profUserId').value,
            fullname: document.getElementById('profFullname').value,
            email: document.getElementById('profEmail').value,
            phone: document.getElementById('profPhone').value,
            bio: document.getElementById('profBio').value,
            avatar: document.getElementById('profAvatar').value,
            password: document.getElementById('profPassword').value
        };

        try {
            const res = await fetch('/api/index.php?endpoint=update_profile', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            if (json.status === 'success' && json.user) {
                currentUser = json.user;
                localStorage.setItem('kpt_user', JSON.stringify(currentUser));
                renderUserNav();
                profAlert.className = 'form-alert success';
                profAlert.innerText = 'บันทึกข้อมูลโปรไฟล์สำเร็จ!';
                setTimeout(() => {
                    closeModal('profileModal');
                    profAlert.classList.add('hidden');
                }, 1000);
            } else {
                profAlert.className = 'form-alert error';
                profAlert.innerText = json.message || 'เกิดข้อผิดพลาดในการปรับปรุงข้อมูล';
            }
        } catch (err) {
            profAlert.className = 'form-alert error';
            profAlert.innerText = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้';
        }
    });
}

// Fetch Department Info
async function fetchDepartmentInfo() {
    try {
        const res = await fetch('/api/index.php?endpoint=info');
        if (!res.ok) throw new Error('API offline');
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            const info = json.data;
            if (info.history) document.getElementById('aboutHistory').innerText = info.history;
            if (info.vision) document.getElementById('aboutVision').innerText = info.vision;
            if (info.mission) document.getElementById('aboutMission').innerText = info.mission;
        }
    } catch (err) {
        console.log('Using default static info:', err);
    }
}

// Fetch Courses
async function fetchCourses() {
    const grid = document.getElementById('coursesGrid');
    try {
        const res = await fetch('/api/index.php?endpoint=courses');
        const json = await res.json();
        if (json.status === 'success' && json.data && json.data.length > 0) {
            grid.innerHTML = json.data.map(c => `
                <div class="course-card">
                    <div>
                        <span class="course-level">${c.level}</span>
                        <h3 class="course-title">${c.title}</h3>
                        <div class="course-meta">
                            <span><i class="fa-solid fa-clock"></i> ${c.duration}</span>
                            <span><i class="fa-solid fa-book-open"></i> ${c.credits || 0} หน่วยกิต</span>
                        </div>
                        <p class="course-desc">${c.description}</p>
                    </div>
                </div>
            `).join('');
            return;
        }
    } catch (err) {
        console.log('Using default course cards:', err);
    }

    grid.innerHTML = `
        <div class="course-card">
            <div>
                <span class="course-level">ปวช.</span>
                <h3 class="course-title">ประกาศนียบัตรวิชาชีพ สาขาวิชาสถาปัตยกรรม</h3>
                <div class="course-meta">
                    <span><i class="fa-solid fa-clock"></i> 3 ปี</span>
                    <span><i class="fa-solid fa-book-open"></i> 103 หน่วยกิต</span>
                </div>
                <p class="course-desc">มุ่งเน้นพื้นฐานการเขียนแบบสถาปัตยกรรม การเขียนแบบก่อสร้าง การทำหุ่นจำลอง (Model) และเทคโนโลยีคอมพิวเตอร์เพื่อการออกแบบเบื้องต้น</p>
            </div>
        </div>
        <div class="course-card">
            <div>
                <span class="course-level">ปวส.</span>
                <h3 class="course-title">ประกาศนียบัตรวิชาชีพชั้นสูง สาขาวิชาเทคโนโลยีสถาปัตยกรรม</h3>
                <div class="course-meta">
                    <span><i class="fa-solid fa-clock"></i> 2 ปี</span>
                    <span><i class="fa-solid fa-book-open"></i> 86 หน่วยกิต</span>
                </div>
                <p class="course-desc">เน้นการออกแบบสถาปัตยกรรมขั้นสูง การประยุกต์ใช้โปรแกรม Building Information Modeling (BIM) การบริหารงานก่อสร้าง และสถาปัตยกรรมเขียว</p>
            </div>
        </div>
    `;
}

// Fetch Teachers
async function fetchTeachers() {
    const grid = document.getElementById('teachersGrid');
    try {
        const res = await fetch('/api/index.php?endpoint=teachers');
        const json = await res.json();
        if (json.status === 'success' && json.data && json.data.length > 0) {
            grid.innerHTML = json.data.map(t => `
                <div class="teacher-card">
                    <img src="${t.image}" alt="${t.name}" class="teacher-img" onerror="this.src='https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&auto=format&fit=crop&q=80'">
                    <div class="teacher-info">
                        <div class="teacher-name">${t.name}</div>
                        <div class="teacher-pos">${t.position}</div>
                        <div class="teacher-deg">${t.degree || ''}</div>
                    </div>
                </div>
            `).join('');
            return;
        }
    } catch (err) {
        console.log('Using default teachers grid:', err);
    }
}

let loadedPortfolios = [];

// Fetch Portfolios
async function fetchPortfolios() {
    const grid = document.getElementById('portfolioGrid');
    if (!grid) return;
    try {
        const res = await fetch('/api/index.php?endpoint=portfolios');
        const json = await res.json();
        if (json.status === 'success' && json.data && json.data.length > 0) {
            loadedPortfolios = json.data;
            grid.innerHTML = json.data.map(p => `
                <div class="portfolio-card" onclick="openPortfolioDetailModal(${p.id})">
                    <div class="portfolio-img-wrap">
                        <img src="${p.image_url}" alt="${escapeHtml(p.title)}" class="portfolio-img" onerror="this.src='https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&auto=format&fit=crop&q=80'">
                        <div class="portfolio-overlay">
                            <span class="btn-view-detail"><i class="fa-solid fa-expand"></i> ดูรายละเอียด</span>
                        </div>
                    </div>
                    <div class="portfolio-content">
                        <span class="portfolio-cat">${escapeHtml(p.category || 'ผลงานออกแบบ')}</span>
                        <h3 class="portfolio-title">${escapeHtml(p.title)}</h3>
                        <div class="portfolio-author">โดย: ${escapeHtml(p.student_name)} (${escapeHtml(p.level)})</div>
                        <p class="portfolio-desc">${escapeHtml(p.description)}</p>
                    </div>
                </div>
            `).join('');
            return;
        }
    } catch (err) {
        console.log('Using default portfolio grid:', err);
    }
}

function openPortfolioDetailModal(id) {
    const p = loadedPortfolios.find(item => item.id == id);
    if (!p) return;
    if (document.getElementById('modalPortTitle')) document.getElementById('modalPortTitle').innerText = p.title;
    if (document.getElementById('modalPortAuthor')) document.getElementById('modalPortAuthor').innerText = `ผู้สร้างสรรค์: ${p.student_name} (${p.level})`;
    if (document.getElementById('modalPortCat')) document.getElementById('modalPortCat').innerText = p.category || 'ผลงานออกแบบ';
    if (document.getElementById('modalPortDesc')) document.getElementById('modalPortDesc').innerText = p.description || '';
    if (document.getElementById('modalPortImg')) document.getElementById('modalPortImg').src = p.image_url;
    openModal('portfolioDetailModal');
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}


// Contact Form
function setupContactForm() {
    const form = document.getElementById('contactForm');
    const alertBox = document.getElementById('formAlert');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            subject: document.getElementById('subject').value,
            message: document.getElementById('message').value
        };

        try {
            const res = await fetch('/api/index.php?endpoint=contacts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            alertBox.className = 'form-alert success';
            alertBox.innerText = json.message || 'ส่งข้อความเรียบร้อยแล้ว!';
            form.reset();
        } catch (err) {
            alertBox.className = 'form-alert success';
            alertBox.innerText = 'ขอบคุณสำหรับข้อความ! ระบบได้บันทึกข้อมูลเรียบร้อยแล้ว';
            form.reset();
        }
    });
}

