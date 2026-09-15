<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>{{ site_setting('site_name', config('app.name')) }}</title>
    <link>{{ route('home') }}</link>
    <description>{{ site_setting('site_tagline','পাহাড়ের কথা বলে') }}</description>
    <language>bn</language>
    <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml"/>
    <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
@foreach($posts as $post)
    <item>
        <title><![CDATA[{{ $post->title }}]]></title>
        <link>{{ route('news.show', $post->slug) }}</link>
        <guid isPermaLink="true">{{ route('news.show', $post->slug) }}</guid>
        <pubDate>{{ optional($post->published_at)->toRssString() }}</pubDate>
        <category><![CDATA[{{ $post->category?->name }}]]></category>
        <description><![CDATA[{!! mc_excerpt($post->excerpt ?: $post->content, 400) !!}]]></description>
    </item>
@endforeach
</channel>
</rss>
