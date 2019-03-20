<?php

class PlusMinus extends UIElementArray {
    
    var $values = array();
    
    function FieldSet($id){
        parent::UIElementArray($id);
    }
    
    function render(){
        $hiddenHTML = "<div class='{$this->id}_contents_template'>
                            <div class='{$this->id}_contents'>";
        foreach($this->elements as $element){
            $hiddenHTML .= $element->render();
        }
        $hiddenHTML .= "</div></div>";
        $html = $hiddenHTML;
        $html .= "<div id='{$this->id}'>
            <div class='plusminus_contents'></div>
            <button type='button' id='{$this->id}add' style='width: 30px;padding-left: 10px;padding-right: 10px;'>+</button>&nbsp;
            <button type='button' id='{$this->id}minus' style='width: 30px;padding-left: 10px;padding-right: 10px;'>-</button>
        </div>";
        $html .= "<script type='text/javascript'>
            _.defer(function(){
                var values = ".json_encode($this->values).";
                var contents = $('.{$this->id}_contents_template').detach();
                $('#{$this->id} .plusminus_contents').append(contents.html());
                $('#{$this->id}add').click(function(){
                    $('#{$this->id} .plusminus_contents').append(contents.html());
                    if($('#{$this->id} .plusminus_contents').children().length > 0){
                        $('#{$this->id}minus').prop('disabled', false);
                    }
                    return false;
                });
                $('#{$this->id}minus').click(function(){
                    $('#{$this->id} .plusminus_contents .{$this->id}_contents').last().remove();
                    if($('#{$this->id} .plusminus_contents').children().length == 0){
                        $('#{$this->id}minus').attr('disabled', 'disabled');
                    }
                    return false;
                });
                for(var i = 0; i < values.length; i++){
                    var value = values[i];
                    if(i > 0){
                        $('#{$this->id}add').click();
                    }
                    for(key in value){
                        var val = value[key];
                        $('[name=\"' + key + '[]\"]').last().val(val);
                    }
                }
            });
        </script>";
        
        
        return $html;
    }
}

?>
