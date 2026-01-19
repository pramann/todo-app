<script lang="ts">
	import { createEventDispatcher } from "svelte";
	import { handleValidationError } from "$lib/services/notificationService";

	const dispatch = createEventDispatcher();

	let title = "";
	let description = "";
	let priority = "medium";
	let status = "pending";

	async function handleSubmit() {
		if (!title.trim()) {
			handleValidationError("Please enter a title");
			return;
		}

		const newTodo = {
			title: title.trim(),
			description: description.trim() || undefined,
			priority: priority as "low" | "medium" | "high" | "unknown",
			status: status as "active" | "inactive" | "pending",
		};

		dispatch("create", newTodo);

		// Reset form
		title = "";
		description = "";
		priority = "medium";
		status = "pending";
	}

	function handleKeydown(event: KeyboardEvent) {
		if (event.key === "Enter" && event.ctrlKey) {
			handleSubmit();
		}
	}
</script>

<div class="todo-form">
	<h2>Create New Todo</h2>

	<form on:submit|preventDefault={handleSubmit}>
		<div class="form-group">
			<label for="title">Title *</label>
			<input
				type="text"
				id="title"
				bind:value={title}
				placeholder="Enter todo title..."
				required
				on:keydown={handleKeydown}
				class="title-input"
			/>
		</div>

		<div class="form-group">
			<label for="description">Description</label>
			<textarea
				id="description"
				bind:value={description}
				placeholder="Enter description (optional)..."
				class="description-input"
				rows="3"
				on:keydown={handleKeydown}
			></textarea>
		</div>

		<div class="form-row">
			<div class="form-group">
				<label for="priority">Priority</label>
				<select
					bind:value={priority}
					id="priority"
					class="priority-select"
				>
					<option value="low">Low</option>
					<option value="medium">Medium</option>
					<option value="high">High</option>
					<option value="unknown">Unknown</option>
				</select>
			</div>

			<div class="form-group">
				<label for="status">Status</label>
				<select bind:value={status} id="status" class="status-select">
					<option value="pending">Pending</option>
					<option value="active">Active</option>
					<option value="inactive">Inactive</option>
				</select>
			</div>
		</div>

		<div class="form-actions">
			<button
				type="submit"
				class="btn btn-primary"
				disabled={!title.trim()}
			>
				Create Todo
			</button>
			<button
				type="button"
				class="btn btn-secondary"
				disabled={!title.trim() && !description.trim()}
				on:click={() => {
					title = "";
					description = "";
					priority = "medium";
					status = "pending";
				}}
			>
				Clear
			</button>
		</div>
	</form>

	<div class="keyboard-hint">Tip: Press Ctrl+Enter to quickly submit</div>
</div>

<style>
	.todo-form {
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		padding: 2rem;
		border-radius: 16px;
		margin-bottom: 2rem;
		color: white;
		box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
	}

	.todo-form h2 {
		margin: 0 0 1.5rem 0;
		font-size: 1.75rem;
		font-weight: 600;
		text-align: center;
	}

	.form-group {
		margin-bottom: 1.25rem;
	}

	.form-group label {
		display: block;
		margin-bottom: 0.5rem;
		font-weight: 500;
		font-size: 0.875rem;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.title-input,
	.description-input,
	.priority-select,
	.status-select {
		width: 100%;
		padding: 0.875rem;
		border: 2px solid rgba(255, 255, 255, 0.2);
		border-radius: 10px;
		background: rgba(255, 255, 255, 0.1);
		color: white;
		font-size: 1rem;
		transition: all 0.3s ease;
		backdrop-filter: blur(10px);
		box-sizing: border-box;
	}

	.title-input:focus,
	.description-input:focus,
	.priority-select:focus,
	.status-select:focus {
		outline: none;
		border-color: rgba(255, 255, 255, 0.5);
		background: rgba(255, 255, 255, 0.15);
		box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
	}

	.title-input::placeholder,
	.description-input::placeholder {
		color: rgba(255, 255, 255, 0.7);
	}

	.description-input {
		resize: vertical;
		min-height: 100px;
		font-family: inherit;
	}

	.priority-select option,
	.status-select option {
		background: #667eea;
		color: white;
	}

	.form-row {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 1rem;
	}

	.form-actions {
		display: flex;
		gap: 1rem;
		justify-content: center;
		margin-top: 1.5rem;
	}

	.btn {
		padding: 0.875rem 2rem;
		border: none;
		border-radius: 8px;
		font-size: 1rem;
		font-weight: 600;
		cursor: pointer;
		transition: all 0.3s ease;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		min-width: 120px;
	}

	.btn-primary {
		background: white;
		color: #667eea;
		box-shadow: 0 4px 16px rgba(255, 255, 255, 0.2);
	}

	.btn-primary:hover:not(:disabled) {
		transform: translateY(-2px);
		box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
	}

	.btn-primary:disabled,
	.btn-secondary:disabled {
		opacity: 0.5;
		cursor: not-allowed;
		transform: none;
	}

	.btn-secondary {
		background: rgba(255, 255, 255, 0.2);
		color: white;
		border: 2px solid rgba(255, 255, 255, 0.3);
	}

	.btn-secondary:hover:not(:disabled) {
		background: rgba(255, 255, 255, 0.3);
		border-color: rgba(255, 255, 255, 0.5);
	}

	.keyboard-hint {
		text-align: center;
		margin-top: 1rem;
		font-size: 0.875rem;
		color: rgba(255, 255, 255, 0.8);
		font-style: italic;
	}

	@media (max-width: 768px) {
		.todo-form {
			padding: 1.5rem;
		}

		.form-row {
			grid-template-columns: 1fr;
		}

		.form-actions {
			flex-direction: column;
		}

		.btn {
			width: 100%;
		}
	}
</style>
