<script lang="ts">
    import { notifications } from '$lib/services/notificationService';
    import Notification from '$lib/components/Notification.svelte';
    import type { Notification as NotificationType } from '$lib/types/notification';

    let notificationList: NotificationType[] = [];
    
    // Subscribe to notifications store
    notifications.subscribe(value => {
        notificationList = value;
    });

    function handleClose(notificationId: number) {
        notifications.update(current => 
            current.filter(n => n.id !== notificationId)
        );
    }
</script>

{#each notificationList as notification (notification.id)}
    <Notification
        message={notification.message}
        type={notification.type}
        duration={notification.duration}
        closable={notification.closable !== false}
        on:close={() => handleClose(notification.id)}
    />
{/each}