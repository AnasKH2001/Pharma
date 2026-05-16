<!DOCTYPE html>
<html>
<head>
    <title>Password Reset OTP</title>
</head>
<body>
    <h1>Hello, {{ $name }}!</h1>
    <p>You requested to reset your password.</p>
    <p>Your OTP for password reset is:</p>
    <h2 style="color: #4CAF50; font-size: 32px;">{{ $otp }}</h2>
    <p>This OTP is valid for 15 minutes.</p>
    <p>If you didn't request this, please ignore this email.</p>
    <br>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>