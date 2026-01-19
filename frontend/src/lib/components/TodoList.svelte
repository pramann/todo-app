<script lang="ts">
	import { onMount } from 'svelte';
	import { todoService, type Todo } from '$lib/todoService';
	import { notificationService, handleApiError } from '$lib/services/notificationService';
	import TodoItem from './TodoItem.svelte';
	import TodoForm from './TodoForm.svelte';

	let todos: Todo[] = [];
	let loading = true;

	async function loadTodos() {
		try {
			loading = true;
			todos = await todoService.getAllTodos();
		} catch (err) {
			handleApiError(err, 'Failed to load todos');
		} finally {
			loading = false;
		}
	}

	async function handleCreateTodo(event: CustomEvent<any>) {
		try {
			const newTodo = await todoService.createTodo(event.detail);
			todos = [...todos, newTodo];
			notificationService.success('Todo created successfully!');
		} catch (err) {
			handleApiError(err, 'Failed to create todo');
		}
	}

	async function handleUpdateTodo(event: CustomEvent<any>) {
		try {
			const { id, updates } = event.detail;
			const updatedTodo = await todoService.updateTodo(id, updates);
			todos = todos.map((todo) => (todo.id === id ? updatedTodo : todo));
			notificationService.success('Todo updated successfully!');
		} catch (err) {
			handleApiError(err, 'Failed to update todo');
		}
	}

	async function handleDeleteTodo(event: CustomEvent<any>) {
		try {
			await todoService.deleteTodo(event.detail);
			todos = todos.filter((todo) => todo.id !== event.detail);
			notificationService.success('Todo deleted successfully!');
		} catch (err) {
			handleApiError(err, 'Failed to delete todo');
		}
	}

	onMount(() => {
		loadTodos();
	});
</script>

<div class="todo-app">
	<header>
		<h1>Todo Manager</h1>
	</header>

	<main>
		<TodoForm on:create={handleCreateTodo} />

		<section>
			<h2>Todos ({todos.length})</h2>

			{#if loading}
				<div class="loading">Loading todos...</div>
			{:else if todos.length === 0}
				<div class="empty">No todos found. Create your first todo above!</div>
			{:else}
				<div class="todo-list">
					{#each todos as todo (todo.id)}
						<TodoItem 
							{todo} 
							on:update={handleUpdateTodo}
							on:delete={handleDeleteTodo}
						/>
					{/each}
				</div>
			{/if}
		</section>
	</main>
</div>

<style>
	.todo-app {
		max-width: 800px;
		margin: 0 auto;
		padding: 2rem;
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
	}

	header {
		text-align: center;
		margin-bottom: 2rem;
	}

	h1 {
		color: #2c3e50;
		margin: 0;
		font-size: 2.5rem;
	}

	h2 {
		color: #34495e;
		margin: 2rem 0 1rem 0;
		font-size: 1.5rem;
	}



	.loading {
		text-align: center;
		padding: 2rem;
		color: #666;
		font-style: italic;
	}

	.empty {
		text-align: center;
		padding: 3rem;
		color: #666;
		font-style: italic;
	}

	.todo-list {
		display: flex;
		flex-direction: column;
		gap: 1rem;
	}
</style>