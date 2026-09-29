{{-- সরাসরি প্লেয়ার। Raw URL, এরর বক্স, ফেসবুক বাটন — কিছুই দেখায় না। autoplay নেই। --}}
@php
    $player = $video->publicPlayer();
    $poster = $video->thumbnail ? mc_image($video->thumbnail) : '';
    $reel = (bool) $video->is_reel && ($player['kind'] ?? '') !== 'file';
    $viewUrl = route('videos.view', ['slug' => $video->slug], false);
@endphp
@if(($player['kind'] ?? 'none') !== 'none')
<div class="mc-video-stage {{ $reel ? 'is-reel' : 'is-wide' }}">
    @if(($player['kind'] ?? '') === 'file')
        <video
            class="mc-video-el"
            data-mc-player
            data-view-url="{{ $viewUrl }}"
            controls
            playsinline
            preload="metadata"
            controlslist="nodownload"
            referrerpolicy="no-referrer"
            @if($poster) poster="{{ $poster }}" @endif
            title="{{ $video->title }}"
        >
            <source src="{{ $player['src'] }}" type="{{ $player['mime'] ?? 'video/mp4' }}">
        </video>
    @elseif(($player['kind'] ?? '') === 'embed')
        <iframe
            class="mc-video-el"
            data-mc-embed
            data-view-url="{{ $viewUrl }}"
            src="{{ $player['src'] }}"
            title="{{ $video->title }}"
            loading="lazy"
            allow="encrypted-media; picture-in-picture; fullscreen"
            allowfullscreen
            referrerpolicy="strict-origin-when-cross-origin"
        ></iframe>
    @endif
</div>
@endif
