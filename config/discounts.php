<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Standard Preset Discount Amounts (₦)
    |--------------------------------------------------------------------------
    |
    | Fixed naira amounts off the course fee. Discounts are Director-only -
    | no other role can initiate one, at any amount - so these are simply
    | the smaller of the two preset tiers Director can choose from.
    |
    */
    'standard_presets' => [1000, 2500, 5000],

    /*
    |--------------------------------------------------------------------------
    | Higher Preset Amounts (₦)
    |--------------------------------------------------------------------------
    |
    | Additional, larger presets on top of the standard tier above - still
    | Director-only, like every discount.
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
