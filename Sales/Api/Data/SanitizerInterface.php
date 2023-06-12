<?php
declare(strict_types=1);

namespace Base\Sales\Api\Data;

interface SanitizerInterface
{
    /**
     * Remove all symbols except digits, dush and latin alphabet characters
     */
    public function sanitizeSearchText(string $searchText): string;
}
