<?php

namespace App\Http\Controllers;

use App\Enum\ApiStatusMessageResponse;
use App\Http\Resources\PendingRequestResource;
use App\Http\Resources\UserInfoResource;
use App\Interfaces\UserInfoRepositoryInterface;
use App\Http\Requests\CreateUserInfoRequest;
use App\Http\Requests\UpdateUserInfoRequest;
use Illuminate\Http\JsonResponse;

class PendingRequestController extends Controller
{
    protected UserInfoRepositoryInterface $userInfoRepository;

    public function __construct(UserInfoRepositoryInterface $userInfoRepository)
    {
        $this->userInfoRepository = $userInfoRepository;
    }

    public function getPendingRequests(): JsonResponse
    {
        $responseData = $this->userInfoRepository->getPendingRequests();

        return $this->successCall(ApiStatusMessageResponse::SUCCESS, 200, PendingRequestResource::collection($responseData), 'All Pending Requests');
    }

    public function create(CreateUserInfoRequest $createUserInfoRequest): JsonResponse
    {
        return $this->updateOrCreate($createUserInfoRequest, 201);
    }

    public function update(UpdateUserInfoRequest $updateUserInfoRequest): JsonResponse
    {
        return $this->updateOrCreate($updateUserInfoRequest, 200);
    }

    public function delete($id, $request_type): JsonResponse
    {
        $responseData = $this->userInfoRepository->requestUserInfoDelete($id, $request_type);

        return $this->successCall(ApiStatusMessageResponse::SUCCESS, 200, new PendingRequestResource($responseData), 'User information will be deleted successfully when approved');
    }

    public function approve($id, $request_type): JsonResponse
    {
        $responseData = $this->userInfoRepository->approveRequest($id, $request_type);

        if ($responseData['status'] == ApiStatusMessageResponse::SUCCESS) {
            return $this->successCall(ApiStatusMessageResponse::SUCCESS, $responseData['status_code'], isset($responseData['data']) ? new UserInfoResource($responseData['data']): [], $responseData['message']);
        }

        return $this->badCall($responseData['status_code'], ApiStatusMessageResponse::ERROR, $responseData['message']);
    }

    public function decline($id): JsonResponse
    {
        $responseData = $this->userInfoRepository->declineRequest($id);

        if ($responseData['status'] == ApiStatusMessageResponse::SUCCESS) {
            return $this->successCall(ApiStatusMessageResponse::SUCCESS, 200, [], $responseData['message']);
        }

        return $this->badCall(403, ApiStatusMessageResponse::ERROR, $responseData['message']);
    }

    private function updateOrCreate($infoRequest, $status_code): JsonResponse
    {
        $responseData = $this->userInfoRepository->requestUserInfoCreateOrUpdate($infoRequest->validated());

        return $this->successCall(ApiStatusMessageResponse::SUCCESS, $status_code, new PendingRequestResource($responseData['data']), $responseData['message']);
    }
}
