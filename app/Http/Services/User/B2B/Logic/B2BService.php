<?php

namespace App\Http\Services\User\B2B\Logic;

use App\Http\Core\Exceptions\DomainException;
use App\Models\CorporateInvoice;
use App\Models\FamilyMember;

class B2BService
{
    public function corporateInvoices(int $userId): array
    {
        $invoices = CorporateInvoice::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->get()
            ->map(fn (CorporateInvoice $i) => [
                'id' => (int) $i->id,
                'user_id' => (int) $i->user_id,
                'month' => $i->month,
                'trips' => (int) $i->trips,
                'amount_minor' => (int) $i->amount_minor,
                'currency_code' => $i->currency_code,
                'status' => $i->status,
            ])
            ->all();

        return ['invoices' => $invoices];
    }

    public function familyList(int $userId): array
    {
        return FamilyMember::query()
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get()
            ->map(fn (FamilyMember $m) => $this->present($m))
            ->all();
    }

    public function familyAdd(int $userId, array $v): array
    {
        $member = FamilyMember::query()->create([
            'user_id' => $userId,
            'name' => $v['name'],
            'phone' => $v['phone'],
            'type' => $v['type'] ?? 'adult',
            'approval_required' => ! empty($v['approvalRequired']),
            'auto_share' => ! empty($v['autoShare']),
        ]);

        return $this->present($member);
    }

    public function familyUpdate(int $userId, int $id, array $v): array
    {
        $member = $this->owned($userId, $id);

        foreach ([['name', 'name'], ['phone', 'phone'], ['type', 'type']] as [$in, $col]) {
            if (array_key_exists($in, $v)) {
                $member->{$col} = $v[$in];
            }
        }
        if (array_key_exists('approvalRequired', $v)) {
            $member->approval_required = (bool) $v['approvalRequired'];
        }
        if (array_key_exists('autoShare', $v)) {
            $member->auto_share = (bool) $v['autoShare'];
        }

        $member->save();

        return $this->present($member);
    }

    public function familyRemove(int $userId, int $id): void
    {
        $this->owned($userId, $id)->delete();
    }

    private function owned(int $userId, int $id): FamilyMember
    {
        $member = FamilyMember::query()->where('id', $id)->where('user_id', $userId)->first();

        if ($member === null) {
            throw DomainException::notFound();
        }

        return $member;
    }

    private function present(FamilyMember $m): array
    {
        return [
            'id' => (int) $m->id,
            'user_id' => (int) $m->user_id,
            'name' => $m->name,
            'phone' => $m->phone,
            'type' => $m->type,
            'approval_required' => (bool) $m->approval_required,
            'auto_share' => (bool) $m->auto_share,
        ];
    }
}
