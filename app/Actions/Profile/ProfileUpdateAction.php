<?php

namespace App\Actions\Profile;

use App\Models\PortfolioImage;
use App\Models\PortfolioLink;
use App\Models\Profile;

class ProfileUpdateAction
{

    public function __invoke(Profile $profile, $profile_data, $languages, $categories, $image, $video, $portfolio_images, $portfolio_links): Profile
    {
        $profile->update($profile_data);

        if ($languages) {
            $profile->linkToLanguages($languages);
        }

        if ($categories) {
            $profile->updateCategories($categories);
        }

        if ($image) {
            $profile->uploadProfileImage($image);
        }

        if ($video) {
            $profile->uploadProfileVideo($video);
        }

        if ($portfolio_images) {
            $result = PortfolioImage::createModels($portfolio_images, $profile->id);
        }

        if ($portfolio_links) {
            $result = PortfolioLink::createModels($portfolio_links, $profile->id);
        }

        return $profile;
    }
}
