<?php

namespace App\Models;

use App\Enum\InquiryResponseType;
use App\Enum\UserRole;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
        'email_verified_at',
        'created_at',
        'updated_at'
    ];

    /**
     * for validation
     * @param array $roles of the users
     */
    public function hasAnyRole(array $allowedRoles): bool
    {
        return in_array($this->role->value, $allowedRoles, true);
    }

    /**
     * Relationship of the warranties
     */
    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function isAdmin()
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isTechnician()
    {
        return $this->role === UserRole::TECHNICIAN;
    }

    public function isStaff()
    {
        return $this->role === UserRole::STAFF;
    }

    /**
     * Customer notification
     */
    public function getCustomerNotifications()
    {
        // all notifications will be shown is 2 months range only
        $now = now()->startOfDay();
        $twoMonths = now()->addMonths(2)->endOfDay();
        $twoMonthsAgo = now()->subMonths(2)->startOfDay();

        // warranty expired and near expiry
        $warrantyNotifications = Warranty::with('product')
            ->whereUserId($this->id)
            ->whereBetween('expiry_date', [$twoMonthsAgo, $twoMonths])
            ->get()
            ->map(function ($w) use ($now) {
                if ($w->expiry_date < $now) {
                    return [
                        'type' => 'error',
                        'message' => "Your product {$w->product->name} expired on {$w->expiry_date->format('M d')}",
                        'date' => $w->expiry_date,
                        'link' => route('warranty.show', $w->id)
                    ];
                }
                return [
                    'type' => 'warning',
                    'message' => "Warranty of {$w->product->name} expires on {$w->expiry_date->format('M d')}",
                    'date' => $w->expiry_date,
                    'link' => route('warranty.show', $w->id)
                ];
            });

        // inquiry status updates
        $inquiryUpdates = InquiryResponse::with('warrantyInquiries.warranty.product')
            ->whereHas('warrantyInquiries', function ($q) {
                $q->where('user_id', $this->id);
            })
            ->where('type', InquiryResponseType::UPDATES->value)
            ->where('created_at', '>=', $twoMonthsAgo)
            ->get()
            ->map(fn($response) => [
                'type' => 'info',
                'message' => "Inquiry for {$response->warrantyInquiries->warranty->product->name}: {$response->message}",
                'date' => $response->created_at,
                'link' => route('inquiry.show', $response->warrantyInquiries->id)
            ]);

        return collect($warrantyNotifications)
            ->concat($inquiryUpdates)
            ->sortByDesc('date')
            ->values();
    }

    /**
     * Admin side notifications
     */
    public function getAdminNotifications()
    {
        // 2 months range
        $twoMonthsAgo = now()->subMonths(2)->startOfDay();

        // new warranty inquiries
        $newInquiries = WarrantyInquiries::with(['warranty.product'])
            ->whereIn('status', ['open', 'pending'])
            ->where('created_at', '>=', $twoMonthsAgo)
            ->get()
            ->map(fn($i) => [
                'type' => 'danger', // high priority
                'message' => "New inquiry from {$i->warranty->user->name} regarding {$i->warranty->product->name}",
                'date' => $i->created_at,
                'link' => route('inquiry-action', $i->id),
            ]);

        // customer reply
        $customerReplies = InquiryResponse::with(['user', 'warrantyInquiries.warranty.product'])
            ->whereHas('warrantyInquiries', function ($q) {
                $q->whereColumn('user_id', 'inquiry_responses.user_id');
            })
            ->where('created_at', '>=', $twoMonthsAgo)
            ->get()
            ->map(fn($r) => [
                'type' => 'info',
                'message' => "Customer {$r->user->name} replied to inquiry #{$r->warranty_inquiries_id}",
                'date' => $r->created_at,
                'link' => route('inquiry-action', $r->warranty_inquiries_id),
            ]);

        // review with no response
        $newReviews = ProductReview::with(['user', 'product'])
            ->whereNull('staff_reply')
            ->where('created_at', '>=', $twoMonthsAgo)
            ->get()
            ->map(fn($rev) => [
                'type' => 'warning',
                'message' => "New {$rev->rating}-star review from {$rev->user->name} on {$rev->product->name}",
                'date' => $rev->created_at,
                'link' => route('show.product', $rev->product->id),
            ]);

        // pending warranty claims of the customer
        $pendingWarranties = Warranty::with(['user', 'product'])
            ->where('status', 'pending')
            ->where('created_at', '>=', $twoMonthsAgo)
            ->get()
            ->map(fn($w) => [
                'type' => 'primary',
                'message' => "Warranty registration pending for {$w->serial_number} ({$w->product->name})",
                'date' => $w->created_at,
                'link' => route('view-warranty', $w->id),
            ]);

        return collect($newInquiries)
            ->concat($customerReplies)
            ->concat($newReviews)
            ->concat($pendingWarranties)
            ->sortByDesc('date')
            ->values();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class
        ];
    }
}
