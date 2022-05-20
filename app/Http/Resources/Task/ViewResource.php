<?php

namespace App\Http\Resources\Task;

use App\Models\Task;
use Auth;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ViewResource extends JsonResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        $user = Auth::guard('sanctum')->user();
        /* @var Task $this */
        return [
            'id' => $this->id,
            'user' => $this->user_info,
            'category' => $this->category->getFullPathName(),
            'name' => $this->name,
            'description' => $this->description,
            'result' => $this->result,
            'languages' => $this->getLanguagesAsString(),
            'accepted_offers' => [],
            'address' => $this->address,
            'place_id' => $this->place_id,
            'images' => $this->images_links,
            'video' => $this->video_link,
            'start_date' => $this->start_date,
            'start_time' => $this->start_time,
            'amount_of_workers' => $this->amount_of_workers,
            'minimum_age' => $this->minimum_age,
            'price' => $this->price,
            'payment_type' => $this->payment_type,
            'safe_deal' => $this->safe_deal,
            'hot_work' => $this->hot_work,
            'account_verified' => $this->account_verified,
            'status' => $this->status_label,
            'created_at' => date('Y-m-d H:i:s', strtotime($this->created_at)),
            'views_number' => $this->views_number,
            $this->mergeWhen($user, [
                "favorite" => $user->checkFavorite($this->id)
            ])
        ];
    }
}
