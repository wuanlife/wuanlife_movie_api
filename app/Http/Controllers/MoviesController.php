<?php

namespace App\Http\Controllers;

use App\Models\Human\{
    Actors, Directors
};
use App\Models\Movies\{
    MoviesActors, MoviesBase, MoviesDetails, MoviesDirectors, MoviesGenres, MoviesGenresDetails, MoviesPoster, MoviesRating, MoviesSummary, MoviesType
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            //type 参数也存在
            $type = $request->input('type');
            $where['type_id'] = $type;
        }

        $movies = DB::table('movies_base')
                    ->join('movies_poster', 'movies_poster.id', 'movies_base.id')
                    ->join('movies_rating', 'movies_rating.id', 'movies_base.id')
                    ->join('movies_type', 'movies_type.movies_id', 'movies_base.id')
                    ->leftJoin(DB::raw('(SELECT movies_id, max( created_at ) AS new_resources_created_at FROM resources GROUP BY movies_id ) resources'),
                        'resources.movies_id', 'movies_base.id')
                    ->where($where)
                    ->orderBy('new_resources_created_at', 'desc')
                    ->select('movies_base.id', 'movies_base.title', 'movies_base.digest', 'movies_poster.url as poster',
                        'movies_rating.rating')
                    ->paginate($limit, ['*'], '', $offset);
        $res = [];
        foreach ($movies as $movie) {
            $res[] = [
                'id'     => $movie['id'],
                'title'  => $movie['title'],
                'digest' => $movie['digest'],
                'poster' => $movie['poster'],
                'rating' => $movie['rating']
            ];
        }

        $base['movies'] = $res;
        $base['total'] = $movies->total();

        return response($base, 200);
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
                return response(['error' => 'Movie does\'t exists'], 400);
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
            return \response(['error' => 'Failed to get movie info:' . $e->getMessage()], 400);
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
                return response(['error' => 'Missing requested param: type'], 422);
            }
            $url = $request->input('url');

            // 解析 url，获得豆瓣 api 地址并判断该影片是否存在
            $url = $this->parseUrl($url);
            $info = $this->accessApi($url);
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

            return response(["id" => $info->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            switch ($e->getCode()) {
                case 1:
                    return response([
                        'error' => 'Failed to add,resource already exists',
                        'id'    => $e->getMessage(),
                    ], 400);
                default:
                    return response([
                        'error' => 'Failed to add: ' . (string)$e->getMessage()
                    ], 400);
            }
        }
    }

    /**
     * 将原始url解析为豆瓣api—url
     * @param $url
     * @return string
     * @throws \Exception
     */
    private function parseUrl(String $url)
    {

        // 正则出影片id
        $pattern = "$.+\/subject\/([0-9]+).?$";
        preg_match($pattern, $url, $res);
        if (count($res) < 2) {
            throw new \Exception('Wrong url');
        }
        $id = $res[1];

        // 检测该影片是否存在于数据库中
        if (MoviesBase::where('id', $id)->first()) {
            throw new \Exception($id, 1);
        };

        // 组合出豆瓣 api 的地址
        $url = env('DOUBAN_API_BASE_URL') . '/' . $id;

        return $url;
    }

    /**
     * 通过豆瓣api获取影片信息
     * @param $url
     * @return mixed
     * @throws \Exception
     */
    private function accessApi(String $url)
    {
        //获取豆瓣 api 返回的 Json
        if (!$info_json = file_get_contents($url)) {
            return response(['error' => 'Movie info does not exists'], 404);
        }
        return json_decode($info_json);
    }

    /**
     * 查询genres是否存在，如果不存在则添加信息，并返回带id的genres数组
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