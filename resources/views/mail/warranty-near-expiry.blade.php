<x-mail::message>

#  Warranty Expiring Soon

Hello **{{ $warranty->user->name }}**,

This is a friendly reminder that your product warranty for **{{ $warranty->product->name }}** is set to expire in **30 days**. 

To ensure you continue to receive support and coverage, we recommend reviewing your product status or checking for any available extension plans.

<x-mail::panel>
###  Product Information

* **Product Name:** {{ $warranty->product->name }}
* **Serial Number:** {{ $warranty->serial_number }}
* **Warranty Coverage Until:** **{{ $warranty->expiry_date->format('F d, Y') }}**
</x-mail::panel>

You can view your full warranty history and manage your registered products by clicking the button below.

<x-mail::button :url="$url" color="primary">
View Warranty Details
</x-mail::button>

If you have any questions regarding your coverage, please don't hesitate to reach out to our support team.

Best regards,  
**{{ config('app.name') }} Support Team**

</x-mail::message>