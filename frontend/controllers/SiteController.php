<?php

namespace frontend\controllers;

use backend\models\LeadMessageImage;
use common\helpers\HelpTextHelper;
use common\models\AvailableSite;
use common\models\Category;
use common\models\FaqCategory;
use common\models\Lead;
use common\models\LeadImage;
use common\models\LeadMessage;
use common\models\Plan;
use common\models\Post;
use common\models\Product;
use common\models\Subject;
use common\models\UploadForm;
use common\models\User;
use common\models\Video;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Exception;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * Site controller
 */
class SiteController extends Controller
{
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
                        'actions' => ['signup', 'login', 'user-login', 'callback', 'error', 'privacy-policy', 'test', 'index'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['login', 'user-login', 'logout', 'index', 'error',
                            'privacy-policy', 'faq', 'training-videos', 'contact', 'test',
                            'post', 'faq-category', 'tickets', 'chat', 'close-chat'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
//        if (parent::beforeAction($action)) {
//            if (Yii::$app->getRequest()->getQueryParam('return-url')) {
//                Yii::$app->session->set('returnUrl', Yii::$app->getRequest()->getQueryParam('return-url'));
//            }
            return true;
//        }
//        return false;

    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
                'layout' => 'error',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
        if (($shop = Yii::$app->request->post('shop')) || ($shop = Yii::$app->request->get('shop'))) {
            $result = User::redirectLoginUrl($shop);
            return $this->redirect($result['url']);
        }
        $buttonName = Yii::$app->request->cookies->has(User::COOKIE_APP_INSTALLED) ? 'Login' : 'Get the app';
        $this->layout = 'login';
        return $this->render('login', compact('buttonName'));
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if (($user = $model->verifyEmail()) && Yii::$app->user->login($user)) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $shop = Yii::$app->request->get('shop');
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/login', 'shop' => $shop]);
        } elseif ($shop) {
            Yii::$app->user->logout();
            return $this->redirect(['/login', 'shop' => $shop]);
        }
        $user = Yii::$app->user->identity;

        $sites = AvailableSite::find()->all();
        if (!$sites) {
            $sites = 'other sites';
        }
        /* @var $user User */

        $productsCount = $user->getProducts()->count();

        $suggestReviewTitle = null;
        $suggestReviewText = null;
        if (!$user->has_left_review &&
            $productsCount >= Product::PRODUCT_COUNT_TO_SUGGEST_REVIEW &&
            $user->review_suggest_count < User::SUGGEST_REVIEW_LIMIT &&
            (((time() - $user->review_suggested_at) / 3600 > User::SUGGEST_REVIEW_INTERVAL) || !$user->review_suggested_at)
        ) {
            $key = 'suggest_review';
            $key .= $user->plan_id == Plan::BASIC_PLAN_ID ? '_free' : '';
            $replacements = ['{name}' => explode(' ', $user->full_name)[0]];

            $suggestReviewTitle = HelpTextHelper::getHelpText($key, 'title', $replacements);
            $suggestReviewText = HelpTextHelper::getHelpText($key, 'text', $replacements);

            $user->saveSuggestReviewDetails();
        }


        return $this->render('index', compact('sites', 'productsCount', 'suggestReviewTitle', 'suggestReviewText'));
    }


    /**
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionCallback($shop, $code)
    {
        $token = User::getAccessToken($shop, $code)['accessToken'];
        $user = Yii::$app->user->loginByAccessToken($token);
        /* @var $user User */
        if (!$user) {
            throw new NotFoundHttpException();
        }
        if ($user->plan_status == Plan::PLAN_ACTIVE) {
            return $this->redirect('/');
        }
        return $this->redirect('/profile/subscribe');
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new Lead();
        $file = new UploadForm();
        $subjects = Subject::getAllSubjects();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user = Yii::$app->user->identity;
            /* @var User $user */
            $model->user_id = $user->id;
            $model->status = Lead::UNREAD_LEAD;
            $file->images = UploadedFile::getInstances($file, 'images');
            $imageNames = [];

            if ($file->images) {
                foreach ($file->images as $key => $image) {
                    $file->imageFile = $image;
                    if ($file->upload('lead_images')) {
                        if ($key == 0) {
                            $model->image = $image->name;
                        } else {
                            $imageNames[] = $image->name;
                        }
                    }
                }
            }

