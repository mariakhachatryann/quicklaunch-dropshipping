<?php
namespace frontend\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class CustomBreadcrumb extends Widget
{
    public $tag = 'ol';
    public $options = [];
    public $encodeLabels = true;
    public $homeLink;
    public $links = [];
    public $itemTemplate = "<li class=\"breadcrumb-item\">{link}</li>\n";
    public $activeItemTemplate = "<li class=\"breadcrumb-item active\" aria-current=\"page\">{link}</li>\n";
    public $navOptions = ['aria-label' => 'breadcrumb', 'class' => 'card card-body'];

    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, ['widget' => 'breadcrumb']);
    }

    /**
     * Renders the widget.
     * @throws InvalidConfigException
     */
    public function run()
    {

        if (empty($this->links)) {
            return '';
        }
        $links = [];
        if ($this->homeLink === null) {
            $links[] = $this->renderItem([
                'label' => Yii::t('yii', 'Home'),
                'url' => Yii::$app->homeUrl,
            ], $this->itemTemplate);
        } elseif ($this->homeLink !== false) {
            $links[] = $this->renderItem($this->homeLink, $this->itemTemplate);
        }
        foreach ($this->links as $link) {
            if (!is_array($link)) {
                $link = ['label' => $link];
            }
            $links[] = $this->renderItem($link, isset($link['url']) ? $this->itemTemplate : $this->activeItemTemplate);
        }

        return Html::tag('nav', Html::tag($this->tag, implode('<span class="px-2">/</span>', $links), $this->options), $this->navOptions);
    }

    protected function renderItem($link, $template)
    {
        $encodeLabel = ArrayHelper::remove($link, 'encode', $this->encodeLabels);
        if (array_key_exists('label', $link)) {
            $label = $encodeLabel ? Html::encode($link['label']) : $link['label'];
        } else {
            throw new InvalidConfigException('The "label" element is required for each link.');
        }
        if (isset($link['template'])) {
            $template = $link['template'];
        }
        if (isset($link['url'])) {
            $options = $link;
            unset($options['template'], $options['label'], $options['url']);
            $options['class'] = 'breadcrumb-item text-muted text-decoration-none';
            $link = Html::a($label, $link['url'], $options);
        } else {
            $link = $label;
        }

        return strtr($template, ['{link}' => $link]);
    }
}
