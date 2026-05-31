<?php

namespace App\Http\Controllers\Layouts;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class UserwebController extends Controller
{
    public function pages()
    {
        return view('userweb', [
            'siteName'       => Setting::get('restaurant_name', 'FastBite'),
            'logoText'       => Setting::get('logo_text', 'FastBite'),
            'tagline'        => Setting::get('tagline', 'Flavor Unleashed.'),
            'description'    => Setting::get('description', 'The best chefs in the city, delivered to your door in under 30 minutes. Fresh, fast, and unforgettable.'),
            'phone'          => Setting::get('phone'),
            'email'          => Setting::get('email'),
            'address'        => Setting::get('address'),
            'heroImage'      => Setting::get('hero_image'),
            'deliveryTime'   => Setting::get('delivery_time', '30'),
            'totalDishes'    => Setting::get('total_dishes', '200+'),
            'happyCustomers' => Setting::get('happy_customers', '10K+'),
            'rating'         => Setting::get('rating', '4.9'),
        ]);
    }
}