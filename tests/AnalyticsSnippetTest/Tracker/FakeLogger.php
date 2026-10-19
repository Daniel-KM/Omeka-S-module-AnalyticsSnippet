<?php declare(strict_types=1);

namespace AnalyticsSnippetTest\Tracker;

/**
 * Minimal replacement for Omeka\Logger: records the errors for assertions.
 */
class FakeLogger
{
    /**
     * @var array
     */
    protected $errors = [];

    public function err($message, $extra = []): void
    {
        $replace = [];
        foreach ($extra as $key => $value) {
            $replace['{' . $key . '}'] = (string) $value;
        }
        $this->errors[] = strtr((string) $message, $replace);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
