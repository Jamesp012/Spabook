/**
 * Notification System JavaScript
 * Handles displaying and managing user notifications
 */

class NotificationManager {
    constructor(userId) {
        this.userId = userId;
        this.notifications = [];
        this.unreadCount = 0;
        this.isInitialized = false;
        this.refreshInterval = null;
        this.init();
    }

    init() {
        this.createNotificationElements();
        this.loadNotifications();
        this.startAutoRefresh();
        this.isInitialized = true;
    }

    createNotificationElements() {
        // Create notification bell icon if it doesn't exist
        if (!document.getElementById('notification-bell')) {
            const bellHtml = `
                <div class="notification-container" style="position: relative; display: inline-block;">
                    <button id="notification-bell" class="btn btn-link position-relative p-2" style="color: #333;">
                        <i class="fas fa-bell" style="font-size: 1.2rem;"></i>
                        <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none; font-size: 0.7rem;">
                            0
                        </span>
                    </button>
                    <div id="notification-dropdown" class="notification-dropdown" style="display: none;">
                        <div class="notification-header">
                            <h6 class="mb-0">Notifications</h6>
                            <button id="mark-all-read" class="btn btn-sm btn-link p-0">Mark all as read</button>
                        </div>
                        <div id="notification-list" class="notification-list">
                            <div class="text-center p-3">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                        <div class="notification-footer">
                            <button id="view-all-notifications" class="btn btn-sm btn-primary w-100">View All</button>
                        </div>
                    </div>
                </div>
            `;
            
            // Try to find a suitable container (navbar, header, etc.)
            const navbar = document.querySelector('.navbar');
            const header = document.querySelector('header');
            const container = navbar || header || document.body;
            
            if (container) {
                container.insertAdjacentHTML('beforeend', bellHtml);
                this.attachEventListeners();
            }
        }

        // Add CSS styles
        this.addNotificationStyles();
    }

    addNotificationStyles() {
        if (document.getElementById('notification-styles')) return;

        const styles = `
            <style id="notification-styles">
                .notification-dropdown {
                    position: absolute;
                    top: 100%;
                    right: 0;
                    width: 350px;
                    max-height: 400px;
                    background: white;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    z-index: 1050;
                    overflow: hidden;
                }

                .notification-header {
                    padding: 12px 16px;
                    border-bottom: 1px solid #eee;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    background: #f8f9fa;
                }

                .notification-list {
                    max-height: 300px;
                    overflow-y: auto;
                }

                .notification-item {
                    padding: 12px 16px;
                    border-bottom: 1px solid #f0f0f0;
                    cursor: pointer;
                    transition: background-color 0.2s;
                }

                .notification-item:hover {
                    background-color: #f8f9fa;
                }

                .notification-item.unread {
                    background-color: #e3f2fd;
                    border-left: 3px solid #2196f3;
                }

                .notification-item.unread:hover {
                    background-color: #bbdefb;
                }

                .notification-title {
                    font-weight: 600;
                    font-size: 0.9rem;
                    margin-bottom: 4px;
                    color: #333;
                }

                .notification-message {
                    font-size: 0.8rem;
                    color: #666;
                    margin-bottom: 4px;
                    line-height: 1.3;
                }

                .notification-time {
                    font-size: 0.7rem;
                    color: #999;
                }

                .notification-type-success { border-left-color: #4caf50; }
                .notification-type-warning { border-left-color: #ff9800; }
                .notification-type-error { border-left-color: #f44336; }
                .notification-type-info { border-left-color: #2196f3; }

                .notification-footer {
                    padding: 8px 16px;
                    border-top: 1px solid #eee;
                    background: #f8f9fa;
                }

                .notification-empty {
                    padding: 40px 20px;
                    text-align: center;
                    color: #999;
                }

                .notification-empty i {
                    font-size: 2rem;
                    margin-bottom: 10px;
                    opacity: 0.5;
                }

                @media (max-width: 768px) {
                    .notification-dropdown {
                        width: 300px;
                        right: -50px;
                    }
                }
            </style>
        `;
        document.head.insertAdjacentHTML('beforeend', styles);
    }

