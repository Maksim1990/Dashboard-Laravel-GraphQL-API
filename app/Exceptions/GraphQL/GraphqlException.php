<?php


namespace App\Exceptions\GraphQL;

use Exception;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Symfony\Component\HttpFoundation\Response;

class GraphqlException extends Exception implements RendersErrorsExtensions
{
    /**
     * @var @string
     */
    private $reason;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $category;

    /**
     * @var int
     */
    protected $code;

    /**
     * GraphqlException constructor.
     *
     * @param string $message
     * @param array $arrParams
     */
    public function __construct($message, array $arrParams = [], $statusCode = Response::HTTP_BAD_REQUEST)
    {
        $this->reason = $arrParams['reason'] ?? '';
        $this->type = $arrParams['type'] ?? '';
        $this->category = $arrParams['category'] ?? 'default';
        $this->code = $arrParams['code'] ?? $statusCode;

        if (is_array($message)) {
            $message = json_encode($message);
        }

        parent::__construct($message, $this->code);
    }

    /**
     * Return the content that is put in the "extensions" part
     * of the returned error.
     *
     * @return array
     */
    public function extensionsContent(): array
    {
        return [
            'type' => $this->type,
            'reason' => $this->reason,
            'category' => $this->category,
            'code' => $this->code,
        ];
    }

    /**
     * Returns true when exception message is safe to be displayed to a client.
     *
     * @return bool
     * @api
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Returns string describing a category of the error.
     *
     * Value "graphql" is reserved for errors produced by query parsing or validation, do not use it.
     *
     * @return string
     * @api
     */
    public function getCategory(): string
    {
        return $this->category;
    }

}
