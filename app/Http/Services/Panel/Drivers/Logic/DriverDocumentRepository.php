<?php

namespace App\Http\Services\Panel\Drivers\Logic;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\DriverDocument;
use Illuminate\Database\Eloquent\Collection;

class DriverDocumentRepository
{
    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    public function forDriver(int $driverId): Collection
    {
        return DriverDocument::on($this->connection())
            ->where('driverId', $driverId)
            ->latest('id')
            ->get();
    }

    public function findForDriver(int $driverId, int $id): DriverDocument
    {
        return DriverDocument::on($this->connection())
            ->where('driverId', $driverId)
            ->findOrFail($id);
    }

    public function create(array $data): DriverDocument
    {
        $document = new DriverDocument($data);

        if ($connection = $this->connection()) {
            $document->setConnection($connection);
        }

        $document->save();

        return $document;
    }

    public function updateStatus(DriverDocument $document, string $status, ?string $note): DriverDocument
    {
        $document->status = $status;
        $document->note = $note;
        $document->save();

        return $document;
    }

    public function delete(DriverDocument $document): void
    {
        $document->delete();
    }
}
