<x-mail::message>

#  Warranty Expired

Hello **{{ $warranty->user->name }}**,

We are writing to inform you that the warranty coverage for your **{{ $warranty->product->name }}** has officially **expired** as of **{{ $warranty->expiry_date->format('F d, Y') }}**.

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 21px;">
    <tr>
        <td style="padding: 16px; background-color: #f8fafc; border-left: 4px solid #e3342f;">
            <h3 style="margin-top: 0;"> Product Information</h3>
            <ul style="list-style-type: none; padding-left: 0;">
                <li><strong>Product Name:</strong> {{ $warranty->product->name }}</li>
                <li><strong>Serial Number:</strong> {{ $warranty->serial_number }}</li>
                <li><strong>Status:</strong> <span style="color: #e3342f;"><strong>EXPIRED</strong></span></li>
            </ul>
        </td>
    </tr>
</table>

If you'd like to check for renewal eligibility or need to submit a repair inquiry, please visit your dashboard.

<x-mail::button :url="$url" color="error">
Go to Dashboard
</x-mail::button>

Thank you for being a valued customer of **{{ config('app.name') }}**.

Best regards,  
**{{ config('app.name') }} Support Team**

</x-mail::message>