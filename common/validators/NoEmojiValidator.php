<?php


namespace common\validators;


use yii\validators\Validator;

class NoEmojiValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $filteredValue = $this->filterEmojis($value);
        
        if ($value !== $filteredValue) {
            $model->$attribute = $filteredValue;
        }
    }
    
    private function filterEmojis($text)
    {
        // Remove emojis from the text using a regular expression
        $string = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{1FAB0}-\x{1FABF}\x{1FAC0}-\x{1FAFF}\x{1FAD0}-\x{1FAFF}\x{1FBC0}-\x{1FBEF}\x{1FBF0}-\x{1FBFF}\x{1FCC0}-\x{1FCFF}\x{1FDC0}-\x{1FDFF}\x{1FEC0}-\x{1FEFF}\x{1FFC0}-\x{1FFFF}\x{2300}-\x{23FF}\x{2B50}\x{1F004}-\x{1F0CF}\x{2B05}\x{2934}\x{25AA}\x{25FE}\x{2B05}\x{1F004}-\x{1F0CF}\x{25AA}\x{25FE}]/u', '', $text);
        return $this->remove_emoji($string);
    }
    
    private function remove_emoji($string)
    {
        // Match Enclosed Alphanumeric Supplement
        $regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
        $clear_string = preg_replace($regex_alphanumeric, '', $string);
        
        // Match Miscellaneous Symbols and Pictographs
        $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clear_string = preg_replace($regex_symbols, '', $clear_string);
        
        // Match Emoticons
        $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clear_string = preg_replace($regex_emoticons, '', $clear_string);
        
        // Match Transport And Map Symbols
        $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clear_string = preg_replace($regex_transport, '', $clear_string);
        
        // Match Supplemental Symbols and Pictographs
        $regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
        $clear_string = preg_replace($regex_supplemental, '', $clear_string);
        
        // Match Miscellaneous Symbols
        $regex_misc = '/[\x{2600}-\x{26FF}]/u';
        $clear_string = preg_replace($regex_misc, '', $clear_string);
        
        // Match Dingbats
        $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
        return preg_replace($regex_dingbats, '', $clear_string);
    }
}