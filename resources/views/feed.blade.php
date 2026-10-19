{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $site['name'] }}</title>
        <link>{{ route('home') }}</link>
        <description>{{ $site['tagline'] }}</description>
        <language>en-gb</language>
        <lastBuildDate>{{ $updated->toRssString() }}</lastBuildDate>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
@foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ route('posts.show', $post->slug) }}</link>
            <guid isPermaLink="true">{{ route('posts.show', $post->slug) }}</guid>
            <pubDate>{{ $post->published_at->toRssString() }}</pubDate>
            <category>{{ $post->typeLabel() }}</category>
            <description>{{ $post->summary(300) }}</description>
        </item>
@endforeach
    </channel>
</rss>
