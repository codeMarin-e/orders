<?php

    namespace App\Http\Middleware;

    use Closure;

    class SetCurrentCart
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Closure  $next
         * @return mixed
         */
        public function handle($request, Closure $next)
        {
            app()->make("Cart");
            return $next($request);
        }
    }
