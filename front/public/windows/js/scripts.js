// Application Data
const apps = {
    dashboard: {
        title: 'داشبورد',
        icon: 'fas fa-chart-line',
        content: `
            <h2>داشبورد مدیریتی</h2>
            <div class="content-grid">
                <div class="stat-card">
                    <h3>کاربران فعال</h3>
                    <div class="stat-value">1,234</div>
                    <div class="stat-change">↑ 12% نسبت به ماه قبل</div>
                </div>
                <div class="stat-card">
                    <h3>بازدید امروز</h3>
                    <div class="stat-value">5,678</div>
                    <div class="stat-change">↑ 8% نسبت به دیروز</div>
                </div>
                <div class="stat-card">
                    <h3>درآمد ماهانه</h3>
                    <div class="stat-value">12.5M</div>
                    <div class="stat-change">↑ 15% نسبت به ماه قبل</div>
                </div>
                <div class="stat-card">
                    <h3>سفارشات</h3>
                    <div class="stat-value">892</div>
                    <div class="stat-change">↑ 5% نسبت به ماه قبل</div>
                </div>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>شناسه</th>
                            <th>نام</th>
                            <th>ایمیل</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>علی احمدی</td>
                            <td>ali@example.com</td>
                            <td>فعال</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>مریم رضایی</td>
                            <td>maryam@example.com</td>
                            <td>فعال</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>حسین کریمی</td>
                            <td>hossein@example.com</td>
                            <td>غیرفعال</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `
    },
    users: {
        title: 'مدیریت کاربران',
        icon: 'fas fa-users',
        content: `
            <h2>مدیریت کاربران</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>شناسه</th>
                            <th>نام کاربری</th>
                            <th>ایمیل</th>
                            <th>نقش</th>
                            <th>تاریخ عضویت</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>admin</td>
                            <td>admin@example.com</td>
                            <td>مدیر</td>
                            <td>1402/01/15</td>
                            <td>فعال</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>user1</td>
                            <td>user1@example.com</td>
                            <td>کاربر</td>
                            <td>1402/02/20</td>
                            <td>فعال</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>user2</td>
                            <td>user2@example.com</td>
                            <td>کاربر</td>
                            <td>1402/03/10</td>
                            <td>غیرفعال</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `
    },
    settings: {
        title: 'تنظیمات سیستم',
        icon: 'fas fa-cog',
        content: `
            <h2>تنظیمات سیستم</h2>
            <div class="content-grid">
                <div class="stat-card">
                    <h3>تنظیمات عمومی</h3>
                    <p>تنظیمات کلی سیستم و پیکربندی</p>
                </div>
                <div class="stat-card">
                    <h3>امنیت</h3>
                    <p>تنظیمات امنیتی و دسترسی‌ها</p>
                </div>
                <div class="stat-card">
                    <h3>پشتیبان‌گیری</h3>
                    <p>تنظیمات پشتیبان‌گیری خودکار</p>
                </div>
                <div class="stat-card">
                    <h3>اعلان‌ها</h3>
                    <p>تنظیمات اعلان‌ها و هشدارها</p>
                </div>
            </div>
        `
    },
    reports: {
        title: 'گزارشات',
        icon: 'fas fa-file-alt',
        content: `
            <h2>گزارشات سیستم</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>شناسه گزارش</th>
                            <th>نوع گزارش</th>
                            <th>تاریخ</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>RPT-001</td>
                            <td>گزارش فروش</td>
                            <td>1403/09/15</td>
                            <td>تکمیل شده</td>
                        </tr>
                        <tr>
                            <td>RPT-002</td>
                            <td>گزارش کاربران</td>
                            <td>1403/09/14</td>
                            <td>تکمیل شده</td>
                        </tr>
                        <tr>
                            <td>RPT-003</td>
                            <td>گزارش عملکرد</td>
                            <td>1403/09/13</td>
                            <td>در حال پردازش</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `
    },
    analytics: {
        title: 'تحلیل و آمار',
        icon: 'fas fa-chart-bar',
        content: `
            <h2>تحلیل و آمار</h2>
            <div class="content-grid">
                <div class="stat-card">
                    <h3>بازدید صفحات</h3>
                    <div class="stat-value">45,678</div>
                    <div class="stat-change">این هفته</div>
                </div>
                <div class="stat-card">
                    <h3>نرخ تبدیل</h3>
                    <div class="stat-value">3.2%</div>
                    <div class="stat-change">↑ 0.5% نسبت به هفته قبل</div>
                </div>
                <div class="stat-card">
                    <h3>زمان متوسط</h3>
                    <div class="stat-value">4:32</div>
                    <div class="stat-change">دقیقه</div>
                </div>
                <div class="stat-card">
                    <h3>نرخ پرش</h3>
                    <div class="stat-value">28%</div>
                    <div class="stat-change">↓ 2% نسبت به هفته قبل</div>
                </div>
            </div>
        `
    },
    files: {
        title: 'مدیریت فایل‌ها',
        icon: 'fas fa-folder',
        content: `
            <h2>مدیریت فایل‌ها</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>نام فایل</th>
                            <th>نوع</th>
                            <th>اندازه</th>
                            <th>تاریخ تغییر</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>document.pdf</td>
                            <td>PDF</td>
                            <td>2.5 MB</td>
                            <td>1403/09/15</td>
                        </tr>
                        <tr>
                            <td>image.jpg</td>
                            <td>تصویر</td>
                            <td>1.2 MB</td>
                            <td>1403/09/14</td>
                        </tr>
                        <tr>
                            <td>data.xlsx</td>
                            <td>اکسل</td>
                            <td>856 KB</td>
                            <td>1403/09/13</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `
    },
    notifications: {
        title: 'اعلان‌ها',
        icon: 'fas fa-bell',
        content: `
            <h2>اعلان‌های سیستم</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>عنوان</th>
                            <th>پیام</th>
                            <th>تاریخ</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>ورود جدید</td>
                            <td>کاربر جدیدی به سیستم وارد شد</td>
                            <td>1403/09/15 10:30</td>
                            <td>خوانده نشده</td>
                        </tr>
                        <tr>
                            <td>پشتیبان‌گیری</td>
                            <td>پشتیبان‌گیری با موفقیت انجام شد</td>
                            <td>1403/09/15 08:00</td>
                            <td>خوانده شده</td>
                        </tr>
                        <tr>
                            <td>هشدار امنیتی</td>
                            <td>تلاش برای ورود غیرمجاز شناسایی شد</td>
                            <td>1403/09/14 22:15</td>
                            <td>خوانده شده</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `
    },
    logs: {
        title: 'لاگ سیستم',
        icon: 'fas fa-list-alt',
        content: `
            <h2>لاگ‌های سیستم</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>زمان</th>
                            <th>سطح</th>
                            <th>پیام</th>
                            <th>منبع</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1403/09/15 10:30:25</td>
                            <td>INFO</td>
                            <td>کاربر با موفقیت وارد شد</td>
                            <td>Auth</td>
                        </tr>
                        <tr>
                            <td>1403/09/15 10:28:10</td>
                            <td>WARNING</td>
                            <td>مصرف حافظه بالا است</td>
                            <td>System</td>
                        </tr>
                        <tr>
                            <td>1403/09/15 10:25:00</td>
                            <td>ERROR</td>
                            <td>خطا در اتصال به پایگاه داده</td>
                            <td>Database</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `
    }
};

