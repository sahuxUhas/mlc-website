@include('public.listing', [
    'title'    => 'ট্যাগ: '.$tag->name,
    'posts'    => $posts,
    'canonical'=> route('tag.show', $tag->slug),
])
