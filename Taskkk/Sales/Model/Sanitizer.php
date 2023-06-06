<?php
declare(strict_types=1);

namespace Base\Sales\Model;

use Base\Sales\Api\Data\SanitizerInterface;

class Sanitizer implements SanitizerInterface
{
    /**
     * @inheritdoc
     */
    public function sanitizeSearchText(string $searchText): string
    {
        $searchText = preg_replace('/[^a-z_\-0-9]*/i', '', $searchText);
        return $searchText;
    }
}
