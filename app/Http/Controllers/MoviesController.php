<?php

namespace App\Http\Controllers;

use App\Models\Human\{
    Actors, Directors
};
use App\Models\Movies\{
    MoviesActors, MoviesBase, MoviesDetails, MoviesDirectors, MoviesGenres, MoviesGenresDetails, MoviesPoster, MoviesRating, MoviesSummary, MoviesType
};
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MoviesController extends Controller
{
    /**
     * 首页/影片分类页接口
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function home(Request $request)
    {
        //主页 limit offset type三个参数
        $limit = $request->input('limit') ?? 20;
        $offset = $request->input('offset') ?? 0;
        $where = [];
        if (!empty($request->input('type'))) {
            //type 参数存在
            $type = $request->input('type');
            $where['movies_type.type_id'] = $type;
        }

        try {
            $movies = DB::table('movies_base')
                        ->join('movies_poster', 'movies_poster.id', 'movies_base.id')
                        ->join('movies_rating', 'movies_rating.id', 'movies_base.id')
                        ->join('movies_type', 'movies_type.movies_id', 'movies_base.id')
                        ->join('movies_type_details', 'movies_type_details.type_id', 'movies_type.type_id')
                        ->leftJoin(DB::raw('(SELECT movies_id, max(created_at) AS new_resources_created_at FROM resources GROUP BY movies_id ) resources'),
                            'resources.movies_id', 'movies_base.id')
                        ->where($where)
                        ->orderBy('new_resources_created_at', 'desc')
                        ->select('movies_base.id', 'movies_base.title', 'movies_base.digest',
                            'movies_poster.url as poster',
                            'movies_rating.rating', 'type_name')
                        ->paginate($limit, ['*'], '', $offset);
            $res = [];
            foreach ($movies as $movie) {
                $res[] = [
                    'id'        => $movie['id'],
                    'title'     => $movie['title'],
                    'digest'    => $movie['digest'],
                    'poster'    => $movie['poster'],
                    'rating'    => $movie['rating'],
                    'type_name' => $movie['type_name'],
                ];
            }
            $base['movies'] = $res;
            $base['total'] = $movies->total();
            return response($base, 200);
        } catch (\Exception $e) {
            Log::error(
                "Failed to get movies list:{$e->getMessage()}.In " . __METHOD__ . " on line {$e->getLine()}",
                [
                    'offset' => $offset,
                    'limit'  => $limit,
                    'type'   => $type ?? 'null',
                ]);
            return response('未知错误', 400);
        }
    }

    /**
     * M1获取影片详情接口
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function moviesDetails($id)
    {
        try {
            // 检测该影片是否存在于数据库中
            if (!MoviesBase::where('id', $id)->first()) {
                return response(['error' => '影片信息不存在，请先添加'], 400);
            };

            // 获取影片详细信息影片信息
            $movie = MoviesDetails::find($id);

            // 获取影片(详情页)类别信息
            $res = MoviesGenres::where('movies_id', $id)->get();
            foreach ($res as $genre) {
                $genres[] = ['id' => $genre->genres_id, 'name' => $genre->detail->genres_name];
            }
            // 获取影片导演信息
            $res = MoviesDirectors::where('movie_id', $id)->get();
            foreach ($res as $director) {
                $directors[] = ['id' => $director->director_id, 'name' => $director->director->name ?? ''];
            }

            //获取影片演员信息
            $res = MoviesActors::where('movie_id', $id)->get();
            foreach ($res as $actor) {
                $actors[] = ['id' => $actor->actor_id, 'name' => $actor->actor->name ?? ''];
            }

            return response([
                'id'             => $id,
                'title'          => $movie->title,
                'poster_url'     => MoviesPoster::find($id)->url,
                'original_title' => $movie->original_title,
                'countries'      => $movie->countries,
                'year'           => $movie->year,
                'genres'         => $genres ?? [],
                'aka'            => $movie->aka,
                'directors'      => $directors ?? [],
                'casts'          => $actors ?? [],
                'summary'        => $movie->summary->summary,
                'rating'         => $movie->rating->rating,
                'url_douban'     => $movie->url_douban,
            ], 200);
        } catch (\Exception $e) {
            Log::error(
                "Failed to get movie info:{$e->getMessage()}.In " . __METHOD__ . " on line {$e->getLine()}",
                [
                    'movies_id' => $movie ?? 'null',
                ]);
            return \response(['error' => '获取影片信息失败'], 400);
        }
    }

    /**
     * M3 发现影视接口
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function addMovie(Request $request)
    {
        try {
            if (!$type = $request->input('type')) {
                return response(['error' => '缺少必要参数类型：type'], 422);
            }
            $url = $request->input('url');

            // 解析 url，获得豆瓣 api 地址并判断该影片是否存在
            $movie_id = $this->getMoviesIdByUrl($url);
            if ($movie_id === false) {
                return response(['error' => 'Url 错误', 400]);
            }
            if (!$this->moviesExists($movie_id)) {
                return response(['error' => '影片不存在或未找到对应的影片信息', 400]);
            }
            $douban_url = $this->getDouBanUrl($movie_id);
            $info = $this->accessApi($douban_url);
            if ($info === false) {
                return response(['error' => '影片信息不存在', 404]);
            }
            DB::beginTransaction();
            // 构造(首页)分类实例
            $movie_type = new MoviesType();
            $movie_type->movies_id = $info->id;
            $movie_type->type_id = $type;
            $movie_type->save();

            //添加(详情页)分类信息
            $genres_arr = $this->genresExists($info->genres);
            foreach ($genres_arr as $genres_info) {
                $genres = new MoviesGenres();
                $genres->movies_id = $info->id;
                $genres->genres_id = $genres_info['id'];
                $genres->save();
            }

            // 构造影片基础信息实例
            $base = new MoviesBase();
            $base->title = $info->title;
            $base->type = $type;
            $base->digest = mb_substr($info->summary, 0, 250);
            $base->id = $info->id;
            $base->created_at = date('Y-m-d H:i:s', time());
            $base->save();

            // 构造影片详细信息实例
            $detail = new MoviesDetails();
            $detail->title = $info->title;
            $detail->original_title = $info->original_title;
            $detail->countries = implode('/', $info->countries);
            $detail->year = $info->year;
            $detail->aka = implode('/', $info->aka);
            $detail->url_douban = $info->alt;
            $detail->id = $info->id;
            $detail->save();

            // 构造影片剧情简介实例
            $summary = new MoviesSummary();
            $summary->summary = $info->summary;
            $summary->id = $info->id;
            $summary->save();

            // 构造影片海报实例
            $poster = new MoviesPoster();
            $poster->url = $info->images->medium;
            $poster->id = $info->id;
            $poster->save();

            // 构造影片评分实例
            $rating = new MoviesRating();
            $rating->rating = round($info->rating->average, 2);
            $rating->id = $info->id;
            $rating->save();

            // 检测影片演员是否存在于数据库中，如果不存在则添加数据
            $this->actorsExists($info->casts);
            // 构造 影片-演员 关系
            foreach ($info->casts as $actor) {
                if (empty($actor->id)) {
                    continue;
                }
                $actors = new MoviesActors();
                $actors->movie_id = $info->id;
                $actors->actor_id = $actor->id;
                $actors->save();
            }

            // 创建影片导演信息
            $this->directorsExists($info->directors);
            // 构造 影片-导演 关系
            foreach ($info->directors as $director) {
                $directors = new MoviesDirectors();
                $directors->movie_id = $info->id;
                $directors->director_id = $director->id;
                $directors->save();
            }
            DB::commit();

            return response(['id' => $info->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(
                "Failed to add movies:{$e->getMessage()}.In " . __METHOD__ . " on line {$e->getLine()}",
                [
                    'url' => $url ?? 'null',
                    'douban_url' => $douban_url ?? 'null',
                    'movie_id' => $movie_id ?? 'null',
                ]);
            return \response(['error' => '添加影片失败'], 400);
        }
    }

    /**
     * 将原始 url 解析为豆瓣 api url
     * @param $id
     * @return mixed
     */
    private function getDouBanUrl($id)
    {
        // 组合出豆瓣 api 的地址
        return env('DOUBAN_API_BASE_URL') . '/' . $id;
    }

    /**
     * 根据 id 判断影片是否存在
     * @param $id
     * @return bool
     */
    private function moviesExists($id)
    {
        return MoviesBase::where('id', $id)->first() !== null;
    }

    /**
     * 根据豆瓣 url 获取影片 id
     * @param String $url
     * @return bool
     */
    private function getMoviesIdByUrl(String $url)
    {
        $pattern = "$.+\/subject\/([0-9]+).?$";
        preg_match($pattern, $url, $res);
        if (count($res) < 2) {
            return false;
        }
        return $res[1];
    }

    /**
     * 通过豆瓣 api 获取影片信息
     * @param $url
     * @return mixed
     * @throws \Exception
     */
    private function accessApi(String $url)
    {
        //获取豆瓣 api 返回的 Json
        if (!$info_json = file_get_contents($url)) {
            return false;
        }
        return json_decode($info_json);
    }

    /**
     * 查询 genres 是否存在，如果不存在则添加信息，并返回带 id 的 genres 数组
     * @param array $genres
     * @return array
     */
    private function genresExists(array $genres)
    {
        foreach ($genres as $genre) {
            $genres_model = new MoviesGenresDetails();
            if ($res = MoviesGenresDetails::where('genres_name', $genre)->first()) {
                $arr[] = ['id' => $res->genres_id, 'name' => $res->genres_name];
                continue;
            }
            $genres_model->genres_name = $genre;
            $genres_model->save();

            $arr[] = ['id' => $genres_model->id, 'name' => $genre];
        }
        return $arr ?? [];
    }

    /**
     * 查询演员是否存在，如果不存在则添加演员信息
     * @param array $actors
     */
    public function actorsExists(array &$actors)
    {
        foreach ($actors as &$actor) {
            if (empty($actor->id) || Actors::where('id', $actor->id)->first()) {
                continue;
            }
            $actor_model = new Actors();
            $actor_model->id = $actor->id;
            $actor_model->name = $actor->name;
            $actor_model->save();
        }
    }

    /**
     * 查询导演是否存在，如果不存在则添加导演信息
     * @param array $directors
     */
    private function directorsExists(array $directors)
    {
        foreach ($directors as $director) {
            $directors_model = new Directors();
            if (Directors::where('id', $director->id)->first()) {
                continue;
            }
            $directors_model->id = $director->id;
            $directors_model->name = $director->name;
            $directors_model->save();
        }
    }

}