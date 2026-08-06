<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Preset Discount Percentages
    |--------------------------------------------------------------------------
    |
    | Every preset a Secretary is allowed to apply unassisted. Director can
    | also approve any of these, plus a custom percentage or fixed amount.
    |
    */
    'secretary_presets' => [5, 10, 15],

    /*
    |--------------------------------------------------------------------------
    | Director-Only Presets
    |--------------------------------------------------------------------------
    |
    | Additional presets only Director can approve, on top of the Secretary
    | presets above.
    */
    'director_presets' => [20, 25, 30],

    /*
    |--------------------------------------------------------------------------
    | Discount Reasons
    |--------------------------------------------------------------------------
    |
    | Shown whenever a non-zero discount is applied, so every discount has a
    | documented reason for later audit review.
    */
    'reasons' => [
        'promotional_offer' => 'Promotional Offer',
        'referral_bonus' => 'Referral Bonus',
        'staff_relative' => 'Staff Relative',
        'corporate_client' => 'Corporate Client',
        'scholarship' => 'Scholarship',
        'directors_approval' => "Director's Approval",
        'other' => 'Other',
    ],

];
