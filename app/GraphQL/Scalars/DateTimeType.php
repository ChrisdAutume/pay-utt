<?php

declare(strict_types=1);

namespace App\GraphQL\Scalars;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ScalarType;
use Rebing\GraphQL\Support\Contracts\TypeConvertible;

class DateTimeType extends ScalarType implements TypeConvertible
{
    /**
     * @var string
     */
    public $name = 'DateTimeType';
    protected $dateFormat = "Y-m-d H:i";

    /**
     * @var string
     */
    public $description = 'DateTime format: d/m/Y H:i';

    /**
     * Serializes an internal value to include in a response.
     *
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws Error
     */
    public function serialize($value)
    {
        return $this->parseValue($value);
    }

    /**
     * Parses an externally provided value (query variable) to use as an input.
     *
     * In the case of an invalid value this method must throw an Exception
     *
     * @param mixed $value
     *
     * @return mixed
     *
     * @throws Error
     */
    public function parseValue($value)
    {
        $date = \DateTime::createFromFormat($this->dateFormat, $value);
        if($date == false)
            throw new Exception("Error mapping to DateTime ".$this->dateFormat);

        return $date;
    }

    /**
     * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input.
     *
     * In the case of an invalid node or value this method must throw an Exception
     *
     * @param Node $valueNode
     * @param mixed[]|null $variables
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        return $this->parseValue($valueNode->value);
    }

    public function toType(): Type
    {
        return new static();
    }
}
