/**
 * RyPanel v3.0 FINAL - Complete JavaScript
 * Smart caching, loading bar, voting animations, theme-aware icons
 */

(function() {
    'use strict';
    
    // ========================================================================
    // SMART CACHING SYSTEM
    // ========================================================================
    const SmartCache = {
        version: '3.0',
        
        init() {
            this.checkCache();
            this.showLoadingIfNeeded();
        },
        
        checkCache() {
            const cachedVersion = localStorage.getItem('rypanel_cache_version');
            
            if (cachedVersion !== this.version) {
                console.log('New version detected, clearing cache');
                localStorage.setItem('rypanel_cache_version', this.version);
                localStorage.setItem('rypanel_cached', 'false');
            }
        },
        
        showLoadingIfNeeded() {
            const isCached = localStorage.getItem('rypanel_cached') === 'true';
            
            if (!isCached) {
                this.showLoadingBar();
                
                window.addEventListener('load', () => {
                    this.hideLoadingBar();
                    localStorage.setItem('rypanel_cached', 'true');
                });
            }
        },
        
        showLoadingBar() {
            const bar = document.createElement('div');
            bar.id = 'loading-bar';
            bar.className = 'active';
            bar.innerHTML = '<div id="loading-progress"></div>';
            document.body.insertBefore(bar, document.body.firstChild);
            
            const progress = document.getElementById('loading-progress');
            let width = 0;
            
            const interval = setInterval(() => {
                width += Math.random() * 30;
                if (width > 90) {
                    width = 90;
                    clearInterval(interval);
                }
                progress.style.width = width + '%';
            }, 200);
            
            this.loadingInterval = interval;
        },
        
        hideLoadingBar() {
            if (this.loadingInterval) clearInterval(this.loadingInterval);
            
            const progress = document.getElementById('loading-progress');
            if (progress) {
                progress.style.width = '100%';
                setTimeout(() => {
                    const bar = document.getElementById('loading-bar');
                    if (bar) bar.remove();
                }, 300);
            }
        }
    };
    
    // ========================================================================
    // RESPONSIVE UI MANAGER
    // ========================================================================
    const ResponsiveUI = {
        init() {
            this.detectDevice();
            this.handleResize();
            window.addEventListener('resize', this.debounce(() => {
                this.handleResize();
            }, 250));
        },
        
        detectDevice() {
            const width = window.innerWidth;
            const height = window.innerHeight;
            const aspectRatio = width / height;
            const orientation = width > height ? 'landscape' : 'portrait';
            
            let device = 'mobile';
            if (width >= 1024) device = 'desktop';
            else if (width >= 768) device = 'tablet';
            
            let aspectCategory = 'standard';
            if (aspectRatio > 1.85) aspectCategory = 'ultrawide';
            else if (aspectRatio > 1.7) aspectCategory = 'wide';
            else if (aspectRatio < 0.75) aspectCategory = 'tall';
            
            document.documentElement.style.setProperty('--vw', `${width}px`);
            document.documentElement.style.setProperty('--vh', `${height}px`);
            document.documentElement.style.setProperty('--aspect-ratio', aspectRatio.toFixed(3));
            
            document.body.setAttribute('data-device', device);
            document.body.setAttribute('data-orientation', orientation);
            document.body.setAttribute('data-aspect', aspectCategory);
            
            return { width, height, aspectRatio, device, orientation, aspectCategory };
        },
        
        handleResize() {
            const info = this.detectDevice();
            this.optimizeTables(info);
        },
        
        optimizeTables(info) {
            const tables = document.querySelectorAll('.table');
            tables.forEach(table => {
                if (info.device === 'mobile') {
                    const headers = Array.from(table.querySelectorAll('th')).map(th => th.textContent.trim());
                    table.querySelectorAll('tbody tr').forEach(row => {
                        row.querySelectorAll('td').forEach((td, index) => {
                            if (headers[index]) {
                                td.setAttribute('data-label', headers[index]);
                            }
                        });
                    });
                }
            });
        },
        
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };
    
    // ========================================================================
    // THEME MANAGER - Theme-Aware Icons
    // ========================================================================
    const ThemeManager = {
        init() {
            this.loadTheme();
            this.setupSwitcher();
            this.watchSystemTheme();
            this.updateIcons();
        },
        
        loadTheme() {
            const theme = localStorage.getItem('theme') || 'auto';
            this.setTheme(theme, false);
        },
        
        setTheme(theme, save = true) {
            document.documentElement.setAttribute('data-theme', theme);
            
            if (save) {
                localStorage.setItem('theme', theme);
                
                fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=change_theme&theme=${theme}`
                }).catch(() => {});
            }
            
            document.querySelectorAll('.theme-option').forEach(opt => {
                opt.classList.toggle('selected', opt.getAttribute('data-theme') === theme);
            });
            
            this.updateIcons();
        },
        
        updateIcons() {
            const isDark = this.isDarkTheme();
            const color = isDark ? 'F8FAFC' : '1A1A1A';
            
            document.querySelectorAll('img[src*="iconify.design"]').forEach(img => {
                const src = img.src;
                if (src.includes('color=')) {
                    img.src = src.replace(/color=%23[0-9A-Fa-f]{6}/, `color=%23${color}`);
                }
            });
        },
        
        isDarkTheme() {
            const theme = document.documentElement.getAttribute('data-theme');
            
            if (theme === 'dark') return true;
            if (theme === 'light') return false;
            
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        },
        
        setupSwitcher() {
            const btn = document.querySelector('.theme-btn');
            const menu = document.querySelector('.theme-menu');
            
            if (!btn || !menu) return;
            
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('active');
            });
            
            document.addEventListener('click', () => {
                menu.classList.remove('active');
            });
            
            document.querySelectorAll('.theme-option').forEach(opt => {
                opt.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const theme = opt.getAttribute('data-theme');
                    this.setTheme(theme);
                    menu.classList.remove('active');
                });
            });
        },
        
        watchSystemTheme() {
            if (window.matchMedia) {
                const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
                darkModeQuery.addEventListener('change', () => {
                    const currentTheme = localStorage.getItem('theme');
                    if (currentTheme === 'auto') {
                        this.updateIcons();
                    }
                });
            }
        }
    };
    
    // ========================================================================
    // VOTING UI - Animated Green Dot
    // ========================================================================
    const VotingUI = {
        init() {
            this.setupVoteOptions();
            this.setupVoteSubmit();
        },
        
        setupVoteOptions() {
            document.querySelectorAll('.vote-option').forEach(option => {
                option.addEventListener('click', function() {
                    const parent = this.closest('.vote-options');
                    parent.querySelectorAll('.vote-option').forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    this.classList.add('selected');
                });
            });
        },
        
        setupVoteSubmit() {
            document.querySelectorAll('.vote-submit-btn').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const voteId = this.getAttribute('data-vote-id');
                    const card = this.closest('.vote-card');
                    const selected = card.querySelector('.vote-option.selected');
                    
                    if (!selected) {
                        alert('Please select an option');
                        return;
                    }
                    
                    const option = selected.getAttribute('data-option');
                    
                    try {
                        const response = await fetch('index.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `action=cast_vote&vote_id=${voteId}&option=${encodeURIComponent(option)}`
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Vote failed');
                        }
                    } catch (error) {
                        alert('Error submitting vote');
                    }
                });
            });
        }
    };
    
    // ========================================================================
    // MOBILE NAVIGATION
    // ========================================================================
    const MobileNav = {
        init() {
            this.highlightActive();
        },
        
        highlightActive() {
            const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
            document.querySelectorAll('.mobile-nav-item').forEach(item => {
                const href = item.getAttribute('href');
                if (href && href.includes('page=' + currentPage)) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }
    };
    
    // ========================================================================
    // ANALYTICS - Lightweight
    // ========================================================================
    const Analytics = {
        sessionId: null,
        
        init() {
            this.sessionId = this.getOrCreateSessionId();
            this.record('page_view');
            
            window.addEventListener('beforeunload', () => {
                this.record('page_leave', Date.now() - performance.timing.navigationStart);
            });
        },
        
        getOrCreateSessionId() {
            let sid = sessionStorage.getItem('sid');
            if (!sid) {
                sid = Date.now().toString(36) + Math.random().toString(36).substr(2);
                sessionStorage.setItem('sid', sid);
            }
            return sid;
        },
        
        record(type, duration = 0) {
            const data = {
                session_id: this.sessionId,
                activity_type: type,
                device_category: this.getDeviceCategory(),
                screen_width: window.innerWidth,
                screen_height: window.innerHeight,
                duration: duration
            };
            
            if (navigator.sendBeacon) {
                const formData = new FormData();
                Object.keys(data).forEach(key => formData.append(key, data[key]));
                navigator.sendBeacon('analytics.php', formData);
            }
        },
        
        getDeviceCategory() {
            const width = window.innerWidth;
            if (width < 768) return 'mobile';
            if (width < 1024) return 'tablet';
            return 'desktop';
        }
    };
    
    // ========================================================================
    // UTILITIES
    // ========================================================================
    const Utils = {
        init() {
            this.setupAutoHideAlerts();
            this.setupConfirmButtons();
        },
        
        setupAutoHideAlerts() {
            document.querySelectorAll('.alert:not(.penalty-warning)').forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        },
        
        setupConfirmButtons() {
            document.querySelectorAll('[data-confirm]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (!confirm(this.getAttribute('data-confirm'))) {
                        e.preventDefault();
                    }
                });
            });
        }
    };
    
    // ========================================================================
    // INITIALIZATION
    // ========================================================================
    document.addEventListener('DOMContentLoaded', () => {
        SmartCache.init();
        ResponsiveUI.init();
        ThemeManager.init();
        MobileNav.init();
        VotingUI.init();
        Analytics.init();
        Utils.init();
    });
    
    window.RyPanel = {
        ResponsiveUI,
        ThemeManager,
        Analytics,
        SmartCache
    };
})();
