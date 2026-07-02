<?php

declare(strict_types=1);

namespace Adeliom\HorizonQueryBuilder\Database;

class DateQuery
{
    private string $column = 'post_date';
    private ?\DateTimeInterface $after = null;
    private ?\DateTimeInterface $before = null;
    private bool $inclusive = true;

    public function __construct() {}

    public function column(string $column = 'post_date'): self
    {
        $this->column = $column;

        return $this;
    }

    public function after(\DateTimeInterface|string|null $after): self
    {
        $this->after = $this->normalize($after);

        return $this;
    }

    public function before(\DateTimeInterface|string|null $before): self
    {
        $this->before = $this->normalize($before);

        return $this;
    }

    public function inclusive(bool $inclusive = true): self
    {
        $this->inclusive = $inclusive;

        return $this;
    }

    private function normalize(\DateTimeInterface|string|null $value): ?\DateTimeInterface
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        return new \DateTimeImmutable($value);
    }

    public function getQuery(): array
    {
        return array_filter([
            'after' => $this->after,
            'before' => $this->before,
        ], static fn ($bound) => null !== $bound);
    }

    public function generateDateQueryArray(): array
    {
        $query = [
            'column' => $this->column,
            'inclusive' => $this->inclusive,
        ];

        if (null !== $this->after) {
            $query['after'] = $this->after->format('Y-m-d H:i:s');
        }

        if (null !== $this->before) {
            $query['before'] = $this->before->format('Y-m-d H:i:s');
        }

        return $query;
    }
}
