<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiExplorerController extends Controller
{
    /**
     * @param  Router  $router
     * @return JsonResponse
     */
    public function getRoutes(Router $router): JsonResponse
    {
        $routes = Cache::remember(
            'messenger.routes',
            now()->addMinutes(30),
            fn () => collect($router->getRoutes())
                ->map(fn ($route) => $this->getRouteInformation($route))
                ->filter(fn ($route) => $this->routeIsMessenger($route))
                ->sortBy('name')
                ->values()
                ->toArray()
        );

        return new JsonResponse([
            'html' => view('explorer.partials.routes')->with('routes', $routes)->render(),
        ]);
    }

    /**
     * @param  Request  $request
     * @param  string  $route
     * @return JsonResponse|View
     */
    public function getRouteResponses(Request $request, string $route)
    {
        $responses = $this->getResponsesArray();

        if (! array_key_exists($route, $responses)) {
            throw new NotFoundHttpException("The route name { $route } was not found.");
        }

        $verb = explode('|', $responses[$route]['methods'])[0];

        $with = [
            'uri' => $responses[$route]['uri'],
            'methods' => $responses[$route]['methods'],
            'query' => $responses[$route]['query'],
            'name' => $route,
            'verb' => $verb,
            'responses' => $responses[$route][$verb],
            'standalone' => false,
        ];

        if ($request->expectsJson()) {
            return new JsonResponse([
                'html' => view('explorer.partials.responses')->with($with)->render(),
            ]);
        }

        $with['standalone'] = true;

        return view('explorer.responses')->with($with);
    }

    /**
     * @return JsonResponse
     */
    public function getBroadcast(): JsonResponse
    {
        $broadcasts = Cache::remember(
            'messenger.broadcast',
            now()->addMinutes(30),
            fn () => collect($this->getBroadcastArray())
                ->map(fn ($contents, $broadcast) => $this->getBroadcastInformation($broadcast, $contents))
                ->sortBy('class')
                ->values()
                ->toArray()
        );

        return new JsonResponse([
            'html' => view('explorer.partials.broadcasts')->with('broadcasts', $broadcasts)->render(),
        ]);
    }

    /**
     * @param  Request  $request
     * @param  string  $broadcast
     * @return JsonResponse|View
     */
    public function getBroadcastData(Request $request, string $broadcast)
    {
        $broadcasts = $this->getBroadcastArray();

        if (! array_key_exists($broadcast, $broadcasts)) {
            throw new NotFoundHttpException("The broadcast class { $broadcast } was not found.");
        }

        $with = [
            'class' => $broadcast,
            'name' => $broadcasts[$broadcast]['name'],
            'broadcast' => $broadcasts[$broadcast]['broadcast'],
            'standalone' => false,
        ];

        if ($request->expectsJson()) {
            return new JsonResponse([
                'html' => view('explorer.partials.broadcast')->with($with)->render(),
            ]);
        }

        $with['standalone'] = true;

        return view('explorer.broadcast')->with($with);
    }

    /**
     * @param  Route  $route
     * @return array
     */
    private function getRouteInformation(Route $route): array
    {
        return [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
        ];
    }

    /**
     * @param  string  $broadcast
     * @param  array  $contents
     * @return array
     */
    private function getBroadcastInformation(string $broadcast, array $contents): array
    {
        return [
            'class' => $broadcast,
            'name' => $contents['name'],
        ];
    }

    /**
     * @param  array  $route
     * @return bool
     */
    private function routeIsMessenger(array $route): bool
    {
        return Str::contains($route['name'], ['api.messenger', 'assets.messenger']);
    }

    /**
     * @return array|null
     */
    private function getResponsesArray(): ?array
    {
        $file = storage_path('app/messenger-docs/messenger-responses.json');

        if (! file_exists($file)) {
            throw new NotFoundHttpException("The messenger-responses.json file was not found. Please run the command 'php artisan download:docs' to download it.");
        }

        return json_decode(file_get_contents($file), true);
    }

    /**
     * @return array|null
     */
    private function getBroadcastArray(): ?array
    {
        $file = storage_path('app/messenger-docs/messenger-broadcast.json');

        if (! file_exists($file)) {
            throw new NotFoundHttpException("The messenger-broadcast.json file was not found. Please run the command 'php artisan download:docs' to download it.");
        }

        return json_decode(file_get_contents($file), true);
    }
}
