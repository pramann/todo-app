<script lang="ts">
    import { createEventDispatcher } from 'svelte';
    
    export let message = '';
    export let type: 'error' | 'success' | 'warning' | 'info' = 'error';
    export let duration = 5000;
    export let closable = true;
    
    let isVisible = false;
    let timeoutId: number;

    $: if (message) {
        isVisible = true;
        if (duration > 0) {
            if (timeoutId) clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                close();
            }, duration) as unknown as number;
        }
    }

    function close() {
        isVisible = false;
        if (timeoutId) clearTimeout(timeoutId);
        // Notify parent that notification is closed
        dispatch('close');
    }

    const dispatch = createEventDispatcher();

    $: iconClass = {
        error: 'fas fa-exclamation-circle',
        success: 'fas fa-check-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    }[type];

    $: bgClass = {
        error: 'bg-red-500',
        success: 'bg-green-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    }[type];
</script>

{#if isVisible}
    <div class="notification-overlay">
        <div class="notification-container">
            <div class="notification {bgClass}" class:closable>
                <div class="notification-content">
                    <i class="{iconClass} notification-icon"></i>
                    <span class="notification-message">{message}</span>
                </div>
                {#if closable}
                    <button class="notification-close" on:click={close} aria-label="Close notification">
                        <i class="fas fa-times"></i>
                    </button>
                {/if}
            </div>
        </div>
    </div>
{/if}

<style>
    .notification-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding-top: 2rem;
    }

    .notification-container {
        max-width: 500px;
        width: 90%;
        pointer-events: auto;
    }

    .notification {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(10px);
        color: white;
        font-weight: 500;
        animation: slideIn 0.3s ease-out;
        transition: all 0.3s ease;
    }

    .notification.closable {
        padding-right: 3rem;
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
    }

    .notification-icon {
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .notification-message {
        font-size: 0.95rem;
        line-height: 1.4;
        word-break: break-word;
    }

    .notification-close {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .notification-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-50%) scale(1.1);
    }

    /* Type-specific backgrounds */
    .bg-red-500 {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .bg-green-500 {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    }

    .bg-yellow-500 {
        background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%);
    }

    .bg-blue-500 {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    @keyframes slideIn {
        from {
            transform: translateY(-100px) scale(0.9);
            opacity: 0;
        }
        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    /* Hover effects */
    .notification:hover {
        transform: scale(1.02);
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
    }

    /* Mobile responsiveness */
    @media (max-width: 640px) {
        .notification-overlay {
            padding: 1rem;
        }

        .notification {
            padding: 0.875rem 1rem;
            font-size: 0.9rem;
        }

        .notification.closable {
            padding-right: 2.5rem;
        }

        .notification-close {
            width: 1.75rem;
            height: 1.75rem;
            font-size: 0.8rem;
        }

        .notification-icon {
            font-size: 1.1rem;
        }
    }
</style>