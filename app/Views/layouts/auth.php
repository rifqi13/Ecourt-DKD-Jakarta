<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Login') ?> — E-Court</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .auth-card {
            background: #fff; padding: 40px; border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
            width: 100%; max-width: 420px;
        }
    </style>
</head>
<body>
    <?= $this->renderSection('content') ?>
</body>
</html>