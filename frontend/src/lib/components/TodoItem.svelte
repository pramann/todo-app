<script lang="ts">
	import { createEventDispatcher } from 'svelte';
	import type { Todo } from '$lib/todoService';

	export let todo: Todo;
	
	const dispatch = createEventDispatcher();

	let isEditing = false;
	let showEditModal = false;
	let showDeleteModal = false;
	let editedTitle = todo.title;
	let editedDescription = todo.description || '';
	let editedPriority = todo.priority;
	let editedStatus = todo.status;

	async function toggleComplete() {
		dispatch('update', { id: todo.id, updates: { completed: !todo.completed } });
	}

	function startEdit() {
		showEditModal = true;
		editedTitle = todo.title;
		editedDescription = todo.description || '';
		editedPriority = todo.priority;
		editedStatus = todo.status;
	}

	async function saveEdit() {
		const updates = {
			title: editedTitle !== todo.title ? editedTitle : undefined,
			description: editedDescription !== todo.description ? editedDescription : undefined,
			priority: editedPriority !== todo.priority ? editedPriority : undefined,
			status: editedStatus !== todo.status ? editedStatus : undefined
		};

		dispatch('update', { id: todo.id, updates });
		showEditModal = false;
	}

	function cancelEdit() {
		showEditModal = false;
		editedTitle = todo.title;
		editedDescription = todo.description || '';
		editedPriority = todo.priority;
		editedStatus = todo.status;
	}

	async function deleteTodo() {
		showDeleteModal = true;
	}

	function confirmDelete() {
		dispatch('delete', todo.id);
		showDeleteModal = false;
	}

	function cancelDelete() {
		showDeleteModal = false;
	}

	function getPriorityColor(priority: string) {
		switch (priority) {
			case 'high': return '#e74c3c';
			case 'medium': return '#f39c12';
			case 'low': return '#27ae60';
			case 'unknown': return '#95a5a6';
			default: return '#95a5a6';
		}
	}

	function getStatusColor(status: string) {
		switch (status) {
			case 'active': return '#27ae60';
			case 'inactive': return '#95a5a6';
			case 'pending': return '#f39c12';
			default: return '#95a5a6';
		}
	}
</script>

