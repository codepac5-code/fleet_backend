<?php

namespace App\Http\Services\Panel\Admin\Documents\Logic;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Document;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DocumentRepository
{
    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    public function paginate(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Document::on($this->connection())
            ->when($search, fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
            ->orderByDesc('is_required')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function activeOptions(): array
    {
        return Document::on($this->connection())
            ->where('status', 1)
            ->orderByDesc('is_required')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function findOrFail(int $id): Document
    {
        return Document::on($this->connection())->findOrFail($id);
    }

    public function create(array $data): Document
    {
        $document = new Document($data);

        if ($connection = $this->connection()) {
            $document->setConnection($connection);
        }

        $document->save();

        return $document;
    }

    public function update(Document $document, array $data): Document
    {
        $document->fill($data)->save();

        return $document;
    }

    public function toggleStatus(Document $document): Document
    {
        $document->status = $document->status ? 0 : 1;
        $document->save();

        return $document;
    }

    public function toggleRequired(Document $document): Document
    {
        $document->is_required = $document->is_required ? 0 : 1;
        $document->save();

        return $document;
    }

    public function delete(Document $document): void
    {
        $document->delete();
    }
}
