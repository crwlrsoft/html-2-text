<?php

arch('there are no leftover development helpers') // @phpstan-ignore-line
    ->expect(['helper_dump', 'helper_dieDump'])
    ->not
    ->toBeUsed();
