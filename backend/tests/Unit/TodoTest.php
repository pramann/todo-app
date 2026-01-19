<?php

namespace App\Tests\Unit;

use App\Entity\Todo;
use App\Enum\Priority;
use App\Enum\Status;
use PHPUnit\Framework\TestCase;

class TodoTest extends TestCase
{
    private Todo $todo;

    protected function setUp(): void
    {
        $this->todo = new Todo();
    }

    public function testTodoCreation(): void
    {
        $this->assertInstanceOf(Todo::class, $this->todo);
        $this->assertNull($this->todo->getId());
        $this->assertFalse($this->todo->isCompleted());
        $this->assertEquals(Priority::MEDIUM, $this->todo->getPriority());
        $this->assertEquals(Status::PENDING, $this->todo->getStatus());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->todo->getCreatedAt());
        $this->assertNull($this->todo->getUpdatedAt());
    }

    public function testFluentInterface(): void
    {
        $result = $this->todo
            ->setTitle('Test Title')
            ->setDescription('Test Description')
            ->setCompleted(true)
            ->setPriority(Priority::HIGH)
            ->setStatus(Status::ACTIVE);
        
        $this->assertInstanceOf(Todo::class, $result);
        $this->assertEquals('Test Title', $this->todo->getTitle());
        $this->assertEquals('Test Description', $this->todo->getDescription());
        $this->assertTrue($this->todo->isCompleted());
        $this->assertEquals(Priority::HIGH, $this->todo->getPriority());
        $this->assertEquals(Status::ACTIVE, $this->todo->getStatus());
    }
}