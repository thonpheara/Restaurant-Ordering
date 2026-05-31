<?php

namespace App\Providers;

use App\Models\Order;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Throwable;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        try {
            $timezone = Setting::get('timezone', config('app.timezone', 'Asia/Phnom_Penh'));
        } catch (Throwable) {
            $timezone = config('app.timezone', 'Asia/Phnom_Penh');
        }

        Config::set('app.timezone', $timezone);
        date_default_timezone_set($timezone);

        // Share settings with all admin views
        View::composer('Admin.*', function ($view) {
            $settings = [
                'restaurant_name' => Setting::get('restaurant_name', 'FastBite'),
                'tagline'         => Setting::get('tagline', 'Flavor Unleashed.'),
                'description'     => Setting::get('description', 'The best chefs in the city, delivered to your door in under 30 minutes.'),
                'phone'           => Setting::get('phone'),
                'email'           => Setting::get('email'),
                'address'         => Setting::get('address'),
                'currency'        => Setting::get('currency', 'USD'),
                'timezone'        => Setting::get('timezone', config('app.timezone', 'Asia/Phnom_Penh')),
                'hero_image'      => Setting::get('hero_image'),
                'logo_text'       => Setting::get('logo_text', 'FastBite'),
                'delivery_time'   => Setting::get('delivery_time', '30'),
                'total_dishes'    => Setting::get('total_dishes', '200+'),
                'happy_customers' => Setting::get('happy_customers', '10K+'),
                'rating'          => Setting::get('rating', '4.9'),
            ];
            $view->with('settings', $settings);
        });

        View::composer('components.asidebar', function ($view) {
            $view->with('orderNotifications', Order::notificationsFor(Auth::user(), 8));
        });
    }
}
