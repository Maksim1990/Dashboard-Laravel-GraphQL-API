<?php


namespace App\GraphQL\Directives;

use Carbon\Carbon;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

class DateTimeDirective implements FieldResolver
{
    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name()
    {
        return 'dateTime';
    }

    /**
     * Resolve the field directive.
     *
     * @param FieldValue $value
     *
     * @return FieldValue
     */
    public function resolveField(FieldValue $value)
    {
        $field = $value->getFieldName();
        return $value->setResolver(function ($root) use ($field) {
            $carbon = data_get($root, $field);
            return $carbon instanceof Carbon ? $carbon->format("Y-m-d H:i:s") : Carbon::parse($carbon->toDateTime())->format("Y-m-d H:i:s");
        });
    }

    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            A description of what this directive does.
            """
            directive @dateTime(
                """
                Value to be converted into DateTime object.
                """
                value: String
            ) on FIELD_DEFINITION
            GRAPHQL;
    }
}