// Global Variables
let openWindows = [];
let windowZIndex = 100;
let isDragging = false;
let dragOffset = { x: 0, y: 0 };
let currentDraggedWindow = null;
let isDraggingIcon = false;
let currentDraggedIcon = null;
let iconDragOffset = { x: 0, y: 0 };

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Check if user is logged in
    checkAuth();
    
    initializeDateTime();
    initializeStartMenu();
    initializeDesktopIcons();
    initializeContextMenu();
    setInterval(updateDateTime, 1000);
});

// Check Authentication
function checkAuth() {
    const isLoggedIn = sessionStorage.getItem('isLoggedIn');
    if (isLoggedIn !== 'true') {
        window.location.href = 'login.html';
    }
}

// Logout Function
function logout() {
    sessionStorage.removeItem('isLoggedIn');
    sessionStorage.removeItem('username');
    window.location.href = 'login.html';
}

// Date and Time
function initializeDateTime() {
    updateDateTime();
}

function updateDateTime() {
    const now = new Date();
    const time = now.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
    const date = now.toLocaleDateString('fa-IR');
    
    const timeElement = document.querySelector('.datetime .time');
    const dateElement = document.querySelector('.datetime .date');
    
    if (timeElement) timeElement.textContent = time;
    if (dateElement) dateElement.textContent = date;
}

