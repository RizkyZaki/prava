<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends BaseApiController
{
    /**
     * Employee list.
     * Endpoint: GET /api/v1/employees
     */
    public function index(Request $request): JsonResponse
    {
        $employees = User::query()
            ->with('employeeProfile')
            ->when($request->filled('search'), function ($q) use ($request) {
                $keyword = (string) $request->string('search');
                $q->where(function ($inner) use ($keyword) {
                    $inner->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%');
                });
            })
            ->orderBy('name')
            ->paginate((int) $request->integer('per_page', 15));

        return $this->paginated($employees);
    }

    /**
     * Employee details.
     * Endpoint: GET /api/v1/employees/{id}
     */
    public function show(int $id): JsonResponse
    {
        $employee = User::query()->with('employeeProfile')->find($id);

        if (!$employee) {
            return $this->notFound('Employee not found');
        }

        return $this->success($employee);
    }
}
