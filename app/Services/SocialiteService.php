<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Laravel\Socialite\Socialite;

class SocialiteService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly WarrantyService $warrantyService
    ) {}

    public function authenticate(): User
    {
        // get the logged in user gmail credentials
        $googleUser = Socialite::driver('google')->user();

        // validate if user already login with gmail or email already exist
        $user = $this->userRepository->findByGoogleIdOrEmail(
            $googleUser->id,
            $googleUser->email
        );

        if (! $user) {
            $user = $this->userRepository->createFromGoogle(
                $googleUser->name,
                $googleUser->email,
                $googleUser->id
            );
        } elseif (! $user->google_id) {
            // if user email already but no google id
            // attach google id so user can directly login with email
            $this->userRepository->linkGoogleAccount($user, $googleUser->id);
        }

        // if there is unclaimed warranty in the email attch it
        $this->warrantyService->claimWarranty($user); 

        return $user;
    }
}
