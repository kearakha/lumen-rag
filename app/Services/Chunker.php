<?php

namespace App\Services;

class Chunker
{
    public function __construct(
        private int $size = 500,
        private int $overlap = 100,
    ) {}

    /**
     * @return array<int, string>
     */
    public function chunk(string $text): array
    {
        $text = trim($text);
        $chunks = [];
        $start = 0;
        $length = mb_strlen($text);

        while ($start < $length) {
            $chunks[] = trim(mb_substr($text, $start, $this->size));
            $start += $this->size - $this->overlap;
        }

        return array_values(array_filter($chunks, fn ($c) => $c !== ''));
    }
}
