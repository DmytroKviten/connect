{{-- resources/views/home/product.blade.php --}}
@extends('layouts.base')

@section('content')
<section class="pt-24 pb-16 bg-gray-100 min-h-screen">
  <div class="container mx-auto px-4">

    <h1 class="text-4xl font-extrabold mb-10 text-center">
      Our Products
    </h1>

    {{-- 👉 Якщо ви передаєте $products з контролера --}}
    @isset($products)
      @if ($products->isEmpty())
        <p class="text-center text-gray-600">No products yet — come back later!</p>
      @else
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          @foreach ($products as $product)
            <div class="bg-white rounded-lg overflow-hidden shadow hover:shadow-lg transition">
              <img src="{{ $product->image_url ?? '/placeholder.png' }}"
                   alt="{{ $product->name }}"
                   class="w-full h-48 object-cover">
              <div class="p-5 flex flex-col h-full">
                <h2 class="text-lg font-semibold mb-2">{{ $product->name }}</h2>
                <p class="text-sm text-gray-600 mb-4 flex-grow">
                  {{ Str::limit($product->short_desc, 90) }}
                </p>
                <div class="mt-auto flex items-center justify-between">
                  <span class="text-xl font-bold text-blue-600">
                    ${{ number_format($product->price, 2) }}
                  </span>
                  <a href="#"
                     class="px-4 py-2 rounded bg-blue-600 text-white
                            hover:bg-blue-700 transition text-sm font-medium">
                    View
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="mt-12">
          {{ $products->links() }}
        </div>
      @endif
    @else
      {{-- 👉 тимчасовий стуб із прикладами, якщо $products не передано --}}
      <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @for ($i = 1; $i <= 8; $i++)
          <div class="bg-white rounded-lg overflow-hidden shadow hover:shadow-lg transition">
            <img src="/placeholder.png" alt="Product {{ $i }}"
                 class="w-full h-48 object-cover">
            <div class="p-5 flex flex-col h-full">
              <h2 class="text-lg font-semibold mb-2">Product {{ $i }}</h2>
              <p class="text-sm text-gray-600 mb-4 flex-grow">
                Short description of product {{ $i }} goes here.
              </p>
              <div class="mt-auto flex items-center justify-between">
                <span class="text-xl font-bold text-blue-600">$99.00</span>
                <a href="#"
                   class="px-4 py-2 rounded bg-blue-600 text-white
                          hover:bg-blue-700 transition text-sm font-medium">
                  View
                </a>
              </div>
            </div>
          </div>
        @endfor
      </div>
    @endisset

  </div>
</section>
@endsection
