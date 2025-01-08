<?php

namespace Stianscholtz\LaravelMenu\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stianscholtz\LaravelMenu\Menu;

abstract class MenuMiddleware
{
    abstract protected function build(Menu $menu): void;

    public function handle(Request $request, Closure $next): mixed
    {
        $this->build($menu = new Menu);

        session()->put('menu', $menu);

        return $next($request);
    }
}