<?php

namespace App\Interfaces;

use App\Models\PendingRequest;

interface UserInfoRepositoryInterface
{
    public function getPendingRequests();
    public function requestUserInfoCreateOrUpdate(array $validatedArray): array;
    public function requestUserInfoDelete(int $id, string $requestType);
    public function approveRequest(int $id, string $requestType): array;
    public function declineRequest(int $id): array;
    public function approveCreateRequest(PendingRequest $pendingRequest): array;
    public function approveUpdateRequest(PendingRequest $pendingRequest): array;
    public function approveDeleteRequest(PendingRequest $pendingRequest): array;
}
