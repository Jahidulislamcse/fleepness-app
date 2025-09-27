<?php

namespace Illuminate\Contracts\Foundation {
    interface Application
    {
        public function isProduction(): bool;

        public function isLocal(): bool;
    }
}

namespace Illuminate\Support\Facades {

    use Illuminate\Http\Client\Factory;

    /**
     * @mixin Factory
     */
    class Http {}
}
