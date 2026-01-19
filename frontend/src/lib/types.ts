export interface Todo {
	id: number;
	title: string;
	description?: string;
	completed: boolean;
	priority: 'low' | 'medium' | 'high' | 'unknown';
	status: 'active' | 'inactive' | 'pending';
	createdAt: string;
	updatedAt?: string;
}

export interface CreateTodoRequest {
	title: string;
	description?: string;
	completed?: boolean;
	priority?: 'low' | 'medium' | 'high' | 'unknown';
	status?: 'active' | 'inactive' | 'pending';
}

export interface UpdateTodoRequest {
	title?: string;
	description?: string;
	completed?: boolean;
	priority?: 'low' | 'medium' | 'high' | 'unknown';
	status?: 'active' | 'inactive' | 'pending';
}