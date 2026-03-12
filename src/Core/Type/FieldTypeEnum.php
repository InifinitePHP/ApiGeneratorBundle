<?php

namespace Rehark\ApiGeneratorBundle\Core\Type;

use Doctrine\DBAL\Types\Types;

enum FieldTypeEnum: string
{
    case INTEGER  = Types::INTEGER;
    case SMALLINT = Types::SMALLINT;

    case FLOAT   = Types::FLOAT;
    case BIGINT  = Types::BIGINT;
    case DECIMAL = Types::DECIMAL;

    case BOOLEAN = Types::BOOLEAN;

    case STRING       = Types::STRING;
    case ASCII_STRING = Types::ASCII_STRING;
    case TEXT         = Types::TEXT;
    case GUID         = Types::GUID;
    // case ENUM         = Types::ENUM;
    case ENUM         = 'enum';

    case DATE_MUTABLE         = Types::DATE_MUTABLE;
    case DATETIME_MUTABLE     = Types::DATETIME_MUTABLE;
    case DATETIMETZ_MUTABLE   = Types::DATETIMETZ_MUTABLE;
    case TIME_MUTABLE         = Types::TIME_MUTABLE;

    case DATE_IMMUTABLE         = Types::DATE_IMMUTABLE;
    case DATETIME_IMMUTABLE     = Types::DATETIME_IMMUTABLE;
    case DATETIMETZ_IMMUTABLE   = Types::DATETIMETZ_IMMUTABLE;
    case TIME_IMMUTABLE         = Types::TIME_IMMUTABLE;

    case DATEINTERVAL = Types::DATEINTERVAL;

    case JSON         = Types::JSON;
    case SIMPLE_ARRAY = Types::SIMPLE_ARRAY;
    case BLOB         = Types::BLOB;
    case BINARY       = Types::BINARY;

    /**
     * @return array<int, self>
     */
    public static function integers(): array
    {
        return [self::INTEGER, self::SMALLINT];
    }

    /**
     * @return array<int, self>
     */
    public static function strings(): array
    {
        return [self::BIGINT, self::DECIMAL, self::STRING, self::ASCII_STRING, self::TEXT, self::GUID, self::ENUM];
    }

    /**
     * @return array<int, self>
     */
    public static function mutableDates(): array
    {
        return [self::DATE_MUTABLE, self::DATETIME_MUTABLE, self::DATETIMETZ_MUTABLE, self::TIME_MUTABLE];
    }

    /**
     * @return array<int, self>
     */
    public static function immutableDates(): array
    {
        return [self::DATE_IMMUTABLE, self::DATETIME_IMMUTABLE, self::DATETIMETZ_IMMUTABLE, self::TIME_IMMUTABLE];
    }

    /**
     * @return array<int, self>
     */
    public static function passThrough(): array
    {
        return [self::JSON, self::SIMPLE_ARRAY, self::BLOB, self::BINARY];
    }

    public function cast(mixed $value): mixed
    {
        if ($value === null) return null;

        if (in_array($this, self::passThrough())) {
            return $value;
        }

        assert(is_scalar($value));

        return match (true) {
            in_array($this, self::integers())       => (int) $value,
            $this === self::FLOAT                   => (float) $value,
            $this === self::BOOLEAN                 => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            in_array($this, self::strings())        => (string) $value,
            in_array($this, self::mutableDates())   => new \DateTime((string) $value),
            in_array($this, self::immutableDates()) => new \DateTimeImmutable((string) $value),
            $this === self::DATEINTERVAL            => new \DateInterval((string) $value),
            default                                 => $value,
        };
    }

    public static function fromDoctrineType(string $doctrineType): self
    {
        $case = self::tryFrom($doctrineType);

        if ($case === null) {
            throw new \InvalidArgumentException(
                sprintf('Type Doctrine "%s" non supporté par FieldTypeEnum.', $doctrineType)
            );
        }

        return $case;
    }
}
