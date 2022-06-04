<?php

namespace spec\App\Services\TokenGenerator;

use PhpSpec\ObjectBehavior;

class TokenGeneratorSpec extends ObjectBehavior
{
    function it_can_generate_token_with_local_generator()
    {
        $this->generate('local')->shouldBeString();
    }
}
