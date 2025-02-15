<?php

namespace console\controllers;

use common\models\Lead;
use common\models\LeadMessage;
use yii\console\Controller;
use yii\helpers\HtmlPurifier;

class GenerateDatasetController extends Controller
{
    /**
     * Generate dataset for fine-tuning
     */
    public function actionIndex()
    {
        $allLeads = Lead::find()->with('leadMessages')->all();
        $dataset = [];

        $purifierConfig = [
            'HTML.Allowed' => '',
        ];

        foreach ($allLeads as $lead) {
            $conversationMessages = $lead->leadMessages;
            $conversation = [];

            $conversation[] = [
                'role' => 'system',
                'content' => "You are a helpful assistant who helps answer questions and provide guidance based on conversations."
            ];

            $conversation[] = [
                'role' => 'user',
                'content' =>  HtmlPurifier::process($lead->message, $purifierConfig)
            ];

            foreach ($conversationMessages as $msg) {
                if ($msg->sender == LeadMessage::SENDER_ADMIN) {
                    $conversation[] = [
                        'role' => 'assistant',
                        'content' =>  HtmlPurifier::process($msg->message, $purifierConfig)
                    ];
                } else {
                    $conversation[] = [
                        'role' => 'user',
                        'content' =>  HtmlPurifier::process($msg->message, $purifierConfig)
                    ];
                }
            }

            if (end($conversation)['role'] === 'user') {
                $conversation[] = [
                    'role' => 'assistant',
                    'content' => 'Let me know if you need anything else.'
                ];
            }

            $dataset[] = [
                'messages' => $conversation
            ];
        }

        $filePath = \Yii::getAlias('@runtime/fine_tune_data.jsonl');
        file_put_contents($filePath, implode("\n", array_map('json_encode', $dataset)));
    }
}
