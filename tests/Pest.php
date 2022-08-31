<?php

use EightAndDouble\LaravelModerator\Tests\TestScope\ModerationScopeCase;
use EightAndDouble\LaravelModerator\Tests\TestTrait\ModerationTraitCase;

uses(ModerationTraitCase::class)->in(__DIR__.'/TestTrait');
uses(ModerationScopeCase::class)->in(__DIR__.'/TestScope');
