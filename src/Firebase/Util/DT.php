<?php

namespace Kreait\Firebase\Util;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Kreait\Firebase\Exception\InvalidArgumentException;

class DT
{
    public static function toUTCDateTimeImmutable($value): DateTimeImmutable
    {
        $dt = self::fromDateTimeInterface($value)
            ?? self::fromSeconds($value)
            ?? self::fromMicroSeconds($value)
            ?? self::fromMicroTime($value)
            ?? self::fromZero($value);

        try {
            $dt = $dt ?? new DateTimeImmutable((string) $value);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        return $dt->setTimezone(new DateTimeZone('UTC'));
    }

    private static function fromDateTimeInterface($value): ?DateTimeImmutable
    {
        $dt = null;

        if ($value instanceof DateTimeInterface) {
            $dt = DateTimeImmutable::createFromFormat('U.u', $value->format('U.u'));
        }

        return $dt ?: null;
    }

    private static function fromSeconds($value): ?DateTimeImmutable
    {
        if (!is_scalar($value)) {
            return null;
        }

        if (ctype_digit((string) $value) && strlen((string) $value) === strlen((string) time())) {
            return DateTimeImmutable::createFromFormat('U', (string) $value) ?: null;
        }

        return null;
    }

    private static function fromMicroSeconds($value): ?DateTimeImmutable
    {
        if (!is_scalar($value)) {
            return null;
        }

        if (ctype_digit((string) $value) && strlen((string) $value) === strlen((string) (time() * 1000))) {
            return DateTimeImmutable::createFromFormat('U.u', sprintf('%F', (int) $value / 1000)) ?: null;
        }

        return null;
    }

    private static function fromMicroTime($value): ?DateTimeImmutable
    {
        if (!is_scalar($value)) {
            return null;
        }

        if (!preg_match('@(?P<msec>^0?\.\d+) (?P<sec>\d+)$@', (string) $value, $matches)) {
            return null;
        }

        $value = (float) $matches['sec'] + (float) $matches['msec'];

        return DateTimeImmutable::createFromFormat('U.u', sprintf('%F', $value)) ?: null;
    }

    private static function fromZero($value): ?DateTimeImmutable
    {
        if (is_bool($value) || empty($value)) {
            return DateTimeImmutable::createFromFormat('U', '0') ?: null;
        }

        return null;
    }
}
