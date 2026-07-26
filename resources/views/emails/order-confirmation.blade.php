<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #F1E9D8; color: #211A14; margin: 0; padding: 0; }
        .wrap { max-width: 560px; margin: 0 auto; padding: 32px 24px; }
        .header { text-align: center; margin-bottom: 32px; }
        .header h1 { font-size: 20px; letter-spacing: 0.05em; margin: 0; }
        .brand-accent { color: #B08D3F; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 24px 0; }
        td { padding: 10px 0; border-bottom: 1px solid rgba(33,26,20,0.1); font-size: 14px; }
        .total-row td { border-bottom: none; font-weight: 600; padding-top: 16px; }
        .footer { text-align: center; font-size: 12px; color: rgba(33,26,20,0.5); margin-top: 32px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <h1>THE PALM CRAFTS</h1>
            <p class="brand-accent">by selvamani</p>
        </div>

        <p>Thank you for your order — your palm-leaf pieces are now being prepared.</p>
        <p style="font-size: 13px; color: rgba(33,26,20,0.6);">Order {{ $order->order_number }}</p>

        <table>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}{{ $item->variant_name ? ' — '.$item->variant_name : '' }} &times; {{ $item->quantity }}</td>
                    <td style="text-align: right;">₹{{ number_format($item->line_total_in_paise / 100, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td>Total Paid</td>
                <td style="text-align: right;">₹{{ number_format($order->total_in_paise / 100, 2) }}</td>
            </tr>
        </table>

        <p style="font-size: 13px;">We'll send a follow-up once your order ships. If anything looks off, just reply to this email.</p>

        <div class="footer">
            &copy; {{ date('Y') }} THE PALM CRAFTS by selvamani. Handcrafted in Tamil Nadu, India.
        </div>
    </div>
</body>
</html>