<div class="todo-item" class:completed={todo.completed}>
	<div class="todo-content">
		<div class="todo-header">
			<div class="todo-main">
				<input 
					type="checkbox" 
					checked={todo.completed} 
					on:change={toggleComplete}
					class="complete-checkbox"
				/>
				<h3 class="todo-title" class:completed-title={todo.completed}>{todo.title}</h3>
			</div>
			
			<div class="todo-badges">
				<span class="badge priority-badge" style="background-color: {getPriorityColor(todo.priority)}">
					{todo.priority}
				</span>
				<span class="badge status-badge" style="background-color: {getStatusColor(todo.status)}">
					{todo.status}
				</span>
			</div>
		</div>
		
		{#if todo.description}
			<p class="todo-description">{todo.description}</p>
		{/if}
		
		<div class="todo-meta">
			<span class="created-date">Created: {new Date(todo.createdAt).toLocaleDateString()}</span>
			{#if todo.updatedAt}
				<span class="updated-date">Updated: {new Date(todo.updatedAt).toLocaleDateString()}</span>
			{/if}
		</div>
		
		<div class="todo-actions">
			<button on:click={startEdit} class="btn btn-secondary" disabled={todo.completed}>Edit</button>
			<button on:click={deleteTodo} class="btn btn-danger" disabled={todo.completed}>Delete</button>
		</div>
	</div>

	<!-- Edit Modal -->
	{#if showEditModal}
		<div class="modal-overlay" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="edit-modal-title-{todo.id}" on:click={cancelEdit} on:keydown={(e) => e.key === 'Escape' && cancelEdit()}>
			<div class="modal-content edit-modal" on:click|stopPropagation>
				<div class="modal-header">
					<h3 id="edit-modal-title">Edit Todo</h3>
					<button class="modal-close" on:click={cancelEdit} aria-label="Close modal">
						<i class="fas fa-times"></i>
					</button>
				</div>
				
				<div class="modal-body">
					<div class="form-group">
						<label for="edit-title-{todo.id}">Title *</label>
						<input 
							id="edit-title-{todo.id}"
							type="text" 
							bind:value={editedTitle} 
							placeholder="Enter todo title..."
							class="title-input"
						/>
					</div>
					
					<div class="form-group">
						<label for="edit-description-{todo.id}">Description</label>
						<textarea 
							id="edit-description-{todo.id}"
							bind:value={editedDescription} 
							placeholder="Enter description (optional)..."
							class="description-input"
							rows="3"
						></textarea>
					</div>
					
					<div class="form-row">
						<div class="form-group">
							<label for="edit-priority-{todo.id}">Priority</label>
							<select id="edit-priority-{todo.id}" bind:value={editedPriority} class="priority-select">
								<option value="low">Low Priority</option>
								<option value="medium">Medium Priority</option>
								<option value="high">High Priority</option>
								<option value="unknown">Unknown Priority</option>
							</select>
						</div>
						
						<div class="form-group">
							<label for="edit-status-{todo.id}">Status</label>
							<select id="edit-status-{todo.id}" bind:value={editedStatus} class="status-select">
								<option value="pending">Pending</option>
								<option value="active">Active</option>
								<option value="inactive">Inactive</option>
							</select>
						</div>
					</div>
				</div>
				
				<div class="modal-footer">
					<button class="btn btn-secondary" on:click={cancelEdit}>
						<i class="fas fa-times"></i>
						Cancel
					</button>
					<button class="btn btn-primary" on:click={saveEdit} disabled={!editedTitle.trim()}>
						<i class="fas fa-save"></i>
						Save Changes
					</button>
				</div>
			</div>
		</div>
	{/if}

		<!-- Delete Confirmation Modal -->
	{#if showDeleteModal}
		<div class="modal-overlay" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title-{todo.id}" on:click={cancelDelete} on:keydown={(e) => e.key === 'Escape' && cancelDelete()}>
			<div class="modal-content" on:click|stopPropagation>
				<div class="modal-header">
					<h3 id="delete-modal-title-{todo.id}">Confirm Delete</h3>
					<button class="modal-close" on:click={cancelDelete} aria-label="Close modal">
						<i class="fas fa-times"></i>
					</button>
				</div>
				
				<div class="modal-body">
					<div class="todo-preview">
						<h4>{todo.title}</h4>
						{#if todo.description}
							<p class="todo-description-preview">{todo.description}</p>
						{/if}
						<div class="todo-badges-preview">
							<span class="badge priority-badge" style="background-color: {getPriorityColor(todo.priority)}">
								{todo.priority}
							</span>
							<span class="badge status-badge" style="background-color: {getStatusColor(todo.status)}">
								{todo.status}
							</span>
						</div>
					</div>
					
					<p class="warning-message">
						<i class="fas fa-exclamation-triangle"></i>
						Are you sure you want to delete this todo? This action cannot be undone.
					</p>
				</div>
				
				<div class="modal-footer">
					<button class="btn btn-secondary" on:click={cancelDelete}>
						<i class="fas fa-times"></i>
						Cancel
					</button>
					<button class="btn btn-danger" on:click={confirmDelete}>
						<i class="fas fa-trash"></i>
						Delete Todo
					</button>
				</div>
			</div>
		</div>
	{/if}
	</div>

<style>
	.todo-item {
		background: white;
		border-radius: 12px;
		padding: 1.5rem;
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
		border: 2px solid transparent;
		transition: all 0.3s ease;
		position: relative;
	}

	.todo-item:hover {
		box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
		border-color: #e1e8ed;
	}

	.todo-item.completed {
		opacity: 0.7;
	}

	.completed-title {
		text-decoration: line-through;
		color: #95a5a6;
	}

	.todo-content {
		display: flex;
		flex-direction: column;
		gap: 1rem;
	}

	.todo-header {
		display: flex;
		align-items: flex-start;
		gap: 1rem;
	}

	.todo-main {
		display: flex;
		align-items: flex-start;
		gap: 0.75rem;
		flex: 1;
		margin-right: 1rem;
	}

	.complete-checkbox {
		margin-top: 0.25rem;
		width: 1.25rem;
		height: 1.25rem;
		cursor: pointer;
	}

	.todo-title {
		margin: 0;
		font-size: 1.25rem;
		color: #2c3e50;
		flex: 1;
		transition: all 0.3s ease;
	}



	.todo-badges {
		display: flex;
		gap: 0.5rem;
		flex-shrink: 0;
	}

	.badge {
		padding: 0.25rem 0.75rem;
		border-radius: 20px;
		font-size: 0.75rem;
		font-weight: 600;
		color: white;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.todo-description {
		margin: 0;
		color: #666;
		line-height: 1.5;
	}

	.todo-meta {
		display: flex;
		gap: 1rem;
		font-size: 0.875rem;
		color: #888;
		position: absolute;
		bottom: 1rem;
		right: 1.5rem;
	}

	.todo-actions {
		display: flex;
		gap: 0.5rem;
	}



	.form-group {
		display: flex;
		flex-direction: column;
		gap: 0.75rem;
	}

	.form-group label {
		font-weight: 600;
		color: #2c3e50;
		font-size: 0.875rem;
		margin-bottom: 0.25rem;
	}

	.form-row {
		display: flex;
		gap: 1.5rem;
	}

	.form-row .form-group {
		flex: 1;
	}

	.title-input,
	.description-input,
	.priority-select,
	.status-select {
		padding: 0.75rem;
		border: 2px solid #e1e8ed;
		border-radius: 8px;
		font-size: 1rem;
		transition: all 0.3s ease;
		background: white;
		color: #2c3e50;
		position: relative;
		z-index: 1001;
		margin-bottom: 1rem;
	}

	.title-input:focus,
	.description-input:focus,
	.priority-select:focus,
	.status-select:focus {
		outline: none;
		border-color: #3498db;
		box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
	}

	.title-input:disabled,
	.description-input:disabled,
	.priority-select:disabled,
	.status-select:disabled {
		opacity: 0.6;
		cursor: not-allowed;
	}

	.description-input {
		resize: vertical;
		min-height: 80px;
	}



	.btn {
		padding: 0.5rem 1rem;
		border: none;
		border-radius: 6px;
		font-size: 0.875rem;
		font-weight: 600;
		cursor: pointer;
		transition: all 0.3s ease;
	}

	.btn-primary {
		background-color: #3498db;
		color: white;
	}

	.btn-primary:hover {
		background-color: #2980b9;
	}

	.btn-secondary {
		background-color: #95a5a6;
		color: white;
	}

	.btn-secondary:hover {
		background-color: #7f8c8d;
	}

	.btn-danger {
		background-color: #e74c3c;
		color: white;
	}

	.btn-danger:hover {
		background-color: #c0392b;
	}

	/* Modal Styles */
	.modal-overlay {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: rgba(0, 0, 0, 0.5);
		backdrop-filter: blur(4px);
		display: flex;
		align-items: center;
		justify-content: center;
		z-index: 1000;
		padding: 1rem;
		animation: fadeIn 0.3s ease-out;
	}

	.modal-content {
		background: white;
		border-radius: 12px;
		max-width: 500px;
		width: 100%;
		max-height: 90vh;
		overflow-y: auto;
		box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
		animation: slideUp 0.3s ease-out, fadeIn 0.3s ease-out;
	}

	.modal-content.closing {
		animation: slideDown 0.3s ease-out, fadeOut 0.3s ease-out;
	}

	.edit-modal {
		max-width: 600px;
	}

	.modal-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 1.5rem 1.5rem 1rem;
		border-bottom: 1px solid #e1e8ed;
	}

	.modal-header h3 {
		margin: 0;
		color: #2c3e50;
		font-size: 1.25rem;
		font-weight: 600;
	}

	.modal-close {
		background: none;
		border: none;
		font-size: 1.25rem;
		color: #95a5a6;
		cursor: pointer;
		padding: 0.25rem;
		border-radius: 4px;
		transition: all 0.2s ease;
	}

	.modal-close:hover {
		color: #2c3e50;
		background-color: #f8f9fa;
	}

	.modal-body {
		padding: 1.5rem;
	}

	.todo-preview {
		background: #f8f9fa;
		border-radius: 8px;
		padding: 1rem;
		margin-bottom: 1rem;
		border-left: 4px solid #e74c3c;
	}

	.todo-preview h4 {
		margin: 0 0 0.75rem 0;
		color: #2c3e50;
		font-size: 1.1rem;
		font-weight: 600;
	}

	.todo-description-preview {
		margin: 0 0 0.75rem 0;
		color: #666;
		font-size: 0.9rem;
		line-height: 1.4;
	}

	.todo-badges-preview {
		display: flex;
		gap: 0.5rem;
		flex-wrap: wrap;
	}

	.todo-badges-preview .badge {
		font-size: 0.7rem;
		padding: 0.2rem 0.5rem;
	}

	.warning-message {
		margin: 0;
		color: #e74c3c;
		font-weight: 500;
		display: flex;
		align-items: center;
		gap: 0.75rem;
		font-size: 1rem;
	}

	.warning-message i {
		font-size: 1.25rem;
		color: #e74c3c;
	}

	.modal-footer {
		display: flex;
		gap: 0.75rem;
		justify-content: flex-end;
		padding: 1rem 1.5rem 1.5rem;
		border-top: 1px solid #e1e8ed;
	}

		.modal-footer .btn {
			width: 100%;
	}

	@media (max-width: 768px) {
		.modal-content {
			margin: 1rem;
			max-width: none;
		}

		.modal-header,
		.modal-body,
		.modal-footer {
			padding: 1rem;
		}

		.modal-footer {
			flex-direction: column-reverse;
		}

		.modal-footer .btn {
			width: 100%;
		}

		.todo-header {
			flex-direction: column;
			align-items: stretch;
		}

		.todo-badges {
			align-self: flex-start;
		}

		.form-row {
			flex-direction: column;
		}
	}

		.modal-header,
		.modal-body,
		.modal-footer {
			padding: 1rem;
		}

		.modal-footer {
			flex-direction: column-reverse;
		}

		.modal-footer .btn {
			width: 100%;
		}

	@keyframes fadeIn {
		from { opacity: 0; }
		to { opacity: 1; }
	}

	@keyframes fadeOut {
		from { opacity: 1; }
		to { opacity: 0; }
	}

	@keyframes slideUp {
		from { 
			opacity: 0;
			transform: translateY(20px);
		}
		to { 
			opacity: 1;
			transform: translateY(0);
		}
	}

	@keyframes slideDown {
		from { 
			opacity: 1;
			transform: translateY(0);
		}
		to { 
			opacity: 0;
			transform: translateY(20px);
		}
	}

</style>