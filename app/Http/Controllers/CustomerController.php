<?php

namespace App\Http\Controllers;

use App\Enum\InquiryStatusType;
use App\Enum\WarrantyStatusType;
use App\Models\Product;
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
        ]);
    }

    public function warrantyList(Request $request): Response
    {
        $warranties = Warranty::query()
            ->select(['id', 'serial_number', 'status', 'expiry_date', 'purchase_date', 'product_id'])
            ->with(['product' => function($query) {
                $query->select([
                    'id',
                    'name',
                    'product_image_url',
                    'category_id',
                    'brand'
                ]);
            }, 'product.category:id,name'])
            ->whereUserId(Auth::user()->id)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('serial_number', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });
                });
            })->when($request->status, function ($query, $status) {
                if ($status === 'active') $query->whereDate('expiry_date', '>', now()->addDays(30));
                if ($status === 'near-expiry') $query->whereBetween('expiry_date', [now(), now()->addDays(30)]);
                if ($status === 'expired') $query->whereDate('expiry_date', '<', now());
            })
            ->paginate(10);

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
                'warranty' => function($query) {
                    $query->select(['id', 'product_id', 'status', 'serial_number']);
                },
                'warranty.product' => function($query) {
                    $query->select(['id', 'category_id', 'brand', 'name', 'product_image_url']);
                },
                'warranty.product.category:id,name'
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
            ->latest('updated_at')
            ->paginate(10);

        return Inertia::render('Customer/Inquiries', [
            'inquiries' => $inquiries,
            'select' => InquiryStatusType::options(),
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    public function products(Request $request): Response
    {
        $products = Product::query()
            ->select(['id', 'name', 'category_id', 'product_image_url', 'warranty_duration', 'brand'])
            ->with('category:id,name')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);
            
        return Inertia::render('Customer/Products', [
            'products' => $products
        ]);
    }

    public function productsReview(string $id): Response
    {
        $product = Product::with('category:name')
            ->findOrFail($id);

        return Inertia::render('Customer/Review', [
            'product' => $product
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

        // refactor with paginator
        // merge the queries to loop it in the blade foreach
        $history = collect()
            ->concat($registeredWarranty)
            ->concat($inquiries)
            ->concat($statusUpdates)
            ->concat($expiredWarranty)
            ->sortByDesc('date')
            ->values();

        return Inertia::render('Customer/History', [
            'histories' => $history
        ]);
    }

    public function show(string $id): Response
    {
        $userId = Auth::id();

        // get the warranty info for the id
        // need resource
        $warranty = Warranty::with(['product.category', 'inquiries', 'inquiries.user', 'inquiries.responses.user'])
            ->whereUserId($userId)
            ->where('id', $id)
            ->firstOrFail();

        $latestInquiry = $warranty->inquiries->last();
        // dd($warranty);

        // check if this warranty does not have a inquiries for the ux
        $containsInquiries = $warranty->inquiries->isNotEmpty();

        $history = WarrantyInquiries::with('user')->where('warranty_id', $warranty->id)->get();
        // use the temporary helper for message
        $messages = $this->inquiryMessages($warranty->inquiries);

        return Inertia::render('Customer/Show', [
            'warranty' => $warranty,
            'history' => $history,
            'id' => $latestInquiry?->id,
            'messages' => $messages,
            'containsInquiries' => $containsInquiries
        ]);
    }

    /**
     * Show specific inquiry
     */
    public function showInquiry(string $id): Response
    {
        $inquiry = WarrantyInquiries::with(['warranty.product.category', 'warranty.user'])
            ->whereUserId(Auth::user()->id)
            ->where('id', $id)
            ->firstOrFail();

        // dd($inquiry);

        return Inertia::render('Customer/Inquiry', [
            'inquiry' => $inquiry
        ]);
    }
}
