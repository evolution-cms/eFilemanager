<?php

namespace Illuminate\Foundation\Bus {
    if (!trait_exists(DispatchesJobs::class)) {
        trait DispatchesJobs
        {
            public function dispatch($job)
            {
                if (function_exists('app') && interface_exists(\Illuminate\Contracts\Bus\Dispatcher::class)) {
                    return app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
                }

                throw new \RuntimeException('Bus dispatcher is not available.');
            }

            public function dispatchSync($job)
            {
                if (function_exists('app') && interface_exists(\Illuminate\Contracts\Bus\Dispatcher::class)) {
                    return app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatchSync($job);
                }

                return $this->dispatch($job);
            }

            public function dispatchNow($job, $handler = null)
            {
                if (function_exists('app') && interface_exists(\Illuminate\Contracts\Bus\Dispatcher::class)) {
                    return app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatchNow($job, $handler);
                }

                return $this->dispatch($job);
            }
        }
    }
}

namespace Illuminate\Foundation\Validation {
    if (!trait_exists(ValidatesRequests::class)) {
        trait ValidatesRequests
        {
            public function validate(\Illuminate\Http\Request $request, array $rules, array $messages = [], array $attributes = [])
            {
                $validator = validator($request->all(), $rules, $messages, $attributes);
                return $validator->validate();
            }

            public function validateWithBag(string $errorBag, \Illuminate\Http\Request $request, array $rules, array $messages = [], array $attributes = [])
            {
                $validator = validator($request->all(), $rules, $messages, $attributes);
                $validator->setErrorBag($errorBag);
                return $validator->validate();
            }
        }
    }
}

namespace {
    if (!function_exists('event')) {
        function event(...$args)
        {
            if (function_exists('app')) {
                $dispatcher = app('events');
                if ($dispatcher && method_exists($dispatcher, 'dispatch')) {
                    return $dispatcher->dispatch(...$args);
                }
            }

            if (class_exists(\Illuminate\Support\Facades\Event::class)) {
                return \Illuminate\Support\Facades\Event::dispatch(...$args);
            }

            return $args[0] ?? null;
        }
    }
}
