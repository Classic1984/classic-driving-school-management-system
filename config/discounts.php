<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Preset Discount Amounts (₦)
    |--------------------------------------------------------------------------
    |
    | Fixed naira amounts off the course fee. Every preset a Secretary is
    | allowed to apply unassisted - ₦5,000 is the routine ceiling. Director
    | can also approve any of these, plus a custom percentage or fixed
    | amount.
    |
    */
    'secretary_presets' => [1000, 2500, 5000],

    /*
    |--------------------------------------------------------------------------
    | Director-Only Presets (₦)
    |--------------------------------------------------------------------------
    |
    | Additional presets only Director can approve, on top of the Secretary
    | presets above.
    */
    'director_presets' => [10000],

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