// Start Menu
function initializeStartMenu() {
    const startBtn = document.getElementById('startBtn');
    const startMenu = document.getElementById('startMenu');
    
    startBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleStartMenu();
    });
    
    startMenu.addEventListener('click', (e) => {
        e.stopPropagation();
    });
    
    document.addEventListener('click', (e) => {
        if (!startMenu.contains(e.target) && !startBtn.contains(e.target)) {
            closeStartMenu();
        }
    });
    
    // App tiles in start menu
    const appTiles = startMenu.querySelectorAll('.app-tile');
    appTiles.forEach(tile => {
        tile.addEventListener('click', () => {
            const appName = tile.getAttribute('data-app');
            openApp(appName);
            closeStartMenu();
        });
    });
    
    // Power button (Logout)
    const powerButton = startMenu.querySelector('.power-button');
    if (powerButton) {
        powerButton.addEventListener('click', () => {
            if (confirm('آیا می‌خواهید از سیستم خارج شوید؟')) {
                logout();
            }
        });
    }
}

function toggleStartMenu() {
    const startMenu = document.getElementById('startMenu');
    const startBtn = document.getElementById('startBtn');
    
    if (startMenu.classList.contains('active')) {
        closeStartMenu();
    } else {
        startMenu.classList.add('active');
        startBtn.classList.add('active');
    }
}

function closeStartMenu() {
    const startMenu = document.getElementById('startMenu');
    const startBtn = document.getElementById('startBtn');
    startMenu.classList.remove('active');
    startBtn.classList.remove('active');
}

