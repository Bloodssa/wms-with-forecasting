<x-mail::message>

# Warranty Registration Successful

Hello **{{ $email }}**,

Thank you for registering your product with **{{ config('app.name') }}**.
Your warranty has been **successfully recorded in our system**, and your product is now covered.

<x-mail::panel>
###  Product Information

**Product Name:** {{ $warranty->product->name }} <br>
**Serial Number:** `{{ $warranty->serial_number }}` <br>
**Warranty Coverage Until:** {{ $warranty->expiry_date->format('F d, Y') }}
</x-mail::panel>

To manage your warranty, track its status, and receive important notifications, please **claim your account** by clicking the button below.

<x-mail::button :url="$registrationUrl" color="success">
Claim Your Account
</x-mail::button>

Once your account is activated, you will be able to:

* View your registered products
* Monitor your warranty status
* Receive reminders before your warranty expires
* Submit service or repair inquiries

Thank you for choosing **{{ config('app.name') }}**.

Best regards,

**{{ config('app.name') }} Support Team**

</x-mail::message>