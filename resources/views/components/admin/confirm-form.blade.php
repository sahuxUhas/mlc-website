{{-- কনফার্মেশনসহ POST/DELETE ফর্ম (ইনলাইন অ্যাকশন) --}}
@props(['action','method'=>'POST','confirm'=>null,'title'=>'','class'=>'inline'])
<form action="{{ $action }}" method="{{ $method === 'GET' ? 'GET' : 'POST' }}" class="{{ $class }}"
      @if($confirm) data-confirm="{{ $confirm }}" @endif>
    @csrf
    @if($method !== 'POST' && $method !== 'GET') @method($method) @endif
    {{ $slot }}
</form>
