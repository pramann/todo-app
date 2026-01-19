export { type Todo, type CreateTodoRequest, type UpdateTodoRequest } from './types';

const API_BASE_URL = 'http://localhost:8000/api';

class TodoService {
	async getAllTodos(): Promise<Todo[]> {
		const response = await fetch(`${API_BASE_URL}/todos`);
		if (!response.ok) {
			throw new Error('Failed to fetch todos');
		}
		const data = await response.json();
		return Array.isArray(data) ? data : data['hydra:member'] || [];
	}

	async getTodo(id: number): Promise<Todo> {
		const response = await fetch(`${API_BASE_URL}/todos/${id}`);
		if (!response.ok) {
			throw new Error(`Failed to fetch todo with id ${id}`);
		}
		return await response.json();
	}

	async createTodo(todo: CreateTodoRequest): Promise<Todo> {
		const response = await fetch(`${API_BASE_URL}/todos`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify(todo),
		});

		if (!response.ok) {
			const error = await response.json();
			throw new Error(error.detail || 'Failed to create todo');
		}

		return await response.json();
	}

	async updateTodo(id: number, todo: UpdateTodoRequest): Promise<Todo> {
		const response = await fetch(`${API_BASE_URL}/todos/${id}`, {
			method: 'PATCH',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify(todo),
		});

		if (!response.ok) {
			const error = await response.json();
			throw new Error(error.detail || 'Failed to update todo');
		}

		return await response.json();
	}

	async deleteTodo(id: number): Promise<void> {
		const response = await fetch(`${API_BASE_URL}/todos/${id}`, {
			method: 'DELETE',
		});

		if (!response.ok && response.status !== 204) {
			throw new Error(`Failed to delete todo with id ${id}`);
		}
	}
}

export const todoService = new TodoService();