// Desktop Icons
function initializeDesktopIcons() {
    const desktopIcons = document.querySelectorAll('.desktop-icon');
    const desktop = document.querySelector('.desktop');
    
    // بارگذاری موقعیت‌ها از localStorage
    loadIconPositions();
    
    desktopIcons.forEach((icon, index) => {
        const appName = icon.getAttribute('data-app');
        
        // تنظیم موقعیت اولیه یا از localStorage
        const savedPosition = getIconPosition(appName);
        if (savedPosition) {
            icon.style.left = `${savedPosition.x}px`;
            icon.style.top = `${savedPosition.y}px`;
        } else {
            // موقعیت پیش‌فرض (grid layout)
            const cols = Math.floor((window.innerWidth - 40) / 100);
            const row = Math.floor(index / cols);
            const col = index % cols;
            const x = 20 + (col * 100);
            const y = 20 + (row * 100);
            icon.style.left = `${x}px`;
            icon.style.top = `${y}px`;
            saveIconPosition(appName, x, y);
        }
        
        // دوبار کلیک برای باز کردن برنامه
        icon.addEventListener('dblclick', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (!isDraggingIcon) {
                openApp(appName);
            }
            isDraggingIcon = false;
        });
        
        // کلیک برای انتخاب
        icon.addEventListener('click', (e) => {
            if (!isDraggingIcon) {
                e.stopPropagation();
                desktopIcons.forEach(i => i.classList.remove('selected'));
                icon.classList.add('selected');
            }
        });
        
        // شروع drag
        let dragStartPos = { x: 0, y: 0 };
        let hasMoved = false;
        
        icon.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return; // فقط کلیک چپ
            
            isDraggingIcon = false;
            hasMoved = false;
            currentDraggedIcon = icon;
            
            const rect = icon.getBoundingClientRect();
            const desktopRect = desktop.getBoundingClientRect();
            
            dragStartPos.x = e.clientX;
            dragStartPos.y = e.clientY;
            
            iconDragOffset.x = e.clientX - rect.left;
            iconDragOffset.y = e.clientY - rect.top;
            
            const handleMouseMove = (e) => {
                if (!currentDraggedIcon) return;
                
                // بررسی اینکه آیا ماوس حرکت کرده است
                const moveDistance = Math.abs(e.clientX - dragStartPos.x) + Math.abs(e.clientY - dragStartPos.y);
                if (moveDistance > 5) {
                    hasMoved = true;
                    if (!isDraggingIcon) {
                        isDraggingIcon = true;
                        currentDraggedIcon.classList.add('dragging');
                    }
                }
                
                if (isDraggingIcon) {
                    const x = e.clientX - desktopRect.left - iconDragOffset.x;
                    const y = e.clientY - desktopRect.top - iconDragOffset.y;
                    
                    // محدود کردن به محدوده دسکتاپ
                    const maxX = desktopRect.width - rect.width;
                    const maxY = desktopRect.height - rect.height;
                    
                    const constrainedX = Math.max(0, Math.min(x, maxX));
                    const constrainedY = Math.max(0, Math.min(y, maxY));
                    
                    currentDraggedIcon.style.left = `${constrainedX}px`;
                    currentDraggedIcon.style.top = `${constrainedY}px`;
                }
            };
            
            const handleMouseUp = () => {
                if (isDraggingIcon && currentDraggedIcon && hasMoved) {
                    const rect = currentDraggedIcon.getBoundingClientRect();
                    const desktopRect = desktop.getBoundingClientRect();
                    const x = rect.left - desktopRect.left;
                    const y = rect.top - desktopRect.top;
                    
                    saveIconPosition(appName, x, y);
                }
                
                if (currentDraggedIcon) {
                    currentDraggedIcon.classList.remove('dragging');
                }
                
                isDraggingIcon = false;
                currentDraggedIcon = null;
                hasMoved = false;
                
                document.removeEventListener('mousemove', handleMouseMove);
                document.removeEventListener('mouseup', handleMouseUp);
            };
            
            document.addEventListener('mousemove', handleMouseMove);
            document.addEventListener('mouseup', handleMouseUp);
        });
    });
    
    document.addEventListener('click', () => {
        if (!isDraggingIcon) {
            desktopIcons.forEach(i => i.classList.remove('selected'));
        }
    });
}

// توابع localStorage برای ذخیره موقعیت آیکون‌ها
function saveIconPosition(appName, x, y) {
    const positions = getIconPositions();
    positions[appName] = { x, y };
    localStorage.setItem('desktopIconPositions', JSON.stringify(positions));
}

function getIconPosition(appName) {
    const positions = getIconPositions();
    return positions[appName] || null;
}

function getIconPositions() {
    const saved = localStorage.getItem('desktopIconPositions');
    return saved ? JSON.parse(saved) : {};
}

function loadIconPositions() {
    const positions = getIconPositions();
    const desktopIcons = document.querySelectorAll('.desktop-icon');
    
    desktopIcons.forEach(icon => {
        const appName = icon.getAttribute('data-app');
        const position = positions[appName];
        if (position) {
            icon.style.left = `${position.x}px`;
            icon.style.top = `${position.y}px`;
        }
    });
}

// Open Application
function openApp(appName) {
    // Check if window is already open
    const existingWindow = openWindows.find(w => w.appName === appName);
    if (existingWindow) {
        focusWindow(existingWindow.id);
        return;
    }
    
    const app = apps[appName];
    if (!app) return;
    
    const windowId = `window-${Date.now()}`;
    const window = createWindow(windowId, appName, app);
    
    openWindows.push({
        id: windowId,
        appName: appName,
        element: window
    });
    
    updateTaskbar();
    focusWindow(windowId);
}

