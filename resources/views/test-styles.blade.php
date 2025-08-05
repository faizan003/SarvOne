<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Style Test - SarvOne</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Style Test</h1>
        
        <div class="space-y-4">
            <div class="bg-blue-500 text-white p-4 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>
                If you can see this blue box with proper styling, Tailwind CSS is working!
            </div>
            
            <div class="bg-green-500 text-white p-4 rounded-lg">
                <i class="fas fa-thumbs-up mr-2"></i>
                Font Awesome icons are also working!
            </div>
            
            <div class="bg-yellow-500 text-white p-4 rounded-lg">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                If styles are not working, check browser console for errors
            </div>
            
            <a href="{{ route('organization.login') }}" class="inline-block bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Go to Organization Login
            </a>
        </div>
    </div>
    
    <script>
        console.log('Test page loaded successfully');
        console.log('Tailwind classes should be applied');
    </script>
</body>
</html> 