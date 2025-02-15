<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 13.04.2019
 * Time: 12:33
 */

namespace backend\controllers;


use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\filters\AccessControl;

class AdminController extends Controller
{
	protected $allowedRoles = [];

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
						'matchCallback' => function($rule, $action) {
							return Yii::$app->user->identity->isAdmin() ||
								($action->id != 'delete' && in_array(Yii::$app->user->identity->role_type, $this->allowedRoles));
						}
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
}