function createWindow(id, appName, app) {
    const windowsContainer = document.querySelector('.windows-container');
    const window = document.createElement('div');
    window.className = 'window';
    window.id = id;
    
    // Center position
    const windowWidth = 800;
    const windowHeight = 600;
    const desktop = document.querySelector('.desktop');
    const desktopRect = desktop ? desktop.getBoundingClientRect() : { width: window.innerWidth, height: window.innerHeight - 50 };
    
    // محاسبه موقعیت وسط با در نظر گیری محدودیت‌ها
    let x = Math.max(0, (desktopRect.width - windowWidth) / 2);
    let y = Math.max(0, (desktopRect.height - windowHeight) / 2);
    
    // اگر پنجره بزرگتر از صفحه است، از بالا و چپ شروع شود
    if (desktopRect.width < windowWidth) {
        x = 0;
    }
    if (desktopRect.height < windowHeight) {
        y = 0;
    }
    
    window.style.left = `${x}px`;
    window.style.top = `${y}px`;
    
    window.innerHTML = `
        <div class="window-header">
            <div class="window-title">
                <i class="${app.icon}"></i>
                <span>${app.title}</span>
            </div>
            <div class="window-controls">
                <button class="window-control minimize" title="کوچک کردن">
                    <i class="fas fa-minus"></i>
                </button>
                <button class="window-control maximize" title="بزرگ کردن">
                    <i class="fas fa-square"></i>
                </button>
                <button class="window-control close" title="بستن">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="window-content">
            ${app.content}
        </div>
    `;
    
    windowsContainer.appendChild(window);
    
    // Window controls
    const closeBtn = window.querySelector('.close');
    const minimizeBtn = window.querySelector('.minimize');
    const maximizeBtn = window.querySelector('.maximize');
    const header = window.querySelector('.window-header');
    
    closeBtn.addEventListener('click', () => closeWindow(id));
    minimizeBtn.addEventListener('click', () => minimizeWindow(id));
    maximizeBtn.addEventListener('click', () => maximizeWindow(id));
    
    // Make window draggable
    makeWindowDraggable(window, header);
    
    // Focus on click
    window.addEventListener('mousedown', () => focusWindow(id));
    
    return window;
}

function closeWindow(windowId) {
    const windowIndex = openWindows.findIndex(w => w.id === windowId);
    if (windowIndex === -1) return;
    
    const window = openWindows[windowIndex].element;
    window.style.animation = 'windowClose 0.3s ease forwards';
    
    setTimeout(() => {
        window.remove();
        openWindows.splice(windowIndex, 1);
        updateTaskbar();
    }, 300);
}

function minimizeWindow(windowId) {
    const window = openWindows.find(w => w.id === windowId);
    if (!window) return;
    
    window.element.style.display = 'none';
    updateTaskbar();
}

function maximizeWindow(windowId) {
    const window = openWindows.find(w => w.id === windowId);
    if (!window) return;
    
    const element = window.element;
    if (element.style.width === '100%') {
        element.style.width = '800px';
        element.style.height = '600px';
        element.style.left = '';
        element.style.top = '';
    } else {
        element.style.width = '100%';
        element.style.height = 'calc(100vh - 50px)';
        element.style.left = '0';
        element.style.top = '0';
    }
}

function focusWindow(windowId) {
    const window = openWindows.find(w => w.id === windowId);
    if (!window) return;
    
    window.element.style.display = 'flex';
    window.element.style.zIndex = ++windowZIndex;
    
    // Update taskbar
    const taskbarApps = document.querySelectorAll('.taskbar-app');
    taskbarApps.forEach(app => app.classList.remove('active'));
    
    const taskbarApp = document.querySelector(`.taskbar-app[data-window="${windowId}"]`);
    if (taskbarApp) {
        taskbarApp.classList.add('active');
    }
}

