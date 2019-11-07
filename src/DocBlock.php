<?php

declare(strict_types=1);

namespace LenderSpender\LaravelEnums;

use Barryvdh\Reflection\DocBlock\Tag;

class DocBlock extends \Barryvdh\Reflection\DocBlock
{
    public function prependTag(Tag $tag): Tag
    {
        if (null === $tag->getDocBlock()) {
            $tag->setDocBlock($this);
        }

        if ($tag->getDocBlock() === $this) {
            array_unshift($this->tags, $tag);

            return $tag;
        }

        throw new \LogicException(
            'This tag belongs to a different DocBlock object.'
        );
    }
}
