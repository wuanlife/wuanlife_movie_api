<?php

namespace App\Models\Resources;

use Illuminate\Database\Eloquent\Model;

class UnreviewedResources extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'resource_id';
    protected $table = 'unreviewed_resources';
    protected $fillable = [
        'resource_id',
    ];

    public function resource()
    {
        return $this->hasOne('App\Models\Resources\Resource', 'resource_id', 'resource_id');
    }

    public static function getUnreviewedResources($limit, $page)
    {
        try {
            $resources = self::with('resource.movie')->paginate($limit, ['*'], '', $page);
            dd($resources);
            $res = [];
            foreach ($resources as $resource) {
                if (!$resource->resource) {
                    continue;
                }
                $response = Builder::requestInnerApi(
                    env('OIDC_SERVER'),
                    "/api/app/users/{$resource->resource->sharer}"
                );
                $user = json_decode($response['contents']);
                $created_at = $resource->resource->created_at;
                $time = explode(' ', $created_at);
                $created_at = $time[0] . 'T' . $time[1] . 'Z';
                $type = '【' . ResourceTypeDetails::find($resource->resource->resource_type)->type_name . '】';
                $title = $resource->resource->title . '<br>';
                $instruction = '说明：' . $resource->resource->instruction . '<br>';
                $url = '链接：<a href=' . $resource->resource->url . '>资源链接</a>；';
                $password = '密码：' . $resource->resource->password;

                $res[] = [
                    'movie_id' => $resource->resource->movie->title,
                    'resource_id' => $resource->resource->resource_id,
                    'name' => $resource->resource->movie->title,
                    'instruction' => $type . $title . $instruction . $url . $password,
                    'sharer' => $user->name,
                    'created_at' => $created_at,
                ];
            }

        } catch (\Exception $e) {
        }
    }
}
