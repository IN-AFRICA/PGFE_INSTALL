<?php

declare(strict_types=1);

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

final class PassThroughArrayImport implements ToArray
{
    /**
     * Simply return the parsed worksheet array as-is.
     */
    public function array(array $array)
    {
        return $array;
    }
}
