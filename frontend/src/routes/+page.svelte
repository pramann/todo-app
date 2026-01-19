<script lang="ts">
	import TodoList from '$lib/components/TodoList.svelte';
</script>

<svelte:head>
	<title>Todo Manager</title>
	<meta name="description" content="A beautiful todo application built with Svelte" />
</svelte:head>

<main>
	<TodoList />
</main>

<style>
	:global(body) {
		margin: 0;
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell,
			'Open Sans', 'Helvetica Neue', sans-serif;
		background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
		min-height: 100vh;
	}

	main {
		min-height: 100vh;
		padding: 2rem 0;
	}
</style>