    attachEventListeners() {
        const bell = document.getElementById('notification-bell');
        const dropdown = document.getElementById('notification-dropdown');
        const markAllRead = document.getElementById('mark-all-read');
        const viewAll = document.getElementById('view-all-notifications');

        if (bell) {
            bell.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleDropdown();
            });
        }

        if (markAllRead) {
            markAllRead.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }

        if (viewAll) {
            viewAll.addEventListener('click', () => {
                this.showAllNotifications();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.notification-container')) {
                this.hideDropdown();
            }
        });
    }

    toggleDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        if (dropdown.style.display === 'none' || !dropdown.style.display) {
            this.showDropdown();
        } else {
            this.hideDropdown();
        }
    }

    showDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        dropdown.style.display = 'block';
        this.loadNotifications();
    }

    hideDropdown() {
        const dropdown = document.getElementById('notification-dropdown');
        dropdown.style.display = 'none';
    }

    async loadNotifications(limit = 10) {
        try {
            const response = await fetch('../controller/notification_contr.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_user_notifications&user_id=${this.userId}&limit=${limit}`
            });

            const data = await response.json();
            
            if (data.status === 'nodata') {
                this.notifications = [];
            } else if (Array.isArray(data)) {
                this.notifications = data;
            } else {
                console.error('Unexpected notification data format:', data);
                this.notifications = [];
            }

            this.updateNotificationDisplay();
            this.loadUnreadCount();
        } catch (error) {
            console.error('Error loading notifications:', error);
            this.showError('Failed to load notifications');
        }
    }

    async loadUnreadCount() {
        try {
            const response = await fetch('../controller/notification_contr.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_unread_count&user_id=${this.userId}`
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                this.unreadCount = data.count;
                this.updateBadge();
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    }

    updateNotificationDisplay() {
        const listContainer = document.getElementById('notification-list');
        
        if (!listContainer) return;

        if (this.notifications.length === 0) {
            listContainer.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    <div>No notifications yet</div>
                </div>
            `;
            return;
        }

        const notificationsHtml = this.notifications.map(notification => {
            const isUnread = !notification.is_read;
            const timeAgo = this.formatTimeAgo(notification.created_at);
            const typeClass = `notification-type-${notification.type || 'info'}`;
            
            return `
                <div class="notification-item ${isUnread ? 'unread' : ''} ${typeClass}" 
                     data-notification-id="${notification.notificationid}">
                    <div class="notification-title">${this.escapeHtml(notification.title)}</div>
                    <div class="notification-message">${this.escapeHtml(notification.message)}</div>
                    <div class="notification-time">${timeAgo}</div>
                </div>
            `;
        }).join('');

        listContainer.innerHTML = notificationsHtml;

        // Add click handlers for individual notifications
        listContainer.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', () => {
                const notificationId = item.dataset.notificationId;
                this.markAsRead(notificationId);
            });
        });
    }

    updateBadge() {
        const badge = document.getElementById('notification-badge');
        if (!badge) return;

        if (this.unreadCount > 0) {
            badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    async markAsRead(notificationId) {
        try {
            const response = await fetch('../controller/notification_contr.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=mark_as_read&notification_id=${notificationId}&user_id=${this.userId}`
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                // Update local notification state
                const notification = this.notifications.find(n => n.notificationid == notificationId);
                if (notification) {
                    notification.is_read = true;
                }
                
                this.updateNotificationDisplay();
                this.loadUnreadCount();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const response = await fetch('../controller/notification_contr.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=mark_all_as_read&user_id=${this.userId}`
            });

            const data = await response.json();
            
            if (data.status === 'success') {
                // Update local notifications state
                this.notifications.forEach(notification => {
                    notification.is_read = true;
                });
                
                this.updateNotificationDisplay();
                this.loadUnreadCount();
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    showAllNotifications() {
        // This could open a modal or navigate to a dedicated notifications page
        // For now, we'll just load more notifications
        this.loadNotifications(50);
    }

    startAutoRefresh() {
        // Refresh notifications every 30 seconds
        this.refreshInterval = setInterval(() => {
            this.loadUnreadCount();
        }, 30000);
    }

    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    showError(message) {
        const listContainer = document.getElementById('notification-list');
        if (listContainer) {
            listContainer.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    <div>${message}</div>
                </div>
            `;
        }
    }

    formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) {
            return 'Just now';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        } else if (diffInSeconds < 604800) {
            const days = Math.floor(diffInSeconds / 86400);
            return `${days} day${days > 1 ? 's' : ''} ago`;
        } else {
            return date.toLocaleDateString();
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Public method to create a new notification (for real-time updates)
    addNotification(notification) {
        this.notifications.unshift(notification);
        this.updateNotificationDisplay();
        this.loadUnreadCount();
    }

    // Cleanup method
    destroy() {
        this.stopAutoRefresh();
        const container = document.querySelector('.notification-container');
        if (container) {
            container.remove();
        }
        const styles = document.getElementById('notification-styles');
        if (styles) {
            styles.remove();
        }
    }
}

// Global notification manager instance
window.NotificationManager = NotificationManager;