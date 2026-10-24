<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $user->name }}'s shopping list</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: Arial, Helvetica, sans-serif; color: #111827;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 8px;">
                    <tr>
                        <td style="padding: 32px;">
                            <h1 style="margin: 0 0 24px; font-size: 22px;">{{ $user->name }}'s shopping list</h1>

                            @if ($items->isEmpty())
                                <p style="margin: 0 0 24px; color: #6b7280;">The list is empty.</p>
                            @else
                                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; margin-bottom: 24px; font-size: 14px;">
                                    <tr style="border-bottom: 2px solid #e5e7eb;">
                                        <th align="left">Item</th>
                                        <th align="center">Qty</th>
                                        <th align="right">Price</th>
                                        <th align="center">Purchased</th>
                                    </tr>
                                    @foreach ($items as $item)
                                        <tr style="border-bottom: 1px solid #f3f4f6;{{ $item->purchased ? ' color: #9ca3af; text-decoration: line-through;' : '' }}">
                                            <td align="left">{{ $item->name }}</td>
                                            <td align="center">{{ $item->quantity }}</td>
                                            <td align="right">£{{ number_format($item->price * $item->quantity, 2) }}</td>
                                            <td align="center" style="text-decoration: none;">{{ $item->purchased ? '✓' : '' }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            <p style="margin: 0 0 8px; font-size: 18px; font-weight: bold;">Total: £{{ number_format($total, 2) }}</p>

                            @if (!empty($user->spending_limit))
                                <p style="margin: 0 0 8px;">Spending limit: £{{ number_format($user->spending_limit, 2) }}</p>

                                @if ($total > $user->spending_limit)
                                    <p style="margin: 0; padding: 12px; background-color: #fee2e2; color: #991b1b; border-radius: 6px; font-weight: bold;">
                                        Over budget by £{{ number_format($total - $user->spending_limit, 2) }}
                                    </p>
                                @endif
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
