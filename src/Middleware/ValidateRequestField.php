<?php

namespace Mpietrucha\RequestValidation\Middleware;

use Closure;
use ReflectionParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;
use Mpietrucha\Support\Reflector;
use Illuminate\Support\Arr;
use ReflectionClass;
use Illuminate\Support\Facades\Validator;

class ValidateRequestField
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $field = $this->validateField($request)) {
            return $next($request);
        }

        $formRequestClass = $this->toFormRequest(...$this->currentControllerAndMethod());

        if (! $formRequestClass) {
            return $next($request);
        }

        $rules = with(new $formRequestClass, fn (FormRequest $request) => $request->rules());

        Validator::make($request->all(), Arr::only($rules, $field))->stopOnFirstFailure()->validate();

        return back();
    }

    protected function validateField(Request $request): ?string
    {
        if (! config('request-validation.enabled')) {
            return null;
        }

        if (! $request->isJson()) {
            return null;
        }

        if (! invade(Route::current())->isControllerAction()) {
            return null;
        }

        $header = config('request-validation.header');

        return $request->header($header);
    }

    protected function currentControllerAndMethod(): array
    {
        return [
            Route::current()->getControllerClass(),
            invade(Route::current())->getControllerMethod()
        ];
    }

    protected function toFormRequest(string $controller, string $method): ?string
    {
        return Reflector::create($controller)
            ->arguments($method)
            ->map(fn (ReflectionParameter $argument) => $argument->getType()?->getName())
            ->filter()
            ->filter(fn (string $argument) => is_subclass_of($argument, FormRequest::class))
            ->first();
    }
}
