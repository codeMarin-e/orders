<?php
    static::$cleaning['session_attachments'] = function($command, \Closure $next) {
        $command->components->task("Cleaning carts", function() use ($command) {
            $files = \Symfony\Component\Finder\Finder::create()
                ->in(config('session.files'))
                ->files()
                ->ignoreDotFiles(true);
            $sessionIds = [];
            foreach ($files as $file) {
                $sessionIds[] = basename($file->getRealPath());
            }
            $expiredCarts = \App\Models\Cart::whereNotIn('session_id', $sessionIds)
                ->where('confirmed_at', null)
                ->where('status', null)->get();
            foreach ($expiredCarts as $cart) {
                $cart->delete();
            }
            $processingCarts = \App\Models\Cart::where('processing_from', '<', now()->sub(config('marinar_orders.clean_older_than_processing_from')))
                ->where('status', 'processing')->get();
            foreach ($processingCarts as $cart) {
                $cart->delete();
            }
            return true;
        });
        return $next($command);
    };
