<div id="app">
    <home-page
        :is-auth="@json(auth()->check())"
        user-name="{{ auth()->user()?->name ?? '' }}"
    ></home-page>
</div>
@vite(['resources/css/app.css', 'resources/js/app.js'])
