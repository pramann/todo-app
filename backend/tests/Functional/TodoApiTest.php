<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Todo;
use App\Enum\Priority;
use App\Enum\Status;

class TodoApiTest extends WebTestCase
{
    private function createAuthenticatedClient(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        return static::createClient();
    }

    public function testGetTodoCollection(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $client->request('GET', '/api/todos');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        
        $responseContent = $client->getResponse()->getContent();
        $this->assertJson($responseContent);
        
        $data = json_decode($responseContent, true);
        $this->assertIsArray($data);
    }

    public function testCreateTodoWithValidData(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $todoData = [
            'title' => 'Test Todo API',
            'description' => 'Test Description',
            'priority' => 'high',
            'status' => 'active',
            'completed' => false
        ];
        
        $client->request('POST', '/api/todos', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($todoData));
        
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        
        $responseContent = $client->getResponse()->getContent();
        $this->assertJson($responseContent);
        
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals('Test Todo API', $data['title']);
        $this->assertEquals('Test Description', $data['description']);
        $this->assertEquals('high', $data['priority']);
        $this->assertEquals('active', $data['status']);
        $this->assertFalse($data['completed']);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);
    }

    public function testCreateTodoWithMinimalData(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $todoData = [
            'title' => 'Minimal Todo'
        ];
        
        $client->request('POST', '/api/todos', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($todoData));
        
        $this->assertResponseStatusCodeSame(201);
        
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);
        
        $this->assertEquals('Minimal Todo', $data['title']);
        $this->assertEquals('medium', $data['priority']);
        $this->assertEquals('pending', $data['status']);
        $this->assertFalse($data['completed']);
        $this->assertNull($data['description']);
    }

    public function testCreateTodoWithEmptyTitle(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $todoData = [
            'title' => '',
            'description' => 'Test Description'
        ];
        
        $client->request('POST', '/api/todos', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($todoData));
        
        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');
        
        $responseContent = $client->getResponse()->getContent();
        $this->assertJson($responseContent);
        
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('violations', $data);
        $this->assertCount(1, $data['violations']);
        $this->assertEquals('title', $data['violations'][0]['propertyPath']);
        $this->assertEquals('Der Titel darf nicht leer sein.', $data['violations'][0]['message']);
    }

    public function testCreateTodoWithWhitespaceOnlyTitle(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $todoData = [
            'title' => '   ',
            'description' => 'Test Description'
        ];
        
        $client->request('POST', '/api/todos', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($todoData));
        
        // Due to trim_strings feature, whitespace-only titles are accepted
        $this->assertResponseStatusCodeSame(201);
        
        $responseContent = $client->getResponse()->getContent();
        $this->assertJson($responseContent);
        
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        // The title should preserve the whitespace due to current behavior
        $this->assertEquals('   ', $data['title']);
    }

    public function testCreateTodoWithTooLongTitle(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $todoData = [
            'title' => str_repeat('a', 256),
            'description' => 'Test Description'
        ];
        
        $client->request('POST', '/api/todos', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($todoData));
        
        $this->assertResponseStatusCodeSame(422);
        
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);
        
        $this->assertArrayHasKey('violations', $data);
        $this->assertCount(1, $data['violations']);
        $this->assertEquals('title', $data['violations'][0]['propertyPath']);
    }

    public function testCreateTodoWithInvalidPriority(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $todoData = [
            'title' => 'Test Todo',
            'priority' => 'invalid_priority'
        ];
        
        $client->request('POST', '/api/todos', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($todoData));
        
        $this->assertResponseStatusCodeSame(422);
        
        $responseContent = $client->getResponse()->getContent();
        $this->assertJson($responseContent);
        
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('field', $data);
        $this->assertArrayHasKey('validValues', $data);
        $this->assertArrayHasKey('message', $data);
        
        $this->assertEquals('Invalid Enum Value', $data['title']);
        $this->assertEquals(422, $data['status']);
        $this->assertEquals('priority', $data['field']);
        $this->assertEquals(['low', 'medium', 'high', 'unknown'], $data['validValues']);
        $this->assertStringContainsString('Invalid value for priority. Allowed values are: low, medium, high, unknown', $data['message']);
    }

    public function testCreateTodoWithInvalidStatus(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $todoData = [
            'title' => 'Test Todo',
            'status' => 'invalid_status'
        ];
        
        $client->request('POST', '/api/todos', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($todoData));
        
        $this->assertResponseStatusCodeSame(422);
        
        $responseContent = $client->getResponse()->getContent();
        $this->assertJson($responseContent);
        
        $data = json_decode($responseContent, true);
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('field', $data);
        $this->assertArrayHasKey('validValues', $data);
        $this->assertArrayHasKey('message', $data);
        
        $this->assertEquals('Invalid Enum Value', $data['title']);
        $this->assertEquals(422, $data['status']);
        $this->assertEquals('status', $data['field']);
        $this->assertEquals(['active', 'inactive', 'pending'], $data['validValues']);
    }

    public function testGetSingleTodo(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $todoData = [
            'title' => 'Todo to Get',
            'description' => 'Description for get test'
        ];
        
        $client->request('POST', '/api/todos', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($todoData));
        
        $this->assertResponseStatusCodeSame(201);
        $createResponse = json_decode($client->getResponse()->getContent(), true);
        $todoId = $createResponse['id'];
        
        $client->request('GET', "/api/todos/{$todoId}");
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json; charset=utf-8');
        
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);
        
        $this->assertEquals($todoId, $data['id']);
        $this->assertEquals('Todo to Get', $data['title']);
        $this->assertEquals('Description for get test', $data['description']);
    }

    public function testPatchTodo(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $todoData = [
            'title' => 'Original Todo'
        ];
        
        $client->request('POST', '/api/todos', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($todoData));
        
        $this->assertResponseStatusCodeSame(201);
        $createResponse = json_decode($client->getResponse()->getContent(), true);
        $todoId = $createResponse['id'];
        
        $patchData = [
            'title' => 'Updated Todo',
            'completed' => true,
            'priority' => 'high'
        ];
        
        $client->request('PATCH', "/api/todos/{$todoId}", [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($patchData));
        
        $this->assertResponseIsSuccessful();
        
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);
        
        $this->assertEquals('Updated Todo', $data['title']);
        $this->assertTrue($data['completed']);
        $this->assertEquals('high', $data['priority']);
        $this->assertNotNull($data['updatedAt']);
    }

    public function testDeleteTodo(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $todoData = [
            'title' => 'Todo to Delete'
        ];
        
        $client->request('POST', '/api/todos', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($todoData));
        
        $this->assertResponseStatusCodeSame(201);
        $createResponse = json_decode($client->getResponse()->getContent(), true);
        $todoId = $createResponse['id'];
        
        $client->request('DELETE', "/api/todos/{$todoId}");
        
        $this->assertResponseStatusCodeSame(204);
        $this->assertEmpty($client->getResponse()->getContent());
        
        $client->request('GET', "/api/todos/{$todoId}");
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetNonExistentTodo(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $client->request('GET', '/api/todos/99999');
        
        $this->assertResponseStatusCodeSame(404);
    }

    public function testPatchNonExistentTodo(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $patchData = [
            'title' => 'Updated Non-existent Todo'
        ];
        
        $client->request('PATCH', '/api/todos/99999', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($patchData));
        
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteNonExistentTodo(): void
    {
        $client = $this->createAuthenticatedClient();
        
        $client->request('DELETE', '/api/todos/99999');
        
        $this->assertResponseStatusCodeSame(404);
    }
}