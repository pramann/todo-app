<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class EnumValidationExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Check if it's a NotNormalizableValueException from BackedEnum
        if (!$exception instanceof NotNormalizableValueException) {
            return;
        }

        // Check if the message indicates a BackedEnum issue
        if (!str_contains($exception->getMessage(), 'backed enumeration')) {
            return;
        }

        // Extract enum class name from the exception message
        $message = $exception->getMessage();
        if (preg_match('/type ([\\\\\w]+)/', $message, $matches)) {
            $enumClass = $matches[1];
            
            // Get valid enum values if the class exists
            $validValues = [];
            $fieldName = 'unknown_field';
            
            if (class_exists($enumClass) && is_subclass_of($enumClass, \BackedEnum::class)) {
                $validValues = array_map(fn($case) => $case->value, $enumClass::cases());
                
                // Determine field name based on enum class
                if (str_contains($enumClass, 'Priority')) {
                    $fieldName = 'priority';
                } elseif (str_contains($enumClass, 'Status')) {
                    $fieldName = 'status';
                }
            }

            // Create a helpful error response
            $errorData = [
                'type' => 'https://symfony.com/errors/validation',
                'title' => 'Invalid Enum Value',
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'detail' => 'The provided value is not valid for this field.',
                'field' => $fieldName,
                'providedValue' => $this->extractInvalidValue($exception->getMessage()),
                'validValues' => $validValues,
                'message' => sprintf(
                    'Invalid value for %s. Allowed values are: %s',
                    $fieldName,
                    implode(', ', $validValues)
                ),
            ];

            $response = new JsonResponse($errorData, Response::HTTP_UNPROCESSABLE_ENTITY);
            $event->setResponse($response);
        }
    }

    private function extractInvalidValue(string $message): ?string
    {
        // Try to extract the invalid value from the exception message
        if (preg_match('/"([^"]+)"/', $message, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}