            if ($model->save()) {
                foreach ($imageNames as $name) {
                    $leadImage = new LeadImage();
                    $leadImage->lead_id = $model->id;
                    $leadImage->name = $name;

                    if ($leadImage->validate()) {
                        $leadImage->save();
                    }
                }

                try {
                    $planName = $user->plan->name;
                } catch (\Throwable $e) {
                    $planName = '';
                }

                $adminUrl = Url::base(true) . '/admin/lead/view?id=' . $model->id;
//                Yii::$app->telegram->sendMessage(implode(PHP_EOL, [
//                    "New ticket #{$model->id} from {$user->username}",
//                    "User id: {$user->id}",
//                    "Plan: " . $planName,
//                    "Message: {$model->message}",
//                    "Additional Data: \n{$model->additional_data}",
//                    '', '',
//                    $adminUrl
//                ]));
            }
            Yii::$app->session->setFlash('success', 'Thanks. We have got your message!');
            return true;

        } else {
            return $this->render('contact', compact('model', 'file', 'subjects'));
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionTrainingVideos()
    {

        $user = Yii::$app->user->identity;
        /* @var $user User */
        if (!$user->videos_checked) {
            $user->videos_checked = User::VIDEOS_CHECKED;
            $user->save();
        }
        $videos = Video::find()->all();
        return $this->render('training-videos', compact('videos'));
    }

    public function actionTickets()
    {
        $user = Yii::$app->user->identity;
        /* @var $user User */
        $leads = $user->getLeads()->orderBy(['id' => SORT_DESC])->all();

        return $this->render('tickets', compact('leads'));
    }

    public function actionFaqCategory($id = null, $post_id = null)
    {
        $category = FaqCategory::findOne($id);
        if ($id) {
            $posts = Post::find()->where(['faq_category_id' => $id])->orderBy(['sort' => SORT_ASC])->all();
        } else {
            $posts = Post::find()->where(['IS', 'faq_category_id', null])->orderBy(['sort' => SORT_ASC])->all();
        }


        return $this->render('category', compact('posts', 'category', 'post_id'));
    }

    public function actionPost($id)
    {
        $post = Post::findOne($id);

        return $this->render('post', compact('post'));
    }

    public function actionChat($lead_id)
    {
        /* @var User $user*/
        $user = Yii::$app->user->identity;
        $lead = $user->getLeads()->andWhere(['id' => $lead_id])->one();
        if (!$lead) {
            throw new NotFoundHttpException('Lead not found!');
        }
        $model = new LeadMessage();
        $file = new UploadForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $model->message = htmlentities($model->message);
            $model->user_id = $lead->user_id;
            $model->lead_id = $lead_id;
            $model->sender = LeadMessage::SENDER_USER;

            $file->images = UploadedFile::getInstances($file, 'images');
            $imageNames = [];

            if ($file->images) {
                foreach ($file->images as $key => $image) {
                    $file->imageFile = $image;
                    if ($file->upload('lead_message_images')) {
                        if ($key == 0) {
                            $model->image = $image->name;
                        } else {
                            $imageNames[] = $image->name;
                        }
                    }
                }

            }

            if ($model->save()) {
                $user = Yii::$app->user->identity;
                /* @var $user User */
                $user->sendEmail(Yii::$app->params['supportEmail'], "Lead #{$lead_id} new message from {$user->username}", $model->message, $user->email);
                $adminUrl = Url::base(true) . '/admin/lead/view?id=' . $lead->id;
                foreach ($imageNames as $name) {
                    $leadImage = new LeadMessageImage();
                    $leadImage->lead_message_id = $model->id;
                    $leadImage->name = $name;

                    if ($leadImage->validate()) {
                        $leadImage->save();
                    } else {
                        print_r($leadImage->getErrors());die;
                    }
                }

                $planName = $user->plan->name ?? null;
//                Yii::$app->telegram->sendMessage(implode(PHP_EOL, [
//                    "New ticket message #{$model->lead_id} from {$user->username}",
//                    "User id: {$user->id}",
//                    "Plan: {$planName}",
//                    "Message: {$model->message}",
//                    '', '',
//                    $adminUrl
//                ]));

                $lead->status = Lead::UNREAD_LEAD;
                $lead->save();
                return true;
            }

            return $this->redirect(['chat', 'lead_id' => $lead_id]);
        }

        return $this->render('chat', compact('lead', 'model', 'file'));
    }

    public function actionCloseChat($lead_id)
    {
        $lead = Lead::findOne($lead_id);
        $user = Yii::$app->user->identity;

        if ($lead && $lead->user_id == $user->id) {
            $lead->status = Lead::CLOSED;
            $lead->save();

            return $this->redirect(['/site/tickets']);
        }
        return $this->render('chat', compact('lead', 'model', 'file'));
    }

}
