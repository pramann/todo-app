import { writable, derived } from 'svelte/store';
import type { Notification } from '$lib/types/notification';

export const notifications = writable<Notification[]>([]);

export const notificationService = {
    error: (message: string, duration?: number) => {
        addNotification({
            id: Date.now() + Math.random(),
            type: 'error',
            message,
            duration: duration ?? 5000
        });
    },
    
    success: (message: string, duration?: number) => {
        addNotification({
            id: Date.now() + Math.random(),
            type: 'success',
            message,
            duration: duration ?? 3000
        });
    },
    
    warning: (message: string, duration?: number) => {
        addNotification({
            id: Date.now() + Math.random(),
            type: 'warning',
            message,
            duration: duration ?? 4000
        });
    },
    
    info: (message: string, duration?: number) => {
        addNotification({
            id: Date.now() + Math.random(),
            type: 'info',
            message,
            duration: duration ?? 4000
        });
    },
    
    clear: () => {
        notifications.set([]);
    },
    
    remove: (id: number) => {
        notifications.update(current => 
            current.filter(notification => notification.id !== id)
        );
    }
};

function addNotification(notification: Notification) {
    notifications.update(current => [...current, notification]);
    
    // Auto-remove notification after duration
    if (notification.duration && notification.duration > 0) {
        setTimeout(() => {
            notificationService.remove(notification.id);
        }, notification.duration);
    }
}

// Helper function for API error handling
export function handleApiError(error: any, fallbackMessage = 'An error occurred') {
    let message = fallbackMessage;
    
    if (error?.message) {
        message = error.message;
    } else if (error?.detail) {
        message = error.detail;
    } else if (typeof error === 'string') {
        message = error;
    } else if (error?.validValues && error?.field) {
        message = `Invalid value for ${error.field}. Allowed values are: ${error.validValues.join(', ')}`;
    }
    
    notificationService.error(message);
}

// Helper for validation errors
export function handleValidationError(message: string) {
    notificationService.error(message, 3000);
}