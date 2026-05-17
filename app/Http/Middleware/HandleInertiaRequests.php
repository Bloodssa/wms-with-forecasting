<?php

namespace App\Http\Middleware;

use App\Enum\UserRole;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'can' => function () use ($user) {
                if (!$user) {
                    return [];
                }
                // can view the admin sidebar with gate in appserviceprovider
                return [
                    'viewAdminArea' => $user->can('view-admin-area'),
                    'canRegisterWarranty' => $user->can('can-register-warranty'),
                    'canAdd' => $user->can('can-add'),
                    'viewInquiryOnly' => $user->can('view-inquiry-only')
                ];
            },
            'flash' => function () {
                return [
                    'success' => session('success'),
                    'error' => session('error'),
                    'warning' => session('warning'),
                    'timestamp' => microtime(),
                    'download_ids' => session('download_ids'),
                ];
            },
            // notification for customers
            'notifications' => function () use ($request) {
                $user = $request->user();

                if (! $user) {
                    return [];
                }

                // customer notification recieve sas props in vue
                if ($user->role === UserRole::CUSTOMER) {
                    return $user->getCustomerNotifications();
                }

                if (in_array($user->role, [UserRole::ADMIN, UserRole::STAFF, UserRole::TECHNICIAN])) {
                    return $user->getAdminNotifications();
                }

                return [];
            }
        ];
    }
}
