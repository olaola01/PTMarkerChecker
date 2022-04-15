<?php


namespace App\Repositories;

use App\Enum\ApiStatusMessageResponse;
use App\Enum\RequestTypes;
use App\Interfaces\UserInfoRepositoryInterface;
use App\Traits\ApiResponder;
use App\Models\User;
use App\Models\UserInfo;
use App\Events\ApproveRequestEvent;
use App\Models\PendingRequest;


class UserInfoRepository implements UserInfoRepositoryInterface
{
    use ApiResponder;

    public function getPendingRequests()
    {
        return PendingRequest::where('created_by', '!=', auth()->user()->id)->get();
    }

    public function requestUserInfoCreateOrUpdate(array $validatedArray): array
    {

        $attributes['created_by'] = auth()->user()->id;
        $attributes += $validatedArray;
        $pendingRequest = PendingRequest::create($attributes);

        $admins = User::select('name', 'email')->where('id', '!=', auth()->user()->id)->get();

        foreach ($admins as $admin) {
            event(new ApproveRequestEvent($admin));
        }

        return [
            'data' => $pendingRequest,
            'message' => "User information will {$attributes['request_type']} successfully when approved"
        ];
    }

    public function requestUserInfoDelete(int $id, string $requestType)
    {
        $userInfo = UserInfo::where('id', $id)->firstOrFail();

        $pendingRequest = PendingRequest::create([
            'first_name' => $userInfo->first_name,
            'last_name' => $userInfo->last_name,
            'email' => $userInfo->email,
            'user_info_id' => $id,
            'created_by' => auth()->user()->id,
            'request_type' => $requestType,
        ]);

        $admins = User::select('name', 'email')->where('id', '!=', auth()->user()->id)->get();

        foreach ($admins as $admin) {
            event(new ApproveRequestEvent($admin));
        }

        return $pendingRequest;
    }

    public function approveRequest(int $id, string $requestType): array
    {
        $response = PendingRequest::where('id', $id)->where('request_type', $requestType)->firstOrFail();

        if ($response->created_by != auth()->user()->id) {
            if ($requestType == RequestTypes::CREATE) {
                return $this->approveCreateRequest($response);
            } else if ($requestType == RequestTypes::UPDATE) {
                return $this->approveUpdateRequest($response);
            } else if ($requestType == RequestTypes::DELETE) {
                return $this->approveDeleteRequest($response);
            }
        }

        return [
            'status_code' => 403,
            'status' => ApiStatusMessageResponse::ERROR,
            'message' => "Can't approve requests you created"
        ];
    }

    public function approveCreateRequest(PendingRequest $pendingRequest): array
    {
        $userInfo = UserInfo::create([
            'first_name' => $pendingRequest->first_name,
            'last_name' => $pendingRequest->last_name,
            'email' => $pendingRequest->email,
            'created_by' => $pendingRequest->created_by,
            'approved_by' => auth()->user()->id,
        ]);

        $pendingRequest->delete();

        return [
            'status_code' => 201,
            'status' => ApiStatusMessageResponse::SUCCESS,
            'data' => $userInfo,
            'message' => 'New User Information created successfully'
        ];
    }

    public function approveUpdateRequest(PendingRequest $pendingRequest): array
    {
        $userInfo = UserInfo::where('id', $pendingRequest->user_info_id)->firstOrFail();

        $userInfo->update([
            "first_name" => $pendingRequest->first_name,
            "last_name" => $pendingRequest->last_name,
            "email" => $pendingRequest->email,
            "created_by" => $pendingRequest->created_by,
            "approved_by" => auth()->user()->id
        ]);

        $pendingRequest->delete();

        return [
            'status_code' => 200,
            'status' => ApiStatusMessageResponse::SUCCESS,
            'data' => $userInfo,
            'message' => 'User Information updated successfully'
        ];

    }

    public function approveDeleteRequest(PendingRequest $pendingRequest): array
    {
        UserInfo::find($pendingRequest->user_info_id)->delete();

        $pendingRequest->delete();

        return [
            'status_code' => 200,
            'status' => ApiStatusMessageResponse::SUCCESS,
            'message' => 'User Information deleted successfully'
        ];
    }

    public function declineRequest(int $id): array
    {
        $response = PendingRequest::where('id', $id)->firstOrFail();
        if ($response->created_by != auth()->user()->id) {
            $response->delete();

            return [
                'status' => ApiStatusMessageResponse::SUCCESS,
                'message' => 'Request declined successfully'
            ];
        } else {

            return [
                'status' => ApiStatusMessageResponse::ERROR,
                'message' => "Can't approve requests you created"
            ];
        }
    }
}
