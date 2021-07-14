<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiExplorerController extends Controller
{
    /**
     * @param Router $router
     * @return JsonResponse
     */
    public function getRoutes(Router $router): JsonResponse
    {
        $routes = collect($router->getRoutes())
            ->map(fn ($route) => $this->getRouteInformation($route))
            ->filter(fn ($route) => $this->routeIsMessenger($route))
            ->sortBy('name')
            ->values()
            ->toArray();

        return new JsonResponse([
            'html' => view('explorer.routes')->with('routes', $routes)->render(),
        ]);
    }

    /**
     * @param string $route
     * @return JsonResponse
     */
    public function getRouteResponses(string $route): JsonResponse
    {
        $file = storage_path('messenger-responses.json');

        if (! file_exists($file)) {
            throw new NotFoundHttpException("The messenger-responses.json file was not found. Please run the command 'php artisan messenger:get:api' to download it.");
        }

        $responses = json_decode(file_get_contents($file), true);

        if (! array_key_exists($route, $responses)) {
            throw new NotFoundHttpException("The route name { $route } was not found.");
        }

        $details = view('explorer.route-details')
            ->with('uri', $responses[$route]['uri'])
            ->with('methods', $responses[$route]['methods'])
            ->with('query', $responses[$route]['query'])
            ->with('name', $route)
            ->render();

        $verb = explode('|', $responses[$route]['methods'])[0];

        $data = view('explorer.route-responses')
            ->with('data', $responses[$route][$verb])
            ->with('verb', $verb)
            ->render();

        return new JsonResponse([
            'details' => $details,
            'data' => $data,
        ]);
    }

    /**
     * @param Route $route
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
     * @param array $route
     * @return bool
     */
    private function routeIsMessenger(array $route): bool
    {
        return Str::contains($route['name'], 'api.messenger');
    }
}
