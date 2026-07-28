<?php

namespace App\Http\Core\Repositories\Driver;

interface DriverDirectoryRepository
{
    public function idsForOffice(int $officeId): array;
}
