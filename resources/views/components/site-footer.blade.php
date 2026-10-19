<footer class="site-footer surface-deep">
    <div class="container">
        <div class="site-footer__top">
            <div class="site-footer__brand">
                <x-logo variant="dark" :width="190" />
                <p class="site-footer__tagline">{{ $site['tagline'] }}</p>
            </div>

            <div class="site-footer__cols">
                <div>
                    <h2>Read</h2>
                    <ul>
                        <li><a href="{{ route('home') }}">All posts</a></li>
                        @foreach (\App\Models\Category::ordered()->get() as $item)
                            <li><a href="{{ route('home', ['category' => $item->slug]) }}">{{ $item->name }}</a></li>
                        @endforeach
                        <li><a href="{{ route('feed') }}">RSS feed</a></li>
                    </ul>
                </div>
                <div>
                    <h2>{{ $site['company'] }}</h2>
                    <ul>
                        <li><a href="{{ $site['main_site'] }}" rel="noopener">Main website</a></li>
                        <li><a href="{{ $site['contact_url'] }}" rel="noopener">Talk to us</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="site-footer__bottom">
            <span>{{ $site['name'] }}</span>
            <span>© {{ now()->year }} {{ $site['company'] }}</span>
        </div>
    </div>
</footer>
