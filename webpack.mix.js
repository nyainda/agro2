const mix = require('laravel-mix');

// Set the public path to 'resources/assets' directory
mix.setPublicPath('resources/assets');

// Compile CSS
mix.postCss('resources/css/app.css', 'css');

// Compile JavaScript
mix.js('resources/js/app.js', 'js');
