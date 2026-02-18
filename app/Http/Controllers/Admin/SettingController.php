<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Store\SettingRequest as StoreRequest;
use App\Http\Requests\Update\SettingRequest as UpdateRequest;
use App\Services\SettingService as Service;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->middleware('permission:settings-index|settings-edit', ['only' => ['index']]);
        $this->middleware('permission:settings-edit', ['only' => ['edit', 'update']]);

        $this->service = $service;
        $this->module = 'settings';
    }

    /**
     * Show settings in tab format
     */
    public function index(): Renderable
    {
        $settings = $this->service->getAll();
        $settingsByGroup = $settings->groupBy('group');

        $this->data = [
            'module' => __('Settings'),
            'title' => __('Configuration'),
            'settingsByGroup' => $settingsByGroup,
            'groups' => $this->getSettingGroups(),
            'groupIcons' => $this->getGroupIcons(),
            'groupDescriptions' => $this->getGroupDescriptions(),
        ];

        return $this->render('index');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Convert value based on type
        $data['value'] = $this->convertValue($data['value'] ?? null, $data['type']);

        $item = $this->service->create($data);

        return $this->redirectSuccess('admin.settings.index', __('Setting created successfully'));
    }

    public function update(UpdateRequest $request, string $id): RedirectResponse
    {
        $data = $request->validated();

        // Convert value based on type
        $data['value'] = $this->convertValue($data['value'] ?? null, $data['type']);

        $this->service->update($id, $data);

        return $this->redirectSuccess('admin.settings.index', __('Setting updated successfully'));
    }

    /**
     * Bulk update settings
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $settings = $request->input('settings', []);

        foreach ($settings as $key => $value) {
            $this->service->setValue($key, $value);
        }

        return $this->redirectSuccessBack(__('Settings updated successfully'));
    }

    /**
     * Get setting groups
     */
    private function getSettingGroups(): array
    {
        return [
            'general' => __('General'),
            'email' => __('Email'),
            'payment' => __('Payment'),
            'security' => __('Security'),
            'api' => __('API'),
            'notification' => __('Notification'),
            'system' => __('System'),
        ];
    }

    /**
     * Get setting types
     */
    private function getSettingTypes(): array
    {
        return [
            'text' => __('Text'),
            'number' => __('Number'),
            'boolean' => __('Boolean'),
            'json' => __('JSON'),
            'file' => __('File'),
            'email' => __('Email'),
            'url' => __('URL'),
            'textarea' => __('Textarea'),
            'select' => __('Select'),
            'radio' => __('Radio'),
            'checkbox' => __('Checkbox'),
        ];
    }

    /**
     * Get group icons
     */
    private function getGroupIcons(): array
    {
        return [
            'general' => 'gear',
            'email' => 'envelope',
            'payment' => 'credit-card',
            'security' => 'shield-check',
            'api' => 'code',
            'notification' => 'bell',
            'system' => 'cpu',
        ];
    }

    /**
     * Get group descriptions
     */
    private function getGroupDescriptions(): array
    {
        return [
            'general' => __('Basic site configuration and general settings'),
            'email' => __('Email configuration and mail settings'),
            'payment' => __('Payment system and transaction settings'),
            'security' => __('Security policies and authentication settings'),
            'api' => __('API configuration and rate limiting settings'),
            'notification' => __('Notification preferences and alerts'),
            'system' => __('System configuration and technical settings'),
        ];
    }

    /**
     * Convert value based on type
     */
    private function convertValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool)$value;
            case 'number':
                return is_numeric($value) ? (float)$value : $value;
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            default:
                return $value;
        }
    }
}
