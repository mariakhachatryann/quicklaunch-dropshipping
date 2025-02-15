<?php

namespace frontend\controllers;

use backend\models\PostSearch;
use common\models\Post;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * PlanController implements the CRUD actions for Plan model.
 */
class PostController extends UserController
{

    /**
     * Lists all Post models.
     * @return mixed
     */
    public function actionIndex()
    {
        $keyword = Yii::$app->request->get('keyword');
        $postsQuery =   Post::find();
        if ($keyword) {
            $postsQuery
                ->where(['like', 'title', $keyword])
                ->orWhere(['like','content', $keyword]);
        }
        $posts = $postsQuery->all();

        return $this->render('index', compact('posts'));
    }
    /**
     * Lists all Plan models.
     * @return mixed
     */
    public function actionSearch()
    {
        $result = [];
        $keyword = \Yii::$app->request->post('keyword');
        $posts = Post::find()
            ->where(['like', 'title', $keyword])
            ->orWhere(['like','content', $keyword])
            ->select(['title', 'id'])
            ->indexBy('id')
            ->column();

        foreach ($posts as $id => $title) {
            $result[] = ["value"=> $id, "label"=> $title];
        }

        return  json_encode($result);
    }


}
