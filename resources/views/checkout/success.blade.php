<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
</head>
<body>
    <h1>Payment Successful!</h1>
    <p>Thank you for your purchase. Your order has been successfully processed.</p>
    <h2>Order Details</h2>
    <p><strong>Order ID:</strong> {{ $order->invoice_id }}</p>
    <p><strong>Amount Paid:</strong> ${{ number_format($order->amount, 2) }}</p>
    <p><strong>Transaction ID:</strong> {{ $transaction->transaction_id }}</p>
    <a href="{{ route('home') }}">Return to Home</a>
</body>
</html>
