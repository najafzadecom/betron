<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Store\BlacklistRequest as StoreRequest;
use App\Http\Requests\Update\BlacklistRequest as UpdateRequest;
use App\Services\BlacklistService as Service;
use App\Services\SiteService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class BlacklistController extends BaseController
{
    private Service $service;

    private SiteService $siteService;

    public function __construct(
        Service     $service,
        SiteService $siteService
    ) {
        $this->middleware('permission:blacklists-index|blacklists-create|blacklists-edit', ['only' => ['index']]);
        $this->middleware('permission:blacklists-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:blacklists-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:blacklists-delete', ['only' => ['destroy']]);

        $this->service = $service;
        $this->siteService = $siteService;
        $this->module = 'blacklists';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Renderable
    {
        $this->data = [
            'module' => __('Blacklists'),
            'title' => __('List'),
            'items' => $this->service->paginate(),
            'sites' => $this->siteService->getAll()
        ];

        return $this->render('list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Renderable
    {
        $this->data = [
            'title' => __('Create'),
            'module' => __('Blacklists'),
            'method' => 'POST',
            'action' => route('admin.' . $this->module . '.store'),
            'sites' => $this->siteService->getAll()
        ];

        return $this->render('form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        $item = $this->service->create($request->validated());

        return $this->redirectSuccess('admin.blacklists.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $this->data = [
            'item' => $this->service->getById($id),
        ];

        return $this->json();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Renderable
    {
        $this->data = [
            'title' => __('Edit'),
            'module' => __('Blacklists'),
            'item' => $this->service->getById($id),
            'method' => 'PUT',
            'action' => route('admin.' . $this->module . '.update', $id),
            'sites' => $this->siteService->getAll()
        ];

        return $this->render('form');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, int $id): RedirectResponse
    {
        $this->service->update($id, $request->validated());

        return $this->redirectSuccess('admin.blacklists.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        // Check if delete confirmation was received
        if (!request()->has('confirmed')) {
            $this->data = [
                'message' => __('Delete confirmation required'),
                'confirmed' => false,
            ];

            return $this->json(422);
        }

        $message = __('Unknown error');
        $code = 500;

        if ($this->service->delete($id)) {
            $message = __('Delete successfully');
            $code = 200;
        }

        $this->data = [
            'message' => $message,
        ];

        return $this->json($code);
    }

    /**
     * Toggle blacklist status (activate/deactivate)
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        try {
            $toggled = $this->service->toggleStatus($id);

            if (!$toggled) {
                return redirect()->back()
                    ->with('error', 'Blacklist tapılmadı.');
            }

            return redirect()->back()
                ->with('success', 'Blacklist statusu dəyişdirildi.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Xəta baş verdi: ' . $e->getMessage());
        }
    }
}
