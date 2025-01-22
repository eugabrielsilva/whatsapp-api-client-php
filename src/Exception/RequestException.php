<?php

namespace WhatsAppPHP\Exception;

use Exception;
use Throwable;

/**
 * WhatsApp PHP Request exception.
 * @author Gabriel Silva
 * @license MIT
 */
class RequestException extends Exception
{
    /**
     * Exception details, if any.
     * @var string|null
     */
    private $details;

    /**
     * Constructs the exception.
     * @param string $message (Optional) Exception message.
     * @param int $code (Optional) Exception error code.
     * @param string|null $details (Optional) Exception details.
     * @param Throwable|null $previous (Optional) The previous throwable used for the exception chaining.
     */
    public function __construct(string $message = '', int $code = 0, ?string $details = null, ?Throwable $previous = null)
    {
        $this->details = $details;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the exception details, if any.
     * @return string|null Returns the details.
     */
    public function getDetails()
    {
        return $this->details;
    }
}
