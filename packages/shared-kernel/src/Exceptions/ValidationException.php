<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Exceptions;

/**
 * Thrown when input data fails validation rules.
 *
 * Carries a field-keyed errors array mirroring Laravel's validation
 * error bag structure so that API handlers can return 422 responses
 * with per-field feedback.
 *
 * Maps to HTTP 422 Unprocessable Entity in API response handlers.
 */
class ValidationException extends DomainException
{
    /** Default HTTP status code for this exception type. */
    public const HTTP_STATUS = 422;

    /**
     * @param  array<string, string|array<int, string>>  $errors   Field-keyed validation messages.
     * @param  string                                     $message  Human-readable summary.
     * @param  array<string, mixed>                       $context  Additional diagnostic context.
     * @param  \Throwable|null                            $previous The originating exception, if any.
     */
    public function __construct(
        private readonly array $errors,
        string $message = 'The given data was invalid.',
        array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $context, self::HTTP_STATUS, $previous);
    }

    /**
     * Named constructor – build from a field-keyed errors array.
     *
     * @param  array<string, string|array<int, string>>  $errors  Field-keyed error messages.
     * @return self
     */
    public static function withErrors(array $errors): self
    {
        return new self($errors);
    }

    /**
     * Named constructor – build with a single field error.
     *
     * @param  string  $field    The field name.
     * @param  string  $message  The error message for the field.
     * @return self
     */
    public static function forField(string $field, string $message): self
    {
        return new self([$field => [$message]]);
    }

    /**
     * Return the field-keyed validation errors array.
     *
     * @return array<string, string|array<int, string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Determine whether a specific field has validation errors.
     *
     * @param  string  $field  The field name to check.
     * @return bool
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    /**
     * Return all error messages as a flat string array.
     *
     * @return array<int, string>
     */
    public function getMessages(): array
    {
        $messages = [];

        foreach ($this->errors as $fieldErrors) {
            if (is_array($fieldErrors)) {
                foreach ($fieldErrors as $msg) {
                    $messages[] = $msg;
                }
            } else {
                $messages[] = $fieldErrors;
            }
        }

        return $messages;
    }
}
