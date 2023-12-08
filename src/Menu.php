<?php

namespace Stianscholtz\LaravelMenu;

use Closure;
use Gate;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JsonSerializable;
use URL;

class Menu implements JsonSerializable, Arrayable
{
    protected Collection $items;

    public function __construct()
    {
        $this->items = collect();
    }

    /**
     * @param string $label
     * @param string $routeName
     * @param array $routeParams
     * @param string|null $icon
     * @param callable|array|string|null  $accessCallback
     * @return $this
     */
    public function route(string $label, string $routeName, array $routeParams = [], string $icon = null, callable|array|string $accessCallback = null): static
    {
        if ($this->callAccessCallback($accessCallback)) {
            $url = URL::route($routeName, $routeParams);
            $active = request()->getPathInfo() === parse_url($url)['path'];
            $this->addItem(compact('label', 'routeName', 'routeParams', 'icon', 'active'));
        }

        return $this;
    }

    /**
     * @param  string  $label
     * @param  string|null  $icon
     * @param  Closure|null  $closure
     * @param $accessCallback
     * @return $this
     */
    public function group(string $label, string $icon = null, Closure $closure = null, $accessCallback = null): static
    {
        if ($closure && $this->callAccessCallback($accessCallback)) {
            $subMenu = new Menu();
            call_user_func($closure, $subMenu);
            $active = $subMenu->items->contains('current', 1);
            $items = $subMenu->toArray()['items'];
            $this->addItem(compact('label', 'icon', 'active', 'items'));
        }

        return $this;
    }

    /**
     * @param  callable|array|string|null  $accessCallback
     * @return bool
     */
    protected function callAccessCallback(callable|array|string $accessCallback = null): bool
    {
        if (is_null($accessCallback)) {
            return true;
        }

        //[Policy::class, method]
        if (is_array($accessCallback)) {
            if (count($accessCallback) !== 2) {
                throw new InvalidArgumentException('Parameter accessCallback must have a length of 2 when of type array.');
            }

            return auth()->user()->can($accessCallback[0], $accessCallback[1]);
        }

        //Gate
        if (is_string($accessCallback)) {
            return Gate::allows($accessCallback);
        }

        //User provided callback used for custom logic
        if (is_callable($accessCallback)) {
            return call_user_func($accessCallback);
        }

        return false;
    }

    private function addItem(array $item): void
    {
        $this->items->push($item);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        $data['items'] = $this->items->toArray();
        return $data;
    }
}
