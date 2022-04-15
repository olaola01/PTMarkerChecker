<?php

namespace Tests\Feature\Controller;

use App\Enum\RequestTypes;
use App\Events\ApproveRequestEvent;
use App\Models\PendingRequest;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Feature\HelperTestCase;

class PendingRequestControllerTest extends HelperTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->login();
    }

    public function test_pending_requests_can_be_fetched()
    {
        $this->create_pending_request(RequestTypes::CREATE);
        $response = $this->get(route('get-pending-requests'));
        $response->assertStatus(200);
        $this->assertCount(1, PendingRequest::all());
        $this->assertNotEmpty($response->getContent());
        $response->assertJson(json_decode($response->getContent(), true));
    }

    public function test_admin_can_request_for_user_info_to_be_created()
    {
        Event::fake();
        User::factory()->times(2)->create();

        $response = $this->post(route('create-user-info'), $this->create_data());
        $response->assertStatus(201);
        $pendingRequest = PendingRequest::first();
        $this->assertNotEmpty($response);
        $this->assertEquals('Olamiposi', $pendingRequest->first_name);
        $this->assertEquals('Olaiya', $pendingRequest->last_name);
        $this->assertEquals(RequestTypes::CREATE, $pendingRequest->request_type);
        $this->assertNull($pendingRequest->user_info_id);
        $response->assertJson(json_decode($response->getContent(), true));

        Event::assertDispatched(ApproveRequestEvent::class);
    }

    public function test_admin_can_request_for_user_info_to_be_updated()
    {
        Event::fake();
        User::factory()->times(2)->create();

        $response = $this->post(route('update-user-info'), $this->update_data());
        $response->assertStatus(200);
        $pendingRequest = PendingRequest::first();
        $this->assertNotEmpty($response);
        $this->assertEquals('Olamiposi', $pendingRequest->first_name);
        $this->assertEquals('Olaiya', $pendingRequest->last_name);
        $this->assertEquals(RequestTypes::UPDATE, $pendingRequest->request_type);
        $this->assertEquals(1,$pendingRequest->user_info_id);
        $response->assertJson(json_decode($response->getContent(), true));

        Event::assertDispatched(ApproveRequestEvent::class);
    }

    public function test_admin_can_request_for_user_info_to_be_deleted()
    {
        Event::fake();
        User::factory()->times(2)->create();

        $userInfo = $this->create_user_info();
        $response = $this->put(route('delete-user-info', ['id' => $userInfo->id, 'request_type' => RequestTypes::DELETE]));
        $response->assertStatus(200);
        $pendingRequest = PendingRequest::first();
        $this->assertNotEmpty($response);
        $this->assertNotNull($pendingRequest->id);
        $this->assertNotNull($pendingRequest->first_name);
        $this->assertNotNull($pendingRequest->last_name);
        $this->assertEquals(RequestTypes::DELETE, $pendingRequest->request_type);
        $response->assertJson(json_decode($response->getContent(), true));

        Event::assertDispatched(ApproveRequestEvent::class);
    }

    public function test_admin_can_approve_user_info_to_be_created()
    {
        $pendingRequest = $this->create_pending_request(RequestTypes::CREATE);
        $response = $this->put(route('approve-request', ['id' => $pendingRequest->id, 'request_type' => RequestTypes::CREATE]));
        $response->assertStatus(201);
        $userInfo = UserInfo::first();
        $this->assertNotEmpty($response);
        $this->assertNotNull($userInfo->id);
        $this->assertNotNull($userInfo->first_name);
        $this->assertNotNull($userInfo->last_name);
        $this->assertNotNull($userInfo->email);
        $response->assertJson(json_decode($response->getContent(), true));
    }

    public function test_admin_can_approve_user_info_to_be_updated()
    {
        $userInfo = $this->create_user_info();
        $this->assertEquals('Ted', $userInfo->first_name);
        $pendingRequest = $this->create_pending_request(RequestTypes::UPDATE, $userInfo->id);
        $response = $this->put(route('approve-request', ['id' => $pendingRequest->id, 'request_type' => RequestTypes::UPDATE]));
        $response->assertStatus(200);
        $userInfo->refresh();
        $this->assertNotEmpty($response);
        $this->assertNotNull($userInfo->id);
        $this->assertEquals('Jacob', $userInfo->first_name);
        $this->assertNotNull($userInfo->last_name);
        $this->assertNotNull($userInfo->email);
        $response->assertJson(json_decode($response->getContent(), true));
    }

    public function test_admin_can_approve_user_info_to_be_deleted()
    {
        $userInfo = $this->create_user_info();
        $this->assertCount(1, UserInfo::all());
        $pendingRequest = $this->create_pending_request(RequestTypes::DELETE, $userInfo->first()->id);
        $this->assertCount(1, PendingRequest::all());
        $response = $this->put(route('approve-request', ['id' => $pendingRequest->first()->id, 'request_type' => RequestTypes::DELETE]));
        $response->assertStatus(200);
        $this->assertNotEmpty($response);
        $this->assertCount(0, UserInfo::all());
        $this->assertCount(0, PendingRequest::all());
        $response->assertJson(json_decode($response->getContent(), true));
    }

    public function test_admin_can_decline_requests()
    {
        $pendingRequest = $this->create_pending_request(RequestTypes::CREATE);
        $this->assertCount(1, PendingRequest::all());
        $response = $this->delete(route('decline-request', ['id' => $pendingRequest->id]));
        $response->assertStatus(200);
        $this->assertCount(0, PendingRequest::all());
        $response->assertJson(json_decode($response->getContent(), true));
    }
}
