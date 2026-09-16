@include('public.listing', [
    'title'    => $category->name,
    'subtitle' => $category->description ?: ($category->union_name ? 'এলাকা: '.$category->union_name : ''),
    'posts'    => $posts,
    'canonical'=> route('category.show', $category->slug),
    'description' => $category->meta_description ?: $category->description,
    'extra'    => $subcategories->isNotEmpty()
        ? '<div class="mb-6 flex flex-wrap gap-2">'.implode('', $subcategories->map(fn($s) => '<a href="'.route('category.show', $s->slug).'" class="mc-chip">'.$s->name.'</a>')->all()).'</div>'
        : '',
])
