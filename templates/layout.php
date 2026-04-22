<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Sentient AI Coach') ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'Transform stress into serenity with a personalized meditation plan designed specifically for high-performers like you.') ?>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../public/assets/css/app.css') ?>">
    
    <!-- Meta tags -->
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#0d9488">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🧘‍♀️</text></svg>">
</head>
<body class="h-full bg-slate-50 transition-colors duration-300">

    <!-- Main content -->
    <main class="min-h-full">
        <?php
        // Include the specific template
        $templateFile = __DIR__ . '/' . $template . '.php';
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            echo '<div class="flex items-center justify-center min-h-screen">';
            echo '<div class="text-center">';
            echo '<h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">Page Not Found</h1>';
            echo '<p class="text-slate-600 dark:text-slate-400 mb-4">The requested page could not be found.</p>';
            echo '<a href="/" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">Return Home</a>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </main>

    <!-- JavaScript -->
    <script src="/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../public/assets/js/app.js') ?>"></script>
</body>
</html> 