function makeWindowDraggable(window, header) {
    header.addEventListener('mousedown', (e) => {
        isDragging = true;
        currentDraggedWindow = window;
        
        const rect = window.getBoundingClientRect();
        dragOffset.x = e.clientX - rect.left;
        dragOffset.y = e.clientY - rect.top;
        
        window.style.cursor = 'move';
    });
    
    document.addEventListener('mousemove', (e) => {
        if (isDragging && currentDraggedWindow) {
            const x = e.clientX - dragOffset.x;
            const y = e.clientY - dragOffset.y;
            
            currentDraggedWindow.style.left = `${Math.max(0, x)}px`;
            currentDraggedWindow.style.top = `${Math.max(0, y)}px`;
        }
    });
    
    document.addEventListener('mouseup', () => {
        isDragging = false;
        if (currentDraggedWindow) {
            currentDraggedWindow.style.cursor = '';
            currentDraggedWindow = null;
        }
    });
}

// Taskbar
function updateTaskbar() {
    const taskbarApps = document.getElementById('taskbarApps');
    taskbarApps.innerHTML = '';
    
    openWindows.forEach(win => {
        const app = apps[win.appName];
        if (!app) return;
        
        const taskbarApp = document.createElement('div');
        taskbarApp.className = 'taskbar-app';
        taskbarApp.setAttribute('data-window', win.id);
        taskbarApp.innerHTML = `
            <i class="${app.icon}"></i>
            <span>${app.title}</span>
        `;
        
        taskbarApp.addEventListener('click', () => {
            if (win.element.style.display === 'none') {
                win.element.style.display = 'flex';
            }
            focusWindow(win.id);
        });
        
        taskbarApps.appendChild(taskbarApp);
    });
}

// Context Menu
function initializeContextMenu() {
    const contextMenu = document.getElementById('contextMenu');
    
    // تابع برای بستن منو
    function closeContextMenu() {
        contextMenu.classList.remove('active');
        contextMenu.style.display = 'none';
    }
    
    // تابع برای باز کردن منو
    function openContextMenu(x, y, icon) {
        // بستن منوی قبلی
        closeContextMenu();
        
        // انتخاب آیکون
        document.querySelectorAll('.desktop-icon').forEach(i => {
            i.classList.remove('selected');
        });
        if (icon) {
            icon.classList.add('selected');
        }
        
        // باز کردن منو در موقعیت جدید
        setTimeout(() => {
            contextMenu.style.left = `${x}px`;
            contextMenu.style.top = `${y}px`;
            contextMenu.style.display = 'flex';
            contextMenu.classList.add('active');
        }, 10);
    }
    
    // رویداد کلیک راست
    document.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        
        const desktopIcon = e.target.closest('.desktop-icon');
        if (desktopIcon) {
            // اگر روی آیکون کلیک راست شد
            openContextMenu(e.clientX, e.clientY, desktopIcon);
        } else {
            // اگر روی دسکتاپ کلیک راست شد، منو را ببند
            closeContextMenu();
        }
    });
    
    // رویداد کلیک چپ - بستن منو در صورت کلیک خارج از منو
    document.addEventListener('click', (e) => {
        // اگر کلیک روی منو نبود، منو را ببند
        if (!contextMenu.contains(e.target)) {
            closeContextMenu();
        }
    });
    
    // رویداد کلیک روی گزینه‌های منو
    const contextItems = contextMenu.querySelectorAll('.context-menu-item');
    contextItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.stopPropagation();
            const action = item.getAttribute('data-action');
            handleContextAction(action);
            // بستن منو بعد از کلیک
            closeContextMenu();
        });
    });
    
    // بستن منو با ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeContextMenu();
        }
    });
}

function handleContextAction(action) {
    const selectedIcon = document.querySelector('.desktop-icon.selected');
    if (!selectedIcon) return;
    
    const appName = selectedIcon.getAttribute('data-app');
    
    switch(action) {
        case 'open':
            openApp(appName);
            break;
        case 'rename':
            // Implement rename functionality
            break;
        case 'delete':
            // Implement delete functionality
            break;
        case 'properties':
            // Implement properties functionality
            break;
    }
    
    // حذف انتخاب بعد از انجام عمل
    selectedIcon.classList.remove('selected');
}

// Add window close animation
const style = document.createElement('style');
style.textContent = `
    @keyframes windowClose {
        to {
            transform: scale(0.8);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);