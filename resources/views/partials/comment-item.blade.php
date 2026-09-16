{{--
    একটি মন্তব্য (compact) — avatar/initial + নাম + সময় + মন্তব্য।
    পাবলিক পেজে শুধুমাত্র approved মন্তব্য পৌঁছায় (কন্ট্রোলারে ফিল্টার করা)।
    $isReply = true হলে ইনডেন্টেড ছোট রূপে রেন্ডার হয়।
--}}
@php
    $isReply = $isReply ?? false;
    $name    = trim((string) $comment->author_name);
    $initial = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : '#';
@endphp

<div class="mc-comment {{ $isReply ? 'is-reply' : '' }}">
    @unless($isReply)
        <span class="mc-comment-avatar" aria-hidden="true">{{ $initial }}</span>
    @endunless

    <div class="mc-comment-main">
        <div class="mc-comment-meta">
            <span class="mc-comment-name">{{ $name }}</span>
            <span class="mc-comment-time"><i class="ph ph-clock" aria-hidden="true"></i> {{ bn_ago($comment->created_at) }}</span>
        </div>

        {{-- {{ }} এস্কেপ করা — HTML/script injection অসম্ভব --}}
        <p class="mc-comment-body">{{ $comment->body }}</p>

        <form method="POST" action="{{ route('news.report', [$post->slug, $comment->id]) }}" class="mc-comment-report">
            @csrf
            <button type="submit" aria-label="এই মন্তব্যটি রিপোর্ট করুন"><i class="ph ph-flag" aria-hidden="true"></i> রিপোর্ট</button>
        </form>

        @if(! $isReply && $comment->replies->isNotEmpty())
            <div class="mc-comment-replies">
                @foreach($comment->replies as $reply)
                    @include('partials.comment-item', ['comment' => $reply, 'post' => $post, 'isReply' => true])
                @endforeach
            </div>
        @endif
    </div>
</div>
