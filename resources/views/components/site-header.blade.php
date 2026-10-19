<header class="site-header surface-deep" data-header>
    <div class="container site-header__inner">
        <x-logo :href="route('home')" variant="dark" :width="210" />

        <nav class="site-nav" aria-label="Main">
            <ul class="site-nav__list">
                <li><a class="site-nav__link" href="{{ route('home') }}" @if (request()->routeIs('home', 'posts.show')) aria-current="page" @endif>Blog</a></li>
                <li><a class="site-nav__link" href="{{ route('feed') }}">RSS</a></li>
                <li><a class="site-nav__link" href="{{ $site['main_site'] }}" rel="noopener">{{ $site['company'] }}</a></li>
            </ul>
            <x-button :href="$site['contact_url']" variant="primary" size="sm" :arrow="false">Talk to us</x-button>
        </nav>

        <button class="menu-toggle js-only" type="button" aria-expanded="false" aria-controls="mobile-nav" data-menu-open>
            <span>Menu</span>
            <span class="menu-toggle__bars" aria-hidden="true"><span></span><span></span></span>
        </button>
    </div>
    <span class="site-header__progress js-only" aria-hidden="true" data-scroll-progress></span>
</header>

<div class="mobile-nav surface-deep bg-grid" id="mobile-nav" role="dialog" aria-modal="true" aria-label="Site menu" hidden data-menu>
    <div class="mobile-nav__top">
        <x-logo :href="route('home')" variant="dark" :width="170" />
        <button class="menu-toggle" type="button" style="display:inline-flex" data-menu-close>
            <span>Close</span>
            <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true"><path d="M2 2l14 14M16 2L2 16" stroke="currentColor" stroke-width="1.5"/></svg>
        </button>
    </div>

    <nav aria-label="Main (mobile)">
        <ul class="mobile-nav__list">
            <li><a class="mobile-nav__link" href="{{ route('home') }}"><span>01</span>Blog</a></li>
            <li><a class="mobile-nav__link" href="{{ route('feed') }}"><span>02</span>RSS</a></li>
            <li><a class="mobile-nav__link" href="{{ $site['main_site'] }}" rel="noopener"><span>03</span>{{ $site['company'] }}</a></li>
        </ul>
    </nav>

    <div class="mobile-nav__footer">
        <x-button :href="$site['contact_url']">Talk to us</x-button>
    </div>
</div>
