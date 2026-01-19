export interface Notification {
    id: number;
    type: 'error' | 'success' | 'warning' | 'info';
    message: string;
    duration?: number;
    closable?: boolean;
}