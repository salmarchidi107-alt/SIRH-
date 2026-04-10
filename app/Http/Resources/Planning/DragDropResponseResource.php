<?php

namespace App\Http\Resources\Planning;

use App\Http\Resources\Planning\PlanningResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResponse;

class DragDropResponseResource
{
    public function __construct(
        public bool $success,
        public PlanningResource $data,
        public string $message = 'Planning updated successfully'
    ) {}

    public function toResponse($request)
    {
        return response()->json([
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
        ]);
    }
}

