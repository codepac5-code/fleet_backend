<?php

namespace App\Http\Services\User\Support\Presenters;

use App\Models\SafetyContact;
use App\Models\SavedPlace;

class ResourcePresenter
{
    public static function place(SavedPlace $place): array
    {
        return [
            'id' => (int) $place->id,
            'user_id' => (int) $place->user_id,
            'label' => $place->label,
            'icon' => $place->icon,
            'title' => $place->title,
            'address' => $place->address,
            'lat' => (float) $place->lat,
            'lng' => (float) $place->lng,
        ];
    }

    public static function safetyContact(SafetyContact $contact): array
    {
        return [
            'id' => (int) $contact->id,
            'user_id' => (int) $contact->user_id,
            'name' => $contact->name,
            'phone' => $contact->phone,
            'relation' => $contact->relation,
            'is_primary' => (bool) $contact->is_primary,
            'auto_share' => (bool) $contact->auto_share,
        ];
    }
}
