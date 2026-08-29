<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CustomersController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::query()
            ->withCount([
                'locations',
                'equipment',
                'workOrders as active_work_orders_count' => fn ($query) => $query->active(),
            ])
            ->orderBy('name')
            ->paginate($request->integer('per_page', 25));

        return CustomerResource::collection($customers);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create([
            ...$request->validated(),
            'status' => $request->validated('status', 'Active'),
        ]);

        return (new CustomerResource($customer->loadCount([
            'locations',
            'equipment',
            'workOrders as active_work_orders_count' => fn ($query) => $query->active(),
        ])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Customer $customer): CustomerResource
    {
        $this->authorize('view', $customer);

        return new CustomerResource($customer->loadCount([
            'locations',
            'equipment',
            'workOrders as active_work_orders_count' => fn ($query) => $query->active(),
        ]));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): CustomerResource
    {
        $customer->update($request->validated());

        return new CustomerResource($customer->loadCount([
            'locations',
            'equipment',
            'workOrders as active_work_orders_count' => fn ($query) => $query->active(),
        ]));
    }

    public function destroy(Customer $customer): Response
    {
        $this->authorize('delete', $customer);

        $customer->delete();

        return response()->noContent();
    }
}
