<?php

namespace App\Providers;

use App\Repositories\Category\CategoryRepository;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductReview\ProductReviewRepository;
use App\Repositories\ProductReview\ProductReviewRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Warranty\WarrantyRepository;
use App\Repositories\Warranty\WarrantyRepositoryInterface;
use App\Repositories\Inquiry\InquiryRepository;
use App\Repositories\Inquiry\InquiryRepositoryInterface;
use App\Repositories\InquiryResponse\InquiryResponseRepository;
use App\Repositories\InquiryResponse\InquiryResponseRepositoryInterface;
use App\Repositories\WarrantyForecast\WarrantyForecastRepository;
use App\Repositories\WarrantyForecast\WarrantyForecastRepositoryInterface;
use App\Repositories\WarrantyServiceRecord\WarrantyServiceRecordRepository;
use App\Repositories\WarrantyServiceRecord\WarrantyServiceRecordRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WarrantyRepositoryInterface::class, WarrantyRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductReviewRepositoryInterface::class, ProductReviewRepository::class);
        $this->app->bind(InquiryRepositoryInterface::class, InquiryRepository::class);
        $this->app->bind(InquiryResponseRepositoryInterface::class, InquiryResponseRepository::class);
        $this->app->bind(WarrantyForecastRepositoryInterface::class, WarrantyForecastRepository::class);
        $this->app->bind(WarrantyServiceRecordRepositoryInterface::class, WarrantyServiceRecordRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
