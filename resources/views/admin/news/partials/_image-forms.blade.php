{{--
    ছবির সহায়ক ফর্মগুলো (ক্যাপশন আপডেট / ফিচার্ড সেট / ছবি মুছে ফেলা / সংবাদ ট্র্যাশে পাঠানো)।
    ---------------------------------------------------------------------------
    এগুলো ইচ্ছাকৃতভাবে মূল <form id="newsForm"> এর **বাইরে** রাখা হয়েছে, কারণ HTML-এ
    ফর্মের ভিতরে আরেকটি ফর্ম (nested form) বৈধ নয়। গ্যালারির ইনপুট/বাটনগুলো `form="…"`
    অ্যাট্রিবিউট দিয়ে এই ফর্মগুলোর সাথে যুক্ত — তাই JavaScript বন্ধ থাকলেও সব কাজ করে,
    আর JS চালু থাকলে AJAX-এ সম্পন্ন হয় (পেজ রিলোড ছাড়াই)।
--}}
<div id="newsAuxForms" class="hidden" aria-hidden="true">
    @if($isEdit)
        @foreach($post->images as $image)
            <form id="cap-{{ $image->id }}" action="{{ route('admin.news.images.update', [$post, $image]) }}" method="POST" data-caption-ajax>
                @csrf @method('PUT')
            </form>
            <form id="feat-{{ $image->id }}" action="{{ route('admin.news.featured', $post) }}" method="POST" data-featured-ajax>
                @csrf<input type="hidden" name="image_id" value="{{ $image->id }}">
            </form>
            <form id="del-{{ $image->id }}" action="{{ route('admin.news.images.destroy', [$post, $image]) }}" method="POST" data-delete-ajax
                  data-confirm="ছবিটি গ্যালারি থেকে মুছে ফেলবেন?">
                @csrf @method('DELETE')
            </form>
        @endforeach

        <form id="del-post-{{ $post->id }}" action="{{ route('admin.news.destroy', $post) }}" method="POST"
              data-confirm="সংবাদটি রিসাইকেল বিনে (ট্র্যাশে) পাঠানো হবে — নিশ্চিত?">
            @csrf @method('DELETE')
        </form>
    @endif
</div>
