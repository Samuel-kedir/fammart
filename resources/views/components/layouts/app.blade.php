<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Application</title>
    @livewireStyles
</head>
<body>
    <div>
        {{ $slot }}  <!-- This will render the content of your Livewire component -->
    </div>
    @livewireScripts
</body>
</html>
