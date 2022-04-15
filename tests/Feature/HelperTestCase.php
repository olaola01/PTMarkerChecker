<?php

namespace Tests\Feature;

use App\Enum\RequestTypes;
use App\Models\PendingRequest;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class HelperTestCase extends TestCase
{
    protected function login($user = null)
    {
        $this->actingAs($user ?: User::factory()->create());
    }

    protected function create_data(): array
    {
        return [
            'first_name' => "Olamiposi",
            'last_name' => "Olaiya",
            'email' => "test@admin.com",
            'request_type' => RequestTypes::CREATE,
        ];
    }

    protected function update_data(): array
    {
        $userInfo = $this->create_user_info();

        return [
            'first_name' => "Olamiposi",
            'last_name' => "Olaiya",
            'email' => "test@admin.com",
            'request_type' => RequestTypes::UPDATE,
            'user_info_id' => $userInfo->first()->id
        ];
    }

    protected function create_user_info(): Model|Collection
    {
        $user = User::factory()->times(2)->create();

        return UserInfo::factory()->state([
            'first_name' => 'Ted',
            'created_by' => $user->first()->id,
            'approved_by' => $user->last()->id
        ])->create();
    }

    protected function create_pending_request($request_type, $user_info_id = null): Model|Collection
    {
        $user = User::factory()->times(2)->create();
        return PendingRequest::factory()->state([
            'first_name' => 'Jacob',
            'user_info_id' => $user_info_id,
            'request_type' => $request_type,
            'created_by' => $user->last()->id
        ])->create();
    }
}
