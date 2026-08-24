<?php

return [

    /*
    |--------------------------------------------------------------------
    | Featured placement scarcity
    |--------------------------------------------------------------------
    |
    | Architecture §16 open decision #3: cap slots per category/district so
    | featured placement stays scarce and stays credible, without giving a
    | specific number. One active placement per exact
    | (service_category_id, district_id) tuple — including the null/null
    | "everywhere" tuple as its own slot — is a deliberately conservative
    | launch choice, not a technical ceiling. Raise it once there's real
    | demand data to size against.
    |
    */

    'featured_slots_per_tuple' => 1,

];
