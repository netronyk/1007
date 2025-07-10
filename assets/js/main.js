/**
 * Sport365 - Main JavaScript
 * JavaScript ראשי למערכת
 */

// ===== הגדרות גלובליות =====
const Sport365 = {
    config: {
        siteUrl: 'https://windex.co.il/sport365/',
        apiUrl: 'https://windex.co.il/sport365/api/',
        animationDuration: 300,
        scrollOffset: 80
    },
    
    // אתחול המערכת
    init() {
        this.setupEventListeners();
        this.initSmoothScrolling();
        this.initNavigation();
        this.initAlerts();
        this.initAnimations();
        this.initMobileOptimizations();
    },
    
    // הגדרת מאזיני אירועים
    setupEventListeners() {
        // טעינת העמוד
        window.addEventListener('load', () => {
            document.body.classList.add('loaded');
            this.hideLoadingSpinner();
        });
        
        // גלילה
        window.addEventListener('scroll', this.throttle(() => {
            this.handleScroll();
        }, 100));
        
        // שינוי גודל מסך
        window.addEventListener('resize', this.throttle(() => {
            this.handleResize();
        }, 250));
        
        // מקלדת
        document.addEventListener('keydown', (e) => {
            this.handleKeyboard(e);
        });
    },
    
    // גלילה חלקה
    initSmoothScrolling() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = anchor.getAttribute('href').substring(1);
                const target = document.getElementById(targetId);
                
                if (target) {
                    const offsetTop = target.offsetTop - this.config.scrollOffset;
                    
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                    
                    // עדכון היסטוריית הדפדפן
                    history.pushState(null, null, anchor.getAttribute('href'));
                }
            });
        });
    },
    
    // ניהול ניווט
    initNavigation() {
        const navbar = document.querySelector('.navbar');
        const navLinks = document.querySelectorAll('.nav-link');
        
        // הדגשת קישור פעיל
        this.updateActiveNavLink();
        
        // ניווט נייד
        this.setupMobileNavigation();
        
        // תפריט דביק
        this.setupStickyNavigation();
    },
    
    // עדכון קישור פעיל בניווט
    updateActiveNavLink() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
        
        window.addEventListener('scroll', () => {
            let currentSection = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop - this.config.scrollOffset;
                const sectionHeight = section.offsetHeight;
                
                if (window.pageYOffset >= sectionTop && 
                    window.pageYOffset < sectionTop + sectionHeight) {
                    currentSection = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${currentSection}`) {
                    link.classList.add('active');
                }
            });
        });
    },
    
    // ניווט נייד
    setupMobileNavigation() {
        const toggleButton = document.querySelector('.navbar-toggler');
        const mobileMenu = document.querySelector('.navbar-nav');
        
        if (toggleButton && mobileMenu) {
            toggleButton.addEventListener('click', () => {
                this.toggleMobileMenu(mobileMenu);
            });
            
            // סגירה בלחיצה מחוץ לתפריט
            document.addEventListener('click', (e) => {
                if (!toggleButton.contains(e.target) && !mobileMenu.contains(e.target)) {
                    this.closeMobileMenu(mobileMenu);
                }
            });
        }
    },
    
    // פתיחה/סגירה של תפריט נייד
    toggleMobileMenu(menu) {
        menu.classList.toggle('d-none');
        menu.classList.toggle('mobile-menu-open');
        
        // הוספת אנימציה
        if (menu.classList.contains('mobile-menu-open')) {
            menu.style.maxHeight = menu.scrollHeight + 'px';
        } else {
            menu.style.maxHeight = '0';
        }
    },
    
    // סגירת תפריט נייד
    closeMobileMenu(menu) {
        menu.classList.add('d-none');
        menu.classList.remove('mobile-menu-open');
        menu.style.maxHeight = '0';
    },
    
    // תפריט דביק
    setupStickyNavigation() {
        const navbar = document.querySelector('.navbar');
        let lastScrollTop = 0;
        
        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > 100) {
                navbar.classList.add('navbar-scrolled');
                
                // הסתרה בגלילה למטה, הצגה בגלילה למעלה
                if (scrollTop > lastScrollTop) {
                    navbar.style.transform = 'translateY(-100%)';
                } else {
                    navbar.style.transform = 'translateY(0)';
                }
            } else {
                navbar.classList.remove('navbar-scrolled');
                navbar.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop;
        });
    },
    
    // ניהול התראות
    initAlerts() {
        // סגירה אוטומטית של התראות
        setTimeout(() => {
            this.autoHideAlerts();
        }, 5000);
        
        // כפתורי סגירה
        document.querySelectorAll('.alert .btn-close').forEach(button => {
            button.addEventListener('click', (e) => {
                this.closeAlert(e.target.closest('.alert'));
            });
        });
    },
    
    // הסתרה אוטומטית של התראות
    autoHideAlerts() {
        const alerts = document.querySelectorAll('.status-alert .alert');
        alerts.forEach(alert => {
            this.closeAlert(alert);
        });
    },
    
    // סגירת התראה
    closeAlert(alert) {
        if (alert) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            
            setTimeout(() => {
                alert.style.display = 'none';
                const container = alert.closest('.status-alert');
                if (container && container.children.length === 1) {
                    container.style.display = 'none';
                }
            }, this.config.animationDuration);
        }
    },
    
    // אנימציות
    initAnimations() {
        // אנימציות בכניסה לתצוגה
        this.setupScrollAnimations();
        
        // אפקטי hover
        this.setupHoverEffects();
        
        // אנימציות מספרים
        this.setupCounterAnimations();
    },
    
    // אנימציות בגלילה
    setupScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);
        
        // צפייה באלמנטים
        document.querySelectorAll('.feature-card, .stat-item, .hero-content > *').forEach(el => {
            observer.observe(el);
        });
    },
    
    // אפקטי hover
    setupHoverEffects() {
        // כרטיסים
        document.querySelectorAll('.card, .feature-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-10px)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });
        
        // כפתורים
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('mouseenter', () => {
                button.style.transform = 'translateY(-2px)';
            });
            
            button.addEventListener('mouseleave', () => {
                button.style.transform = 'translateY(0)';
            });
        });
    },
    
    // אנימציות מספרים
    setupCounterAnimations() {
        const counters = document.querySelectorAll('.stat-number');
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        });
        
        counters.forEach(counter => {
            counterObserver.observe(counter);
        });
    },
    
    // אנימצית מונה
    animateCounter(element) {
        const text = element.textContent;
        const numbers = text.match(/[\d,]+/);
        
        if (numbers) {
            const finalNumber = parseInt(numbers[0].replace(/,/g, ''));
            const icon = element.querySelector('i');
            const iconHtml = icon ? icon.outerHTML : '';
            
            let currentNumber = 0;
            const increment = Math.ceil(finalNumber / 50);
            
            const timer = setInterval(() => {
                currentNumber += increment;
                if (currentNumber >= finalNumber) {
                    currentNumber = finalNumber;
                    clearInterval(timer);
                }
                
                element.innerHTML = iconHtml + this.formatNumber(currentNumber);
            }, 30);
        }
    },
    
    // עיצוב מספר
    formatNumber(num) {
        return num.toLocaleString('he-IL');
    },
    
    // אופטימיזציות מובייל
    initMobileOptimizations() {
        // זיהוי מכשיר מובייל
        if (this.isMobile()) {
            document.body.classList.add('is-mobile');
            
            // מניעת זום באינפוטים
            this.preventMobileZoom();
            
            // אופטימיזציית מגע
            this.optimizeTouchEvents();
        }
    },
    
    // זיהוי מובייל
    isMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    },
    
    // מניעת זום באינפוטים
    preventMobileZoom() {
        document.querySelectorAll('input, select, textarea').forEach(input => {
            if (input.type !== 'file') {
                input.style.fontSize = '16px';
            }
        });
    },
    
    // אופטימיזציית מגע
    optimizeTouchEvents() {
        // הוספת מחלקת מגע לכפתורים
        document.querySelectorAll('.btn, .nav-link, .card').forEach(element => {
            element.addEventListener('touchstart', () => {
                element.classList.add('touching');
            });
            
            element.addEventListener('touchend', () => {
                setTimeout(() => {
                    element.classList.remove('touching');
                }, 150);
            });
        });
    },
    
    // טיפול בגלילה
    handleScroll() {
        const scrollTop = window.pageYOffset;
        
        // אפקט פרלקסה פשוט
        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.style.transform = `translateY(${scrollTop * 0.5}px)`;
        }
        
        // כפתור חזרה למעלה
        this.toggleBackToTopButton(scrollTop);
    },
    
    // כפתור חזרה למעלה
    toggleBackToTopButton(scrollTop) {
        let backToTop = document.querySelector('.back-to-top');
        
        if (!backToTop) {
            backToTop = this.createBackToTopButton();
        }
        
        if (scrollTop > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    },
    
    // יצירת כפתור חזרה למעלה
    createBackToTopButton() {
        const button = document.createElement('button');
        button.className = 'back-to-top';
        button.innerHTML = '<i class="fas fa-arrow-up"></i>';
        button.setAttribute('aria-label', 'חזרה למעלה');
        
        button.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        document.body.appendChild(button);
        return button;
    },
    
    // טיפול בשינוי גודל מסך
    handleResize() {
        // סגירת תפריט נייד בשינוי למסך גדול
        if (window.innerWidth >= 992) {
            const mobileMenu = document.querySelector('.navbar-nav');
            if (mobileMenu) {
                this.closeMobileMenu(mobileMenu);
            }
        }
        
        // עדכון גובה תפריט נייד
        this.updateMobileMenuHeight();
    },
    
    // עדכון גובה תפריט נייד
    updateMobileMenuHeight() {
        const mobileMenu = document.querySelector('.navbar-nav.mobile-menu-open');
        if (mobileMenu) {
            mobileMenu.style.maxHeight = mobileMenu.scrollHeight + 'px';
        }
    },
    
    // טיפול במקלדת
    handleKeyboard(e) {
        // ESC לסגירת תפריטים
        if (e.key === 'Escape') {
            const mobileMenu = document.querySelector('.navbar-nav.mobile-menu-open');
            if (mobileMenu) {
                this.closeMobileMenu(mobileMenu);
            }
        }
        
        // נגישות - ניווט במקלדת
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    },
    
    // הסתרת ספינר טעינה
    hideLoadingSpinner() {
        const spinner = document.querySelector('.loading-spinner');
        if (spinner) {
            spinner.style.opacity = '0';
            setTimeout(() => {
                spinner.style.display = 'none';
            }, this.config.animationDuration);
        }
    },
    
    // פונקציית throttle
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },
    
    // פונקציית debounce
    debounce(func, wait, immediate) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    },
    
    // הצגת הודעה
    showMessage(message, type = 'info') {
        const alertContainer = document.querySelector('.status-alert') || this.createAlertContainer();
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            <i class="fas fa-${this.getAlertIcon(type)}"></i>
            <strong>${message}</strong>
            <button type="button" class="btn-close" onclick="Sport365.closeAlert(this.closest('.alert'))">×</button>
        `;
        
        alertContainer.appendChild(alert);
        
        // סגירה אוטומטית
        setTimeout(() => {
            this.closeAlert(alert);
        }, 5000);
    },
    
    // יצירת מכולת התראות
    createAlertContainer() {
        const container = document.createElement('div');
        container.className = 'status-alert';
        document.body.appendChild(container);
        return container;
    },
    
    // קבלת אייקון התראה
    getAlertIcon(type) {
        const icons = {
            success: 'check-circle',
            warning: 'exclamation-triangle',
            danger: 'times-circle',
            info: 'info-circle'
        };
        return icons[type] || icons.info;
    }
};

// הפעלת המערכת כשהדף נטען
document.addEventListener('DOMContentLoaded', () => {
    Sport365.init();
});

// הוספת CSS דינמי לאנימציות
const dynamicCSS = `
    .navbar-scrolled {
        background: rgba(255, 255, 255, 0.98) !important;
        box-shadow: 0 2px 20px rgba(0,0,0,0.15);
    }
    
    .mobile-menu-open {
        display: block !important;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }
    
    .animate-in {
        animation: slideInUp 0.6s ease forwards;
    }
    
    .touching {
        transform: scale(0.95);
        transition: transform 0.1s ease;
    }
    
    .back-to-top {
        position: fixed;
        bottom: 30px;
        left: 30px;
        background: var(--primary-gradient, linear-gradient(135deg, #1e40af 0%, #0891b2 100%));
        color: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .back-to-top.show {
        opacity: 1;
        visibility: visible;
    }
    
    .back-to-top:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }
    
    .is-mobile .hover-lift:hover {
        transform: none;
    }
    
    .keyboard-navigation button:focus,
    .keyboard-navigation a:focus {
        outline: 2px solid var(--sport365-orange, #ea580c);
        outline-offset: 2px;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;

// הוספת ה-CSS לדף
const styleSheet = document.createElement('style');
styleSheet.textContent = dynamicCSS;
document.head.appendChild(styleSheet);