<?php

namespace App\Http\Controllers;

use App\Models\Product;   // Eloquent-модель (id, name, image, short_desc, price …)
use PHPUnit\TextUI\Configuration\Php;

class ProductController extends Controller
{
    /** Сторінка каталогу */
    public function index()
    {
        // витягаємо активні продукти, нові — першими
        $products = Product::where('is_active', true)
                           ->latest()
                           ->paginate(12);      // по 12 карток на сторінку

        return view('products.index', compact('products'));
    }
}

