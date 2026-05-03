<x-mail::message>

# Warranty Registration Successful

Hello **{{ $email }}**,

Thank you for registering your product with **{{ config('app.name') }}**.
Your warranty has been **successfully recorded in our system**, and your product is now covered.

<x-mail::panel>
###  Product Information

@foreach ($warranties as $warranty)
    Product Name: {{ $warranty->product->name }}
        Serial Number: {{ $warranty->serial_number }}
        Warranty Coverage Until: {{ $warranty->expiry_date->format('F d, Y') }}
@endforeach
</x-mail::panel>

To manage your warranty, track its status, report product issue's and receive important notifications, please **claim your account** by clicking the button below.

<x-mail::button :url="$registrationUrl" color="success">
Claim Your Account
</x-mail::button>

Thank you for choosing **{{ config('app.name') }}**.

Best regards,

**{{ config('app.name') }} Support Team**

</x-mail::message>