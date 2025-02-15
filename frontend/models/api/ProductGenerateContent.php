<?php


namespace frontend\models\api;

use common\models\Lead;
use common\models\LeadMessage;
use common\models\Post;
use Orhanerday\OpenAi\OpenAi;
use Yii;
use yii\base\Model;

class ProductGenerateContent extends Model
{

    public $url;
    public $type;
    public $message;
    public $answer;
    public $leadId;
    public $messageId;
    public $previousMessages;
    public $mostRelevantFaqs;
    const TYPE_TITLE = 'title';
    const TYPE_DESCRIPTION = 'description';
    const TYPE_LEAD_REPLY = 'lead-reply';
    const TYPE_TICKET_AUTO_REPLAY = 'no-admin-answer';

    const TYPES = [
        self::TYPE_TITLE, self::TYPE_DESCRIPTION, self::TYPE_LEAD_REPLY, self::TYPE_TICKET_AUTO_REPLAY
    ];

    const GENERATE_TEMPLATE = [
        self::TYPE_TITLE => 'Generate title for this product ',
        self::TYPE_DESCRIPTION => 'Generate long description for this product ',
        self::TYPE_LEAD_REPLY => '
            Provide a helpful and contextually accurate response based on this information - 
            User query: "{message}",
            Conversation history: "{conversationHistory}"
            Change this answer text to look professional support agent answer: "{answer}" ',
        self::TYPE_TICKET_AUTO_REPLAY => '
            Provide a helpful and contextually accurate response based on this information.
            Conversation history: "{conversationHistory}"
            User query: "{message}"
        ',
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['url'], 'url'],
            [['url'], 'required', 'when' => function (self $model) {
                return $model->type != self::TYPE_LEAD_REPLY;
            }],
            ['type', 'in', 'range' => self::TYPES],
            [['answer', 'message'], 'trim'],
            [['answer', 'message'], 'string'],
            [['leadId', 'messageId'], 'integer'],
            [['leadId'], 'required', 'when' => function (self $model) {
                return $model->type == self::TYPE_LEAD_REPLY;
            }],
            //['url', 'filter', 'filter' => [$this, 'fixUrl']],
        ];
    }

    public function preparePreviousMessages()
    {
        /* @var Lead $lead*/
        $lead = Lead::findOne($this->leadId);
        $messages = $lead->leadMessages;

        $this->previousMessages = 'User: ' . $lead->message;
        $this->previousMessages .= implode("\n", array_map(function ($message) {
            return ($message->sender == LeadMessage::SENDER_ADMIN ? 'Admin: ' : 'User: ') . $message->message;
        }, $messages));
    }

    public function prepareFaqs()
    {
        $words = preg_split('/\s+/', $this->message, -1, PREG_SPLIT_NO_EMPTY);
        $mostRelevantFaqs = Post::find()->where(['like', 'title', implode(' ', $words)])->limit(3)->all();
        $faqTemplate = '';
        $faqCounter = 1;

        foreach ($mostRelevantFaqs as $faq) {
            $faqTemplate .= "FAQ {$faqCounter}\n";
            $faqTemplate .= "Q: \"{$faq->title}\"\n";
            $faqTemplate .= "A: \"{$faq->content}\"\n\n";
            $faqCounter++;
        }
        $this->mostRelevantFaqs = $faqTemplate;
    }

    public function generateReplyContent(): ?string
    {
        $template = self::GENERATE_TEMPLATE[$this->type];

        if ($this->type == self::TYPE_TICKET_AUTO_REPLAY) {
            $template = str_replace(
                ['{message}', '{mostRelevantFaqs}'],
                [$this->message, $this->mostRelevantFaqs],
                $template
            );
        } elseif ($this->type == self::TYPE_LEAD_REPLY) {
            $template = !empty($this->previousMessages)
                ? str_replace('{conversationHistory}', $this->previousMessages, $template)
                : str_replace('Conversation history: "{conversationHistory}"', '', $template);

            $template = str_replace(
                ['{message}', '{answer}'],
                [$this->message, $this->answer],
                $template
            );
        }

        $open_ai = new OpenAi(Yii::$app->params['openAiKey']);
        $complete = json_decode($open_ai->chat([
            'model' => 'ft:gpt-4o-mini-2024-07-18:personal::AdwNtxSo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $template . $this->url
                ]
            ]
        ]), true);

        $content = $complete['choices'][0]['message']['content'] ?? null;
        if ($content) {
            return str_replace('.', '.<br>', $content);
        }
        Yii::error($complete, 'GeneratedAIContentMissing');
        return null;
    }

    public function generateContent(): ?string
    {
        $template = self::GENERATE_TEMPLATE[$this->type];
        $template = str_replace(['{message}', '{answer}'], [$this->message, $this->answer], $template);
        $open_ai = new OpenAi(Yii::$app->params['openAiKey']);
        $complete = json_decode($open_ai->chat([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $template . $this->url
                ]
            ]
        ]), true);

        $content = $complete['choices'][0]['message']['content'] ?? null;
        if ($content) {
            return str_replace('.', '.<br>', $content);
        }
        Yii::error($complete, 'GeneratedAIContentMissing');
        return null;
    }



}