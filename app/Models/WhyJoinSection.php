<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhyJoinSection extends Model
{
    protected $table = 'whyjoinsections';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $guarded = [];

    protected $attributes = [
        'Title' => 'Why Join Sinaglahi Artists?',
        'Subtitle' => 'Create. Connect. Grow. Inspire.',
        'Introduction' => 'Sinaglahi is more than a group of artists—it is a community where creativity is nurtured, talents are shared, and Filipino art and culture are celebrated.',
        'FeaturedStatement' => 'Your art has a story. Let Sinaglahi help you share it.',
        'SupportingParagraph' => 'Whether you are an emerging artist, an experienced painter, or simply someone who loves art, Sinaglahi gives you a place to create, connect, learn, and be seen.',
        'ButtonText' => 'JOIN SINAGLAHI',
        'ButtonUrl' => '/join',
    ];
}
