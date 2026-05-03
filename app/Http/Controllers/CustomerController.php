<?php

namespace App\Http\Controllers;

use App\Enum\InquiryStatusType;
use App\Enum\WarrantyStatusType;
use App\Models\InquiryResponse;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Warranty;
use App\Models\WarrantyInquiries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
    {
        $userId = Auth::user()->id;
        $activeWarranties = Warranty::whereUserId($userId)
            ->where('status', '!=', WarrantyStatusType::EXPIRED->value)
            ->count();

        $expWarCount = Warranty::whereUserId($userId)
            ->where('status', WarrantyStatusType::NEAR_EXPIRY->value)
            ->count();

        $recentlyPurchased = Warranty::query()
            ->whereUserId($userId)
            ->select(['id', 'purchase_date', 'product_id'])
            ->with('product:id,name,product_image_url')
            ->latest('purchase_date')
            ->take(3)
            ->get();

        $resolvedInquiryCount = WarrantyInquiries::whereUserId($userId)
            ->where('status', InquiryStatusType::RESOLVED)
            ->count();

        $expiringWarranties = Warranty::query()
            ->select(['id', 'purchase_date', 'expiry_date', 'product_id'])
            ->whereUserId($userId)
            ->with('product:id,name,product_image_url')
            ->whereIn('status', [WarrantyStatusType::EXPIRED->value, WarrantyStatusType::NEAR_EXPIRY->value])
            ->latest('expiry_date')
            ->limit(3)
            ->get();

        return Inertia::render('Customer/Home', [
            'stats' => [
                'activeWarranties' => $activeWarranties,
                'expWarCount' => $expWarCount,
                'resolvedInquiryCount' => $resolvedInquiryCount
            ],
            'recentlyPurchased' => $recentlyPurchased,
            'expiringWarranties' => $expiringWarranties,
            'products' => Product::query()
                ->with(['category:id,name'])
                ->withAvg('reviews as averageRating', 'rating')
                ->withCount('reviews')
                ->latest()
                ->limit(10)
                ->get()
        ]);
    }

    public function warrantyList(Request $request): Response
    {
        $warranties = Warranty::query()
            ->select(['id', 'serial_number', 'status', 'expiry_date', 'purchase_date', 'product_id'])
            ->with(['product' => function ($query) {
                $query->select([
                    'id',
                    'name',
                    'product_image_url',
                    'category_id',
                    'brand'
                ]);
            }, 'product.category:id,name'])
            ->withCount([ // count unread messages that send by technician
                'inquiries as unread_message_count' => function ($q) {
                    $q->whereHas('responses', function ($q2) {
                        $q2->whereNull('read_at')
                            ->where('user_id', '!=', Auth::id());
                    });
                }
            ])
            ->whereUserId(Auth::user()->id)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('serial_number', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });
                });
            })->when($request->status, function ($query, $status) {
                if ($status === 'active') $query->where('expiry_date', '>', now()->addDays(30));
                if ($status === 'near-expiry') $query->whereBetween('expiry_date', [now(), now()->addDays(30)]);
                if ($status === 'expired') $query->where('expiry_date', '<', now());
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Customer/Warranties', [
            'warranties' => $warranties,
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    public function inquiries(Request $request): Response
    {
        $inquiries = WarrantyInquiries::query()
            ->select(['id', 'warranty_id', 'message', 'status', 'updated_at'])
            ->with([
                'warranty:id,product_id,status,serial_number',
                'warranty.product:id,category_id,brand,name,product_image_url',
                'warranty.product.category:id,name'
            ])
            ->withCount([
                'responses as unread_count' => function ($query) {
                    $query->whereNull('read_at')
                        ->where('user_id', '!=', Auth::id());
                }
            ])
            ->whereUserId(Auth::user()->id)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('warranty', function ($q1) use ($search) {
                        $q1->where('serial_number', 'like', "%{$search}%");
                    })
                        ->orWhereHas('warranty.product', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->withMax('responses', 'created_at')
            ->orderByDesc('responses_max_created_at')
            ->paginate(10)
            ->withQueryString();

        // for add new inquiry
        $warranties = Warranty::with(['product'])
            ->whereUserId(Auth::user()->id)
            ->get();

        return Inertia::render('Customer/Inquiries', [
            'inquiries' => $inquiries,
            'select' => InquiryStatusType::options(),
            'filters' => $request->only(['search', 'status']),
            'warranties' => $warranties
        ]);
    }

    public function history(): Response
    {
        $userId = Auth::user()->id;

        // registered warranty history map it with the type, date, title, and description
        $registeredWarranty = Warranty::with('product')
            ->whereUserId($userId)
            ->get()
            ->map(fn($registered) => (object)[
                'type' => 'success',
                'date' => $registered->created_at,
                'title' => "Registered {$registered->product->name}",
                'description' => "Serial: {$registered->serial_number}",
                'url' => route('warranty.show', $registered->id)
            ]);

        $inquiries = WarrantyInquiries::with('warranty.product')
            ->whereUserId($userId)
            ->get()
            ->map(fn($inquiry) => (object)[
                'type' => 'new',
                'date' => $inquiry->created_at,
                'title' => "Opened an inquiry for {$inquiry->warranty->product->name}",
                'description' => Str::limit($inquiry->message, 60),
                'url' => route('inquiry.show', $inquiry->id)
            ]);

        $statusUpdates = WarrantyInquiries::with('warranty.product')
            ->whereUserId($userId)
            ->whereIn('status', ['resolved', 'replaced', 'closed'])
            ->get()
            ->map(function ($update) {

                $type = match ($update->status->value) {
                    'resolved' => 'success',
                    'replaced' => 'success',
                    'closed' => 'default',
                };

                return (object)[
                    'type' => $type,
                    'date' => $update->updated_at,
                    'title' => "Inquiry {$update->status->value}",
                    'description' => "Your inquiry for {$update->warranty->product->name} was {$update->status->value}.",
                    'url' => route('inquiry.show', $update->id)
                ];
            });

        $expiredWarranty = Warranty::with('product')
            ->whereUserId($userId)
            ->whereDate('expiry_date', '<=', now())
            ->get()
            ->map(fn($w) => (object)[
                'type' => 'expire',
                'date' => $w->expiry_date,
                'title' => "{$w->product->name} warranty expired",
                'description' => "Expired on " . $w->expiry_date->format('M d, Y'),
                'url' => route('warranty.show', $w->id)
            ]);

        $productReviews = ProductReview::with('product')
            ->whereUserId($userId)
            ->get()
            ->map(fn($review) => (object)[
                'type' => 'success',
                'date' => $review->created_at,
                'title' => "Reviewed {$review->product->name}",
                'description' => "You gave {$review->rating} stars: " . Str::limit($review->comment, 60),
                'url' => route('product-reviews', $review->product_id)
            ]);

        // refactor with paginator
        // merge the queries to loop it in the blade foreach
        $history = collect()
            ->concat($registeredWarranty)
            ->concat($inquiries)
            ->concat($statusUpdates)
            ->concat($expiredWarranty)
            ->concat($productReviews)
            ->sortByDesc('date')
            ->values();

        return Inertia::render('Customer/History', [
            'histories' => $history
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $userId = Auth::id();
        $tab = $request->query('tab', 'records');

        // get the warranty info for the id
        // need resource
        $warranty = Warranty::with(['product.category', 'inquiries', 'inquiries.user', 'inquiries.responses.user'])
            ->whereUserId($userId)
            ->where('id', $id)
            ->firstOrFail();
        // dd($warranty);

        $history = WarrantyInquiries::with('user')->where('warranty_id', $warranty->id)->get();
        // use the temporary helper for message
        $messages = $this->inquiryMessages($warranty->inquiries);

        // get the latest inquiry of this warranty
        $latestInquiry = WarrantyInquiries::where('user_id', $userId)
            ->where('warranty_id', $warranty->id)
            ->latest()
            ->first();

        // get the inquiry where its not final or closed
        $activeInquiry = WarrantyInquiries::where('user_id', $userId)
            ->where('warranty_id', $warranty->id)
            ->whereNotIn('status', [InquiryStatusType::RESOLVED, InquiryStatusType::CLOSED, InquiryStatusType::REPLACED,])
            ->latest()
            ->first();

        // get the review of the product of the customer
        $review = ProductReview::whereUserId($userId)
            ->where('product_id', $warranty->product_id)
            ->first();

        return Inertia::render('Customer/Show', [
            'warranty' => $warranty,
            'history' => $history,
            'messages' => $messages,
            'latestInquiry' => $latestInquiry,
            'activeInquiry' => $activeInquiry,
            'review' => $review,
            'isExpired' => now()->greaterThan($warranty->expiry_date)
        ]);
    }

    /**
     * Show specific inquiry
     */
    public function showInquiry(Request $request, string $id): Response
    {
        $activeTab = $request->query('tab', 'messages');

        $inquiry = WarrantyInquiries::with([
            'warranty.product.category',
            'user',
            'responses.user'
        ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $messages = $this->inquiryMessages(collect([$inquiry]));

        $status = $inquiry->status instanceof InquiryStatusType
            ? $inquiry->status
            : InquiryStatusType::from($inquiry->status);

        $inquiry->is_done = $status->isFinal();

        return Inertia::render('Customer/Inquiry', [
            'inquiry' => $inquiry,
            'messages' => $messages,
            'activeTab' => $activeTab
        ]);
    }
}
