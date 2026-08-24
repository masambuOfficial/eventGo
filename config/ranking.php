<?php

return [

    /*
    |--------------------------------------------------------------------
    | Paid ranking multiplier caps
    |--------------------------------------------------------------------
    |
    | Architecture §9.6: "Cap the paid multipliers and hold the cap. Every
    | uncapped point of paid boost is borrowed against organiser trust...
    | the cap should be a config value with a written rationale so the
    | argument only has to be had once."
    |
    | plan_boost_cap is architecture's own explicit number for the
    | subscription-plan multiplier. featured_multiplier has no number given
    | in the doc — 1.5 is this build's chosen default, bounded so that a
    | well-fit unpaid provider (base score near 1.0) can still outrank a
    | poorly-fit featured one (a low base score times 1.5 is still low).
    | Featured placement should buy visibility, not guarantee the top slot.
    |
    */

    'plan_boost_cap' => 1.3,

    'featured_multiplier' => 1.5,

];
